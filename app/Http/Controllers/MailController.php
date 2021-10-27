<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\Email_detail;
use Illuminate\Support\Facades\Mail;
class MailController extends Controller
{
    public static function sendSignupEmail($username, $email, $code){
        $data = [
            'username' => $username,
            'code' => $code
        ];
        Mail::to($email)->send(new Email_detail($data));
}
}
