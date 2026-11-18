<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\Exceptions\ServiceAlreadyResolvedException;
use SpeedySpec\WP\Seo\Domain\Exceptions\ServiceNotRegisteredException;
use SpeedySpec\WP\Seo\Domain\SeoServiceContainer;

afterEach(function () {
    SeoServiceContainer::resetShared();
});

test('get resolves lazily, passes the container, and caches', function () {
    $c = new SeoServiceContainer();
    $calls = 0;
    $c->add(stdClass::class, function (SeoServiceContainer $inner) use (&$calls, $c) {
        $calls++;
        expect($inner)->toBe($c);
        return new stdClass();
    });
    expect($calls)->toBe(0)->and($c->get(stdClass::class))->toBe($c->get(stdClass::class))->and($calls)->toBe(1);
});

test('a later add replaces an unresolved binding', function () {
    $c = (new SeoServiceContainer())
        ->add('x', fn () => new ArrayObject(['first']))
        ->add('x', fn () => new ArrayObject(['second']));
    expect($c->get('x')[0])->toBe('second');
});

test('add and remove throw once the contract was resolved', function () {
    $c = (new SeoServiceContainer())->add('x', fn () => new stdClass());
    $c->get('x');
    expect(fn () => $c->add('x', fn () => new stdClass()))->toThrow(ServiceAlreadyResolvedException::class)
        ->and(fn () => $c->remove('x'))->toThrow(ServiceAlreadyResolvedException::class);
});

test('extend throws once the contract was resolved', function () {
    $c = (new SeoServiceContainer())->add('x', fn () => new stdClass());
    $c->get('x');
    expect(fn () => $c->extend('x', fn ($inner) => $inner))->toThrow(ServiceAlreadyResolvedException::class);
});

test('extend decorates the existing binding lazily', function () {
    $c = (new SeoServiceContainer())->add('x', fn () => new ArrayObject(['inner']));
    $c->extend('x', fn (ArrayObject $inner, SeoServiceContainer $container) => new ArrayObject([$inner[0], 'outer']));
    expect($c->get('x')->getArrayCopy())->toBe(['inner', 'outer']);
});

test('extend stacks decorators with the first added innermost', function () {
    $c = (new SeoServiceContainer())->add('x', fn () => new ArrayObject(['base']));
    $c->extend('x', fn (ArrayObject $inner) => new ArrayObject([...$inner->getArrayCopy(), 'first']));
    $c->extend('x', fn (ArrayObject $inner) => new ArrayObject([...$inner->getArrayCopy(), 'second']));
    expect($c->get('x')->getArrayCopy())->toBe(['base', 'first', 'second']);
});

test('add after extend discards the pending decorators', function () {
    $c = (new SeoServiceContainer())->add('x', fn () => new ArrayObject(['old']));
    $c->extend('x', fn (ArrayObject $inner) => new ArrayObject([...$inner->getArrayCopy(), 'decorated']));
    $c->add('x', fn () => new ArrayObject(['new']));
    expect($c->get('x')->getArrayCopy())->toBe(['new']);
});

test('remove unbinds an unresolved contract and discards its decorators', function () {
    $c = (new SeoServiceContainer())->add('x', fn () => new stdClass());
    $c->extend('x', fn ($inner) => $inner);
    $c->remove('x');
    expect($c->has('x'))->toBeFalse();
    $c->add('x', fn () => new ArrayObject(['fresh']));
    expect($c->get('x')->getArrayCopy())->toBe(['fresh']);
});

test('extend throws when nothing is bound', function () {
    expect(fn () => (new SeoServiceContainer())->extend('x', fn ($inner) => $inner))
        ->toThrow(ServiceNotRegisteredException::class);
});

test('get throws for an unbound contract and has reports it', function () {
    $c = new SeoServiceContainer();
    expect($c->has('x'))->toBeFalse()->and(fn () => $c->get('x'))->toThrow(ServiceNotRegisteredException::class);
});

test('has reports a bound contract', function () {
    $c = (new SeoServiceContainer())->add('x', fn () => new stdClass());
    expect($c->has('x'))->toBeTrue();
});

test('shared returns one instance until reset', function () {
    $a = SeoServiceContainer::shared();
    expect(SeoServiceContainer::shared())->toBe($a);
    SeoServiceContainer::resetShared();
    expect(SeoServiceContainer::shared())->not->toBe($a);
});
