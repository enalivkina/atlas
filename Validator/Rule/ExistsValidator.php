<?php

declare(strict_types=1);

namespace Atlas\Validator\Rule;

use Atlas\Validator\Contract\RuleValidatorInterface;
use Atlas\Validator\Exception\ValidationException;

final class ExistsValidator implements RuleValidatorInterface
{
    public function __construct(
        private readonly DataBaseConnectionInterface $connection,
        private readonly QueryBuilderInterface $queryBuilder,
    ) {}

    public function validate(mixed $value, array $options = []): void
    {
        $this->queryBuilder
            ->reset()
            ->select('*')
            ->from($options['resource'])
            ->where([$options['target'] => $value]);

        if (is_null($this->connection->selectOne($this->queryBuilder)) === true) {
            $message = $options['errorMessage']
                ?? 'Не найдено значение [' . $value
                . '] для [' . $options['target'] . '] в ' . $options['resource'];

            throw new ValidationException($message);
        }
    }
}
