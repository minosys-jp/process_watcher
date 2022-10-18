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
        Schema::create('finger_prints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_module_id')->comment('モジュールID');
            $table->unsignedBigInteger('version')->comment('バージョン');
            $table->unique(['program_module_id', 'version']);
            $table->tinyInteger('algorithm_id')->default(1)->comment('アルゴリズムID');
            $table->string('finger_print', 1024)->comment('フィンガープリント');
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
        Schema::dropIfExists('finger_prints');
    }
};
