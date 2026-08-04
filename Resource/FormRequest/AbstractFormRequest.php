<?php

declare(strict_types=1);

namespace Atlas\Resource\FormRequest;

use Atlas\Resource\FormRequest\Contract\FormRequestInterface;
use Atlas\Validator\Contract\ValidatorInterface;
use Atlas\Validator\Exception\ValidationException;
use InvalidArgumentException;

abstract class AbstractFormRequest implements FormRequestInterface
{
    protected bool $skipEmptyValues = false;
    private array $errors = [];
    private array $values = [];
    private array $dynamicRules = [];

    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {}

    /**
     * Возврат правил валидации формы
     *
     * @return array
     * Пример:
     * [
     *     [['name'], 'required'],
     *     [['name'], 'string'],
     * ]
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Динамическая установка правил валидации
     *
     * @param array $attributes
     * @param array|string $rule
     * @return void
     * Пример:
     * $form->addRule(['name'], 'required');
     */
    public function addRule(array $attributes, array|string $rule): void
    {
        $this->dynamicRules[] = [$attributes, $rule];
    }

    public function validate(): void
    {
        $values = $this->getValues();

        foreach ($this->getRules() as $rule) {
            if (count($rule) !== 2) {
                throw new InvalidArgumentException('Правила должны быть заданы в формате [[аттрибуты], правило]');
            }

            $this->validateByRule($values, $rule[0], $rule[1]);
        }
    }

    private function validateByRule(array $values, array $attributes, array|string $rule): void
    {
        foreach ($attributes as $attribute) {
            $value = $this->getValueToValidate($attribute, $values);

            if ($this->skipEmptyValues === true && ($value === '' || $value === null)) {
                continue;
            }

            try {
                $this->validator->validate($value, $rule);
            } catch (ValidationException $e) {
                $this->addAttributesError($attribute, $e->getMessage());
            }
        }
    }

    private function getValueToValidate(string|array $attribute, array $values): mixed
    {
        if (is_string($attribute) === true) {
            return $values[$attribute] ?? null;
        }

        $valuesList = [];

        foreach ($attribute as $attributeName) {
            $valuesList[] = $values[$attributeName] ?? null;
        }

        return $valuesList;
    }

    public function addError(string $attribute, string $message): void
    {
        $this->errors[$attribute][] = $message;
    }

    private function addAttributesError(array|string $attributes, string $rule): void
    {
        if (is_string($attributes) === true) {
            $this->addError($attributes, $rule);

            return;
        }

        foreach ($attributes as $attribute) {
            $this->addError($attribute, $rule);
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setSkipEmptyValues(): void
    {
        $this->skipEmptyValues = true;
    }

    /**
     * Возврат значений формы
     *
     * @return array
     * Пример:
     * [
     *     "id" => 1,
     *     "order_id" => 3,
     *     "name" => "Некоторое имя 1"
     * ]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function getFields(): array
    {
        return array_unique(array_filter(array_merge(...array_column($this->getRules(), 0)), 'is_string'));
    }

    protected function getRules(): array
    {
        return array_merge($this->rules(), $this->dynamicRules);
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function clearErrors(): void
    {
        $this->errors = [];
    }

    public function clearRules(): void
    {
        $this->dynamicRules = [];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->values);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->values[$name] ?? $default;
    }
}
