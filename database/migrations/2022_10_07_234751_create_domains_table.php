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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('code', 256)->index()->comment('ドメインコード');
            $table->string('name', 256)->comment('ドメイン名');
            $table->unsignedBigInteger('tenant_id')->index()->comment('テナントID');
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
        Schema::dropIfExists('domains');
    }
};
