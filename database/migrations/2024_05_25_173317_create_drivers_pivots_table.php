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
        Schema::create('drivers_pivots', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); //COD/MOD
            $table->string('tipoDoc')->nullable();
            $table->string('nroDoc')->nullable();
            $table->string('licencia')->nullable();
            $table->string('nombres')->nullable();
            $table->string('apellidos')->nullable();
            $table->timestamps();

            $table->foreignId('data_sends_id')
            ->constrained('data_sends')
            ->onDelete('cascade');

            $table->boolean("active")->default(true);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers_pivots');
    }
};
