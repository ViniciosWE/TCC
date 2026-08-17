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
        Schema::create('partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campeonato_id')->constrained('campeonatos')->restrictOnUpdate()->restrictOnDelete();
            $table->foreignId('mandante_id')->constrained('equipes')->restrictOnUpdate()->restrictOnDelete();
            $table->foreignId('visitante_id')->constrained('equipes')->restrictOnUpdate()->restrictOnDelete();
            $table->unsignedTinyInteger('gols_mandante')->default(0);
            $table->unsignedTinyInteger('gols_visitante')->default(0);
            $table->dateTime('data_hora');
            $table->enum('status', ['AGENDADA', 'FINALIZADA']);
            $table->string('fase')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partidas');
    }
};
