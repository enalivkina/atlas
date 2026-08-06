<?php

namespace Atlas\ConfigurationStorage;

interface ConfigurationStorageInterface
{
    public function get(string $key): mixed;
}
