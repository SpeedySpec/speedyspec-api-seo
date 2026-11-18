<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\ValueObjects\RobotsDirectives;
use SpeedySpec\WP\Seo\Domain\ValueObjects\SeoOverrides;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;

test('none is empty', function () {
    expect(SeoOverrides::none()->isEmpty())->toBeTrue();
});

test('any field makes it non-empty', function (array $args) {
    expect((new SeoOverrides(...$args))->isEmpty())->toBeFalse();
})->with([
    'title' => [['title' => 'T']],
    'description' => [['description' => 'D']],
    'canonical' => [['canonical' => Url::fromString('https://example.com/')]],
    'robots' => [['robots' => RobotsDirectives::none()]],
    'image' => [['image' => Url::fromString('https://example.com/a.png')]],
]);
