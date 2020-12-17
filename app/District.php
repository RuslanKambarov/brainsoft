<?php

namespace App;

use App\Consumption;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    public function devices()
    {
        return $this->hasMany('App\Device', 'district_id', 'id');
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

    public static function offlineData()
    {
        $districts = \Auth::user()->getUserDistricts();
        $districts = $districts->map(function($district){
            
            $devices = $district->devices()->get();
            $devices_data = $devices->map(function($device) use ($district){
                
                $audits = $device->audits()->get();
                
                $audits = $audits->map(function($audit) use ($device){
                    
                    $questions = $audit->questions()->get();

                    $questions = $questions->map(function($question){
                        $question->question_id = $question->id;
                        return $question->only("question_id", "audit_id", "question", "photo");
                    });
                    $arr = [
                        "device_id" => $device->id,
                        "name" => $device->name,
                        "auditor" => \Auth::user()->only("name", "email"),
                        "questions" => $questions
                    ];
                    $audit->questions_data = $arr;

                    // $audit->questions_data["device_id"] = $device->id;
                    // $audit->questions_data["name"] = $device->name;
                    // $audit->questions_data["auditor"] = \Auth::user()->only("name", "email");
                    // $audit->questions_data["questions"] = $questions; 
                    
                    $audit->audit_id = $audit->id;
                    return $audit->only("audit_id", "name", "questions_data"); 
                });
                
                $arr = [
                    "device_id" => $device->id,
                    "name" => $device->name,
                    "audits" => $audits
                ];

                $device->audits_data = $arr;

                // $device->audits_data["device_id"] = $device->id;
                // $device->audits_data["name"] = $device->name;
                // $device->audits_data["audits"] = $audits;

                $device->district_id = $district->id;
                $device->device_id = $device->id;                
                return $device->only("district_id", "device_id", "name", "audits_data"); 
            });

            $district->district_id = $district->id;
            $district->devices_data = $devices_data;
            
            return $district->only("district_id", "name", "devices_data");
        });
        return $districts;
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
        ->where('objects.district_id', '=', $this->id)
        ->get();
    }

    public function getConsumption(){
        $this->coal_reserve  = array_sum($this->devices()->pluck('coal_reserve')->toArray());
        $date = Carbon::now();
        $consumption = Consumption::getDistrictTotalConsumption($this->id);   
        $this->income        = $consumption->sum('income') ?? 0;
        $this->consumption   = $consumption->sum('consumption') ?? 0;
        $this->balance       = $this->income - $this->consumption;       
        $this->district_id   = $this->id;
        unset($this->owen_id, $this->id);
        return $this;
    }

}
