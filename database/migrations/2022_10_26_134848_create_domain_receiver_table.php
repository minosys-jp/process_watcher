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
        Schema::create('domain_receiver', function (Blueprint $table) {
            $table->unsignedBigInteger('domain_id')->index()->comment('ドメインID');
            $table->unsignedBigInteger('receiver_id')->comment('受信者ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domain_receiver');
    }
};
