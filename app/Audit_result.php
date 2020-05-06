<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Audit_result extends Model
{
    public static function checkLastAuditTime($object_id){

        $lastAuditTime = self::where("object_id", $object_id)->latest()->first()->audit_date ?? null;
        if($lastAuditTime){
            return Carbon::parse($lastAuditTime)->addDays(7) > Carbon::now();
        }else{
            return false;
        }

    }
}
