<?php

declare(strict_types=1);

namespace Atlas\Resource\Query\MySql;

use Atlas\Resource\Query\QueryBuilderInterface;

interface DbQueryBuilderInterface extends QueryBuilderInterface
{
    public function getStatement(): StatementParameter;
}
