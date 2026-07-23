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
        Schema::create('evento_partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnUpdate()->restrictOnDelete();
            $table->foreignId('participante_id')->constrained('participantes')->restrictOnUpdate()->restrictOnDelete();
            $table->foreignId('partida_id')->constrained('partidas')->restrictOnUpdate()->restrictOnDelete();
            $table->enum('tipo', ['GOL', 'CARTAO_AMARELO', 'CARTAO_VERMELHO', 'ASSISTENCIA', 'GOL_CONTRA', 'GOLS_SOFRIDOS']);
            $table->time('tempo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evento_partidas');
    }
};
