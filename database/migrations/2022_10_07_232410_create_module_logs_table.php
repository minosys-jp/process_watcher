<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('status')->default(1)->comment('プロセス判定状態');
            $table->unsignedBigInteger('finger_print_id')->nullable(true)->index()->comment('Finger Print ID');
            $table->unsignedTinyInteger('flg_noticed')->default(0)->comment('host 通知済みかどうか');
            $table->unsignedTinyInteger('flg_discord')->default(0)->comment('discord 通知済みかどうか');
   
            $table->datetime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->datetime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('module_logs');
    }
};
