<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \korchasa\LaravelTelegramBot\Context;

class CreateTelegramBotContextTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Context::TABLE, function (Blueprint $table) {
            $table->integer('user_id')->unique();
            $table->string('first_name', 45)->nullable();
            $table->string('last_name', 45)->nullable();
            $table->string('username', 45)->nullable();
            $table->string('state', 45);
            $table->text('params');
            $table->text('last_message');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(Context::TABLE);
    }
}
