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
        Schema::create('despatches', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->string('serie');
            $table->bigInteger('correlativo');
            $table->dateTime('fechaEmision');
            $table->text('hash')->nullable();
            $table->boolean('estHash')->default(0);
            $table->text('xml')->nullable();
            $table->boolean('estXml')->default(0);
            $table->text('ticket')->nullable();
            $table->text('cdrZip')->nullable();
            $table->boolean('estcdrZip')->default(0);
            $table->text('urlcodeqr')->nullable();
            $table->text('cdrResponse')->nullable();
            $table->string('areatrabajo');
                     

            $table->foreignId('companie_id')            
            ->constrained('companies')
            ->onDelete('cascade');

            $table->foreignId('client_id')            
            ->constrained('clients')
            ->onDelete('cascade');

            $table->foreignId('sucursal_id')            
            ->constrained('sucursal')
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
        Schema::dropIfExists('despatches');
    }
};
