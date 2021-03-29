<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Cloud;
use App\Device;
use App\District;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class SettingsController extends Controller
{
    public function index(){
        $user = Auth::user();
        if(!$user->hasAnyRole(3)){ return abort(403, "Эта страница доступна только администраторам"); }        
        $devices = Device::select('owen_id', 'controller', 'name', 'coal_reserve', 'district_id', 'required_t', 'required_p')->get();
        $districts = District::with('devices')->get();
        $settings = DB::table("app_settings")->get();
        return view('settings', ['devices' => $devices, 'districts' => $districts , 'settings' => $settings]);
    }

    public function update(Request $request, $id){
        if($request->target == "device"){            
            $device = Device::find($id);
            $device->{$request->param_name} = $request->param_value;
            $device->save();
        }
        if($request->target == "district"){
            $district = District::find($id);
            $district->{$request->param_name} = $request->param_value;
            $district->save();            
        }
        if($request->target == "setting"){            
            $setting = DB::table("app_settings")->where('id', $id)->update(["value" => $request->setting['value']]);
        }
        return response()->json(["type" => "success", "text" => "Сохранено"]);
    }

    public function create(Request $request){

        if($request->target == "district"){
            $district = new District;
            $district->name = $request->name;
            $district->save();
        }
        if($request->target == "device"){
            
            $response = [];

            foreach($request->devices as $device){
                
                $new_device = new Device;
                
                // $request_body = [
                //         "identifier"            => $device['identifier'], 
                //         "address"               => "16", 
                //         "device_type_id"        => "141", 
                //         "name"                  => $device['name'], 
                //         "categories_list"       => [$device['district_id']],
                //         "time_zone"             => "360",
                //         "archive_storage_time"  => "90"
                // ];                

                $new_device->name = $device['name'];
                $new_device->district_id = $device['district_id'];
                
                if($new_device->save()){
                    $response[] = "Объект добавлен";
                }else{
                    $response[] = "Ошибка";
                }                
            }

            return response()->json($response);
        }

    }
}
