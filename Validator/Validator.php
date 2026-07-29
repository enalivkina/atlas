<?php

declare(strict_types=1);

namespace Atlas\Validator;

use Atlas\Container\ContainerInterface;
use Atlas\Validator\Contract\RuleValidatorInterface;
use Atlas\Validator\Contract\ValidatorInterface;
use Atlas\Validator\Exception\ValidationException;
use Atlas\Validator\Exception\ValidatorNotSupportedException;
use Atlas\Validator\Rule\BooleanValidator;
use Atlas\Validator\Rule\DateValidator;
use Atlas\Validator\Rule\EmailValidator;
use Atlas\Validator\Rule\ExistsValidator;
use Atlas\Validator\Rule\IntegerValidator;
use Atlas\Validator\Rule\RequiredValidator;
use Atlas\Validator\Rule\SafeValidator;
use Atlas\Validator\Rule\StringValidator;
use Atlas\Validator\Rule\UniqueValidator;

final class Validator implements ValidatorInterface
{
    private array $defaultConfig = [
        'int' => IntegerValidator::class,
        'integer' => IntegerValidator::class,
        'bool' => BooleanValidator::class,
        'boolean' => BooleanValidator::class,
        'string' => StringValidator::class,
        'required' => RequiredValidator::class,
        'safe' => SafeValidator::class,
        'date' => DateValidator::class,
        'email' => EmailValidator::class,
        'unique' => UniqueValidator::class,
        'exists' => ExistsValidator::class,
    ];

    private array $validators;

    public function __construct(
        private readonly ContainerInterface $container,
        array $validatorsConfig = [],
    ) {
        $this->validators = array_merge($this->defaultConfig, $validatorsConfig);
    }

    /**
     * @param mixed $value
     * @param string|array $rule
     * @return void
     * @throws ValidationException
     * @throws ValidatorNotSupportedException|\Atlas\Container\DependencyNotFoundException
     */
    public function validate(mixed $value, string|array $rule): void
    {
        $ruleName = $rule;
        $options = [];

        if (is_array($rule) === true) {
            $ruleName = $rule[0] ?? throw new ValidatorNotSupportedException('Правило валидации не указано');
            $options = array_slice($rule, 1, null, true);

            if (
                isset($options['skipOnEmpty']) === true
                && is_null($value) === true
            ) {
                return;
            }
        }

        if (isset($this->validators[$ruleName]) === false) {
            throw new ValidatorNotSupportedException("Валидатор для правила '{$ruleName}' не задан");
        }

        if (is_subclass_of($this->validators[$ruleName], RuleValidatorInterface::class) === false) {
            throw new ValidatorNotSupportedException(
                'Валидатор для правила \''
                . $ruleName . '\' должен имплементировать интерфейс '
                . RuleValidatorInterface::class,
            );
        }

        $this->container->get($this->validators[$ruleName])->validate($value, $options);
    }
}
