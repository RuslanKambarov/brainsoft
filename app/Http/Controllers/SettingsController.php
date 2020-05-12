<?php

namespace App\Http\Controllers;

use DB;
use Cloud;
use App\Device;
use App\District;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(){
        $devices = Device::select('owen_id', 'controller', 'name', 'coal_reserve', 'district_id', 'required_t', 'required_p')->get();
        $districts = District::with('devices')->get();
        $settings = DB::table("app_settings")->get();
        return view('settings', ['devices' => $devices, 'districts' => $districts , 'settings' => $settings]);
    }

    public function update(Request $request, $id){
        if($request->target == "device"){
            $device = Device::where('owen_id', $id)->first();
            foreach($request->device as $key => $setting){
                $device->$key = $setting;
            }
            $device->save();
        }
        if($request->target == "district"){
            $district = District::where('owen_id', $id)->first();
            foreach($request->district as $key => $setting){
                $district->$key = $setting;
            }
            $district->save();            
        }
        if($request->target == "setting"){            
            $setting = DB::table("app_settings")->where('id', $id)->update(["value" => $request->setting['value']]);
        }
        return response()->json($id);
    }

    public function create(Request $request){

        if($request->target == "district"){
            $district = new District;
            $district->name = $request->name;
            $district->owen_id = $request->owen_id;
            $district->parent_id = $request->parent;
            $district->save();
        }
        if($request->target == "device"){
            
            $token = Cloud::getToken();
            
            foreach($request->devices as $device){
                $new_device = new Device;
                //$owen = collect(Cloud::getContent(Cloud::request("v1/device-management/register", [])));
                $new_device->name = $device['name'];
                $new_device->owen_id = $device['owen_id'];
                $new_device->district_id = $device['district_id'];
                $new_device->save();
            }
        }

    }
}
