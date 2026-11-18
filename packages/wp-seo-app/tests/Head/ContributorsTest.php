<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\App\Head\Contributors\CanonicalContributor;
use SpeedySpec\WP\Seo\App\Head\Contributors\DescriptionContributor;
use SpeedySpec\WP\Seo\App\Head\Contributors\RobotsContributor;
use SpeedySpec\WP\Seo\App\Head\Contributors\TitleContributor;
use SpeedySpec\WP\Seo\Domain\Contracts\CanonicalUrlResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\DescriptionResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\RobotsDirectivesResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\TitleResolverInterface;
use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\RobotsDirectives;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;

test('title contributor always sets the title', function () {
    $doc = new HeadDocument();
    (new TitleContributor(new class implements TitleResolverInterface {
        public function resolve(PageContext $context): string
        {
            return 'T';
        }
    }))->contribute($doc, post());

    expect($doc->title())->toBe('T');
});

test('description contributor sets the tag only when a description exists', function () {
    $make = fn (?string $value) => new DescriptionContributor(new class ($value) implements DescriptionResolverInterface {
        public function __construct(private readonly ?string $value)
        {
        }

        public function resolve(PageContext $context): ?string
        {
            return $this->value;
        }
    });

    $doc = new HeadDocument();
    $make('D')->contribute($doc, post());
    $empty = new HeadDocument();
    $make(null)->contribute($empty, post());

    expect($doc->meta('description')->content)->toBe('D')
        ->and($empty->meta('description'))->toBeNull();
});

test('robots contributor sets the tag only when directives exist', function () {
    $make = fn (RobotsDirectives $value) => new RobotsContributor(new class ($value) implements RobotsDirectivesResolverInterface {
        public function __construct(private readonly RobotsDirectives $value)
        {
        }

        public function resolve(PageContext $context): RobotsDirectives
        {
            return $this->value;
        }
    });

    $doc = new HeadDocument();
    $make(RobotsDirectives::none()->with('noindex'))->contribute($doc, post());
    $empty = new HeadDocument();
    $make(RobotsDirectives::none())->contribute($empty, post());

    expect($doc->meta('robots')->content)->toBe('noindex')
        ->and($empty->meta('robots'))->toBeNull();
});

test('canonical contributor sets the link only when a url exists', function () {
    $make = fn (?Url $value) => new CanonicalContributor(new class ($value) implements CanonicalUrlResolverInterface {
        public function __construct(private readonly ?Url $value)
        {
        }

        public function resolve(PageContext $context): ?Url
        {
            return $this->value;
        }
    });

    $doc = new HeadDocument();
    $make(Url::fromString('https://example.com/a/'))->contribute($doc, post());
    $empty = new HeadDocument();
    $make(null)->contribute($empty, post());

    expect($doc->link('canonical')->href)->toBe('https://example.com/a/')
        ->and($empty->link('canonical'))->toBeNull();
});
