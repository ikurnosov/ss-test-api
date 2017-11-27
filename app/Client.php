<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = array('id', 'balance');
}
