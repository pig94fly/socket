<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class RedisController extends Controller
{
    //
    public function test()
    {
        Redis::set('name','猪猪猪');
        $val = Redis::get('name');
        echo $val;
    }
}
