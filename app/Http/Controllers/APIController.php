<?php

namespace App\Http\Controllers;

use DB;
use Log;
use Auth;
use Cloud;
use Storage;
use Session;
use App\User;
use App\Audit;
use App\Event;
use App\Device;
use App\District;
use App\Question;
use App\Consumption;
use App\Audit_result;
use Carbon\Carbon;	
use App\Objectcard;
use Illuminate\Http\Request;

class APIController extends Controller
{
     public function login(){

        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            $success = $user->createToken('MyApp')->accessToken;
            return response()->json(['token' => $success], 200);
        }else{
            return response()->json(['error' => 'Unauthorised'], 401);
        }

    }

    //User profile
    public function user() 
    { 
        $user = Auth::user();
				$user->user_id = $user->id;
				$user->role_name = 	$user->roles()->first()->name ?? "Не назначено";
				$user->role_id = 	$user->roles()->first()->id ?? 0;
				unset($user->id);
				unset($user->role); 
        return response()->json($user, 200); 
    } 

    //All Districs
    public function allDistricts(){
        $user = Auth::user();
        $districts = $user->getDistrictsTree();
        return response()->json($districts, 200);        
    }

    public function getParameters($id){
		$device = Device::where("owen_id", $id)->select("id", "owen_id as device_id", "name")->first();
		$event = Event::where('object_id', $id)->select("object_id", "outside_t", "direct_t", "back_t", "object_t", "pressure", "message")->latest()->first();
		$card = Objectcard::where([["object_id", $event->object_id], ["outside_t", $event->outside_t]])->select("object_id", "outside_t", "direct_t", "back_t")->first();
		if(!$card){
			$card = Objectcard::first();
		}
		$inside_temp = DB::table("insidetemps")->where("device_id", $id)->first()->value;
		$device->engineer = $device->getEngineer();
	
		$device->employee = "";
		$device->has_alert = true;
		$event->mode = $card;
		$event->mode->object_t = $inside_temp;
		$event->mode->pressure = 2;
		if($event->message == "offline"){
			$device->status = false;
			$device->power = false;
		}else{
			$device->power = true;
			$device->status = true;
		}
		$device->parameters = $event;
		unset($device->id);

		return response()->json($device);
    }

    public function districtDevices($id){
    	$user = Auth::user();
  		$devices = $user->getDevicesTree($id);
  		return response()->json($devices);
    }

	
	public function lastData($id){
		$end = Carbon::now();
		$start = Carbon::now()->subHours(5);
		$data = Event::where("object_id", $id)->latest()->limit(10)->get();
		$data = Event::where([["object_id", $id], ["created_at", ">", $start], ["created_at", "<", $end]])->groupBy(DB::raw("EXTRACT(HOUR FROM created_at), EXTRACT(DAY FROM created_at)"))->orderBy("created_at")->get();
		foreach($data as $row){
			if($row->message == "offline"){
				$row->status = false;
				$row->power = false;
			}else{
				$row->status = true;
				$row->power = true;
			}
			unset($row->message);
			unset($row->id);
			$card = Objectcard::where([["object_id", $id], ["outside_t", $row->outside_t]])->select("outside_t", "direct_t", "back_t")->first();
			if(!$card){
				$card = Objectcard::first();
			}
			$card->pressure = 2;
			$card->object_t = 22;
			unset($card->id);
			unset($card->object_id);
			$row->mode = $card;
		}
		return response()->json($data);
	}

	public function audits($device_id){
		$device = Device::where("owen_id", $device_id)->select("owen_id as device_id", "name")->first();
		$device->audits = Audit::select("id as audit_id", "name")->get();
		return response()->json($device);
	}

	public function getQuestions($device_id, $audit_id){

		$device = Device::where("owen_id", $device_id)->select('owen_id as device_id', 'name', 'district_id')->first();
		$device->auditor = Auth::user()->only("role_id", "name", "email");
		$device->questions = Question::where("audit_id", $audit_id)->select("id as question_id", "audit_id", "question", "photo")->get();
		return response()->json($device);

	}
	
		//Gets audit data from mobile app and save. Move files on directory storage/app/public/{device_id}
    public function saveResult(Request $request, $object_id, $audit_id){

		if(Audit_result::checkLastAuditTime($object_id)){
			return response()->json(["success" => false, "message" => "Аудит уже проводился"], 200);
		}	

		$array = [];
		
		foreach($request->questions as $key => $question){
			if(isset($question['photo'])){
				$storeFileName = microtime().".jpg";
				$data = base64_decode($question['photo']);
				$array[] = ["question_id" => $question['question_id'], "answer" => $question['answer'], "comment" => $question['comment'] ?? "", "photo" => $storeFileName];
				Storage::disk('local')->put("public/".$object_id."/".$storeFileName, $data);	
			}else{
				$array[] = ["question_id" => $question['question_id'], "answer" => $question['answer'], "comment" => $question['comment'] ?? ""];
			}
		}
        $result = new Audit_result;
        $result->object_id  = $object_id;
        $result->audit_date = date("Y-m-d H:i:S");
				$result->audit_id   = $audit_id;
				$result->auditor_id = Auth::id();
        $result->audit_json = json_encode($array);
        if($result->save()){
			return response()->json(["success" => true, "message" => "Аудит успешно завершен"], 200);
		}else{
			return response()->json(["success" => false, "message" => "Произошла ошибка"], 200);
		}

    }

    public function consumeView($id){
    	$device = Device::where('owen_id', $id)->select('owen_id as device_id','name')->first();
    	$device->engineer = "Пользователь";
    	$device->user = Auth::user()->only('name');
			return response()->json($device);
    }

    public function consumeCoal(Request $request, $id){

		if(Consumption::checkLastConsumeTime($id)){
			return response()->json(["success" => false, "message" => "Данные о расходе угля можно вносить только раз в сутки"]);
		}
			
		$lastConsumption = Consumption::getLastConsume($id);

		if(!$lastConsumption){
			$balance = 0;
		}else{
			$balance = $lastConsumption->balance;
		}

		$consume = new Consumption;
		$consume->object_id = $id;
		$consume->consumption = $request->consumption/1000;
		$consume->income = $request->income;
		$consume->balance = $consume->income + $balance - $consume->consumption; 
    	$consume->save();
		
		if($consume){
    		return response()->json(["success" => true, "message" => "Операция прошла успешно"]);
    	}else{
    		return response()->json(["success" => false, "message" => "Произошла ошибка"]);
    	}
    }

    public function allDistrictsConsumption(){
    	$districts = District::all();
    	
    	$districts->map(function($item){
    		return $item->getConsumption();
    	});
    	
    	return response()->json($districts);
    }

    public function districtConsumption($district_id){
    	$devices = Device::where('district_id', $district_id)->get();
    	
    	$devices->map(function($item){
    		return $item->getConsumption();
    	});

    	return $devices;
    }

    public function deviceConsumption($device_id){
    	$device = Device::where('owen_id', $device_id)->select("id", "owen_id", "name", "coal_reserve")->first()->getConsumption();
    	return $device;
    }

    public function takeToWork($device_id){
    	return response()->json(["success" => true, "message" => "Принято в работу"]);
    }
}
