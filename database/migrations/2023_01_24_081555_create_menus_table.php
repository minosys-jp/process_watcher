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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('flg_admin')->default(0)->comment('ADMIN なら1');
            $table->string('title', 1024)->comment('タイトル');
            $table->string('icon', 1024)->nullable(true)->comment('icon html');
            $table->string('link', 1024)->nullable(true)->comment('リンク先 URL');
            $table->unsignedBigInteger('parent_id')->nullable(true)->comment('親 メニュー ID');
            $table->integer('sort_number')->default(0)->index()->comment('表示順序');
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
        Schema::dropIfExists('menus');
    }
};
