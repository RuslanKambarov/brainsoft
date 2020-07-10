<?php

namespace App\Http\Controllers;

use Auth;
use App\Alert;
use Illuminate\Http\Request;

class AlarmController extends Controller
{
    public function index($start = null, $end = null){

        if(($start) && ($end)){
        
            $start = explode("(", $start)[0];        
            $start = \Carbon\Carbon::parse($start);
            $end = explode("(", $end)[0];
            $end = \Carbon\Carbon::parse($end);
    
            $meta["start"] = $start;
            $meta["end"] = $end;
            
            $currentAlarms = Alert::where('status', 0)
              ->whereRaw("created_at > '$start' AND created_at < '$end'")
              ->paginate(15);  
            $fixedAlarms = Alert::where('status', 1)
              ->whereRaw("created_at > '$start' AND created_at < '$end'")
              ->paginate(15);
        } else{
            $currentAlarms = Alert::where('status', 0)->paginate(15);
            $fixedAlarms = Alert::where('status', 1)->paginate(15);
        }

        return view("monitor", ["include" => "alarms", "currentAlarms" => $currentAlarms, "fixedAlarms" => $fixedAlarms,]);

    }

    public function closeAlarm($id){

        $alarm = Alert::find($id);
        $alarm->status = 1;
        $alarm->save();
        return "Авария успешно закрыта";

    }
}
