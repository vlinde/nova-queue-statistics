<?php

namespace Vlinde\NovaQueueStatistics;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Vlinde\NovaQueueStatistics\Http\Middleware\Authorize;
use Vlinde\NovaQueueStatistics\Models\QueueStatistic;
use Vlinde\NovaQueueStatistics\Observers\QueueStatisticObserver;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-queue-statistics');

        $this->publishes([
            __DIR__ . '/../src/Migrations/create_queue_statistics_table.php' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_queue_statistics_table.php'),
        ], 'migrations');

        $this->publishes([
            __DIR__
            . '/../src/config/queue_statistics.php' => config_path('queue_statistics.php'),
        ], 'config');

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            //
        });

        QueueStatistic::observe(QueueStatisticObserver::class);
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/nova-queue-statistics')
                ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
