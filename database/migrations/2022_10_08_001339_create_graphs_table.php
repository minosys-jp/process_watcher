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
        Schema::create('graphs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->comment('親ID');
            $table->unsignedBigInteger('child_id')->comment('子ID');
            $table->unsignedBigInteger('parent_version')->comment('親バージョン');
            $table->unsignedBigInteger('child_version')->comment('子バージョン');
            $table->index(['parent_id', 'parent_version']);
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
        Schema::dropIfExists('graphs');
    }
};
