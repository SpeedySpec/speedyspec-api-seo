<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\App\Head;

use SpeedySpec\WP\Seo\Domain\Contracts\HeadRendererInterface;
use SpeedySpec\WP\Seo\Domain\Entities\HeadDocument;
use SpeedySpec\WP\Seo\Domain\ValueObjects\LinkTag;
use SpeedySpec\WP\Seo\Domain\ValueObjects\MetaTag;

/**
 * Renders head elements as HTML, escaping every value exactly once so stored entities are not corrupted.
 *
 * @since 0.1.0
 */
class HtmlHeadRenderer implements HeadRendererInterface
{
    /**
     * Renders the title element.
     *
     * @since 0.1.0
     */
    public function renderTitle(string $title): string
    {
        return '<title>' . $this->escape($title) . "</title>\n";
    }

    /**
     * Renders one meta element.
     *
     * @since 0.1.0
     */
    public function renderMeta(MetaTag $tag): string
    {
        return sprintf(
            "<meta %s=\"%s\" content=\"%s\" />\n",
            $tag->attribute->value,
            $this->escape($tag->key),
            $this->escape($tag->content)
        );
    }

    /**
     * Renders one link element with its extra attributes after href.
     *
     * An attribute whose name is not a valid attribute name is left out, since no escaping can make a name safe.
     *
     * @since 0.1.0
     */
    public function renderLink(LinkTag $tag): string
    {
        $extra = '';
        foreach ($tag->attributes as $name => $value) {
            if (1 !== preg_match('/^[a-zA-Z][a-zA-Z0-9:-]*\z/', (string) $name)) {
                continue;
            }

            $extra .= sprintf(' %s="%s"', $name, $this->escape($value));
        }

        return sprintf(
            "<link rel=\"%s\" href=\"%s\"%s />\n",
            $this->escape($tag->rel),
            $this->escape($tag->href),
            $extra
        );
    }

    /**
     * Renders the title, then the metas, then the links.
     *
     * @since 0.1.0
     */
    public function render(HeadDocument $document): string
    {
        $title = $document->title();
        $output = null === $title ? '' : $this->renderTitle($title);

        foreach ($document->metas() as $meta) {
            $output .= $this->renderMeta($meta);
        }

        foreach ($document->links() as $link) {
            $output .= $this->renderLink($link);
        }

        return $output;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', false);
    }
}
