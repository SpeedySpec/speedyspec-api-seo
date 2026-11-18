<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;

test('only the Singular kind is singular', function () {
    expect((new PageContext(PageKind::Singular, 'post', 7))->isSingular())->toBeTrue()
        ->and((new PageContext(PageKind::Search))->isSingular())->toBeFalse();
});

test('page below one is normalised to one', function () {
    expect((new PageContext(PageKind::PostsIndex, page: 0))->page)->toBe(1)
        ->and((new PageContext(PageKind::PostsIndex, page: -3))->page)->toBe(1)
        ->and((new PageContext(PageKind::PostsIndex, page: 4))->page)->toBe(4);
});

test('equals compares every field', function () {
    $a = new PageContext(PageKind::Singular, 'post', 7, 2);
    expect($a->equals(new PageContext(PageKind::Singular, 'post', 7, 2)))->toBeTrue()
        ->and($a->equals(new PageContext(PageKind::Singular, 'post', 7, 3)))->toBeFalse()
        ->and($a->equals(new PageContext(PageKind::Other, 'post', 7, 2)))->toBeFalse()
        ->and($a->equals(new PageContext(PageKind::Singular, 'page', 7, 2)))->toBeFalse()
        ->and($a->equals(new PageContext(PageKind::Singular, 'post', 8, 2)))->toBeFalse()
        ->and($a->equals(new PageContext(PageKind::Singular, 'post', 7, 2, 'en_US')))->toBeFalse()
        ->and($a->equals(new PageContext(PageKind::Singular, 'post', 7, 2, null, 404)))->toBeFalse();
});

test('defaults are page one with status 200', function () {
    $c = new PageContext(PageKind::FrontPage);
    expect($c->page)->toBe(1)->and($c->status)->toBe(200)->and($c->locale)->toBeNull()
        ->and($c->objectType)->toBeNull()->and($c->objectId)->toBeNull();
});

test('page kind values are the snake_case names', function () {
    expect(PageKind::PostTypeArchive->value)->toBe('post_type_archive')
        ->and(PageKind::NotFound->value)->toBe('not_found')
        ->and(PageKind::Singular->value)->toBe('singular');
});

test('a page has a post when it carries post identity, whatever its kind', function () {
    expect((new PageContext(PageKind::Singular, 'post', 7))->hasPost())->toBeTrue()
        ->and((new PageContext(PageKind::FrontPage, 'post', 7))->hasPost())->toBeTrue()
        ->and((new PageContext(PageKind::FrontPage, 'post', 7))->isSingular())->toBeFalse()
        ->and((new PageContext(PageKind::Singular, 'post'))->hasPost())->toBeFalse()
        ->and((new PageContext(PageKind::TermArchive, 'term', 7))->hasPost())->toBeFalse()
        ->and((new PageContext(PageKind::FrontPage))->hasPost())->toBeFalse();
});
