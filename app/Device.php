<?php

namespace App;

use DB;
use Cloud;
use App\Consumption;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    public $table = "objects";

    protected $appends = ['audit_ids'];

    public function audits()
    {
        return $this->belongsToMany("App\Audit", "object_audit", "object_id", "audit_id");
    }

    public function getAuditIdsAttribute()
    {
        return $this->audits->pluck('id');
    }

    public function getEngineerName(){
        return $this->belongsToMany("App\User", "user_objects", "object_id", "user_id")->first()->name ?? "Не назначен";
    }

    public function getEngineer(){
        return $this->belongsToMany("App\User", "user_objects", "object_id", "user_id")->first();
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

    public function getLastData(){
        return DB::table('last_data')->where("object_id", $this->id)->first();
    }

    public function getObjectRequiredTemp(){
        return $this->required_t ?? DB::table('app_settings')->find(5)->value();
    }

    public function getObjectRequiredPressure(){
        return $this->required_p ?? DB::table('app_settings')->find(6)->value();
    }

    public function getObjectTotalIncome(){
        $incomed = DB::select(DB::raw("SELECT SUM(income) as incomed FROM consumption WHERE object_id = $this->id"))[0]->incomed ?? null;
        return $incomed;        
    }

    public function getObjectTotalConsume(){
        $consumed = DB::select(DB::raw("SELECT SUM(consumption) as consumed FROM consumption WHERE object_id = $this->id"))[0]->consumed ?? null;
        return $consumed;        
    }

    public function getConsumption(){       
        $consumption = Consumption::where('object_id', $this->id)->latest()->first();
        $this->device_id 	 = $this->id;        
        $this->income 		 = $this->getObjectTotalIncome() ?? 0;
        $this->consumption   = $this->getObjectTotalConsume() ?? 0;
        unset($this->owen_id, $this->id);        
        $this->balance 		 = $this->income - $this->consumption;
        $this->status 		 = $this->balance > $this->consumption * 14;
        return $this;
    }
}
