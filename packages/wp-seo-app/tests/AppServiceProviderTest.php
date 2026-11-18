<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\Contracts\CanonicalUrlResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\ContentSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\DescriptionResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadContributorRegistryInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadDocumentBuilderInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\HeadRendererInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\PageContextProviderInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\PermalinkSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\RobotsDirectivesResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\TitleResolverInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;

test('every application contract resolves to an implementation', function (string $contract) {
    expect(container()->get($contract))->toBeInstanceOf($contract);
})->with([
    CanonicalUrlResolverInterface::class,
    TitleResolverInterface::class,
    DescriptionResolverInterface::class,
    RobotsDirectivesResolverInterface::class,
    HeadContributorRegistryInterface::class,
    HeadDocumentBuilderInterface::class,
    HeadRendererInterface::class,
]);

test('builds title, description, robots and canonical for a post', function () {
    $c = container();
    $c->get(PageContextProviderInterface::class)->set(post());
    $c->get(ContentSourceInterface::class)->set(post(), 'Hello', 'A summary.');
    $c->get(PermalinkSourceInterface::class)->set(post(), Url::fromString('https://example.com/hello/'));
    $doc = $c->get(HeadDocumentBuilderInterface::class)->build(post());
    expect($doc->title())->toBe('Hello')
        ->and($doc->meta('description')->content)->toBe('A summary.')
        ->and($doc->meta('robots')->content)->toBe('max-image-preview:large')
        ->and($doc->link('canonical')->href)->toBe('https://example.com/hello/');
});

test('no description tag when there is no summary', function () {
    $doc = container()->get(HeadDocumentBuilderInterface::class)->build(post());
    expect($doc->meta('description'))->toBeNull();
});

test('every build returns a fresh document, even for an equal context', function () {
    $c = container();
    $c->get(ContentSourceInterface::class)->set(post(), 'Hello', null);
    $b = $c->get(HeadDocumentBuilderInterface::class);
    $first = $b->build(post());
    $c->get(ContentSourceInterface::class)->set(post(), 'Changed', null);
    $second = $b->build(post());
    expect($second)->not->toBe($first)
        ->and($first->title())->toBe('Hello')
        ->and($second->title())->toBe('Changed');
});

test('skipped contributors do not run and the others still do', function () {
    $c = container();
    $c->get(ContentSourceInterface::class)->set(post(), 'Hello', 'A summary.');
    $c->get(PermalinkSourceInterface::class)->set(post(), Url::fromString('https://example.com/hello/'));
    $resolved = 0;
    $c->add(TitleResolverInterface::class, function () use (&$resolved) {
        return new class ($resolved) implements TitleResolverInterface {
            public function __construct(private int &$resolved)
            {
            }

            public function resolve(PageContext $context): string
            {
                $this->resolved++;

                return 'T';
            }
        };
    });
    $doc = $c->get(HeadDocumentBuilderInterface::class)->build(post(), ['title', 'robots', 'canonical', 'unknown']);
    expect($resolved)->toBe(0)
        ->and($doc->title())->toBeNull()
        ->and($doc->meta('robots'))->toBeNull()
        ->and($doc->link('canonical'))->toBeNull()
        ->and($doc->meta('description')->content)->toBe('A summary.')
        ->and($c->get(HeadContributorRegistryInterface::class)->has('title'))->toBeTrue()
        ->and($c->get(HeadDocumentBuilderInterface::class)->build(post())->title())->toBe('T');
});

test('replacing one resolver changes the built document and nothing else', function () {
    $c = container();
    $c->add(TitleResolverInterface::class, fn () => new class implements TitleResolverInterface {
        public function resolve(PageContext $context): string
        {
            return 'Replaced';
        }
    });
    $doc = $c->get(HeadDocumentBuilderInterface::class)->build(post());
    expect($doc->title())->toBe('Replaced')->and($doc->meta('robots')->content)->toBe('max-image-preview:large');
});

test('a plugin can remove a contributor', function () {
    $c = container();
    $c->get(HeadContributorRegistryInterface::class)->remove('description');
    $c->get(ContentSourceInterface::class)->set(post(), 'Hello', 'A summary.');
    expect($c->get(HeadDocumentBuilderInterface::class)->build(post())->meta('description'))->toBeNull();
});
