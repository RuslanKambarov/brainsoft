<?php

namespace App;

use DB;
use App\Device;
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

    public static function getDistrictTotalConsumption($district_id){
        return DB::table('objects')
        ->select("users.name as user_name", "objects.coal_reserve", "objects.abbreviation", "objects.name as object_name", "consumption.created_at as created_at", "consumption.*")
        ->leftJoin("user_objects", "object_id", "=", "objects.id")
        ->leftJoin("users", "user_id", "=", "users.id")
        ->leftJoin("consumption", function($join){
            $join->on("objects.id", "=", "consumption.object_id");
        })
        ->where("objects.district_id", "=", $district_id)
        ->get();        
    }

    public static function getDistrictConsumption($district_id, $date){
        return DB::table('objects')
        ->select("users.name as user_name", "objects.coal_reserve", "objects.abbreviation", "objects.name as object_name", "consumption.created_at as created_at", "consumption.*")
        ->leftJoin("user_objects", "object_id", "=", "objects.id")
        ->leftJoin("users", "user_id", "=", "users.id")
        ->leftJoin("consumption", function($join) use ($date){
            $join->on("objects.id", "=", "consumption.object_id")
                 ->on(DB::raw("MONTH(consumption.created_at)"), "=", DB::raw("MONTH('$date')"));
        })
        ->where("objects.district_id", "=", $district_id)
        ->get();        
    }

    public static function getDistrictMonthConsumption($district_id, $date){
        return DB::table('objects')
        ->select(DB::raw("objects.name as object_name, users.name as user_name, objects.coal_reserve, objects.abbreviation, SUM(income) as income, SUM(consumption) as consumption, (CASE WHEN DAY(LAST_DAY('$date')) - 3 > COUNT(consumption) THEN 1 ELSE 0 END) AS input, '$date' as date"))
        ->leftJoin("user_objects", "object_id", "=", "objects.id")
        ->leftJoin("users", "user_id", "=", "users.id")
        ->leftJoin("consumption", function($join) use ($date){
            $join->on("objects.id", "=", "consumption.object_id")
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

        //dd($previous_month_balance);
        
        $query_data = $query_data->groupBy("user_name");        

        $district_total = [];
        $reserve["Всего по району"] = 0;
        
        foreach($query_data as $user_name => $coll){
            if($user_name == ""){
                $user_name = "Не назначен";
            }
            $coll = $coll->groupBy("object_name");
            $engineer_total = [];
            $reserve[$user_name] = 0;
            foreach($coll as $object_name => $coll2){
                $reserve[$object_name] = $coll2[0]->coal_reserve ?? 0;
                $abbreviation[$object_name] = $coll2[0]->abbreviation ?? "";
                $reserve[$user_name] += $reserve[$object_name];
                $reserve["Всего по району"] += $reserve[$object_name];
                $month_total = array("income" => 0, "consumption" => 0, "input" => 0);                                
                
                foreach($period as $day){
                    $formated = $day->format("Y-m-d");

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

                $consumption_analytics[$user_name][$object_name]["Всего"]["balance"] = $obj_balance = Device::where("name", $object_name)->first()->getConsumption()->balance;
                $iid = Device::where("name", $object_name)->first()->id;
                $consumption_analytics[$user_name][$object_name]["Всего"]["logist"] = $log_balance = self::objectMonthTotalLogis($iid, $month, "logist");
                //$consumption_analytics[$user_name][$object_name]["Всего"]["diff"] = 
            }                
            $consumption_analytics[$user_name]["Всего"] = $engineer_total;

            
            $logist = round(array_reduce($consumption_analytics[$user_name], function($carry, $item){
                if(isset($item['Всего'])){
                    $carry += $item['Всего']['logist'];
                }
                return $carry;
            }), 2);

            $consumption = array_reduce($consumption_analytics[$user_name]["Всего"], function($carry, $item){
                $carry += $item["consumption"];
                return $carry;
            });
            $income = array_reduce($consumption_analytics[$user_name]["Всего"], function($carry, $item){
                $carry += $item["income"];
                return $carry;
            });
            $balance = array_reduce($consumption_analytics[$user_name], function($carry, $item){                
                if(isset($item["Всего"])){
                    $carry += $item["Всего"]["balance"];
                }
                return $carry;
            });
            $input = array_reduce($consumption_analytics[$user_name], function($carry, $item){
                if(isset($item["Всего"])) $carry += $item["Всего"]["input"];
                return $carry;
            });

            $consumption_analytics[$user_name]["Всего"]["Всего"] = array("logist" => $logist,
                                                                         "income" => $income, 
                                                                         "consumption" => $consumption, 
                                                                         "input" => $input, 
                                                                         "balance" => $balance);
            
            $pop = array_pop($consumption_analytics[$user_name]["Всего"]);
            $consumption_analytics[$user_name]["Всего"] = array("Всего" => $pop) + $consumption_analytics[$user_name]["Всего"];

        }

        $logist = round(array_reduce($consumption_analytics, function($carry, $item){
            $carry += $item['Всего']['Всего']['logist'];
            return $carry;
        }), 2);

        $consumption_analytics["Всего по району"]["Всего"] = $district_total;

        
        $consumption = array_reduce($district_total, function($carry, $item){
            $carry += $item["consumption"];
            return $carry;
        });
        
        $income = array_reduce($district_total, function($carry, $item){
            $carry += $item["income"];
            return $carry;
        });
        $balance = array_reduce($consumption_analytics, function($carry, $item){                

            if(isset($item["Всего"]["Всего"])){
                $carry += $item["Всего"]["Всего"]["balance"];
            }

            return $carry;
        });
        $input = array_reduce($district_total, function($carry, $item){
            $carry += $item["input"];
            return $carry;
        });

        $consumption_analytics["Всего по району"]["Всего"]["Всего"] = array("income" => $income,
                                                                            "logist" => $logist,
                                                                            "consumption" => $consumption, 
                                                                            "input" => $input, 
                                                                            "balance" => $balance);
        
        $pop = array_pop($consumption_analytics["Всего по району"]["Всего"]);
        $consumption_analytics["Всего по району"]["Всего"] = array("Всего" => $pop) + $consumption_analytics["Всего по району"]["Всего"];

        foreach($period as $day){
            $days[] = $day->format('Y-m-d');             
        }
        //dd($reserve);
        //dd($consumption_analytics["Всего по району"]);
        return  ["consumption_analytics" => $consumption_analytics, "period" => $days, "reserve" => $reserve, "abbreviation" => $abbreviation];
    }


    //====      Season consumption analytics   ========// 
    public static function getConsumptionSeasonAnalytics($district_id){

        $data = [];
        $dates = [];
        $period = [];
        $reserve = [];

        $dates[] = new \Carbon\Carbon("2020-08");
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

        $reserve["Всего по району"] = 0;
        foreach($users as $key => $row){

            if($key == ""){
                $key = "Не назначен";
            }

            $engineer_total = [];
            $reserve[$key] = 0;
            $objects = $row->groupBy('object_name');

            foreach($objects as $key1 => $row1){
                
                $reserve[$key1] = $row1[0]->coal_reserve ?? 0;
                $abbreviation[$key1] = $row1[0]->abbreviation ?? "";
                $reserve[$key] += $reserve[$key1];
                $reserve["Всего по району"] += $reserve[$key1];
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
            $consumption_analytics[$key]["Всего"]["Всего"] = array("income" => $income, "consumption" => $consumption, "input" => $input, "balance" => $income - $consumption);
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
        $consumption_analytics["Всего по району"]["Всего"]["Всего"] = array("income" => $income, "consumption" => $consumption, "input" => $input, "balance" => $income - $consumption);
        $pop = array_pop($consumption_analytics["Всего по району"]["Всего"]);
        $consumption_analytics["Всего по району"]["Всего"] = array("Всего" => $pop) + $consumption_analytics["Всего по району"]["Всего"];
        
        return  ["consumption_analytics" => $consumption_analytics, "period" => $period, "reserve" => $reserve, "abbreviation" => $abbreviation];
    }


    public static function getLogistData($district, $month = null){
        
        if($month){
            $date = explode("(", $month)[0];
        }else{
            $date = date("Y-m-d");                             
        }
        
        $month = \Carbon\Carbon::parse($date);
        $start = $month->startOfMonth();         //create first day of the month Carbon instance   
        $end = clone $month;                     //clone Carbon instance   
        $end->addMonths(1)->subDays(1);          //make last day instance  


        $period  = \Carbon\CarbonPeriod::create($start, $end); // create period - collection of Carbon days
        
        $result = DB::table("objects")
        ->select("name", "amount", "label", "date", "objects.id as object_id", "logist.id as record_id")
        ->leftJoin("logist", function($join) use ($month){
            $join->on("objects.id", "=", "object_id")
                 ->on(DB::raw("MONTH(logist.date)"), "=", DB::raw("MONTH('$month')"));
        })
        ->where("district_id", $district)
        ->get();

        $plan_result = DB::table("objects")
        ->select("name", "amount", "label", "date", "objects.id as object_id", "logist_plan.id as record_id")
        ->leftJoin("logist_plan", function($join) use ($month){
            $join->on("objects.id", "=", "object_id")
                 ->on(DB::raw("MONTH(logist_plan.date)"), "=", DB::raw("MONTH('$month')"));
        })
        ->where("district_id", $district)
        ->get();
        
        $coal_labels = DB::table("labels")->get()->pluck("id");
        //$coal_labels = [1, 2, 3, 4, 5, 6];
        
        $source = $result->groupBy("name");        

        $data = [];
        $total_per_day_fact = [];
        $total_per_day_plan = [];        
        foreach($source as $name => $object){
            
            $array = [];
        
            foreach($period as $day){

                $dbFormat = $day->format("Y-m-d H:i:s");
                $formated = $day->isoFormat("Do MMM");                                
                                      
                $day_planned_data = $plan_result->where("object_id", $object[0]->object_id)
                                                ->where("date", $dbFormat)
                                                ->where("record_id", "!=", null)
                                                ->except("name", "owen_id");

                $day_data = $object->where("date", "=", $dbFormat)->except("name", "object_id");
                
                if(isset($total_per_day_fact[$formated])){
                    $total_per_day_fact[$formated] += $day_data->sum("amount");
                }else{
                    $total_per_day_fact[$formated] = $day_data->sum("amount");
                }
                
                if(isset($total_per_day_plan[$formated])){
                    $total_per_day_plan[$formated] += $day_planned_data->sum("amount");
                }else{
                    $total_per_day_plan[$formated] = $day_planned_data->sum("amount");
                }                
                $day_data = array_values($day_data->toArray());
                $day_planned_data = $day_planned_data->toArray();
                $array[] = array(
                    "iso"       => $formated, 
                    "db"        => $dbFormat, 
                    "data"      => $day_data, 
                    "object_id" => $object[0]->object_id,
                    "plan"      => $day_planned_data
                );                

            }
 
            $total = array("plan" => [], "fact" => []);
 
            foreach($coal_labels as $label){
                $total["fact"][$label] = $result->where("name", $name)->where("label", $label)->sum("amount");
                $total["plan"][$label] = $plan_result->where("name", $name)->where("label", $label)->sum("amount");
            }
 
            $data[] = array("id" => $object[0]->object_id, "name" => $name, "data" => $array, "total" => $total);
        }
        $acc = 0;
        
        foreach($total_per_day_fact as $key => $day){
            $acc += $day;
            $total_per_day_fact[$key] = $acc;            
        }
        $acc = 0;
        foreach($total_per_day_plan as $key => $day){
            $acc += $day;
            $total_per_day_plan[$key] = $acc;            
        }
               
        return [$data, $total_per_day_fact, $total_per_day_plan];       
    }

    public static function objectMonthTotalLogis($object_id, $month, $table)
    {
        return (float)DB::table($table)
            ->select(DB::raw("SUM(amount) as sum"))
            ->where("object_id", $object_id)
            ->whereRaw("MONTH(date) = MONTH('$month')")
            ->first()->sum;
    }

}