<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const RENAMED_FIELDS = [
        'desafio' => 'interacao_livro',
        'ceia' => 'fruta_da_manha',
        'cha_noite' => 'cha_da_manha',
        'ceia_tarde' => 'fruta_da_tarde',
        'cha_tarde' => 'cha_da_tarde',
        'ceia_noite' => 'fruta_da_noite',
    ];

    public function up(): void
    {
        Schema::table('user_dailies', function (Blueprint $table) {
            foreach (self::RENAMED_FIELDS as $oldField => $newField) {
                $table->renameColumn($oldField, $newField);
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_dailies', function (Blueprint $table) {
            foreach (self::RENAMED_FIELDS as $oldField => $newField) {
                $table->renameColumn($newField, $oldField);
            }
        });
    }
};
