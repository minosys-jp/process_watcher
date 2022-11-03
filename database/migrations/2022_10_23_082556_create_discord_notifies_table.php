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
        Schema::create('discord_notifies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->comment('テナントID');
            $table->unsignedBigInteger('domain_id')->comment('ドメインID');
            $table->unsignedBigInteger('hostname_id')->comment('ホストID');
            $table->unsignedBigInteger('graph_id')->nullable(true)->comment('グラフID');
            $table->unsignedBigInteger('finger_print_id')->nullable(true)->comment('足跡ID');
            $table->integer('type_id')->default(1)->comment('変更タイプ');
            $table->text('description')->nullable(true)->comment('変更内容');
            $table->datetime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->datetime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->index(['tenant_id', 'domain_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discord_notifies');
    }
};
