<?php

declare(strict_types=1);

/**
 * Stubs the WordPress functions the adapters call, backed by {@link FakeWordPress}.
 *
 * Every stub is guarded so the file can never clash with a loaded WordPress.
 */

use SpeedySpec\WP\Seo\Infra\WP\Tests\Support\FakeWordPress;

if (!function_exists('add_filter')) {
    function add_filter(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): bool
    {
        FakeWordPress::$filters[$hook_name][] = $callback;

        return true;
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters(string $hook_name, mixed $value, mixed ...$args): mixed
    {
        FakeWordPress::$appliedFilters[] = [$hook_name, $value, $args];
        foreach (FakeWordPress::$filters[$hook_name] ?? [] as $callback) {
            $value = $callback($value, ...$args);
        }

        return $value;
    }
}

if (!function_exists('do_action')) {
    function do_action(string $hook_name, mixed ...$args): void
    {
        FakeWordPress::$actions[] = [$hook_name, $args];
        foreach (FakeWordPress::$filters[$hook_name] ?? [] as $callback) {
            $callback(...$args);
        }
    }
}

if (!function_exists('get_option')) {
    function get_option(string $option, mixed $default_value = false): mixed
    {
        return FakeWordPress::$options[$option] ?? $default_value;
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo(string $show = '', string $filter = 'raw'): string
    {
        return match ($show) {
            'name' => (string) (FakeWordPress::$options['blogname'] ?? ''),
            'description' => (string) (FakeWordPress::$options['blogdescription'] ?? ''),
            default => '',
        };
    }
}

if (!function_exists('get_locale')) {
    function get_locale(): string
    {
        return FakeWordPress::$locale;
    }
}

if (!function_exists('is_ssl')) {
    function is_ssl(): bool
    {
        return FakeWordPress::$ssl;
    }
}

if (!function_exists('is_404')) {
    function is_404(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('is_search')) {
    function is_search(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('is_front_page')) {
    function is_front_page(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('is_home')) {
    function is_home(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('is_singular')) {
    function is_singular(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('is_post_type_archive')) {
    function is_post_type_archive(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('is_category')) {
    function is_category(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('is_tag')) {
    function is_tag(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('is_tax')) {
    function is_tax(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('is_author')) {
    function is_author(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('is_date')) {
    function is_date(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('is_year')) {
    function is_year(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('is_month')) {
    function is_month(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('is_day')) {
    function is_day(mixed ...$args): bool
    {
        return FakeWordPress::$conditionals[__FUNCTION__] ?? false;
    }
}

if (!function_exists('get_queried_object_id')) {
    function get_queried_object_id(): int
    {
        FakeWordPress::requireQuery(__FUNCTION__);

        return FakeWordPress::$queriedObjectId;
    }
}

if (!function_exists('get_queried_object')) {
    function get_queried_object(): ?object
    {
        FakeWordPress::requireQuery(__FUNCTION__);

        return FakeWordPress::$queriedObject;
    }
}

if (!function_exists('get_query_var')) {
    function get_query_var(string $query_var, mixed $default_value = ''): mixed
    {
        FakeWordPress::requireQuery(__FUNCTION__);

        return FakeWordPress::$queryVars[$query_var] ?? $default_value;
    }
}

if (!function_exists('get_post')) {
    /**
     * Falls back to the global post for an empty argument and accepts numeric ids, as WordPress does.
     */
    function get_post(mixed $post = null): ?object
    {
        if (empty($post)) {
            $post = $GLOBALS['post'] ?? null;
        }

        if (is_object($post)) {
            return $post;
        }

        return is_numeric($post) ? (FakeWordPress::$posts[(int) $post] ?? null) : null;
    }
}

if (!function_exists('get_post_status')) {
    function get_post_status(mixed $post = null): string|false
    {
        return get_post($post)->post_status ?? false;
    }
}

if (!function_exists('get_permalink')) {
    function get_permalink(mixed $post = 0): string|false
    {
        $id = is_object($post) ? (int) $post->ID : (int) $post;

        return FakeWordPress::$permalinks[$id] ?? false;
    }
}

if (!function_exists('add_query_arg')) {
    /**
     * Supports only the key, value and URL form used by the adapters.
     */
    function add_query_arg(string $key, mixed $value, string $url): string
    {
        return $url . (str_contains($url, '?') ? '&' : '?') . urlencode($key) . '=' . urlencode((string) $value);
    }
}

if (!function_exists('trailingslashit')) {
    function trailingslashit(string $value): string
    {
        return rtrim($value, '/\\') . '/';
    }
}

if (!function_exists('user_trailingslashit')) {
    /**
     * Follows the trailing slash of the permalink structure, as WordPress does.
     */
    function user_trailingslashit(mixed $url, string $type_of_url = ''): string
    {
        $url = rtrim((string) $url, '/\\');

        return str_ends_with((string) get_option('permalink_structure', ''), '/') ? $url . '/' : $url;
    }
}

if (!function_exists('get_comments_pagenum_link')) {
    function get_comments_pagenum_link(int $pagenum = 1, int $max_page = 0): string
    {
        $permalink = (string) get_permalink(get_queried_object_id());

        return trailingslashit($permalink) . 'comment-page-' . $pagenum . '/#comments';
    }
}

if (!function_exists('single_post_title')) {
    function single_post_title(string $prefix = '', bool $display = true): ?string
    {
        return $prefix . (FakeWordPress::$queriedObject->post_title ?? '');
    }
}

if (!function_exists('single_term_title')) {
    function single_term_title(string $prefix = '', bool $display = true): ?string
    {
        return $prefix . (FakeWordPress::$queriedObject->name ?? '');
    }
}

if (!function_exists('post_type_archive_title')) {
    function post_type_archive_title(string $prefix = '', bool $display = true): ?string
    {
        return $prefix . (FakeWordPress::$queriedObject->labels->name ?? '');
    }
}

if (!function_exists('get_search_query')) {
    function get_search_query(bool $escaped = true): string
    {
        return (string) (FakeWordPress::$queryVars['s'] ?? '');
    }
}

if (!function_exists('get_the_date')) {
    /**
     * Echoes the requested format back so tests can tell which date format was asked for.
     */
    function get_the_date(string $format = '', mixed $post = null): string
    {
        return 'date(' . $format . ')';
    }
}

if (!function_exists('__')) {
    function __(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (!function_exists('_x')) {
    function _x(string $text, string $context, string $domain = 'default'): string
    {
        return $text;
    }
}

if (!function_exists('wp_strip_all_tags')) {
    /**
     * Drops script and style elements with their contents before stripping tags, as WordPress does.
     */
    function wp_strip_all_tags(string $text, bool $remove_breaks = false): string
    {
        $text = (string) preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $text);
        $text = strip_tags($text);
        if ($remove_breaks) {
            $text = (string) preg_replace('/[\r\n\t ]+/', ' ', $text);
        }

        return trim($text);
    }
}

if (!function_exists('strip_shortcodes')) {
    /**
     * Removes enclosing and self-closing shortcodes whole, as WordPress does for registered shortcodes.
     */
    function strip_shortcodes(string $content): string
    {
        $content = (string) preg_replace('/\[([a-zA-Z][\w-]*)\b[^\]]*\].*?\[\/\1\]/s', '', $content);

        return (string) preg_replace('/\[\/?[a-zA-Z][\w-]*\b[^\]]*\]/', '', $content);
    }
}

if (!function_exists('wp_trim_words')) {
    /**
     * Cuts text to a number of words as WordPress does, without the `wp_trim_words` filter.
     */
    function wp_trim_words(string $text, int $num_words = 55, ?string $more = null): string
    {
        $more ??= '&hellip;';
        $words = preg_split('/[\n\r\t ]+/', wp_strip_all_tags($text), $num_words + 1, PREG_SPLIT_NO_EMPTY);
        if (count($words) > $num_words) {
            array_pop($words);

            return implode(' ', $words) . $more;
        }

        return implode(' ', $words);
    }
}

if (!function_exists('post_password_required')) {
    /**
     * Treats every post with a password as locked, as for a visitor who has not entered it.
     */
    function post_password_required(mixed $post = null): bool
    {
        return '' !== (string) (get_post($post)->post_password ?? '');
    }
}
