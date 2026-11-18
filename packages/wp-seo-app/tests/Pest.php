<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\App\AppServiceProvider;
use SpeedySpec\WP\Seo\Domain\SeoServiceContainer;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Infra\Memory\MemoryServiceProvider;

function post(int $id = 7, int $page = 1, int $status = 200): PageContext
{
    return new PageContext(PageKind::Singular, 'post', $id, $page, null, $status);
}

function container(): SeoServiceContainer
{
    $container = new SeoServiceContainer();
    (new MemoryServiceProvider())->register($container);
    (new AppServiceProvider())->register($container);

    return $container;
}
