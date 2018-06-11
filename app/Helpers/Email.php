<?php

namespace CannaPlan\Helpers;
use Illuminate\Support\Facades\Mail;

class Email{

    public static function sendMail($view ,$data, $subject,$to_email){
        Mail::send($view,$data, function($message) use ($to_email,$subject)
        {
            $message->to($to_email,'CannaPlanUser')->subject($subject);
        });
    }
}