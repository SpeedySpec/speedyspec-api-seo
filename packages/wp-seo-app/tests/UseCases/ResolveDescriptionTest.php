<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\App\UseCases\ResolveDescription;
use SpeedySpec\WP\Seo\Domain\ValueObjects\SeoOverrides;
use SpeedySpec\WP\Seo\Infra\Memory\ArrayContentSource;
use SpeedySpec\WP\Seo\Infra\Memory\InMemorySeoOverridesRepository;

test('description strips tags, collapses whitespace and is not truncated', function () {
    $long = str_repeat('word ', 200);
    $content = new ArrayContentSource();
    $content->set(post(), 'T', "<p>Hello\n\n  <b>world</b></p> " . $long);
    $r = new ResolveDescription($content, new InMemorySeoOverridesRepository());
    expect($r->resolve(post()))->toStartWith('Hello world word')->and(strlen($r->resolve(post())))->toBeGreaterThan(900);
});

test('whitespace-only or tag-only summary gives no description', function (string $summary) {
    $content = new ArrayContentSource();
    $content->set(post(), 'T', $summary);
    expect((new ResolveDescription($content, new InMemorySeoOverridesRepository()))->resolve(post()))->toBeNull();
})->with(['', "  \n\t ", '<p> </p>', '<img src="a.png">']);

test('description override wins over the summary', function () {
    $overrides = new InMemorySeoOverridesRepository();
    $overrides->save('post', 7, new SeoOverrides(description: 'Custom  text'));
    $content = new ArrayContentSource();
    $content->set(post(), 'T', 'Summary');
    expect((new ResolveDescription($content, $overrides))->resolve(post()))->toBe('Custom text');
});

test('no summary and no override gives no description', function () {
    expect((new ResolveDescription(new ArrayContentSource(), new InMemorySeoOverridesRepository()))->resolve(post()))->toBeNull();
});

test('description keeps text that is not valid UTF-8, collapsing its whitespace byte-wise', function () {
    $content = new ArrayContentSource();
    $content->set(post(), 'T', "<p>Caf\xe9 \n\n au  lait</p>");
    expect((new ResolveDescription($content, new InMemorySeoOverridesRepository()))->resolve(post()))
        ->toBe("Caf\xe9 au lait");
});
