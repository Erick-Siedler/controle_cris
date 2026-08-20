<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name', 'start_date', 'end_date',
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

    public function notes()
    {
        return $this->hasMany(GroupNote::class, 'groups_id');
    }

    public function effectiveStartDate(): Carbon
    {
        return $this->start_date->copy()->addDay();
    }
}
