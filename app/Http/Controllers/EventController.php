<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request){
      $events = Event::latest()->paginate(20);	
      return view("monitor", ["include" => "events", "events" => $events]);
    }

    public function deviceEvents($id, $start = null, $end = null){
      
      
      $path = "/events/device/".$id;

      $object_name = \App\Device::where("owen_id", $id)->first()->name;

      $meta = ["name" => $object_name];

      if(($start) && ($end)){
        
        $start = explode("(", $start)[0];        
        $start = \Carbon\Carbon::parse($start);
        $end = explode("(", $end)[0];
        $end = \Carbon\Carbon::parse($end);

        $meta["start"] = $start;
        $meta["end"] = $end;
        
        $events = Event::where('object_id', $id)
          ->whereRaw("created_at > '$start' AND created_at < '$end'")
          ->paginate(20);  
      } else{
        $events = Event::where('object_id', $id)->latest()->paginate(20);
      }

    	return view("monitor", ["include" => "events", "meta" => $meta, "path" => $path, "events" => $events]);
    }

    public function graph($id){
      $events = Event::where('object_id', $id)->whereDate('created_at', \Carbon\Carbon::today())->get();
      $events = Event::where('object_id', $id)->latest()->take(144)->get();
      $events = $events->map(function($item){
        $item->formatedDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $item->created_at)->isoFormat('HH:mm Do MMMM YYYY');
        return $item;        
      });
      return view("monitor", ["include" => "chart", 'events' => $events]);
    }
}
