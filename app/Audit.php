<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    public function questions()
    {
        return $this->hasMany('App\Question');
    }
}
