<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\ContractTests;

use Closure;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadContributorInterface;
use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Registers the invariants every head contributor registry must hold, so ordering and replacement behave alike.
 *
 * @param Closure $make
 *   Builds a fresh, empty registry for each test.
 *
 * @since 0.1.0
 */
function headContributorRegistryContract(Closure $make): void
{
    $contributor = static fn (): HeadContributorInterface => new class implements HeadContributorInterface
    {
        public function contribute(HeadDocument $document, PageContext $context): void
        {
        }
    };

    describe('HeadContributorRegistryInterface contract', function () use ($make, $contributor) {
        it('orders by priority and then by insertion', function () use ($make, $contributor) {
            $registry = $make();
            [$a, $b, $c, $d] = [$contributor(), $contributor(), $contributor(), $contributor()];
            $registry->add('a', $a, 20);
            $registry->add('b', $b, 10);
            $registry->add('c', $c, 20);
            $registry->add('d', $d);

            $all = $registry->all();

            expect($all)->toHaveCount(4)
                ->and($all[0])->toBe($b)
                ->and($all[1])->toBe($d)
                ->and($all[2])->toBe($a)
                ->and($all[3])->toBe($c);
        });

        it('replaces a contributor added with an existing id', function () use ($make, $contributor) {
            $registry = $make();
            $first = $contributor();
            $second = $contributor();
            $registry->add('a', $first);
            $registry->add('a', $second);

            expect($registry->all())->toHaveCount(1)
                ->and($registry->all()[0])->toBe($second);
        });

        it('treats removing an unknown id as a no-op', function () use ($make, $contributor) {
            $registry = $make();
            $registry->add('a', $contributor());
            $registry->remove('missing');

            expect($registry->has('a'))->toBeTrue()
                ->and($registry->all())->toHaveCount(1);
        });

        it('reports registered ids through has', function () use ($make, $contributor) {
            $registry = $make();
            expect($registry->has('a'))->toBeFalse();

            $registry->add('a', $contributor());
            expect($registry->has('a'))->toBeTrue();

            $registry->remove('a');
            expect($registry->has('a'))->toBeFalse()
                ->and($registry->all())->toBe([]);
        });
    });
}
