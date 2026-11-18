<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\ContractTests;

use Closure;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Domain\ValueObjects\RobotsDirectives;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;

/**
 * Registers the determinism invariant shared by every resolver, whatever kind of value it resolves.
 *
 * Results are compared by string form for value objects, so implementations need not return the same instance.
 *
 * @param Closure $make
 *   Builds a resolver, any object with a resolve({@link PageContext}) method, for each test.
 *
 * @since 0.1.0
 */
function resolverContract(Closure $make): void
{
    describe('Resolver contract', function () use ($make) {
        foreach (PageKind::cases() as $kind) {
            it("resolves equal contexts to equal results for {$kind->value}", function () use ($make, $kind) {
                $resolver = $make();
                $normalize = static fn (mixed $result): mixed => match (true) {
                    $result instanceof Url, $result instanceof RobotsDirectives => $result->toString(),
                    default => $result,
                };

                $first = $normalize($resolver->resolve(new PageContext($kind, 'post', 5, 2, 'en_US', 200)));
                $second = $normalize($resolver->resolve(new PageContext($kind, 'post', 5, 2, 'en_US', 200)));

                expect($second)->toBe($first);
            });
        }
    });
}
