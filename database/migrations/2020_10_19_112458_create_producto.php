<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProducto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('producto', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->integer('categoria_id');
            $table->text('sku');
            $table->string('nombre');
            $table->text('descripcion');
            $table->bigInteger('cantidad')->nullable();
            $table->bigInteger('precio_1');
            $table->bigInteger('precio_2');
            $table->char('stock',1);
            $table->text('imagen')->nullable();
            $table->char('activo',1)->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('producto');
    }
}
