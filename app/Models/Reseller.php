<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reseller extends Model
{
    protected $fillable = [
        'name',
        'contact_name',
        'contact_number',
        'address',
        'description',
    ];
}