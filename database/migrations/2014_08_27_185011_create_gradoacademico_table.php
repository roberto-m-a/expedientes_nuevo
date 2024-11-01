<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gradoacademico', function (Blueprint $table) {
            $table->id('IdGradoAcademico');
            $table->string('nombreGradoAcademico');
        });
        DB::table('gradoacademico')->insert([
            ['nombreGradoAcademico'=>'Licenciatura'],
            ['nombreGradoAcademico'=>'Posgrado'],
            ['nombreGradoAcademico'=>'Maestría'],
            ['nombreGradoAcademico'=>'Doctorado'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gradoacademico');
    }
};
