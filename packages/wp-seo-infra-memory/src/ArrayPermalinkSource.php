<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\Memory;

use SpeedySpec\WP\Seo\Domain\Contracts\PermalinkSourceInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;
use SpeedySpec\WP\Seo\Domain\ValueObjects\Url;

/**
 * Serves permalinks from a list registered by the caller so tests control every address.
 *
 * Contexts are matched with {@link PageContext::equals()}, so a lookup need not use the registered instance.
 *
 * @since 0.1.0
 */
class ArrayPermalinkSource implements PermalinkSourceInterface
{
    /** @var list<array{PageContext, Url}> */
    private array $entries = [];

    /**
     * Registers the address of a page, replacing the address of an equal context.
     *
     * @since 0.1.0
     */
    public function set(PageContext $context, Url $url): void
    {
        foreach ($this->entries as $index => [$known]) {
            if ($known->equals($context)) {
                $this->entries[$index] = [$context, $url];

                return;
            }
        }

        $this->entries[] = [$context, $url];
    }

    public function permalinkFor(PageContext $context): ?Url
    {
        foreach ($this->entries as [$known, $url]) {
            if ($known->equals($context)) {
                return $url;
            }
        }

        return null;
    }
}
