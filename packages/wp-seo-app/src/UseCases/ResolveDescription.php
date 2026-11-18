<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\App\UseCases;

use SpeedySpec\WP\Seo\Domain\Contracts\ContentSourceInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\DescriptionResolverInterface;
use SpeedySpec\WP\Seo\Domain\Contracts\SeoOverridesRepositoryInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Builds the meta description from an override or the content summary.
 *
 * @since 0.1.0
 */
class ResolveDescription implements DescriptionResolverInterface
{
    public function __construct(
        private readonly ContentSourceInterface $content,
        private readonly SeoOverridesRepositoryInterface $overrides,
    ) {
    }

    /**
     * Resolves a plain-text description that is never truncated.
     *
     * Markup is stripped and whitespace collapsed so the value is safe to place in an attribute.
     *
     * @since 0.1.0
     */
    public function resolve(PageContext $context): ?string
    {
        $text = null;
        if (null !== $context->objectType && null !== $context->objectId) {
            $text = $this->overrides->find($context->objectType, $context->objectId)->description;
        }

        $text ??= $this->content->summaryFor($context);
        if (null === $text) {
            return null;
        }

        $text = strip_tags($text);
        // The Unicode-aware collapse fails on invalid UTF-8; the byte-wise one still keeps the description.
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? (string) preg_replace('/\s+/', ' ', $text));

        return '' === $text ? null : $text;
    }
}
