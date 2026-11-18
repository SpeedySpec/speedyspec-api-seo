<?php

declare(strict_types=1);

namespace SpeedySpec\WP\Seo\Infra\Memory;

use SpeedySpec\WP\Seo\Domain\Contracts\ResponseHeaderEmitterInterface;

/**
 * Records headers instead of sending them so tests can assert what would have been emitted.
 *
 * @since 0.1.0
 */
class RecordingResponseHeaderEmitter implements ResponseHeaderEmitterInterface
{
    /** @var list<array{string, string, bool}> */
    private array $emitted = [];

    public function emit(string $name, string $value, bool $replace = true): void
    {
        $this->emitted[] = [$name, $value, $replace];
    }

    /**
     * Lists the headers emitted so far, oldest first.
     *
     * @return list<array{string, string, bool}>
     *   Each entry is the name, the value and the replace flag.
     *
     * @since 0.1.0
     */
    public function emitted(): array
    {
        return $this->emitted;
    }
}
