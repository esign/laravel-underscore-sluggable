<?php

namespace Esign\UnderscoreSluggable;

use Illuminate\Support\ServiceProvider;

class UnderscoreSluggableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([$this->configPath() => config_path('underscore-sluggable.php')], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'underscore-sluggable');

        $this->app->singleton('underscore-sluggable', function () {
            return new UnderscoreSluggable;
        });
    }

    protected function configPath(): string
    {
        return __DIR__ . '/../config/underscore-sluggable.php';
    }
}
