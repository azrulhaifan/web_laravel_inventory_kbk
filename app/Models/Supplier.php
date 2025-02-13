<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'contact_name',
        'contact_number',
        'address',
        'description',
        'type',
    ];

    protected $casts = [
        'type' => 'string',
    ];
}
