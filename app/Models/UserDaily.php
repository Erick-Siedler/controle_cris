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

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'peso' => 'float',
            'check_in' => 'boolean',
            'desafio' => 'boolean',
            'balanca' => 'boolean',
            'cafe_da_manha' => 'boolean',
            'ceia' => 'boolean',
            'cha_tarde' => 'boolean',
            'almoco' => 'boolean',
            'ceia_tarde' => 'boolean',
            'cha_noite' => 'boolean',
            'jantar' => 'boolean',
            'ceia_noite' => 'boolean',
            'check_out' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
