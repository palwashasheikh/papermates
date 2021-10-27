Hello {{$email_data['username']}}
<br><br>
Welcome to my Website!
<br>
Please click the below link to verify your email and activate your account!
<br><br>
{{--<a href="{{url('verify',$email_data['verification_code'])}}">Click Here!</a>--}}
{{--<a href="{{url('http://localhost:8080/studentpaper/public/User/verify?code='.$email_data['code'])}}">Click Here!</a>--}}
<a href="{{ route('user.verify', $email_data['code']) }}">Verify Email</a>

<br><br>
Thank you!
<br>
Paper Mates
