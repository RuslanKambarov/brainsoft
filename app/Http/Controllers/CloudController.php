<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Cloud;
use App\User;
use App\Event;
use App\Device;
use App\District;
use App\Objectcard;
use Illuminate\Http\Request;

class CloudController extends Controller
{
    public function home(){

        $districts = collect([]);
        $user = Auth::user();

        /*  =========CLOUD STRUCTURE======= */

        // $owen = collect(Cloud::getContent(Cloud::request("v1/category/index", [])));
        // $root = $owen->filter(function($value){
        //     return $value->parent_id == 0;
        // })->first()->id;


        // if($user->hasAnyRole(3)){
        //     $districts = $owen->filter(function($value) use ($root){
        //         return $value->parent_id == $root;
        //     });
        // }

        // if($user->hasAnyRole(4) || $user->hasAnyRole(1)){
        //     $districts = $user->districts()->select('name', 'owen_id as id')->get();
        // }

        // if($user->hasAnyRole(2)){
        //     $devices = $user->devices()->select('name', 'owen_id as id')->get();
        //     return view("monitor", ["include" => "district", "devices" => $devices]);
        // }

        // foreach($districts as $district){
        //     $district->childs = $owen->filter(function($value) use ($district){
        //         return $value->parent_id == $district->id;
        //     });
        // }

        /* =========DATABASE STRUCTURE========== */

        if($user->hasAnyRole(3)){
            $districts = district::all();            
        }

        if($user->hasAnyRole(4) || $user->hasAnyRole(1)){
            $districts = $user->districts()->get();
        }

        if($user->hasAnyRole(2)){
            $devices = $user->devices()->select('name', 'owen_id as id')->get();
            foreach($devices as $device){
                $device->parameters = DB::table('last_data')->where("object_id", $device->owen_id)->first();            	
            }	
            $district = $user->districts()->first();
            if($district){
                $district->director = $district->director();
                $district->engineer = $district->manager();                            
            }
            return view("monitor", ["include" => "district", "devices" => $devices, "district" => $district]);
        }

        if($user->hasAnyRole(6)){
            return redirect('/consumption');
        }
        //dd($districts);
        return view("monitor", ["include" => "home", "districts" => $districts->sortBy('name')]);        
	
    }

    public function devicesByDistricts(Request $request){
        
        $devices = collect(Cloud::getContent(Cloud::request("v1/device/index", [])));
        $result = collect([]);
        foreach($request->districts as $district){
            $districts_devices = $devices->filter(function($value) use ($district){
                return $value->categories[0] == $district;
            });
            $result->push($districts_devices);
        }

        return response()->json($result);
    }

    public function district($id){

        /*  =========CLOUD STRUCTURE======= */

        // $district = collect(Cloud::getContent(Cloud::request("v1/category/index", [])));
        // $district = $district->filter(function($item) use ($id){
        //     return $item->id == $id;
        // });
        // $district = $district->first();
        // $owen = collect(Cloud::getContent(Cloud::request("v1/user-object/index", []))->devices);

        // $devices = $owen->filter(function($item) use ($id){
        //     return $item->categories[0] == $id;
        // });

        // $db_district = District::where("owen_id", $district->id)->orWhere('owen_id', $district->parent_id)->first();
        // $district->director = $db_district->director();
        // $district->engineer = $db_district->engineer();
        // foreach($devices as $device){
        //     if($device->status == "online"){
        //         $device->parameters = Event::where("object_id", $device->id)->latest()->first();
        //     }else{
        //         $device->parameters = null;
        //     }	
        // }	

        /* =========DATABASE STRUCTURE========== */

        $district = District::find($id);
        $district->director = $district->director();
        $district->engineer = $district->manager();

        $devices = $district->devices()->get();

        foreach($devices as $device){
            $device->parameters = $device->getLastData();            	
        }	
        
        $sorted = $devices->sortBy(function($device){
            if(stristr($device->name, 'ДС')){
                return 1;
            }elseif(stristr($device->name, 'СШ')){
                return 2;
            }elseif(stristr($device->name, 'ОШ')){
                return 3;
            }elseif(stristr($device->name, 'НШ')){
                return 4;
            }elseif(stristr($device->name, 'ДК')){
                return 5;
            }else{
                return 6;
            } 
        });

        return view("monitor", ["include" => "district", "devices" => $sorted, "district" => $district]);
    }

    public function device($id){
        $device = Device::find($id);
        $owen_device = [];
        if($device->controller === 1){
            $owen_device = Cloud::request("v1/device/".$device->owen_id, []);
            $owen_device = Cloud::getContent($owen_device);    
        }        
        $user = $device->getEngineerName();
        
        $district = District::where("id", $device->district_id)->first();	
        $temperature_card = Objectcard::where("object_id", $device->owen_id)->get();
        return view("monitor", ["include" => "device", "device" => $device, "district" => $district, "owen_device" => $owen_device, "temperature_card" => $temperature_card, "user" => $user]);
    }

    public function deviceUpdate($id, Request $request){

        $device = Device::where('owen_id', $id)->first();
        $device->required_t = $request->parameters['required_t'];
        $device->required_p = $request->parameters['required_p'];
        if($device->save()){
            return "Изменения внесены";
        }else{
            return "Не удалось сохранить изменения";
        }
    }


    public function deviceHistory($device_id, Request $request = null){

        if(isset($request->period)){
            $data = Event::where("object_id", $device_id)->latest()->limit(10)->groupBy(DB::raw("EXTRACT(HOUR FROM created_at), EXTRACT(DAY FROM created_at)"))->get();
        }else{
            $data = Event::where("object_id", $device_id)->latest()->limit(10)->get();
        }
        return view("monitor", ["include" => "history", "data" => $data]);
    }

    public function createTempCard(Request $request){
        
        $tempcard = new Objectcard;
        $tempcard->object_id = $request->parameters['id'];
        $tempcard->outside_t = $request->parameters['outside_t'];
        $tempcard->direct_t = $request->parameters['direct_t'];
        $tempcard->back_t = $request->parameters['back_t'];
        if($tempcard->save()){
            return "Сохранено";
        }else{
            return "Произошла ошибка";
        }

    }

    public function updateTempCard(Request $request){
        $card = Objectcard::find($request->id);
        $card->outside_t = $request->parameters['outside_t'];
        $card->direct_t = $request->parameters['direct_t'];
        $card->back_t = $request->parameters['back_t'];
        $card->save();
        return "Сохранена запись: наружняя ".$card->outside_t.", подача ".$card->direct_t.", обратка ".$card->back_t;
    }

    public function removeTempCard($id){
        $card = Objectcard::find($id);
        $card->delete();
        return "Удалена запись: наружняя ".$card->outside_t.", подача ".$card->direct_t.", обратка ".$card->back_t;
    }

    public function deviceConsumption($id){
        $consumption = DB::table('consumption')->where('object_id', $id)->first();
        $device = Device::find($id);
        $district = District::where("id", $device->district_id)->first();
        return view("monitor", ["include" => "consumption", "device" => $device, "district" => $district, "consumption" => $consumption]);
    }

    public function setIncome(Request $request, $id){
        $insert = DB::table('objects')->updateOrInsert(["id" => $id], ["coal_reserve" => $request->coal_reserve]);
        if($insert){
            $class = "success";
            $message = "Данные сохранены";
        }else{
            $class = "error";
            $message = "Произошла ошибка";
        }
        return redirect()->back()->with(["message" => $message, "class" => $class]);
    }

    public function usersActions(){
        $actions = DB::table("actions")->orderBy("time", "desc")->paginate(25);        
        return view("actions", ["actions" => $actions]);
    }
    
}
