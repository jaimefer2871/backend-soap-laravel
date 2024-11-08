<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'funds',
        'client_id'
    ];
}
