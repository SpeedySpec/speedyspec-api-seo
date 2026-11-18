<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\Exceptions\InvalidUrlException;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;

test('accepts absolute http and https urls', function () {
    expect(Url::fromString('https://example.com/a?b=1')->toString())->toBe('https://example.com/a?b=1');
});

test('rejects relative, scheme-less and non-http urls', function (string $value) {
    expect(fn () => Url::fromString($value))->toThrow(InvalidUrlException::class);
})->with(['/path', 'example.com/a', '//example.com/a', 'ftp://example.com', 'javascript:alert(1)', '']);

test('tryFromString returns null for garbage', function (mixed $value) {
    expect(Url::tryFromString($value))->toBeNull();
})->with([false, null, '', '/relative', 42]);

test('withScheme swaps only the scheme', function () {
    expect(Url::fromString('http://example.com/a')->withScheme('https')->toString())->toBe('https://example.com/a');
});

test('withScheme rejects schemes other than http and https', function (string $scheme) {
    expect(fn () => Url::fromString('http://example.com/a')->withScheme($scheme))
        ->toThrow(InvalidUrlException::class);
})->with(['ftp', 'https://evil:/']);
