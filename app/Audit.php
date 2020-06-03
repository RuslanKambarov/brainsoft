<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    public function questions()
    {
        return $this->hasMany('App\Question');
    }

    public function countNOK($results){
        
        $data = [];

        foreach($this->questions as $question){
            $data[$question->id] = 0;
        }

        if($results[0]->audit_json === null){
            return $data;
        }
        
        foreach($results as $result){

            $answers = json_decode($result->audit_json);
            
            foreach($answers as $answer){
                
                if($answer->answer === false)
                $data[$answer->question_id]++;

            }            

        }

        return $data;
    }
}
