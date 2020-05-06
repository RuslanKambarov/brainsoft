<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Consumption extends Model
{
    public $table = 'consumption';

    public static function checkLastConsumeTime($object_id){

        $lastConsumeTime = self::where("object_id", $object_id)->latest()->first()->created_at ?? null;
        if($lastConsumeTime){
            return Carbon::parse($lastConsumeTime)->addDays(1) > Carbon::now();
        }else{
            return false;
        }

    }

    public static function getLastConsume($object_id){
    	return self::where("object_id", $object_id)->latest()->first();
    }
}
