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
        Schema::create('table_user_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')
                ->constrained()
                ->onDelete('cascade');

            $table->boolval('check_in');
            $table->boolval('desafio');
            $table->boolval('balanca');
            $table->boolval('cafe_da_manha');
            $table->boolval('ceia');
            $table->boolval('cha_tarde');
            $table->boolval('almoco');
            $table->boolval('ceia_tarde');
            $table->boolval('cha_noite');
            $table->boolval('jantar');
            $table->boolval('ceia_noite');
            $table->boolval('check_out');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_user_daily');
    }
};
