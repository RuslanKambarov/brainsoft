<?php

namespace App;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Consumption extends Model
{
    public $table = 'consumption';

    public $fillable = ["created_at"];
    
    public static function checkLastConsumeTime($object_id){

        $lastConsumeTime = self::where("object_id", $object_id)->latest()->first()->created_at ?? null;
        if($lastConsumeTime){
            return Carbon::parse($lastConsumeTime)->addDays(1) > Carbon::now();
        }else{
            return false;
        }

    }
    
    public static function getLastConsume($object_id){
    	return self::where("object_id", $object_id)->latest()->first();
    }

    public static function getDistrictConsumption($district_id, $date){
        return DB::table('objects')
        ->select("users.name as user_name", "objects.name as object_name", "consumption.created_at as created_at", "consumption.*")
        ->leftJoin("user_objects", "object_id", "=", "objects.id")
        ->leftJoin("users", "user_id", "=", "users.id")
        ->leftJoin("consumption", function($join) use ($date){
            $join->on("owen_id", "=", "consumption.object_id")
                 ->on(DB::raw("MONTH(consumption.created_at)"), "=", DB::raw("MONTH('$date')"));
        })
        ->where("objects.district_id", "=", $district_id)
        ->get();        
    }

    public static function getDistrictMonthConsumption($district_id, $date){
        return DB::table('objects')
        ->select(DB::raw("objects.name as object_name, users.name as user_name, SUM(income) as income, SUM(consumption) as consumption, (CASE WHEN DAY(LAST_DAY('$date')) - 3 > COUNT(consumption) THEN 1 ELSE 0 END) AS input, '$date' as date"))
        ->leftJoin("user_objects", "object_id", "=", "objects.id")
        ->leftJoin("users", "user_id", "=", "users.id")
        ->leftJoin("consumption", function($join) use ($date){
            $join->on("owen_id", "=", "consumption.object_id")
                 ->on(DB::raw("MONTH(consumption.created_at)"), "=", DB::raw("MONTH('$date')"));
        })
        ->where("objects.district_id", "=", $district_id)
        ->groupBy("objects.name", "users.name")
        ->get();            
    }


    //====              Month consumption analytics   ========// 
    public static function getConsumptionAnalytics($district_id, $date){

        $consumption_analytics = [];

        $date = explode("(", $date)[0];          //get rid of "(timezone)"
        $month = \Carbon\Carbon::parse($date);   //create a Carbon instance from recieved date

        $start = $month->startOfMonth();         //create first day of the month Carbon instance   
        $end = clone $month;                     //clone Carbon instance   
        $end->addMonths(1)->subDays(1);          //make last day instance  

        $period  = \Carbon\CarbonPeriod::create($start, $end); // create period - collection of Carbon days

        $query_data = self::getDistrictConsumption($district_id, $month); //get data from database
        
        //sort data
        $query_data = $query_data->sortBy(function($device){
            if(stristr($device->object_name, 'ДС')){
                return 1;
            }elseif(stristr($device->object_name, 'СШ')){
                return 2;
            }elseif(stristr($device->object_name, 'ОШ')){
                return 3;
            }elseif(stristr($device->object_name, 'НШ')){
                return 4;
            }elseif(stristr($device->object_name, 'ДК')){
                return 5;
            }else{
                return 6;
            } 
        });

        $query_data = $query_data->groupBy("user_name");        
        $district_total = [];

        foreach($query_data as $user_name => $coll){
            $coll = $coll->groupBy("object_name");
            $engineer_total = [];
            foreach($coll as $object_name => $coll2){
                $month_total = array("income" => 0, "consumption" => 0, "input" => 0);                                
                foreach($period as $day){
                    $formated = $day->format("Y-m-d");
                    if($user_name == ""){
                        $user_name = "Не назначен";
                    }
                    $consumption_analytics[$user_name][$object_name]["Всего"] = $month_total;

                    //take record with required date and object
                    $parameters = $coll2->first(function($value) use ($formated, $object_name){
                        return \Carbon\Carbon::create($value->created_at)->format('Y-m-d') == $formated AND $value->object_name == $object_name;
                    });

                    //float result
                    $income = round($parameters->income ?? 0, 2);
                    $consumption = round($parameters->consumption ?? 0, 2);

                    $consumption_analytics[$user_name][$object_name][$formated] = array("income" => $income, "consumption" => $consumption);

                    
                    if(!isset($engineer_total[$formated])){
                        $engineer_total[$formated] = array("income" => 0, "consumption" => 0, "input" => 0);
                    }
                    if(!isset($district_total[$formated])){
                        $district_total[$formated] = array("income" => 0, "consumption" => 0, "input" => 0);
                    }
                    if($parameters === null){
                        //single date
                        $consumption_analytics[$user_name][$object_name][$formated]["input"] = 0;
                    }else{
                        //single date
                        $consumption_analytics[$user_name][$object_name][$formated]["input"] = 1;
                        //month total
                        $month_total["income"] += $income;
                        $month_total["consumption"] += $consumption;
                        $month_total["input"]++;                            
                        //engineer total
                        $engineer_total[$formated]["income"] += $income;
                        $engineer_total[$formated]["consumption"] += $consumption;
                        $engineer_total[$formated]["input"]++;
                        //district total
                        $district_total[$formated]["income"] += $income;
                        $district_total[$formated]["consumption"] += $consumption;
                        $district_total[$formated]["input"]++;
                    }                         

                }
                // dump($month_total);
                // dump($consumption_analytics[$user_name][$object_name]["Всего"]);                    
                if($consumption_analytics[$user_name][$object_name]["Всего"]["input"] > (count($period) - 3)){
                    $consumption_analytics[$user_name][$object_name]["Всего"]["input"] = 0;
                }else{
                    $consumption_analytics[$user_name][$object_name]["Всего"]["input"] = 1;
                }
                $consumption_analytics[$user_name][$object_name]["Всего"]["balance"] = $consumption_analytics[$user_name][$object_name]["Всего"]["income"] - $consumption_analytics[$user_name][$object_name]["Всего"]["consumption"];
                // dump($month_total);
                // dump($consumption_analytics[$user_name][$object_name]["Всего"]);                    
            }                
            $consumption_analytics[$user_name]["Всего"] = $engineer_total;
            $consumption = array_reduce($consumption_analytics[$user_name]["Всего"], function($carry, $item){
                $carry += $item["consumption"];
                return $carry;
            });
            $income = array_reduce($consumption_analytics[$user_name]["Всего"], function($carry, $item){
                $carry += $item["income"];
                return $carry;
            });
            $input = array_reduce($consumption_analytics[$user_name], function($carry, $item){
                if(isset($item["Всего"])) $carry += $item["Всего"]["input"];
                return $carry;
            });
            $consumption_analytics[$user_name]["Всего"]["Всего"] = array("income" => $income, "consumption" => $consumption, "input" => $input, "balance" => $income - $consumption);
            $pop = array_pop($consumption_analytics[$user_name]["Всего"]);
            $consumption_analytics[$user_name]["Всего"] = array("Всего" => $pop) + $consumption_analytics[$user_name]["Всего"];

        }
        $consumption_analytics["Всего по району"]["Всего"] = $district_total;
        $consumption = array_reduce($district_total, function($carry, $item){
            $carry += $item["consumption"];
            return $carry;
        });
        $income = array_reduce($district_total, function($carry, $item){
            $carry += $item["income"];
            return $carry;
        });
        $input = array_reduce($district_total, function($carry, $item){
            $carry += $item["input"];
            return $carry;
        });

        $consumption_analytics["Всего по району"]["Всего"]["Всего"] = array("income" => $income, "consumption" => $consumption, "input" => $input, "balance" => $income - $consumption);
        $pop = array_pop($consumption_analytics["Всего по району"]["Всего"]);
        $consumption_analytics["Всего по району"]["Всего"] = array("Всего" => $pop) + $consumption_analytics["Всего по району"]["Всего"];

        foreach($period as $day){
            $days[] = $day->format('Y-m-d');             
        }
        //dd($consumption_analytics);
        return  ["consumption_analytics" => $consumption_analytics, "period" => $days];
    }


    //====              Season consumption analytics   ========// 
    public static function getConsumptionSeasonAnalytics($district_id){

        $data = [];
        $dates = [];
        $period = [];

        $dates[] = new \Carbon\Carbon("2020-09");
        $dates[] = new \Carbon\Carbon("2020-10");
        $dates[] = new \Carbon\Carbon("2020-11");
        $dates[] = new \Carbon\Carbon("2020-12");
        $dates[] = new \Carbon\Carbon("2020-01");
        $dates[] = new \Carbon\Carbon("2020-02");
        $dates[] = new \Carbon\Carbon("2020-03");
        $dates[] = new \Carbon\Carbon("2020-04");
        $dates[] = new \Carbon\Carbon("2020-05");

        foreach($dates as $date){
            if(isset($collection)){
                $collection = $collection->merge(Consumption::getDistrictMonthConsumption($district_id, $date));
            }else{
                $collection = Consumption::getDistrictMonthConsumption($district_id, $date);
            }
            $period[] = $date->isoFormat("MMMM");                
        }        

        $users = $collection->groupBy('user_name');

        $consumption_analytics = [];
        $district_total = [];

        foreach($users as $key => $row){
            
            $engineer_total = [];
            $objects = $row->groupBy('object_name');

            foreach($objects as $key1 => $row1){
                if($key == ""){
                    $key = "Не назначен";
                }
                $consumption_analytics[$key][$key1]["Всего"] = array("income" => 0, "consumption" => 0, "input" => 0, "balance" => 0);
                

                foreach($row1 as $key2 => $row2){

                    $formated = \Carbon\Carbon::create($row2->date)->isoFormat("MMMM");

                    $income      = round($row2->income, 2) ?? 0;
                    $consumption = round($row2->consumption, 2) ?? 0;
                    $input       = $row2->input ?? 0;

                    $consumption_analytics[$key][$key1][$formated] = array("income" => $income, "consumption" => $consumption, "input" => $input);

                    if(!isset($engineer_total[$formated])){
                        $engineer_total[$formated] = array("income" => 0, "consumption" => 0, "input" => 0);
                    }
                    if(!isset($district_total[$formated])){
                        $district_total[$formated] = array("income" => 0, "consumption" => 0, "input" => 0);
                    }

                    //object total
                    $consumption_analytics[$key][$key1]["Всего"]["income"] += $income;
                    $consumption_analytics[$key][$key1]["Всего"]["consumption"] += $consumption;
                    $consumption_analytics[$key][$key1]["Всего"]["input"] += $input;
                    $consumption_analytics[$key][$key1]["Всего"]["balance"] += $income - $consumption; 
                    //engineer total
                    $engineer_total[$formated]["income"] += $income;
                    $engineer_total[$formated]["consumption"] += $consumption;
                    $engineer_total[$formated]["input"] += $input;
                    //district_total
                    $district_total[$formated]["income"] += $income;
                    $district_total[$formated]["consumption"] += $consumption;
                    $district_total[$formated]["input"] += $input; 
                }
            }
            $consumption_analytics[$key]["Всего"] = $engineer_total;
            $consumption = array_reduce($consumption_analytics[$key]["Всего"], function($carry, $item){
                $carry += $item["consumption"];
                return $carry;
            });
            $income = array_reduce($consumption_analytics[$key]["Всего"], function($carry, $item){
                $carry += $item["income"];
                return $carry;
            });
            $input = array_reduce($consumption_analytics[$key], function($carry, $item){
                if(isset($item["Всего"])) $carry += $item["Всего"]["input"];
                return $carry;
            });
            $consumption_analytics[$key]["Всего"]["Всего"] = array("income" => $income, "consumption" => $consumption, "input" => $input);
            $pop = array_pop($consumption_analytics[$key]["Всего"]);
            $consumption_analytics[$key]["Всего"] = array("Всего" => $pop) + $consumption_analytics[$key]["Всего"];
     
        }
        $consumption_analytics["Всего по району"]["Всего"] = $district_total;
        $consumption = array_reduce($district_total, function($carry, $item){
            $carry += $item["consumption"];
            return $carry;
        });
        $income = array_reduce($district_total, function($carry, $item){
            $carry += $item["income"];
            return $carry;
        });
        $input = array_reduce($district_total, function($carry, $item){
            $carry += $item["input"];
            return $carry;
        });
        $consumption_analytics["Всего по району"]["Всего"]["Всего"] = array("income" => $income, "consumption" => $consumption, "input" => $input);
        $pop = array_pop($consumption_analytics["Всего по району"]["Всего"]);
        $consumption_analytics["Всего по району"]["Всего"] = array("Всего" => $pop) + $consumption_analytics["Всего по району"]["Всего"];

        return  ["consumption_analytics" => $consumption_analytics, "period" => $period];
    }
}