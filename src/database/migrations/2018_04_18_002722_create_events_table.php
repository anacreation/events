<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('venue_id')->nullable();
            $table->unsignedInteger('organiser_id');
            $table->string('name');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->timestamp('enrollment_start_date')->nullable();
            $table->timestamp('enrollment_end_date')->nullable();
            $table->boolean('member_only');
            $table->boolean('is_active');
            $table->unsignedInteger('vacancy');
            $table->timestamps();
        });
        Schema::create('registrations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('participant_id');
            $table->foreign("event_id")->references('id')->on('events')
                  ->onDelete('cascade');
            $table->foreign("participant_id")->references('id')
                  ->on('participants')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('events');
    }
}
