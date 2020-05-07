<?php
namespace App\Cloud;

use App\Cloud\Contracts\Cloud;
use Illuminate\Http\Request;
use App\User;
use App\Objectcard;
use App\Insidetemp;
use Session;

class OwenCloud implements Cloud{
        
    public function getToken(){
        $client = new \GuzzleHttp\Client();
        $response = $client->post("https://api.owencloud.ru/v1/auth/open", [
            'headers' => [
                'Content-Type'  => 'application/x-www-form-urlencoded',
                "Content-Length" => "68",
		'User-Agent' => null,
                "Accept"         => "*/*",    
            ],
            'body' => json_encode(['login' => "kambarov.rs@gmail.com", 'password' => "wwwggg123Q"])
        ]);
        return $this->getContent($response)->token;
    }

    public function request($uri, $body){
        $client = new \GuzzleHttp\Client();

            Session::put("CloudToken", $this->getToken());

        $response = $client->post("https://api.owencloud.ru/".$uri , [
            'headers' => $this->getHeaders(),
            'body' => json_encode($body)
        ]);
        return $response;
    }

    public function getHeaders(){
        return [
            'Content-Type'  => 'application/x-www-form-urlencoded',
            "Content-Length" => "68",
            "Accept"         => "*/*",
	    "User-Agent" => null,
            "Authorization" => 'Bearer '.Session::get("CloudToken")
        ];
    }

    public function getContent($response){
        return json_decode($response->getBody()->getContents());
    }
    
    public function compare($data){
        
        foreach($data as $param){
            if(is_null($param)){
                return [
                    "status"    => 1,
                    "message"   => "Один из параметров не возващает значение"
                ];
            }
        }

        $temp_card = Objectcard::where([
            ["object_id", "=", $data['owen_id']],
            ["outside_t", "=", $data['outside_t']]
        ])->first();

        if(!$temp_card){
            return [
                "status"    => 0,
                "message"   => $data['name'].". Не найдена температурная карта"
            ];
        }

        $direct_different = $this->getDifferent($data["direct_t"], $temp_card->direct_t);
        $back_different = $this->getDifferent($data["back_t"], $temp_card->back_t);

        if(($direct_different === false) || ($back_different === false)){
            return [
                "status"    => 1,
                "message"   => $data['name'].". Один из параметров не возващает значение"
            ];
        }
        if(($direct_different > 10) || ($back_different > 10)){
            return  [
                "status"    => 4,
                "message"   => $data['name'].". Недопустимое отклонение. Подача на  - ".$direct_different.", обратка на - ".$back_different
            ];
        }
        if(($direct_different > 5 ) || ($back_different > 5)){
            return  [
                "status"    => 3,
                "message"   => $data['name'].". Отклонение за пределами нормы. Подача на  - ".$direct_different.", обратка на - ".$back_different
            ];
        }
        if(($direct_different < 5) || ($back_different < 5)){
            return  [
                "status"    => 2,
                "message"   => $data['name'].". Отклонение в пределах нормы. Подача на  - ".$direct_different.", обратка на - ".$back_different
            ]; 
        }

    }

    public function floatOwenData($owen){

        if(($owen > 1000) || ($owen < -1000)){
            $owen = 0;
        }

        return $owen;
    }

    public function getDifferent($owen, $card){
        $owen = $this->floatOwenData($owen);
        if($owen == 0){
            return false;
        }
        return abs($owen - $card);
    }

    public function sendNotifications($users, $message, $device){

        foreach($users as $user){
            $content = array(
                "en" => $message
                );
            
            $fields = array(
                'app_id' => "9f70cb55-3cb9-41c5-97f3-56a41cfb8fe4",
                'filters' => array(array("field" => "tag", "key" => "user_id", "relation" => "=", "value" => $user)),
                'data' => array("device" => $device, "mobile_url" => "/smartheating/device/".$device),
                'web_url' =>"http://brainsoft.kz/device/".$device,
                'app_url' => "",
                'contents' => $content
            );
            
            $fields = json_encode($fields);
            print("\nJSON sent:\n");
            print($fields);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                                       'Authorization: Basic ODY1OGRjZGQtOWMwMy00MmNhLTkxMGItYjg3Zjg0Y2RjN2U0'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
            $response = curl_exec($ch);
            curl_close($ch);
            
        }
        
        return $response;
    }
}