<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditoDeudaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credito_deuda', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('cliente_id');
            $table->text('detalle_credito');
            $table->bigInteger('monto_credito');
            $table->char('activo', 1); // S : credito pendiente, N : credito cancelado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credito_deuda');
    }
}
