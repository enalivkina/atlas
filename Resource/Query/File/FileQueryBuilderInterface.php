<?php

declare(strict_types=1);

namespace Atlas\Resource\Query\File;

use Atlas\Resource\Query\QueryBuilderInterface;

interface FileQueryBuilderInterface extends QueryBuilderInterface
{
    public function getStatement(): StatementParameter;
}
