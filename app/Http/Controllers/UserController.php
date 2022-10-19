<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'name'=>'required',
            'number'=>'required',
            'email'=>'required|email',
            'password'=>'required|confirmed',
            'tc'=>'required',
        ]);
        if(User::where('email', $request->email)->first()){
            return response([
                'msg'=>'email already exists',
                'status'=>'failed',
            ], 200);
        }
        if(User::where('number', $request->number)->first()){
            return response([
                'msg'=>'number already exists',
                'status'=>'failed',
            ], 200);
        }
        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'number'=>$request->number,
            'password'=>Hash::make($request->password),
            'tc'=>json_decode($request->tc),
        ]);
        $token = $user->createToken($request->email)->plainTextToken;
        return response([
            'token'=>$token,
            'name'=>$request->name,
            'number'=>$request->number,
            'email'=>$request->email,
            'status'=>'success',
        ], 201);
    }
    
    public function login(Request $request){
        $request->validate([
            'email'=>'required|email',
            'password'=>'required',
        ]);
        $user = (User::where('email', $request->email)->first());
        if($user && Hash::check($request->password,$user->password)){
            $token = $user->createToken($request->email)->plainTextToken;
            return response([
                'token'=>$token,
                'name'=>$user->name,
                'number'=>$user->number,
                'email'=>$user->email,
                'status'=>'success',
            ], 200);
        };
        return response([
            'message' => 'The Provided Credentials are incorrect',
            'status'=>'failed'
        ], 401);
    }
    public function logout(){
        auth()->user()->tokens()->delete();
        return response([
            'msg'=>'logout successfully',
            'status'=>'success',
        ], 200);
    }
}
