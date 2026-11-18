<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Infra\Memory\InMemoryEventDispatcher;

test('filters run in registration order with extra arguments', function () {
    $d = new InMemoryEventDispatcher();
    $d->addFilter('f', fn ($v, $x) => $v . $x);
    $d->addFilter('f', fn ($v) => strtoupper($v));
    expect($d->filter('f', 'a', 'b'))->toBe('AB');
});

test('dispatch records events and notifies listeners of the event class', function () {
    $d = new InMemoryEventDispatcher();
    $seen = [];
    $d->listen(ArrayObject::class, function (object $e) use (&$seen) {
        $seen[] = $e;
    });
    $d->listen(stdClass::class, function () {
        throw new RuntimeException('wrong listener');
    });
    $event = new ArrayObject();
    $d->dispatch($event);

    expect($seen)->toBe([$event])
        ->and($d->dispatched())->toBe([$event]);
});

test('dispatch records events nobody listens to', function () {
    $d = new InMemoryEventDispatcher();
    $event = new stdClass();
    $d->dispatch($event);

    expect($d->dispatched())->toBe([$event]);
});
