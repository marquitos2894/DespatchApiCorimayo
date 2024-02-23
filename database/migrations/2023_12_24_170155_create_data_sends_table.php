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
        Schema::create('data_sends', function (Blueprint $table) {
            $table->id();
            $table->string('codtraslado');//motivo traslado
            $table->string('modtraslado');//modalidad: privado o publico
            $table->dateTime('fecTraslado');
            $table->float('pesoTotal');
            $table->string('undPesoTotal');

            //DIRECCION LLEGADA
            $table->string('ubigueollegada');
            $table->string('direccionLlegada');
            $table->string('codLocalLlegada')->nullable();
            $table->string('rucLlegada')->nullable();
            //DIRECCION PARTIDA
            $table->string('ubigueoPartida');
            $table->string('direccionPartida');
            $table->string('codLocalPartida')->nullable();
            $table->string('rucPartida')->nullable();
            //Datos del conductor
            $table->string('tipoDocChofer')->nullable();
            $table->string('nroDocChofer')->nullable();
            $table->string('licenciaChofer')->nullable();
            $table->string('nombresChofer')->nullable();
            $table->string('apellidosChofer')->nullable();
            //Datos del vehiculo
            $table->string('placaVehiculo')->nullable();
            $table->string('mtcCirculacion')->nullable();
            
            //Datos de transportista
            $table->string('tipoDocTransp');
            $table->string('numDocTransp');
            $table->string('rzSocialTransp');
            $table->string('nroMtcTransp')->nullable();

            /*$table->string('tipoDocChoferTransp');
            $table->string('nroDocChoferTransp');
            $table->string('licenciaChoferTransp');
            $table->string('nombresChoferTransp');
            $table->string('apellidosChoferTransp');

            $table->string('placaVehiculoTransp');
            $table->string('mtcCirculacionTransp');*/





            $table->foreignId('despatch_id')
            ->constrained('despatches')
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
        Schema::dropIfExists('data_sends');
    }
};
