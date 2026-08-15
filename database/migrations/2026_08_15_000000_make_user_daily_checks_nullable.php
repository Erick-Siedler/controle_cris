<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CHECK_FIELDS = [
        'check_in',
        'desafio',
        'balanca',
        'cafe_da_manha',
        'ceia',
        'cha_tarde',
        'almoco',
        'ceia_tarde',
        'cha_noite',
        'jantar',
        'ceia_noite',
        'check_out',
    ];

    public function up(): void
    {
        Schema::table('user_dailies', function (Blueprint $table) {
            foreach (self::CHECK_FIELDS as $field) {
                $table->boolean($field)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        foreach (self::CHECK_FIELDS as $field) {
            DB::table('user_dailies')->whereNull($field)->update([$field => false]);
        }

        Schema::table('user_dailies', function (Blueprint $table) {
            foreach (self::CHECK_FIELDS as $field) {
                $table->boolean($field)->nullable(false)->change();
            }
        });
    }
};
