<?php
namespace App\Cloud;

use App\Cloud\Contracts\Cloud;
use Illuminate\Http\Request;
use App\User;
use App\Objectcard;
use App\Insidetemp;
use Session;
use Arr;
use App\Device;

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
            'body' => json_encode(['login' => config('services.owen.login'), 'password' => config('services.owen.password')])
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

    public function owenAddDistrict($body){
        return $this->request("v1/modbus/create-parameter-category/", $body);   
    }

    public function owenAddDevice($body){
        return $this->request("v1/device-management/register", $body);
    }

    public function getContent($response){
        return json_decode($response->getBody()->getContents());
    }
    
    public function compare($data){
        
        //Проверка на пустые значения с измерительного прибора
        foreach($data as $key => $param){
            if(is_null($param)){
                return [
                    "status"    => 1,
                    "message"   => $data['name'].". Один из параметров не возвращает значение (".$key.")"
                ];
            }
        }

        //Проверка на корректность значений с измерительного прибора
        foreach(Arr::except($data, ['name', 'owen_id']) as $key => $param){
            if(($param > 1000) || ($param < -1000)){
                return [
                    "status"    => 1,
                    "message"   => $data['name'].". Один из параметров возвращает некорректное значение ($key)"
                ];
            }    
        }

        //Поиск температурной карты этого прибора при данной температуре
        $temp_card = Objectcard::where([
            ["object_id", "=", $data['owen_id']],
            ["outside_t", "=", $data['outside_t']]
        ])->first();

        //Если не найдена температурная карта 
        if(!$temp_card){
            return [
                "status"    => 0,
                "message"   => $data['name'].". Не задана температурная карта для температуры улицы ".$data['outside_t']
            ];
        }

        //Определние задданых пераметров объекта (какая должна быть температура и давление)
        $object = Device::where("owen_id", $data['owen_id'])->first();        
        $defined_temperature = $object->required_t;
        $defined_pressure    = $object->required_p;
        
        //Проверка температуры на соответствие
        $object_t_diff = abs($data['object_t'] - $defined_temperature);

        if($object_t_diff > 2){
            return [
                "status"    => 4,
                "message"   => $data['name'].". Температура объекта: недопустимое отклонение. Температура объекта - ".$data['object_t'].", небходимая температура -  $defined_temperature"
            ];
        }

        //Проверка температуры на соответствие
        $object_p_diff = abs($data['pressure'] - $defined_pressure);

        if($object_p_diff > 0.5){
            return [
                "status"    => 4,
                "message"   => $data['name'].". Давление: недопустимое отклонение. Давление на объекте - ".$data['pressure'].", необходимое давление - $defined_pressure"
            ];
        }


        $direct_different   = $data["direct_t"] - $temp_card->direct_t;
        $back_different     = $data["back_t"] - $temp_card->back_t;

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


    public function sendNotifications($users, $message, $device){

        foreach($users as $user){
            $content = array(
                "en" => $message
                );
            
            $fields = array(
                'app_id' => env('ONE_SIGNAL_APP_ID'),
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
                                                       'Authorization: Basic '.env('ONE_SIGNAL_REST_API_KEY')));
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

    public function testMessage()
    {
        $users = [1];
        $message = "Content";
        $device = 5;
        return $this->sendNotifications($users, $message, $device);       
    }
}