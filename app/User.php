<?php

namespace App;

use DB;
use Cloud;
use App\Event;
use App\Device;
use App\District;
use App\Objectcard;
use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

	public function roles(){
		return $this->belongsToMany('App\Role', 'user_role', 'user_id', 'role_id');
	}

	public function districts(){
		return $this->belongsToMany('App\District', 'user_district', 'user_id', 'district_id');
	}

	public function devices(){
		return $this->belongsToMany('App\Device', 'user_objects', 'user_id', 'object_id');
	}

	public function hasAnyRoles($roles){
		return null !== $this->roles()->whereIn('role_id', $roles)->first();
	}

	public function hasAnyRole($role){
		return null !== $this->roles()->where('role_id', $role)->first();
	}

    public function relationToDistrict(){
        if($this->hasAnyRoles([1, 4])){
            return $this->districts()->first()->name ?? "Не назначено";
        }elseif($this->hasAnyRole(2)){
            $district_id = $this->devices()->first()->district_id ?? 0;
            return $districts = District::find($district_id)->name ?? "Не назначено"; 
        }else{
            return "Администраторы";
        }
    }

    public function getUserDistricts()
    {
        if($this->hasAnyRole(2)){
            $district_id = $this->devices()->groupBy("district_id")->first()->district_id ?? null;
            $districts = District::find($district_id)->get();
        }
        if($this->hasAnyRoles([1, 4])){
            $districts = $this->districts()->get();
        }
        if($this->hasAnyRole(3)){
            $districts = District::all();
        }

        return $districts;
    }
    
    public function getDistrictsTree(){

        $districts = $this->getUserDistricts();
		foreach($districts as $district){
    	    $district->district_id = $district->id;			
    	    $district->director = $district->director();
    	    $district->engineer = $district->manager();
            $district->devices_count = count($district->devices()->get()->toArray());
            $district->devices_with_controller = count($district->devices()->get()->where("controller", 1)->toArray());
    	    unset($district->id, $district->owen_id);
		}
		return $districts;
    }

    public function getDevicesTree($id){
        if($this->hasAnyRole(2)){
            $devices = $this->devices()->select("id", "owen_id", "name", "district_id", "required_t", "required_p")->where('controller', 1)->get();
        }
        if($this->hasAnyRoles([1, 3, 4])){          
            $devices = Device::where("district_id", $id)->select("id", "owen_id", "name", "district_id", "required_t", "required_p")->where('controller', 1)->get();
        }
        foreach($devices as $device){
            $device->engineer = $device->getEngineerName();
            $device->device_id = $device->id;
            $parameters = $device->getLastData();
            
            $mode = Objectcard::where([
                ["object_id", $device->device_id], 
                ["outside_t", $parameters->outside_t]
            ])->first();
            
            if(!$mode){
                $mode = Objectcard::first();
            }
            $mode->object_t = $device->required_t;
            $mode->pressure = $device->required_p;
            if(!$parameters->status){
                $device->status = false;
                $device->power = false;
            }else{
                $device->status = true;
                $device->power = true;
            }
            unset($parameters->id, 
                  $parameters->object_id, 
                  $parameters->status, 
                  $device->pivot, 
                  $device->id);
            
            $device->parameters = $parameters;
            $device->parameters->mode = $mode->only("outside_t", "direct_t", "back_t", "object_t", "pressure");
        }
        $devices = $devices->sortByDesc('status')->flatten();
        return $devices;
    }
  
}
