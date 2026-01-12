<?php

namespace Qmrp\CustomField;

use Illuminate\Support\ServiceProvider;
use Qmrp\CustomField\Config\FieldConfigManager;
use Qmrp\CustomField\View\Components\CustomFieldTable;
use Qmrp\CustomField\View\Components\CustomFieldValue;
use Qmrp\CustomField\View\Components\CustomFieldForm;

class CustomFieldServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('customFieldService', function ($app) {
            return new Services\CustomFieldService();
        });

        $this->app->singleton(FieldConfigManager::class, function ($app) {
            return new FieldConfigManager();
        });
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'customfield');

        $this->loadViewComponentsAs('customfield', [
            'table' => CustomFieldTable::class,
            'value' => CustomFieldValue::class,
            'form' => CustomFieldForm::class,
        ]);

        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');

        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/customfield'),
        ], 'customfield-views');

        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], 'customfield-migrations');

        $this->publishes([
            __DIR__ . '/config/customfield.php' => config_path('customfield.php'),
        ], 'customfield-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\InstallCustomField::class,
                Commands\ExportFields::class,
                Commands\ImportFields::class,
            ]);
        }
    }
}
