<?php

namespace App;

use App\Consumption;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    public $table = "objects";

    public function getEngineer(){
        return $this->belongsToMany("App\User", "user_objects", "object_id", "user_id")->first()->name ?? "Не назначен";
    }

    public function getParameters(){

    }

    public function getConsumption(){       
        $consumption = Consumption::where('object_id', $this->owen_id)->latest()->first();
        $this->device_id 	 = $this->owen_id;
        unset($this->owen_id, $this->id);
        $this->income 		 = $consumption->income ?? 0;
        $this->consumption   = $consumption->consumption ?? 0;
        $this->balance 		 = $consumption->balance ?? 0;
        $this->status 		 = $this->balance > $this->consumption * 14;
        return $this;
    }
}
