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
        Schema::create('configures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable(true)->comment('対象テナントID');
            $table->unsignedBigInteger('domain_id')->nullable(true)->comment('対象ドメインID');
            $table->string('ckey')->comment('キー文字列');
            $table->string('cvalue')->nullable(true)->comment('値');
            $table->integer('cnum')->nullable(true)->comment('値(数値)');
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
        Schema::dropIfExists('configures');
    }
};
