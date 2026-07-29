<?php

declare(strict_types=1);

namespace Atlas\Resource\FormRequest\Contract;

interface FormRequestFactoryInterface
{
    public function create(string $formClassName, array $rules = []): FormRequestInterface;
}
