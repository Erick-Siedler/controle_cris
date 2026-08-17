<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDaily extends Model
{
    protected $fillable = [
        'users_id',
        'groups_id',
        'date',
        'peso',
        'check_in',
        'interacao_livro',
        'balanca',
        'cafe_da_manha',
        'fruta_da_manha',
        'cha_da_manha',
        'almoco',
        'fruta_da_tarde',
        'cha_da_tarde',
        'jantar',
        'fruta_da_noite',
        'check_out',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'peso' => 'float',
            'check_in' => 'boolean',
            'interacao_livro' => 'boolean',
            'balanca' => 'boolean',
            'cafe_da_manha' => 'boolean',
            'fruta_da_manha' => 'boolean',
            'cha_da_manha' => 'boolean',
            'almoco' => 'boolean',
            'fruta_da_tarde' => 'boolean',
            'cha_da_tarde' => 'boolean',
            'jantar' => 'boolean',
            'fruta_da_noite' => 'boolean',
            'check_out' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
