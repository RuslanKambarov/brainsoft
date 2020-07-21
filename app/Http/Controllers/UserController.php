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
        $users = User::with('roles', 'districts')->get();
        foreach($users as $user){
            $user->role = implode(', ', $user->roles->pluck('name')->unique()->toArray());
            $user->district = $user->relationToDistrict();
        }
        $users = $users->groupBy('district');
        
        return view("monitor", ["include" => "users", "users" => $users]);                 
    }

    public function userInfo($id){
        $user = User::with('roles', 'districts', 'devices')->find($id);
        $roles = Role::all();
        return view("monitor", ["include" => "userinfo", "user" => $user, 'roles' => $roles]);

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

    public function profile(){
        return view("monitor", ['include' => 'profile']);        
    }

    public function changePassword(Request $request){
        
        $user = Auth::user();

        if(\Hash::check($request->old_password, $user->password)){
            $user->password = \Hash::make($request->new_password);
            $user->save();
            return view("monitor", ['include' => 'profile', 'class' => 'success', 'message' => "Пароль успешно сохранен"]);
        }else{
            return view("monitor", ['include' => 'profile', 'class' => 'danger', 'message' => "Не верный пароль"]);
        }                
    }

    public function deleteUser($user_id){
        $user = User::find($user_id);
        $user->districts()->detach();
        $user->devices()->detach();        
        $user->delete();
        return $this->index();        
    }
}
