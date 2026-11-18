<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\ValueObjects;

use SpeedySpec\WP\Seo\Domain\Exceptions\InvalidUrlException;

/**
 * Represents an absolute http or https URL so SEO output never carries relative or unsafe links.
 *
 * @since 0.1.0
 */
class Url
{
    private function __construct(private readonly string $value)
    {
    }

    /**
     * Creates a URL from a string, rejecting anything that is not absolute http or https.
     *
     * @param string $value
     *   The candidate URL.
     *
     * @since 0.1.0
     */
    public static function fromString(string $value): self
    {
        $parts = parse_url($value);
        if (
            !is_array($parts)
            || !isset($parts['scheme'], $parts['host'])
            || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
            || $parts['host'] === ''
        ) {
            throw new InvalidUrlException('Expected an absolute http or https URL.');
        }

        return new self($value);
    }

    /**
     * Creates a URL without throwing, for callers handling untrusted or optional input.
     *
     * @param mixed $value
     *   The candidate URL.
     * @return self|null
     *   The URL, or null when the value is not a valid string URL.
     *
     * @since 0.1.0
     */
    public static function tryFromString(mixed $value): ?self
    {
        if (!is_string($value)) {
            return null;
        }

        try {
            return self::fromString($value);
        } catch (InvalidUrlException) {
            return null;
        }
    }

    /**
     * Serializes the URL as originally given.
     *
     * @since 0.1.0
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Exposes the lower-cased scheme.
     *
     * @since 0.1.0
     */
    public function scheme(): string
    {
        return strtolower((string) parse_url($this->value, PHP_URL_SCHEME));
    }

    /**
     * Creates a copy using another scheme, for example to upgrade to https.
     *
     * @param string $scheme
     *   The new scheme, either http or https.
     *
     * @throws InvalidUrlException
     *   When the scheme is not http or https.
     *
     * @since 0.1.0
     */
    public function withScheme(string $scheme): self
    {
        if (!in_array(strtolower($scheme), ['http', 'https'], true)) {
            throw new InvalidUrlException('Expected the http or https scheme.');
        }

        $position = strpos($this->value, '://');
        if ($position === false) {
            throw new InvalidUrlException('Expected an absolute http or https URL.');
        }

        return self::fromString($scheme . substr($this->value, $position));
    }

    /**
     * Compares two URLs by their serialized form.
     *
     * @since 0.1.0
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
