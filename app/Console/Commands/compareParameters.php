<?php

namespace App\Console\Commands;

use DB;
use Log;
use Cloud;
use App\Event;
use App\Alert;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;

class compareParameters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compare:params';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Опрос всех устройств и сравнение параметров с температурной картой';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	
	$start = microtime(true);

	//Get auth token from OWEN CLOUD
	$token = Cloud::getToken();

	//Get array of devices from local database
	$devices = DB::table("objects")->get();

    //Get array of devices from OWEN CLOUD
    $client = new \GuzzleHttp\Client();     
    $response = $client->post("https://api.owencloud.ru/v1/user-object/index", [
        'headers' => [
            'Content-Type'  => 'application/x-www-form-urlencoded',
            "Content-Length" => "68",
            "Accept"         => "*/*",
            "Authorization" => 'Bearer '.$token
        ],
        'body' => json_encode([])
    ]);

    //Get devices only from response
    $owen_status = json_decode($response->getBody()->getContents())->devices;

    
    //Foreach devices from database
	foreach($devices as $device){

        //Get status (online/offline)
        
        $status = Arr::first($owen_status, function($item) use ($device){
            return $item->id == $device->owen_id;
        })->status;

        
        

        //Create new Event instance and set object_id
        $event = new Event;
        $event->object_id = $device->owen_id;

        //if device is currently online
	    if($status == 'online'){

            $owen_device = $device->getParameters($token);
            //Get actual parameters from OWEN CLOUD
	        
            //Insert parameters into array
            $data = [
		        "name"	    => $owen_device->name,
                "owen_id"   => $owen_device->id,
                "object_t"  => $owen_device->parameters[0]->value,
                "direct_t"  => $owen_device->parameters[1]->value,
                "back_t"    => $owen_device->parameters[2]->value,
                "outside_t" => $owen_device->parameters[3]->value,
                "pressure"  => $owen_device->parameters[4]->value
            ];	

            //Compare actual data with temp card
            $compare_data = Cloud::compare($data);

            $event->message     = $compare_data["message"];                    
            $event->outside_t   = Cloud::floatOwenData($data["outside_t"]);
            $event->direct_t    = Cloud::floatOwenData($data["direct_t"]);
            $event->back_t      = Cloud::floatOwenData($data["back_t"]);
            $event->object_t    = Cloud::floatOwenData($data["object_t"]);
            $event->pressure    = Cloud::floatOwenData($data["pressure"]);
			
            switch ($compare_data["status"]) {
                case 0:
                    break;
                case 1:
                    break;
                case 2:
                    //change satus of alert as fixed (row status set = 1)
                    Alert::where(['object_id' => $device->owen_id, 'status' => 0])->update(['status' => 1]);
                    break;
                case 3:
                    break;
                case 4:
                    $object_users = DB::table("user_objects")->where("object_id", $device->owen_id)->pluck("user_id");
                    $object_users = [2];
                    Alert::firstOrCreate(['object_id' => $device->owen_id, 'status' => 0], ['message' => $compare_data["message"]]);
                    echo Cloud::sendNotifications($object_users,  $compare_data["message"], $device->owen_id);                  
                    break;
                                
                default:
                    
                    break;

            }
            DB::table('last_data')->updateOrInsert(['object_id' => $device->owen_id],
                                       ['object_t' => Cloud::floatOwenData($data["object_t"]),
                                        'outside_t' => Cloud::floatOwenData($data["outside_t"]), 
                                        'back_t' => Cloud::floatOwenData($data["back_t"]),
                                        'direct_t' => Cloud::floatOwenData($data["direct_t"]),
                                        'pressure' => Cloud::floatOwenData($data["pressure"]),
                                        'status' => true]);
        }else{
            DB::table('last_data')->updateOrInsert(['object_id' => $device->owen_id],
                           ['status' => false]);
            $event->message = "offline";
        }   
        $event->save();	
	}

        $exec_time = microtime(true) - $start;
        echo "Выполнено за".$exec_time." секунд";

    }
    
}
