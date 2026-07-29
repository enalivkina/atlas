<?php

namespace Atlas\View;

final class ViewNotFoundException extends \Exception
{
    public function __construct(string $viewName)
    {
        parent::__construct("Представление {$viewName} не найдено");
    }
}
