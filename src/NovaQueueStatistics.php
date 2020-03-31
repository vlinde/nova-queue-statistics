<?php

namespace Vlinde\NovaQueueStatistics;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Vlinde\NovaQueueStatistics\Nova\QueueStatistic as NovaModel;


class NovaQueueStatistics extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('nova-queue-statistics', __DIR__ . '/../dist/js/tool.js');
        Nova::style('nova-queue-statistics', __DIR__ . '/../dist/css/tool.css');

        Nova::resources([
            NovaModel::class,
        ]);

    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function renderNavigation()
    {
        return view('nova-queue-statistics::navigation');
    }
}
