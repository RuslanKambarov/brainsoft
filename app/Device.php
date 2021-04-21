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

    /*
    Total income for all time ex.
    */
    public function getObjectTotalIncome(){
        $incomed = DB::select(DB::raw("SELECT SUM(income) as incomed FROM consumption WHERE object_id = $this->id"))[0]->incomed ?? null;
        return $incomed;        
    }

    public function getObjectTotalIncomeByMonth($month){
        $incomed = DB::select(DB::raw("SELECT SUM(income) as incomed FROM consumption WHERE object_id = $this->id AND created_at <= '$month'"))[0]->incomed ?? null;
        return $incomed;        
    }

    /*
    Total consumption for all time ex.
    */
    public function getObjectTotalConsume(){
        $consumed = DB::select(DB::raw("SELECT SUM(consumption) as consumed FROM consumption WHERE object_id = $this->id"))[0]->consumed ?? null;
        return $consumed;        
    }

    public function getObjectTotalConsumeByMonth($month){
        $consumed = DB::select(DB::raw("SELECT SUM(consumption) as consumed FROM consumption WHERE object_id = $this->id AND created_at <= '$month'"))[0]->consumed ?? null;
        return $consumed;        
    }


    /**
     * Total consumption for month ex. from 01.03 to 31.03.
     * @param string $month javascript datetime string
     * @return float total coal consumed for month
     */
    public function getObjectMonthConsume($month){
        list($start, $end) = getStartEndMonth($month);
        $consumed = DB::select(DB::raw("SELECT SUM(consumption) as consumed FROM consumption WHERE object_id = $this->id AND created_at >= '$start' AND created_at <= '$end'"))[0]->consumed ?? null;
        return $consumed;
    }

    /*
    Total income for month ex. from 01.03 to 31.03
    */
    public function getObjectMonthIncome($month){
        list($start, $end) = getStartEndMonth($month);
        $consumed = DB::select(DB::raw("SELECT SUM(income) as incomed FROM consumption WHERE object_id = $this->id AND created_at >= '$start' AND created_at <= '$end'"))[0]->incomed ?? null;
        return $consumed;
    }

    public function isHasDisturbance($date){
        list($start, $end) = getStartEndMonth($date);
        $daysInMonth = count(getMonthDays($date));
        $minFilling = $daysInMonth - 3;
        $fills = DB::select(DB::raw("SELECT count(*) as fillings FROM consumption WHERE object_id = $this->id AND created_at >= '$start' AND created_at <= '$end'"))[0]->fillings ?? null;
        return $fills <= $minFilling ? true : false;
    }

    /*
    Method from API form mobile app Accounting/District/
    */
    public function getConsumptionByMonth($month){
        $this->device_id 	 = $this->id;        
        $this->income 		 = $this->getObjectTotalIncomeByMonth($month) ?? 0;
        $this->consumption   = $this->getObjectTotalConsumeByMonth($month) ?? 0;
        unset($this->owen_id, $this->id);        
        $this->balance 		 = $this->income - $this->consumption;
        $this->status 		 = $this->balance > $this->consumption * 14;
        return $this;
    }   

    /*
    Method from API form mobile app Accounting/District/Device/
    */
    public function getConsumption(){       
        $consumption = Consumption::where('object_id', $this->id)->latest()->first(); //unused
        $this->engineer      = $this->getEngineerName();
        $this->device_id 	 = $this->id;        
        $this->income 		 = $this->getObjectTotalIncome() ?? 0;
        $this->consumption   = $this->getObjectTotalConsume() ?? 0;
        unset($this->owen_id, $this->id);        
        $this->balance 		 = $this->income - $this->consumption;
        $this->status 		 = $this->balance > $this->consumption * 14;
        return $this;
    }
}
