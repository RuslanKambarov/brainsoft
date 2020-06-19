<?php

namespace App;

use Cloud;
use App\Consumption;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    public $table = "objects";

    public function getEngineer(){
        return $this->belongsToMany("App\User", "user_objects", "object_id", "user_id")->first()->name ?? "Не назначен";
    }

    public function getEngineerId(){
        return $this->belongsToMany("App\User", "user_objects", "object_id", "user_id")->first()->id ?? 0;
    }

    public function getParameters($token){
        
        $client = new \GuzzleHttp\Client();		
        $response = $client->post("https://api.owencloud.ru/v1/device/".$this->owen_id, [
            'headers' => [
                'Content-Type'  => 'application/x-www-form-urlencoded',
                "Content-Length" => "68",
                "Accept"         => "*/*",
                "Authorization" => 'Bearer '.$token
            ],
            'body' => json_encode([])
        ]);
        
        return json_decode($response->getBody()->getContents());		
    }

    public function getObjectRequiredTemp(){
        return $this->required_t ?? DB::table('app_settings')->find(5)->value();
    }

    public function getObjectRequiredPressure(){
        return $this->required_p ?? DB::table('app_settings')->find(6)->value();
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
