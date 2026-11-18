<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\Memory;

use SpeedySpec\WP\Seo\Domain\Contracts\ContentSourceInterface;
use SpeedySpec\WP\Seo\Domain\ValueObjects\PageContext;

/**
 * Serves page text from a list registered by the caller so tests control every title and summary.
 *
 * Contexts are matched with {@link PageContext::equals()}.
 *
 * @since 0.1.0
 */
class ArrayContentSource implements ContentSourceInterface
{
    /** @var list<array{PageContext, ?string, ?string}> */
    private array $entries = [];

    /**
     * Registers the text of a page, replacing the text of an equal context.
     *
     * @since 0.1.0
     */
    public function set(PageContext $context, ?string $title, ?string $summary): void
    {
        foreach ($this->entries as $index => [$known]) {
            if ($known->equals($context)) {
                $this->entries[$index] = [$context, $title, $summary];

                return;
            }
        }

        $this->entries[] = [$context, $title, $summary];
    }

    public function titleFor(PageContext $context): ?string
    {
        return $this->find($context)[1] ?? null;
    }

    public function summaryFor(PageContext $context): ?string
    {
        return $this->find($context)[2] ?? null;
    }

    /**
     * @return array{PageContext, ?string, ?string}|null
     */
    private function find(PageContext $context): ?array
    {
        foreach ($this->entries as $entry) {
            if ($entry[0]->equals($context)) {
                return $entry;
            }
        }

        return null;
    }
}
