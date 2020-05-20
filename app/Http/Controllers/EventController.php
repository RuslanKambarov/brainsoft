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

      if(($start) && ($end)){        
        $start = \Carbon\Carbon::createFromFormat('Y-m-d', $start);
        $end = \Carbon\Carbon::createFromFormat('Y-m-d', $end);
        $events = Event::where('object_id', $id)
          ->whereRaw("created_at > DATE('$start') AND created_at < DATE('$end')")
          ->paginate(20);  
      } else{
        $events = Event::where('object_id', $id)->latest()->paginate(20);
      }
      
    	return view("monitor", ["include" => "events", "path" => $path, "events" => $events]);
    }

    public function graph($id){
      $events = Event::where('object_id', $id)->limit(100)->latest()->get();
      $events = $events->map(function($item){
        $item->formatedDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $item->created_at)->isoFormat('HH:mm Do MMMM YYYY');
        return $item;        
      });
      $events = $events->reverse();
      return view("monitor", ["include" => "chart", 'events' => $events]);
    }
}
