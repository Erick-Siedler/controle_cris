<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable =[
        'name', 'start_date', 'end_date'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user_groups()
    {
        return $this->hasMany(UserGroup::class, 'groups_id');
    }
}
