<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVentasFacturaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventas_factura', function (Blueprint $table) {
            $table->bigInteger('ventas_id');
            $table->bigInteger('empresa_id');
            $table->integer('configuraciones_id');
            $table->text('folio')->nullable();
            $table->text('xml')->nullable();
            $table->text('timbre')->nullable();
            $table->char('activo', 1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ventas_factura');
    }
}
