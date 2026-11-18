<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;
use SpeedySpec\WP\Seo\Infra\Memory\InMemoryEventDispatcher;
use SpeedySpec\WP\Seo\Infra\Memory\InMemorySeoOverridesRepository;

use function SpeedySpec\WP\Seo\ContractTests\eventDispatcherContract;
use function SpeedySpec\WP\Seo\ContractTests\seoOverridesRepositoryContract;
use function SpeedySpec\WP\Seo\ContractTests\resolverContract;

seoOverridesRepositoryContract(fn () => new InMemorySeoOverridesRepository());
eventDispatcherContract(fn () => new InMemoryEventDispatcher());

// Exercises the resolver contract itself, since no resolver exists in this package.
resolverContract(fn () => new class
{
    public function resolve(PageContext $context): Url
    {
        return Url::fromString('https://example.com/' . $context->kind->value);
    }
});
