<?php

declare(strict_types=1);

namespace Atlas\Console\Dto;

final class OptionDTO
{
    public function __construct(
        public string $name,
        public bool $hasValue = false,
        public ?string $description = null,
    ) {}
}
