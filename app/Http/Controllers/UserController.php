<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Cloud;
use App\Role;
use App\User;
use App\Device;
use App\District;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(){
        $users = User::with('roles')->get();
        return view("monitor", ["include" => "users", "users" => $users]);                 
    }
    public function userInfo($id){
        $user = User::with('roles', 'districts', 'devices')->find($id);
        $roles = Role::all();
        return view("monitor", ["include" => "userinfo", "user" => $user, 'roles' => $roles]);

    }
    public function update(){
        
        $request = request();

	$user = User::find($request->user);
	if($request->role == "engineer"){
		if($request->districts){
		foreach($request->districts as $district){
                    $exist = DB::table("user_objects")->where([
                        ['user_id', $request->user],
                        ['district_id', $district]
                    ])->first();
                    if(!$exist){
                        DB::table("user_objects")->insert([
                            ['user_id' => $request->user, 'district_id' => $district]
                        ]);
                    }
                }
		}		
	}
	if($request->role == "fireman"){
		if($request->devices){
                foreach($request->devices as $device){
                    $exist = DB::table("user_objects")->where([
                        ['user_id', $request->user],
                        ['object_id', $device]
                    ])->first();
                    if(!$exist){
                        DB::table("user_objects")->insert([
                            ['user_id' => $request->user, 'object_id' => $device]
                        ]);
                    }
                }  
		}	
	}

	$user->role = $request->role;
	$user->save();
	
	return "Сохранено";

    }
    public function removeUser($user_id){
    	$user = User::find($id);
	$user_objcets = DB::table("user_objects")->where("user_id", $id)->get();
	$user_objects->delete();
	if($user->delete()){
		$message  = "Пользователь ".$user." удален";
	}else{
		$message  = "Пользователь ".$user."не был удален";
	}
	return $message;	
    }

    public function detachObject($user_id, $object_id){        
        $user = User::find($user_id);
        if($user->hasAnyRole(2)){
            $user->devices()->detach($object_id);    
        }else{
            $user->districts()->detach($object_id);
        }
        return redirect()->back();
    }

    public function attachObject($user_id, Request $request){
        $user = User::find($user_id);   
        $user->districts()->attach($request->districts);
        $user->devices()->attach($request->devices);
    }


    public function changeRole($user_id, Request $request){
        $user = User::find($user_id);
        $user->roles()->sync($request->roles);   
    }

    public function getNotAttachedObjects($user_id){
        $user = User::with('roles', 'districts')->find($user_id);
        $attachedDistricts = $user->districts->pluck('owen_id');
        $attachedDevices = $user->devices->pluck('owen_id');
        $notAttached = new \stdClass;
        $notAttached->districts = District::whereNotIn('owen_id', $attachedDistricts)->get();
        $notAttached->devices = Device::whereNotIn('owen_id', $attachedDevices)->get();
        return response()->json($notAttached);
    }
}
