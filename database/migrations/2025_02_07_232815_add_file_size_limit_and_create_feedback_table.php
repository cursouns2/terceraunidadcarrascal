<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileManagementAndFeedbackToTasks extends Migration
{
    public function up()
    {
        // Agregar campo para límite de tamaño de archivo
        Schema::table('tareas', function (Blueprint $table) {
            $table->integer('file_size_limit')->nullable()->after('fecha_vencimiento');
        });

        // Crear tabla para versiones de archivos
        Schema::create('versiones_archivo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('archivo_tarea_id');
            $table->string('nombre');
            $table->string('ruta');
            $table->unsignedBigInteger('usuario_id');
            $table->text('comentario')->nullable();
            $table->timestamp('fecha_subida');
            $table->boolean('es_final')->default(false);
            $table->timestamps();

            $table->foreign('archivo_tarea_id')->references('id')->on('archivo_tareas')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Crear tabla para feedback
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tarea_id');
            $table->unsignedBigInteger('usuario_id');
            $table->text('comentario');
            $table->string('archivo_adjunto')->nullable();
            $table->timestamps();

            $table->foreign('tarea_id')->references('id')->on('tareas')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->dropColumn('file_size_limit');
        });

        Schema::dropIfExists('versiones_archivo');
        Schema::dropIfExists('feedback');
    }
}
