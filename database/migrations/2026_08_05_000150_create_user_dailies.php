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
        Schema::create('user_dailies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('groups_id')
                ->constrained()
                ->onDelete('cascade');

            $table->boolean('check_in');
            $table->boolean('desafio');
            $table->boolean('balanca');
            $table->decimal('peso', 6, 2)->nullable();
            $table->boolean('cafe_da_manha');
            $table->boolean('ceia');
            $table->boolean('cha_tarde');
            $table->boolean('almoco');
            $table->boolean('ceia_tarde');
            $table->boolean('cha_noite');
            $table->boolean('jantar');
            $table->boolean('ceia_noite');
            $table->boolean('check_out');

            $table->date('date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_dailies');
    }
};
