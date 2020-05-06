<?php

namespace App\Http\Controllers;

use DB;
use App\User;
use App\Audit;
use App\Device;
use App\District;
use App\Question;
use App\Audit_result;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    
    public function index(Request $request){
    
        $devices = Device::all();
        $audits = Audit::all();
        return view("audit.index", ["devices" => $devices, "audits" => $audits]);

    }

    public function addAudit(Request $request){

        $audit = new Audit;
        $audit->name = $request->name;
        $audit->save();
        return redirect('/audit/types');

    }

    public function deleteAudit($id){
    
        $audit = Audit::find($id);
        $audit->delete();
        return redirect('/audit/types');

    }

    public function auditControl(){
        
        $audits = Audit::all();
        return view("audit.control", ["audits" => $audits]);
    
    }

    public function showAudit($id){

        $audit = Audit::find($id);
        $questions = Question::all();
        return view("audit.show", ["audit" => $audit]);

    }

    public function results(Request $request){
        if($request->device_id){
            $results = Audit_result::where('object_id', $request->device_id)->get();   
        }else{
            $results = Audit_result::all();
        }
        return view("audit.results", ["results" => $results]);
    }

    public function showResult($id){
        $result = Audit_result::find($id);
        $result->questions = Question::where('audit_id', $result->audit_id)->get();
        $result->answers = json_decode($result->audit_json);
        //dd($result);
        return view("audit.singleResult", ["result" => $result]);
    }

    public function addQuestion($id){
        
        return view("audit.add", ["id" => $id]);

    }

    public function saveQuestion(Request $request, $id){

        $question = new Question;
        $question->audit_id = $id;
        $question->question = $request->question;
        if($request->photo){
            $question->photo = 1;
        }else{
            $question->photo = 0;
        }
        $question->save();
        return redirect("/audit/types/".$id);

    }

    public function removeQuestion($id){

        $question = Question::find($id);
        $question->delete();
        return redirect("/audit");

    }

    public function analytics($id){
        //Get instance of device
        $device = \App\Device::where("owen_id", $id)->first();
        //Select audit results of devices with auditor and audit 
        $audits = Audit_result::where("object_id", $id)
                                ->join('users', 'audit_results.auditor_id', '=', 'users.id')
                                ->join('audits', 'audit_results.audit_id', '=', 'audits.id')
                                ->whereRaw("MONTH(audit_date) = MONTH(NOW())")
                                ->select("users.name", "audit_id", "auditor_id", "audits.name as audit", "object_id", "audit_json", "audit_results.created_at", "audit_results.updated_at")
                                ->get();

        //Get count of planned audits in current month                                
        foreach($audits as $audit){
            $audit->assigned = $this->getAssignedAuditsCount($id, $audit->audit_id);
        }
        //Group by auditor
        $auditsByName = $audits->groupBy("audit");

        //Group by audit
        foreach($auditsByName as $key => $user){
            $auditsByName[$key] = $user->groupBy("name");
        }

        return response()->json(["audits" => $auditsByName, "device" => $device]);

    }

    function getAssignedAuditsCount($device_id, $audit_id, $date = NULL){

        if(!$date){
            $date = \Carbon\Carbon::now();
        }

        $audits_assigned = DB::table("audit_assigned")
            ->where([["device_id", $device_id], ["audit_id", $audit_id]])
            ->whereRaw("MONTH(month) = MONTH(NOW())")
            ->get();
        
            return $audits_assigned[0]->audits_count ?? DB::table("app_settings")->where("id", 1)->get()[0]->value;
    
    }

    function getConductedAuditsCount($device_id, $audit_id, $date = NULL){

        if(!$date){
            $date = \Carbon\Carbon::now();
        }

        $audits = Audit_result::where([["object_id", $device_id], ["audit_id", $audit_id]])
            ->whereRaw("MONTH(audit_date) = MONTH('$date')")
            ->get();
        
        return count($audits);
    
    }

    function compareAudits($audits){

        $users = array_keys($audits);
        $audit1 = array_shift($audits);
        $audit2 = array_pop($audits);
        $compareResult = [];

        foreach($audit1 as $key => $value){

                        
            $audits1_array = $value;
            $audits2_array = $audit2[$key];

            if(($audits1_array->isEmpty()) || ($audits2_array->isEmpty())){
                $compareResult[] = [];
                continue;
            }elseif(count($audits1_array) != count($audits2_array)){
                $compareResult[] = [];
                continue;
            }else{
				
				$temp = [];
                foreach($audits1_array as $key => $result){
					
					
                    foreach(json_decode($result->audit_json) as $questionKey => $question){

                        $opposite_answer = json_decode($audits2_array[$key]->audit_json)[$questionKey];                    
                        if($question->answer !== $opposite_answer->answer){
                            $mismatch = [];
                            $mismatch["text"] = "Расхождение в ответах. Вопрос: ".\App\Question::find($question->question_id)->question;
                            $mismatch["id"] = $question->question_id;
                            $mismatch["user1"] = $users[0];
                            $mismatch["date1"] = $result->audit_date;
                            $mismatch["answer1"] = $question->answer;
                            $mismatch["user2"] = $users[1];
                            $mismatch["date2"] = $audits2_array[$key]->audit_date;
                            $mismatch["answer2"] = $opposite_answer->answer;
                            $temp[] = $mismatch;                        
                        }
                    }

                }
                $compareResult[] = $temp;
            }

        }
        return $compareResult;

    }

    function analyticsDetail($device_id){
        
        $device = Device::where("owen_id", $device_id)->first();
        $dates = [];
        $compare = [];
        $auditsTotal = [];
        $auditsPlanned = [];
        $auditsConducted = [];
        $auditsConductedByUser = [];

        for($i = -5; $i <= 6; $i++){
            $dates[] = $date = \Carbon\Carbon::now()->add($i, 'month');
            $auditsTotal[] = Audit_result::where("object_id", $device_id)->whereRaw("MONTH(audit_date) = MONTH('$date')")->get(); 
        }
        
        $allAudits = Audit_result::where("object_id", $device_id)->get();
        
        //Users who conducted audits
        $users = $allAudits->pluck("auditor_id")->unique();
        
        //Audit types conducted
        $auditTypes = $allAudits->pluck("audit_id")->unique();

        foreach($auditTypes as $type){
            $array = [];
            foreach($dates as $date){                                
                $array[] =  $this->getAssignedAuditsCount($device_id, $type);
            }
            $auditName = Audit::find($type)->name;
            $auditsPlanned[$auditName] = $array;
        }

        foreach($auditTypes as $type){
            $array = [];
            foreach($dates as $date){                                
                $array[] =  $this->getConductedAuditsCount($device_id, $type, $date);
            }
            $auditsConducted[$type] = $array;
        }

        foreach($auditTypes as $type){
            foreach($users as $user){
                $array = [];
                foreach($dates as $date){
                    
                    $array[] = $a = Audit_result::where([["object_id", $device_id], 
                                         ["audit_id", $type], 
                                         ["auditor_id", $user]])
                                         ->whereRaw("MONTH(audit_date) = MONTH('$date')")
                                         ->get();
                }
                $userName = User::find($user)->name;
                $auditName = Audit::find($type)->name;
                $auditsConductedByUser[$auditName][$user] = $array;
            }

            $compare[$auditName] = $this->compareAudits($auditsConductedByUser[$auditName]);
        }


        //dd($auditsConductedByUser);
        //dd($auditsConducted);
        //dd($auditsPlanned);
        //dd($auditsTotal);
        //dd($compare);
		
        return view("audit.detail", ["dates"            => $dates,
                                     "device"           => $device,
                                     "compare"          => $compare, 
                                     "auditsTotal"      => $auditsTotal, 
                                     "auditsPlanned"    => $auditsPlanned,
                                     "auditsConducted"  => $auditsConducted,
                                     "auditsConductedByUser" => $auditsConductedByUser]);

    }

    public function analyticsUser($user_id){

        $user = $user = User::find($user_id);
        $audits = Audit_result::where("auditor_id", $user_id)->whereRaw("MONTH(audit_date) = MONTH(NOW())")->get();
        foreach($audits as $audit){
            $audit->answers = json_decode($audit->audit_json);

        }
        $answersCount = $this->countAnswers($audits);
        return view("audit.user", ["answersCount" => $answersCount, "audits" => $audits, "user" => $user]);

    }


    public function analyticsAudit($device_id, $audit_id){

        $device = Device::where("owen_id", $device_id)->first();
        $audit = Audit::find($audit_id);
        $audits = Audit_result::where("audit_id", $audit_id)->whereRaw("MONTH(audit_date) = MONTH(NOW())")->get();
        foreach($audits as $audit){
            $audit->result = json_decode($audit->audit_json);
        }
        $audits = $audits->groupBy("auditor_id");
        
        return view("audit.type", ["device" => $device, "audit" => $audit, "audits" => $audits]);
    }

    public function countAnswers($audits){

        $data = [];
        foreach($audits as $audit){

            foreach(json_decode($audit->audit_json) as $answer){
                 if(!isset($data[$answer->question_id][$answer->answer])){
                    $data[$answer->question_id][$answer->answer] = 1;
                }else{    
                    ++$data[$answer->question_id][$answer->answer]; 
                }
            }

        }

        return $data;  

    }

    public function director(){

        $districts = District::with('devices')->get();
        foreach($districts as $district){
            
            foreach($district->devices as $device){

                $device->audits = Audit_result::where('object_id', $device->owen_id)->get()->groupBy('audit_id');

            }

        }
        // /dd($districts);
        return view('audit.director', ["districts" => $districts]);
    }
}