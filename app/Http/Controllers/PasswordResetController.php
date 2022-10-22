<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Mail\Message;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function resetPasswordEmail(Request $request){
        $validator = Validator::make($request->all(),[
            'email'=>'required|email',
        ]);
        $email = $request->email;
        if($validator->fails()){
            return response([
                'msg' => $validator->messages(),
                'status'=>'Bad request'
            ], 400);
        }else{
            $user = User::where('email', $email)->first();
            if(!$user){
                return response([
                    'msg'=>'email does not exists',
                    'status'=>'failed',
                ], 404);
            }
            $token = Str::random(60);
            PasswordReset::create([
                'email'=>$email,
                'token'=>$token,
                'created_at'=>Carbon::now()
            ]);
            Mail::send('reset', ['token'=>$token], function(Message $message)use($email){
                $message->subject('Reset Your Password');
                $message->to($email);
            });
            return response([
                'msg'=>'Password Reset Email Sent... Check Your Email',
                'status'=>'success',
            ], 200);
        }
    }
}
