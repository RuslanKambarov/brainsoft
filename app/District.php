<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    public function devices()
    {
        return $this->hasMany('App\Device', 'district_id', 'owen_id');
    }

    public function users(){
    	return $this->belongsToMany('App\User', 'user_district', 'district_id', 'user_id');
    }

    public function director(){
        return DB::table('user_district')
        ->join('user_role', 'user_role.user_id',  '=', 'user_district.user_id')
        ->join('users', 'user_district.user_id', '=', 'users.id')
        ->where([['user_district.district_id', '=', $this->id], ['user_role.role_id', '=', 4]])->pluck('users.name')->first() ?? "Не назначен";
    }

    public function engineer(){
        return DB::table('objects')
        ->distinct()
        ->join('user_objects', 'object_id', '=', 'objects.id')
        ->join('users', 'user_id', '=', 'users.id')
        ->where('district_id', '=', $this->owen_id)->get('users.*');
    }

    public function manager(){
        return DB::table('user_district')
        ->join('user_role', 'user_role.user_id',  '=', 'user_district.user_id')
        ->join('users', 'user_district.user_id', '=', 'users.id')
        ->where([['user_district.district_id', '=', $this->id], ['user_role.role_id', '=', 1]])->pluck('users.name')->first() ?? "Не назначен";
    }

    public function getManagerId(){
        return DB::table('user_district')
        ->join('user_role', 'user_role.user_id',  '=', 'user_district.user_id')
        ->join('users', 'user_district.user_id', '=', 'users.id')
        ->where([['user_district.district_id', '=', $this->id], ['user_role.role_id', '=', 1]])->pluck('users.id')->first() ?? 0;
    }

    public function getAuditResults(){
        return DB::table('audit_results')
        ->join('objects', 'object_id', '=', 'owen_id')
        ->join('districts', 'districts.owen_id', '=', 'district_id')
        ->where('districts.owen_id', '=', $this->owen_id)->get();
    }

    public function getAuditResultsByUser($date){
        return DB::table('objects')->select("users.name as username", "objects.name as name", "audit_json", "user_id", "auditor_id", 'owen_id')
        ->leftJoin('user_objects', 'objects.id', '=', 'user_objects.object_id')
        ->leftJoin('users', 'users.id', '=', 'user_id')
        ->leftJoin('audit_results', function ($join) use($date){
            $join->on('owen_id', '=', 'audit_results.object_id')
                 ->where('audit_id', '=', 4)->whereRaw("MONTH(audit_date) = MONTH('$date')");
        })
        ->where('objects.district_id', '=', $this->owen_id)
        ->get();
    }

    public function getConsumption(){
        $this->district_id   = $this->owen_id;
        unset($this->owen_id, $this->id);
        $this->coal_reserve  = array_sum($this->devices()->pluck('coal_reserve')->toArray());   
        $this->income        = $consumption->income ?? 0;
        $this->consumption   = $consumption->consumption ?? 0;
        $this->balance       = $this->income - $this->consumption;       
        return $this;
    }

}
