<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function index()
    {
        $token = csrf_token();
        echo "<form action='' method='post'>
                <input type='text' name='email'>
                <input type='text' name='password'>
                <input type='hidden' name='_token' value='{$token}'>
                <input type='submit'>
              </form>";
    }
    public function login(Request $request)
    {
        $credentials = $request->only(['email','password']);
        $rule = ['email'=>'required|min:5|email','password'=>'required|min:5'];
        $validator = Validator::make($credentials,$rule);

        if ($validator->fails()){
//            return 'bad';
            return Redirect::back()->withInput()->withErrors($validator);
        }
        if (Auth::attempt($credentials)){
//            return 'true';
            return redirect()->intended('home');
        }else{
            return 'false';
        }
    }
    public function logout()
    {
        Auth::logout();
    }
    public function register(Request $request)
    {
        $method = $request->method();
        if ($method == 'GET'){
            return view("register");
        }
        $registerInfo = $request->only(['email','password']);
        $registerInfo['password'] = Hash::make($registerInfo['password']);
        $user = User::create($registerInfo);
        if ($user){
            return redirect('login');
        }
    }
}
