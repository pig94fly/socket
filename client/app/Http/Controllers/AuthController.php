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
        return view('auth.login');
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
            return Redirect::back()->withInput()->withErrors($validator);
        }
        if (Auth::attempt($credentials)){
//            return 'true';
            return redirect()->intended('home');
        }else{
            return Redirect::back()->withInput()->withErrors(['email' => 'These cridential not match our record!']);
        }
    }
    public function logout()
    {
        Auth::logout();
        return redirect()->intended('login');
    }
    public function register(Request $request)
    {
        $method = $request->method();
        if ($method == 'GET'){
            return view("auth.register");
        }
        $registerInfo = $request->only(['email','password','name','password_confirmation']);
        if ($registerInfo['password']!==$registerInfo['password_confirmation']){
            return Redirect::back()->withInput()->withErrors(['password'=>'两次密码输入不一致']);
        }
        $registerInfo['password'] = Hash::make($registerInfo['password']);
        if (User::where('email',$registerInfo['email'])->first())
            return Redirect::back()->withInput()->withErrors(['email'=>'该邮箱已被注册']);
        $user = User::create($registerInfo);
        if ($user){
            Auth::login($user);
            return redirect('/');
        }
    }
}
