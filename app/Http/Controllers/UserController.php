<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'number'=>'required||min:11',
            'email'=>'required|email',
            'password'=>'required|confirmed||min:6',
            'tc'=>'required',
        ]);
        if($validator->fails()){
            return response([
                'msg' => $validator->messages(),
                'status'=>'Bad request'
            ], 400);
        }else{
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
    }
    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required||min:6',
        ]);
        if($validator->fails()){
            return response([
                'msg' => $validator->messages(),
                'status'=>'Bad request'
            ], 400);
        }
        $user = User::where('email', $request->email)->first();
        if($user && Hash::check($request->password, $user->password)){
            $token = $user->createToken($request->email)->plainTextToken;
            return response([
                'token'=>$token,
                'name'=>$user->name,
                'number'=>$user->number,
                'email'=>$user->email,
                'status'=>'success',
               ], 200);
        }
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
    public function logged_user(){
        $loggeduser = auth()->user();
        $collection = collect(['name'=>$loggeduser->name,
        'number'=>$loggeduser->number,
        'email'=>$loggeduser->email,]);
        return response([
            'user'=>$collection,
            'status'=>'success',
        ], 200);
    }
    public function change_password(Request $request){
        $validator = Validator::make($request->all(),[
            'password'=> 'required||min:6',
        ]);
        if($validator->fails()){
            return response([
                'msg' => $validator->messages(),
                'status'=>'Bad request'
            ], 400);
        }else{
            $loggeduser = auth()->user();
            $loggeduser->password = Hash::make($request->password);
            $loggeduser->save();
            return response([
                'msg' => 'Password Changed Successfully',
                'status'=>'success'
            ], 200);
        }
        
    }
}
