<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckOut extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'items' => 'array', // cast the 'items' column to an array
    ];
}
