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
        Schema::create('campeonatos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnUpdate()->restrictOnDelete();
            $table->integer('minimo_jogadores_equipes');
            $table->integer('maximo_equipes');
            $table->string('nome');
            $table->enum('tipo', ['MATA_MATA', 'GRUPOS_MATA_MATA', 'PONTOS_CORRIDOS']);
            $table->string('categoria');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->enum('status', ['INSCRICOES', 'EM_ANDAMENTO', 'FINALIZADO']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campeonatos');
    }
};
