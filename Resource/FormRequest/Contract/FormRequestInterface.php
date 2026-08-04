<?php

declare(strict_types=1);

namespace Atlas\Resource\FormRequest\Contract;

interface FormRequestInterface
{
    public function rules(): array;

    public function addRule(array $attributes, array|string $rule): void;

    public function validate(): void;

    public function addError(string $attribute, string $message): void;

    public function getErrors(): array;
    public function hasErrors(): bool;

    public function setSkipEmptyValues(): void;

    public function getValues(): array;

    public function getFields(): array;
    public function clearErrors(): void;
    public function clearRules(): void;
    public function has(string $name): bool;
    public function get(string $name, mixed $default = null): mixed;
}
