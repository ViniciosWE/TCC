<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('participantes', function (Blueprint $table) {
            $table->id();
            $table->integer('numero')->nullable();
            $table->enum('funcao', ['GOLEIRO','FIXO','ALA_DIREITO','ALA_ESQUERDO','PIVO','GOLEIRO_LINHA',
                'TECNICO','AUXILIAR_TECNICO','PREPARADOR_FISICO'])->nullable();
            $table->string('nome', 255);
            $table->char('cpf', 11);
            $table->enum('status', ['ATIVO', 'SEM_EQUIPE', 'APOSENTADO', 'SUSPENSO']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participantes');
    }
};
