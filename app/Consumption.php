<?php

namespace App;

use DB;
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

    public static function getDistrictConsumption($district_id, $date){
        return DB::table('objects')
        ->select("users.name as user_name", "objects.name as object_name", "consumption.created_at as created_at", "consumption.*")
        ->leftJoin("user_objects", "object_id", "=", "objects.id")
        ->leftJoin("users", "user_id", "=", "users.id")
        ->leftJoin("consumption", function($join) use ($date){
            $join->on("owen_id", "=", "consumption.object_id")
                 ->on(DB::raw("MONTH(consumption.created_at)"), "=", DB::raw("MONTH('$date')"));
        })
        ->where("objects.district_id", "=", $district_id)
        ->get();        
    }

    public static function getDistrictMonthConsumption($district_id, $date){
        return DB::table('objects')
        ->select(DB::raw("objects.name as object_name, users.name as user_name, SUM(income) as income, SUM(consumption) as consumption, (CASE WHEN DAY(LAST_DAY('$date')) - 3 > COUNT(consumption) THEN 1 ELSE 0 END) AS input, '$date' as date"))
        ->leftJoin("user_objects", "object_id", "=", "objects.id")
        ->leftJoin("users", "user_id", "=", "users.id")
        ->leftJoin("consumption", function($join) use ($date){
            $join->on("owen_id", "=", "consumption.object_id")
                 ->on(DB::raw("MONTH(consumption.created_at)"), "=", DB::raw("MONTH('$date')"));
        })
        ->where("objects.district_id", "=", $district_id)
        ->groupBy("objects.name", "users.name")
        ->get();            
    }
}