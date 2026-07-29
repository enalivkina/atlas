<?php

namespace Atlas\Resource\Query;

enum Operator: string
{
    case EQ = '$eq';
    case NE = '$ne';
    case GT = '$gt';
    case GTE = '$gte';
    case LT = '$lt';
    case LTE = '$lte';
    case IN = '$in';
    case NIN = '$nin';
    case LIKE = '$like';
}
