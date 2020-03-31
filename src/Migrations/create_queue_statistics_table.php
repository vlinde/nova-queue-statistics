<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueueStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('queue_statistics')) {

            Schema::create('queue_statistics', function (Blueprint $table) {
                $table->increments('id');
                $table->string('connection');
                $table->string('queue');
                $table->unsignedMediumInteger('count')->default(0)->comment('count of queued jobs per day');
                $table->boolean('failed')->default(0)->comment('this gets updated if the queues don\'t run like expected');
            });

        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vld_email_templates');
    }
}
