<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Domain\ValueObjects;

use SpeedySpec\WP\Seo\Domain\Exceptions\InvalidRobotsDirectiveException;

/**
 * Holds robots directives as an immutable, insertion-ordered map so unknown future directives pass through.
 *
 * @since 0.1.0
 */
class RobotsDirectives
{
    private const NAME_PATTERN = '/^[a-z][a-z0-9_-]*\z/';

    /**
     * @param array<string, true|string> $directives
     *   Directive names mapped to true for flags or a string for valued directives.
     */
    private function __construct(private readonly array $directives)
    {
    }

    /**
     * Rejects values that could inject directives or split a header when serialized.
     */
    private static function isValidValue(string $value): bool
    {
        return preg_match('/^[^,\x00-\x1F\x7F]+\z/', $value) === 1;
    }

    /**
     * Creates an empty set of directives.
     *
     * @since 0.1.0
     */
    public static function none(): self
    {
        return new self([]);
    }

    /**
     * Builds directives from a WordPress-style array.
     *
     * Invalid names, invalid string values and non-string falsy values are dropped silently, so one bad filter
     * callback cannot break the whole tag.
     *
     * @param mixed $directives
     *   Map of directive name to value; anything other than an array yields no directives.
     *
     * @since 0.1.0
     */
    public static function fromArray(mixed $directives): self
    {
        if (!is_array($directives)) {
            return self::none();
        }

        $result = [];
        foreach ($directives as $name => $value) {
            if (!is_string($name)) {
                continue;
            }

            if (is_string($value) ? !self::isValidValue($value) : !$value) {
                continue;
            }

            $name = strtolower($name);
            if (preg_match(self::NAME_PATTERN, $name) !== 1) {
                continue;
            }

            $result[$name] = is_string($value) ? $value : true;
        }

        return new self($result);
    }

    /**
     * Creates a copy with a directive set; false removes it.
     *
     * @param string $name
     *   The directive name.
     * @param bool|string $value
     *   True for a flag, a string for a valued directive, false to remove.
     *
     * @throws InvalidRobotsDirectiveException
     *   When the name is invalid, or a string value is empty or contains a comma or control character.
     *
     * @since 0.1.0
     */
    public function with(string $name, bool|string $value = true): self
    {
        $name = strtolower($name);
        if (preg_match(self::NAME_PATTERN, $name) !== 1) {
            throw new InvalidRobotsDirectiveException(sprintf('Invalid robots directive name "%s".', $name));
        }

        if ($value === false) {
            return $this->without($name);
        }

        if (is_string($value) && !self::isValidValue($value)) {
            throw new InvalidRobotsDirectiveException(
                sprintf('Invalid value for robots directive "%s".', $name)
            );
        }

        $directives = $this->directives;
        $directives[$name] = is_string($value) ? $value : true;

        return new self($directives);
    }

    /**
     * Creates a copy with a directive removed.
     *
     * @since 0.1.0
     */
    public function without(string $name): self
    {
        $directives = $this->directives;
        unset($directives[strtolower($name)]);

        return new self($directives);
    }

    /**
     * Checks whether a directive is present.
     *
     * @since 0.1.0
     */
    public function has(string $name): bool
    {
        return isset($this->directives[strtolower($name)]);
    }

    /**
     * Retrieves a directive's value.
     *
     * @return true|string|null
     *   True for a flag, the string value, or null when absent.
     *
     * @since 0.1.0
     */
    public function get(string $name): true|string|null
    {
        return $this->directives[strtolower($name)] ?? null;
    }

    /**
     * Combines two sets, letting the other set win on conflicts so later sources can override earlier ones.
     *
     * @since 0.1.0
     */
    public function merge(self $other): self
    {
        return new self(array_merge($this->directives, $other->directives));
    }

    /**
     * Checks whether no directives are set.
     *
     * @since 0.1.0
     */
    public function isEmpty(): bool
    {
        return $this->directives === [];
    }

    /**
     * Exports the directives in insertion order.
     *
     * @return array<string, true|string>
     *   Directive names mapped to true or a string value.
     *
     * @since 0.1.0
     */
    public function toArray(): array
    {
        return $this->directives;
    }

    /**
     * Serializes for a robots meta tag content attribute or X-Robots-Tag header.
     *
     * @since 0.1.0
     */
    public function toString(): string
    {
        $parts = [];
        foreach ($this->directives as $name => $value) {
            $parts[] = $value === true ? $name : $name . ':' . $value;
        }

        return implode(', ', $parts);
    }
}
