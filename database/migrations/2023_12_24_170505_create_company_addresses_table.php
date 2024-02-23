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
        Schema::create('company_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('ubigueo');
            $table->string('departamento');
            $table->string('provincia');
            $table->string('distrito');
            $table->string('urbanizacion')->nullable();
            $table->string('direccion');
            $table->string('codLocal')->nullable();

            $table->foreignId('companie_id')            
            ->constrained('companies')
            ->onDelete('cascade');

            $table->timestamps();

            $table->boolean("active")->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_addresses');
    }
};
