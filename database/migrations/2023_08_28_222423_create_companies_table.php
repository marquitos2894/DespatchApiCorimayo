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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social');
            $table->string('ruc');
            $table->string('direccion');
            $table->string('logo_path')->nullable();

            //Credenciales
            $table->string('sol_user');
            $table->string('sol_pass');
            $table->string('cert_path');//pem

            //Credenciales API
            $table->string('client_idCode')->nullable();
            $table->string('client_secretCode')->nullable();

            $table->boolean('production')->default(false);

            $table->foreignId('user_id')
                ->constrained('users')
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
        Schema::dropIfExists('companies');
    }
};
