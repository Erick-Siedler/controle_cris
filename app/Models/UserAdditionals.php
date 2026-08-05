<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAdditionals extends Model
{
    protected $fillable = [
        'users_id', 'peso_inicial', 'meta_peso', 'semana_bonus', 'groups_id'
    ];

    protected function casts(): array
    {
        return [
            'peso_inicial' => 'float',
            'meta_peso' => 'float',
            'semana_bonus' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
