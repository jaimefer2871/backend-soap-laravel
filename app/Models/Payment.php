<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'amount',
        'client_id',
        'token',
        'session_id',
        'confirmed',
        'date_confirmed',
    ];
}
