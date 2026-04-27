<?php

namespace Esign\UnderscoreSluggable\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Sluggable\SluggableServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            SluggableServiceProvider::class,
        ];
    }

    protected function setUpDatabase(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title_en');
            $table->string('title_nl');
            $table->string('slug_en')->unique();
            $table->string('slug_nl')->unique();
            $table->string('country_nl')->nullable();
            $table->string('country_en')->nullable();
            $table->string('year')->nullable();
        });
    }
} 