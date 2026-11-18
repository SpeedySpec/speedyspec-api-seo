<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\ContractTests;

use Closure;
use stdClass;
use Throwable;

/**
 * Registers the invariants every event dispatcher must hold, so hosts can rely on a silent default.
 *
 * @param Closure $make
 *   Builds a fresh dispatcher with no listeners for each test.
 *
 * @since 0.1.0
 */
function eventDispatcherContract(Closure $make): void
{
    describe('EventDispatcherInterface contract', function () use ($make) {
        it('returns the value unchanged when filtering without a listener', function () use ($make) {
            $value = ['a' => 1];

            expect($make()->filter('seo.unknown', $value, 'extra'))->toBe($value);
        });

        it('does not throw when dispatching without a listener', function () use ($make) {
            expect(fn () => $make()->dispatch(new stdClass()))->not->toThrow(Throwable::class);
        });
    });
}
