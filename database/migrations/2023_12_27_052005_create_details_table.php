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
        Schema::create('details', function (Blueprint $table) {
            $table->id();

            $table->string("codigo")->nullable();
            $table->string("descripcion");
            $table->string("cantidad");
            $table->string("unidad");
            $table->text("equipo");

            $table->foreignId('despatch_id')
            ->constrained('despatches')
            ->onDelete('cascade');

            $table->boolean("active")->default(true);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('details');
    }
};
