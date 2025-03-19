<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivoTareasTable extends Migration
{
    public function up()
    {
        Schema::create('archivo_tareas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tarea_id');
            $table->string('nombre');
            $table->string('ruta');
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();

            $table->foreign('tarea_id')->references('id')->on('tareas')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('archivo_tareas');
    }
}
