<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

class WebSocketController extends Controller
{
    //
    public function token()
    {
        $conf = socket_conf();
        $userId = Auth::id();
        $host = Redis::hget('WsConf','host');
        $port = Redis::hget('WsConf','port');
        $token = time().rand(0,255).$userId;
//        $token = Hash::make($token);
        $token = sha1($token);
//        Redis::set('UserWsToken-'.$userId,$token);
        Redis::set('WsToken-'.$token,$userId);
//        Redis::expire('UserWsToken-'.$userId,30);
        Redis::expire('WsToken-'.$token,20);
        return json_encode(['token'=>$token,'host'=>$host,'port'=>$port]);
    }
    public function client()
    {
        return view('ws.client');
    }
}
