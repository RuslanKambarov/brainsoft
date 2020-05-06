<?php
namespace App\Cloud\Facades;
use Illuminate\Support\Facades\Facade;

class Cloud extends Facade{
    
    protected static function getFacadeAccessor()
    {
        return 'cloud';
    }
}