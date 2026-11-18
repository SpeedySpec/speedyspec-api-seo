<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Infra\WP\Resolvers\WordPressRobotsDirectivesResolver;

use function SpeedySpec\WP\Seo\ContractTests\resolverContract;

test('robots resolver applies the wp_robots filter to an empty array', function () {
    add_filter('wp_robots', fn (array $r) => $r + ['max-image-preview' => 'large']);
    add_filter('wp_robots', fn (array $r) => $r + ['noindex' => true, 'follow' => false]);
    expect((new WordPressRobotsDirectivesResolver())->resolve(post())->toString())->toBe('max-image-preview:large, noindex');
});

test('robots resolver survives a filter returning garbage', function (mixed $garbage, string $expected) {
    add_filter('wp_robots', fn () => $garbage);
    expect((new WordPressRobotsDirectivesResolver())->resolve(post())->toString())->toBe($expected);
})->with([['noindex', ''], [null, ''], [['NOINDEX' => true, 'bad key' => true], 'noindex']]);

test('robots resolver yields no directives without a filter', function () {
    expect((new WordPressRobotsDirectivesResolver())->resolve(post())->isEmpty())->toBeTrue();
});

describe('WordPressRobotsDirectivesResolver', function () {
    resolverContract(fn () => new WordPressRobotsDirectivesResolver());
});
