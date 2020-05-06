<?php

namespace App\Http\Controllers;

use Auth;
use App\Alert;
use Illuminate\Http\Request;

class AlarmController extends Controller
{
    public function index(){
        
        $currentAlarms = Alert::where('status', 0)->paginate(15);
        $fixedAlarms = Alert::where('status', 1)->paginate(15);
        return view("monitor", ["include" => "alarms", "currentAlarms" => $currentAlarms, "fixedAlarms" => $fixedAlarms,]);

    }

    public function closeAlarm($id){

        $alarm = Alert::find($id);
        $alarm->status = 1;
        $alarm->save();
        return "Авария успешно закрыта";

    }
}
