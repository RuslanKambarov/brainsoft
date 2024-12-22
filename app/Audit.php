<?php

namespace App;

use DB;
use App\District;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $hidden = ["pivot"];

    public function questions()
    {
        return $this->hasMany('App\Question');
    }

    public function countNOK($results){    
        $data = [];
        foreach($this->questions as $question){
            $data[$question->id] = 0;
        }
        if($results[0]->audit_json === null){
            return $data;
        }        
        foreach($results as $result){
            $answers = json_decode($result->audit_json);           
            foreach($answers as $answer){               
                if($answer->answer === false)
                $data[$answer->question_id]++;
            }            
        }
        return $data;
    }

    function getAssignedAuditsCount($device_id, $date = NULL){

        if(!$date){
            $date = \Carbon\Carbon::now();
        }

        $audits_assigned = DB::table("audit_assigned")
            ->where([["device_id", $device_id], ["audit_id", $this->id]])
            ->whereRaw("MONTH(month) = MONTH(NOW())")
            ->get();

        if (!$audits_assigned->isEmpty()) {
            return $audits_assigned[0]->audits_count;
        }
        return DB::table("app_settings")->where("id", 1)->get()[0]->value;
    
    }

    public function getAuditAnalytics($district_id, $date = null){
        
        $date = explode("(", $date)[0];         //get rid of "(timezone)"
        $date = \Carbon\Carbon::parse($date);   //create a Carbon instance from recieved date
        $district = District::find($district_id);
        $objects = $district->devices()->get();
        $manager_id = $district->getManagerId();
        $district_audits_by_user = $district->getAuditResultsByUser($date)->groupBy('user_id');
        $analytics_data = [];
        
        //Total by district
        $total_district["object_name"] = "Итого по району";
        $total_district["engineer_assigned"] = 0;
        $total_district["engineer_conducted"] = 0;
        $total_district["manager_assigned"] = 0;
        $total_district["manager_conducted"] = 0;
        $total_district["total_conducted"] = 0;
        $total_district["total_objects"] = 0;
        $total_district["kpi1"] = 0;
        $total_district["kpi2"] = 0;
        $i = 0;

        foreach($district_audits_by_user as $user_id => $user_audits){

            $object_audits = $user_audits->groupBy('owen_id');
            $total = [];

            //Total by engineer
            $total[$user_id]["engineer_assigned"] = 0;
            $total[$user_id]["engineer_conducted"] = 0;
            $total[$user_id]["manager_assigned"] = 0;
            $total[$user_id]["manager_conducted"] = 0;
            $total[$user_id]["total_conducted"] = 0;
            $total[$user_id]["total_objects"] = 0;
            $total[$user_id]["kpi1"] = 0;
            $total[$user_id]["kpi2"] = 0;

            foreach($object_audits as $object_id => $audits){

                $temp = [];
                $temp['number'] = ++$i;
                $temp["engineer"] = $audits[0]->username;
                $temp["object_name"] = $audits[0]->name;

                $temp["engineer_assigned"] = $this->getAssignedAuditsCount($object_id, $this->id);
                if($audits[0]->audit_json === null){
                    $temp["engineer_conducted"] = 0;    
                }else{
                    $temp["engineer_conducted"] = count($audits->where("auditor_id", $user_id));
                }                
                
                $manager_audits = Audit_result::where("object_id", $object_id)
                    ->where("audit_id", $this->id)
                    ->where("auditor_id", $manager_id)
                    ->whereRaw("MONTH(audit_date) = MONTH('$date')")
                    ->get();

                if ($manager_audits->isEmpty()) {
                    continue;
                }

                $temp["manager_assigned"] = count($manager_audits);
                $temp["manager_conducted"] = count($manager_audits);
                $temp["total_conducted"] = $temp["engineer_conducted"] + $temp["manager_conducted"];
                $total[$user_id]["engineer_assigned"] += $temp["engineer_assigned"];
                $total[$user_id]["engineer_conducted"] += $temp["engineer_conducted"];    
                $total[$user_id]["total_conducted"] += $temp["total_conducted"];
                $total[$user_id]["manager_assigned"] += $temp["manager_assigned"];
                $total[$user_id]["manager_conducted"] += $temp["manager_conducted"];
                $total[$user_id]["total_objects"]++;

                $total_district["engineer_assigned"] += $temp["engineer_assigned"];
                $total_district["engineer_conducted"] += $temp["engineer_conducted"];    
                $total_district["total_conducted"] += $temp["total_conducted"];
                $total_district["manager_assigned"] += $temp["manager_assigned"];
                $total_district["manager_conducted"] += $temp["manager_conducted"];
                $total_district["total_objects"]++;

                $temp["NOK"] = $this->countNOK($audits);
                if(array_slice($temp["NOK"], 0, 1)[0] >= 3){ $temp["kpi1"] = 1; }else{ $temp["kpi1"] = 0; }
                if(array_slice($temp["NOK"], 1, 1)[0] >= 3){ $temp["kpi2"] = 1; }else{ $temp["kpi2"] = 0; }
                foreach($temp["NOK"] as $question_id => $answer){
                    isset($total_district["NOK"][$question_id]) ? $total_district["NOK"][$question_id] += $answer : $total_district["NOK"][$question_id] = $answer;
                    isset($total[$user_id]["NOK"][$question_id]) ? $total[$user_id]["NOK"][$question_id] += $answer : $total[$user_id]["NOK"][$question_id] = $answer;
                }

                $total[$user_id]["kpi1"] += $temp["kpi1"];
                $total[$user_id]["kpi2"] += $temp["kpi2"];    
                $total_district["kpi1"] += $temp["kpi1"];
                $total_district["kpi2"] += $temp["kpi2"];

                $analytics_data[] = $temp;
            }

            $analytics_data[] = $total;    

        }

        $analytics_data[] = $total_district;

        return $analytics_data;
    }

    public static function getAuditAppends()
    {
        $districts = District::with([
                "devices" => function($query){
                    $query->select("id", "district_id", "name");
                }, 
                "devices.audits"
        ])
        ->select("id", "name")
        ->get();
        
        
        return $districts;
    }

}
