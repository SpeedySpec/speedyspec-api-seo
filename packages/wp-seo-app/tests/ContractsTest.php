<?php

declare(strict_types=1);

use SpeedySpec\WP\Seo\App\Head\HeadContributorRegistry;
use SpeedySpec\WP\Seo\App\UseCases\ResolveCanonicalUrl;
use SpeedySpec\WP\Seo\App\UseCases\ResolveDescription;
use SpeedySpec\WP\Seo\App\UseCases\ResolveRobotsDirectives;
use SpeedySpec\WP\Seo\App\UseCases\ResolveTitle;
use SpeedySpec\WP\Seo\Infra\Memory\ArrayContentSource;
use SpeedySpec\WP\Seo\Infra\Memory\ArrayPermalinkSource;
use SpeedySpec\WP\Seo\Infra\Memory\ArraySiteInfoProvider;
use SpeedySpec\WP\Seo\Infra\Memory\InMemorySeoOverridesRepository;

use function SpeedySpec\WP\Seo\ContractTests\headContributorRegistryContract;
use function SpeedySpec\WP\Seo\ContractTests\resolverContract;

describe('ResolveCanonicalUrl', function () {
    resolverContract(fn () => new ResolveCanonicalUrl(
        new ArrayPermalinkSource(),
        new InMemorySeoOverridesRepository(),
        new ArraySiteInfoProvider('Site'),
    ));
});

describe('ResolveTitle', function () {
    resolverContract(fn () => new ResolveTitle(
        new ArrayContentSource(),
        new InMemorySeoOverridesRepository(),
        new ArraySiteInfoProvider('Site', 'Tagline'),
    ));
});

describe('ResolveDescription', function () {
    resolverContract(fn () => new ResolveDescription(new ArrayContentSource(), new InMemorySeoOverridesRepository()));
});

describe('ResolveRobotsDirectives', function () {
    resolverContract(fn () => new ResolveRobotsDirectives(new InMemorySeoOverridesRepository(), new ArraySiteInfoProvider()));
});

describe('HeadContributorRegistry', function () {
    headContributorRegistryContract(fn () => new HeadContributorRegistry());
});
