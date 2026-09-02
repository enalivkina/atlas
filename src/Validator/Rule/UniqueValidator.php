<?php

declare(strict_types=1);

namespace Atlas\Validator\Rule;

use Atlas\Validator\Contract\RuleValidatorInterface;
use Atlas\Validator\Exception\ValidationException;

final class UniqueValidator implements RuleValidatorInterface
{
    public function __construct(
        private readonly DataBaseConnectionInterface $connection,
        private readonly QueryBuilderInterface $queryBuilder,
    ) {}

    public function validate(mixed $value, array $options = []): void
    {
        if (is_array($value) === false) {
            $value = (array) $value;
        }

        if (is_array($options['target']) === false) {
            $options['target'] = (array) $options['target'];
        }

        $this->queryBuilder
            ->reset()
            ->select('*')
            ->from($options['resource'])
            ->where(array_combine($options['target'], $value));

        if (is_null($this->connection->selectOne($this->queryBuilder)) === false) {
            $message = $options['errorMessage']
                ?? 'Значение [' . implode(', ', $value) . '] для [' . implode(', ', $options['target'])
                . '] уже существует в ' . $options['resource'];

            throw new ValidationException($message);
        }
    }
}
