<?php

namespace CannaPlan\Http\Controllers\Auth;

use CannaPlan\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use CannaPlan\User;
use CannaPlan\Helpers\Email;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function getResetToken(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            return response()->fail('User Not Found');
        }
        $token = $this->broker()->createToken($user);
        $data=array('token'=>$token, 'email' => $user->email);
        Email::sendMail('emails/forgot_password',array('data' => $data), "FORGOT PASSWORD",$user->email);
        return response()->success($token,'Token');

    }
}
