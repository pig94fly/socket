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
        $token = time().rand(0,255).$userId;
        $token = Hash::make($token);
        Predis
        return json_encode(['token'=>$token]);
    }
}
