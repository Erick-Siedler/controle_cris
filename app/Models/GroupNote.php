<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupNote extends Model
{
    protected $fillable = [
        'groups_id',
        'date',
        'content',
        'color',
        'position_x',
        'position_y',
        'is_pinned',
        'z_index',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'position_x' => 'float',
            'position_y' => 'float',
            'is_pinned' => 'boolean',
            'z_index' => 'integer',
        ];
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'groups_id');
    }
}
