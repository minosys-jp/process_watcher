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
        Schema::create('program_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 1024)->index()->comment('プロセス名');
            $table->unsignedBigInteger('hostname_id')->comment('ホストID');
            $table->unsignedBigInteger('version')->comment('バージョン');
            $table->tinyInteger('status')->default(1)->comment('W/B/G status');
            $table->tinyInteger('flg_notified')->default(1)->comment('host へ通知済みかどうか');
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
        Schema::dropIfExists('program_modules');
    }
};
