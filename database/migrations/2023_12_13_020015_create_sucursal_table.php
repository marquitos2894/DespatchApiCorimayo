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
        Schema::create('sucursal', function (Blueprint $table) {
            $table->id();
            $table->string("nombreSucursal");
            $table->string("ubigueo");
            $table->string("departamento");
            $table->string("provincia");
            $table->string("distrito");
            $table->string("urbanizacion")->nullable();;
            $table->string("direccion");
            $table->string("codLocal");
            $table->boolean("active")->default(true);
            
            $table->foreignId('companie_id')            
            ->constrained('companies')
            ->onDelete('cascade');
            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursal');
    }
};
