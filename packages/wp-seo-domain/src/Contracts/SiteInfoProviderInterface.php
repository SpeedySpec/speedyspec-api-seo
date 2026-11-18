<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\Contracts;

/**
 * Supplies site-wide facts that feed titles, descriptions and indexing decisions.
 *
 * @since 0.1.0
 */
interface SiteInfoProviderInterface
{
    /**
     * Retrieves the site name used as the title fallback and suffix.
     *
     * @since 0.1.0
     */
    public function name(): string;

    /**
     * Retrieves the site tagline used as the description fallback on pages without their own summary.
     *
     * @since 0.1.0
     */
    public function tagline(): string;

    /**
     * Reports whether the site asks search engines to index it, so private sites can emit blocking directives.
     *
     * @since 0.1.0
     */
    public function isPublic(): bool;

    /**
     * Reports whether the site is served over HTTPS, so canonical URLs can use the matching scheme.
     *
     * @since 0.1.0
     */
    public function usesHttps(): bool;
}
