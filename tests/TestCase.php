<?php

namespace Esign\UnderscoreSluggable\Tests;

use Esign\UnderscoreSluggable\UnderscoreSluggableServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [UnderscoreSluggableServiceProvider::class];
    }
} 