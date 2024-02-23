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
        Schema::create('client_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('ubigueo');
            $table->string('departamento');
            $table->string('provincia');
            $table->string('distrito');
            $table->string('urbanizacion')->nullable();
            $table->string('direccion');
            $table->string('codLocal')->nullable();


            $table->foreignId('client_idAddresses')            
            ->constrained('clients')
            ->onDelete('cascade');
            
            $table->timestamps();
            
            $table->boolean("domFiscal")->default(false);

            $table->boolean("active")->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_addresses');
    }
};
