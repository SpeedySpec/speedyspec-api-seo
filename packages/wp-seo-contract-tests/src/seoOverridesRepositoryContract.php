<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\ContractTests;

use Closure;
use SpeedySpec\WP\Seo\Domain\ValueObjects\RobotsDirectives;
use SpeedySpec\WP\Seo\Domain\ValueObjects\SeoOverrides;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;

/**
 * Registers the invariants every overrides repository must hold, so each implementation proves the same contract.
 *
 * @param Closure $make
 *   Builds a fresh, empty repository for each test.
 *
 * @since 0.1.0
 */
function seoOverridesRepositoryContract(Closure $make): void
{
    describe('SeoOverridesRepositoryInterface contract', function () use ($make) {
        it('returns empty overrides for an unknown object', function () use ($make) {
            expect($make()->find('post', 1)->isEmpty())->toBeTrue();
        });

        it('round-trips title, description, canonical and robots', function () use ($make) {
            $repository = $make();
            $repository->save('post', 7, new SeoOverrides(
                title: 'T',
                description: 'D',
                canonical: Url::fromString('https://example.com/a'),
                robots: RobotsDirectives::none()->with('noindex'),
            ));

            $found = $repository->find('post', 7);

            expect($found->title)->toBe('T')
                ->and($found->description)->toBe('D')
                ->and($found->canonical?->toString())->toBe('https://example.com/a')
                ->and($found->robots?->toString())->toBe('noindex');
        });

        it('returns empty overrides after delete', function () use ($make) {
            $repository = $make();
            $repository->save('post', 7, new SeoOverrides(title: 'T'));
            $repository->delete('post', 7);

            expect($repository->find('post', 7)->isEmpty())->toBeTrue();
        });

        it('does not collide objects of different types with the same id', function () use ($make) {
            $repository = $make();
            $repository->save('post', 1, new SeoOverrides(title: 'Post'));
            $repository->save('category', 1, new SeoOverrides(title: 'Category'));
            $repository->delete('post', 1);

            expect($repository->find('post', 1)->isEmpty())->toBeTrue()
                ->and($repository->find('category', 1)->title)->toBe('Category');
        });
    });
}
