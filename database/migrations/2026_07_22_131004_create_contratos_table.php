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
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipe_id')->constrained('equipes')->restrictOnUpdate()->restrictOnDelete();
            $table->foreignId('participante_id')->constrained('participantes')->restrictOnUpdate()->restrictOnDelete();
            $table->enum('status', ['ATIVO', 'ENCERRADO']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
