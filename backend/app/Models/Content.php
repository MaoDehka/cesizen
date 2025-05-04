<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'page',
        'title',
        'content',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}