<?php

declare(strict_types=1);

namespace Sat\Auth;

final class SatToken
{
    public function __construct(
        public readonly string $value,
        public readonly \DateTimeImmutable $created,
        public readonly \DateTimeImmutable $expires,
    ) {
    }
}
