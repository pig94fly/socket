<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function index()
    {
        $token = csrf_token();
        echo "<form action='' method='post'>
                <input type='text' name='username'>
                <input type='text' name='password'>
                <input type='hidden' name='_token' value='{$token}'>
                <input type='submit'>
              </form>";
    }
    public function login(Request $request)
    {
        $credentials = $request->only(['username','password']);
        $rule = ['username'=>'required|min:5','password'=>'required|min:5'];
        $validator = Validator::make($credentials,$rule);

        if ($validator->fails()){
            return 'bad';
//            return Redirect::back()->withInput()->withErrors($validator);
        }
        if (Auth::attempt($credentials)){
            return redirect()->intended('dashboard');
        }
    }
    public function logout()
    {
        Auth::logout();
    }
    public function register(Request $request)
    {
        $registerInfo = $request->only(['username','password']);
        $registerInfo['password'] = Hash::make($registerInfo['password']);
        $user = User::create($registerInfo);
        if ($user){
            return print_r($user);
        }
    }
}
