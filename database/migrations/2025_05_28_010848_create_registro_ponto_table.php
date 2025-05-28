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
        Schema::create('registro_ponto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('data');
            $table->dateTime('marcacao1')->nullable()->comment('Entrada');
            $table->dateTime('marcacao2')->nullable()->comment('Saída para almoço');
            $table->dateTime('marcacao3')->nullable()->comment('Retorno do almoço');
            $table->dateTime('marcacao4')->nullable()->comment('Saída');
            $table->timestamps();
            
            // Índice para busca rápida por usuário e data
            $table->index(['user_id', 'data']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_ponto');
    }
};
