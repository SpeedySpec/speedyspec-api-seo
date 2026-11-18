<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\Contracts\CanonicalUrlResolverInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageKind;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;
use SpeedySpec\WP\Seo\Infra\WP\Tests\Support\FakeWordPress;

require_once __DIR__ . '/Support/wordpress-functions.php';
require_once __DIR__ . '/Support/header-shims.php';

pest()->beforeEach(function () {
    FakeWordPress::reset();
})->in(__DIR__);

function post(int $id = 7, int $page = 1, int $status = 200): PageContext
{
    return new PageContext(PageKind::Singular, 'post', $id, $page, null, $status);
}

function fixedCanonical(?string $url): CanonicalUrlResolverInterface
{
    return new class ($url) implements CanonicalUrlResolverInterface
    {
        public function __construct(private readonly ?string $url)
        {
        }

        public function resolve(PageContext $context): ?Url
        {
            return Url::tryFromString($this->url);
        }
    };
}
