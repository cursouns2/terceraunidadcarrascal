<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToTareaUsuarioTable extends Migration
{
    public function up()
    {
        Schema::table('tarea_usuario', function (Blueprint $table) {
            $table->timestamps(); // Esto agregará las columnas created_at y updated_at
        });
    }

    public function down()
    {
        Schema::table('tarea_usuario', function (Blueprint $table) {
            $table->dropTimestamps(); // Esto eliminará las columnas created_at y updated_at
        });
    }
}
