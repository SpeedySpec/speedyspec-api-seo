<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\Exceptions\InvalidRobotsDirectiveException;
use SpeedySpec\WP\Seo\Domain\ValueObjects\RobotsDirectives;

test('renders flags and valued directives in insertion order', function () {
    $d = RobotsDirectives::none()->with('noindex')->with('max-image-preview', 'large');
    expect($d->toString())->toBe('noindex, max-image-preview:large');
});

test('noarchive, nocache and nosnippet stay distinct', function () {
    $d = RobotsDirectives::none()->with('noarchive')->with('nocache');
    expect($d->has('noarchive'))->toBeTrue()->and($d->has('nocache'))->toBeTrue()->and($d->has('nosnippet'))->toBeFalse();
});

test('accepts directives it has never heard of', function () {
    expect(RobotsDirectives::none()->with('some-future-directive')->toString())->toBe('some-future-directive');
});

test('fromArray follows WordPress truthiness and drops invalid names', function () {
    $d = RobotsDirectives::fromArray(['NOINDEX' => true, 'follow' => 0, 'max-snippet' => '-1', 'bad key' => true, 5 => true]);
    expect($d->toArray())->toBe(['noindex' => true, 'max-snippet' => '-1']);
});

test('fromArray returns none for a non-array', function () {
    expect(RobotsDirectives::fromArray('noindex')->isEmpty())->toBeTrue();
});

test('with rejects an invalid name', function () {
    expect(fn () => RobotsDirectives::none()->with('bad key'))->toThrow(InvalidRobotsDirectiveException::class);
});

test('merge lets the other set win and is immutable', function () {
    $a = RobotsDirectives::none()->with('max-snippet', '50');
    $b = $a->merge(RobotsDirectives::none()->with('max-snippet', '-1'));
    expect($b->get('max-snippet'))->toBe('-1')->and($a->get('max-snippet'))->toBe('50');
});

test('with rejects string values that are empty or could inject directives', function (string $value) {
    expect(fn () => RobotsDirectives::none()->with('max-snippet', $value))
        ->toThrow(InvalidRobotsDirectiveException::class);
})->with(['', '50, noindex', "50\r\nX-Evil: 1", "5\x000"]);

test('fromArray drops invalid string values', function () {
    $d = RobotsDirectives::fromArray(['a' => '', 'b' => '1, noindex', 'c' => "1\n2", 'max-snippet' => '5']);
    expect($d->toArray())->toBe(['max-snippet' => '5']);
});

test('fromArray keeps the string 0 but drops non-string falsy values', function () {
    $d = RobotsDirectives::fromArray(['max-snippet' => '0', 'a' => false, 'b' => 0, 'c' => null]);
    expect($d->toArray())->toBe(['max-snippet' => '0'])->and($d->toString())->toBe('max-snippet:0');
});

test('a trailing newline is rejected in values and names', function () {
    expect(fn () => RobotsDirectives::none()->with('max-snippet', "50\n"))
        ->toThrow(InvalidRobotsDirectiveException::class);
    expect(fn () => RobotsDirectives::none()->with("noindex\n"))
        ->toThrow(InvalidRobotsDirectiveException::class);
});

test('fromArray drops a value or name with a trailing newline', function () {
    $d = RobotsDirectives::fromArray(["max-snippet" => "50\n", "noindex\n" => true, 'nofollow' => true]);
    expect($d->toArray())->toBe(['nofollow' => true]);
});
