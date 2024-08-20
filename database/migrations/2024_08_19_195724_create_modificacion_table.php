<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('modificacion', function (Blueprint $table) {
            $table->id('IdModificación');
            $table->string('Nombre');
            $table->string('Apellidos');
            $table->string('Titulo');
            $table->text('Modificaciones');
            $table->foreignId('IdDocumento')->references('IdDocumento')->on('documento');
            $table->foreignId('IdUsuario')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modificacion');
    }
};
