<?php

/**
 * ModelAvailabilityGate
 * ---------------------
 * Wraps a Symfony\AI\Platform\PlatformInterface call so that a model which is
 * temporarily unavailable (Anthropic rate limit / 429) is not hit again until
 * the cooldown period has passed. Uses any PSR-6 cache (Symfony Cache) to
 * remember the cooldown across requests/processes.
 *
 * Built against symfony/ai-platform (main, July 2026):
 *   - Symfony\AI\Platform\Exception\RateLimitExceededException
 *       -> thrown on rate limiting, has getRetryAfter(): ?int (seconds)
 *   - Symfony\AI\Platform\Exception\ModelNotFoundException
 *       -> thrown when the model name itself is invalid/unknown
 *          (this is NOT a "wait it out" case — the model doesn't exist)
 *
 * NOTE: the Anthropic bridge is marked experimental by Symfony (no BC
 * promise). If you hit an exception this class doesn't expect, dump
 * get_class($e) during testing and extend the catch block below —
 * e.g. some providers surface transport-level errors (5xx / overloaded)
 * as Symfony\Component\HttpClient\Exception\* instead of a Platform
 * exception; add a catch for TransportExceptionInterface if you see that.
 */

namespace App\Services\Utilities;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\AI\Platform\Exception\ModelNotFoundException;
use Symfony\AI\Platform\Exception\RateLimitExceededException;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class ModelAvailabilityGate
{
    private const CACHE_PREFIX = 'ai_model_cooldown_';

    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly CacheItemPoolInterface $cache,
        /** Hard ceiling so a bad Retry-After value can never block a request forever. */
        private readonly int $maxWaitSeconds = 120,
        /** Fallback wait when Anthropic doesn't send a Retry-After value at all. */
        private readonly int $defaultCooldownSeconds = 30,
    ) {
    }

    /**
     * True if the model has no active cooldown right now.
     */
    public function isAvailable(string $model): bool
    {
        return null === $this->cooldownEndsAt($model);
    }

    /**
     * Seconds remaining before the model can be used again (0 = usable now).
     */
    public function waitTime(string $model): int
    {
        $until = $this->cooldownEndsAt($model);

        return null === $until ? 0 : max(0, $until - time());
    }

    /**
     * Invoke the model. If it's cooling down from a previous rate limit and
     * the wait is within $maxWaitSeconds, this blocks (sleep) until it's
     * ready, then calls it. If the wait is longer than that, it fails fast
     * instead of blocking indefinitely — catch RateLimitExceededException
     * and retry later (e.g. from a queued job) in that case.
     *
     * @throws RateLimitExceededException if still cooling down beyond maxWaitSeconds,
     *                                     or if Anthropic rate-limits this call itself
     * @throws ModelNotFoundException     if the model name is invalid — not retryable
     */
    public function invoke(string $model, MessageBag $messages, array $options = []): ResultInterface
    {
        $wait = $this->waitTime($model);

        if ($wait > 0) {
            if ($wait > $this->maxWaitSeconds) {
                throw new RateLimitExceededException(
                    $wait,
                    \sprintf(
                        'Model "%s" is cooling down for %d more second(s), which exceeds the configured max wait of %d second(s). Try again later.',
                        $model,
                        $wait,
                        $this->maxWaitSeconds,
                    ),
                );
            }

            sleep($wait);
        }

        try {
            return $this->platform->invoke($model, $messages, $options);
        } catch (RateLimitExceededException $e) {
            $this->registerCooldown($model, $e->getRetryAfter() ?? $this->defaultCooldownSeconds);

            throw $e;
        } catch (TransportExceptionInterface $e) {
            // Covers transport-level failures (e.g. connection errors, some
            // "overloaded" 5xx responses) that don't come back as a Platform
            // RateLimitExceededException. Treat as a short, generic cooldown.
            $this->registerCooldown($model, $this->defaultCooldownSeconds);

            throw $e;
        }
        // ModelNotFoundException intentionally NOT caught here: it means the
        // model name itself is wrong, waiting will never fix that.
    }

    private function cooldownEndsAt(string $model): ?int
    {
        $item = $this->cache->getItem($this->cacheKey($model));

        return $item->isHit() ? $item->get() : null;
    }

    private function registerCooldown(string $model, int $retryAfterSeconds): void
    {
        $retryAfterSeconds = max(1, $retryAfterSeconds);
        $until = time() + $retryAfterSeconds;

        $item = $this->cache->getItem($this->cacheKey($model));
        $item->set($until);
        $item->expiresAfter($retryAfterSeconds);

        $this->cache->save($item);
    }

    private function cacheKey(string $model): string
    {
        return self::CACHE_PREFIX.preg_replace('/[^a-zA-Z0-9_.]/', '_', $model);
    }
}