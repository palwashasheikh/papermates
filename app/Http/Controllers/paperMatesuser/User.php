<?php

namespace App\Http\Controllers\paperMatesuser;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController;
use App\Mail\Email_detail;
use App\Helper\ApiResponseBuilder;
use App\Models\paperMatesAcademiclevel;
use App\Models\paperMatesCategory;
use App\Models\paperMatesOrder;
use App\Models\paperMatesRole;
use App\Models\paperMatesUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Validator;

class User extends Controller
{

    private $authorizedUser;
    private $profileImage ='/paperMates/content/user/user-default/';
    private $profilePicture;
    public function register(Request $request)
    {
        $rules = [
            'username'     => 'required',
            'userEmail'    => 'required|email|unique:papermatesuser,userEmail',
            'UserPhone'    => 'required|numeric|unique:papermatesuser,UserPhone',
            'userpassword' => 'required',
            'role'  => 'required|string',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            $user = new paperMatesUser();
            $user->username= $request->username;
            $user->userEmail= $request->userEmail;
            $user->UserPhone= $request->UserPhone;
            $user ->userpassword=Hash::make($request->userpassword);
            $user ->role=$request->role;
            $user->code = sha1(time());
            if($user !== null) {
                MailController::sendSignupEmail($user->username, $request->userEmail, $user->code);
                $user->save();
                $this->responseData = array(
                    "User" => $user,
                );
                if($this->responseData){
                    ApiResponseBuilder::body(0, "Failed", null , null);
                }
            }
            ApiResponseBuilder::body(1, "success", $this->responseData, null);
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }

    public function login(Request $request)
    {
        $rules = [
            'userEmail' => 'required|email',
            'userpassword' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            $user = paperMatesUser::where('userEmail', $request->userEmail)->where('is_verified', '=', 1)->first();
            if (!$user || !Hash::check($request->userpassword, $user->userpassword)) {
                ApiResponseBuilder::body(0, "These credentials do not match our records.", null, null);
            } else {
                $user = paperMatesUser::where('userEmail', $request->userEmail)->where('is_verified', '=', 1)->first();
//                $role = paperMatesRole::select('Role')->where('RoleId', $user->role_id)->first();
//                $user['role'] = $role['Role'];
                if (!$user || !Hash::check($request->userpassword, $user->userpassword)) {
                    ApiResponseBuilder::body(0, "These credentials do not match our records.", null, null);
                } else {
                    if ($user) {
                        $token = $user->createToken($request->ip())->plainTextToken;
                        $this->responseData = ['User' => $user, 'Token' => $token];
                        ApiResponseBuilder::body(1, "Success", $this->responseData, null);
                    } else {
                        ApiResponseBuilder::body(0, "These credentials do not match our records.", null, null);
                    }

                }
            }
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function verifyAccount($token)
    {
        $verifyUser = paperMatesUser::where('code', $token)->first();

        if(!is_null($verifyUser) ){
            $verifyUser->is_verified = 1;
            $verifyUser->save();
                $message = "Your e-mail is verified. You can now login.";
            } else {
                $message = "Your e-mail is already verified. You can now login.";
        }
        return redirect('http://admin.papersmates.com/');
    }
    public function submitForgetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:papermatesuser',
        ]);

        $token = Str::random(64);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        Mail::send('email.forgetPassword', ['token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Reset Password');
        });

        return back()->with('message', 'We have e-mailed your password reset link!');
    }
    public function updateProfile(Request $request){

        $this->authorizedUser=$request->user();
        $rules=[
            'username' => 'required',
            'UserImage' => 'nullable|mimes:jpeg,jpg,png|max:5000',
            'UserPhone'=>'required|numeric|min:8',
            'userEmail' => 'required|email',
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            ApiResponseBuilder::body(0,ApiResponseBuilder::getMessage($validator),null,$validator->errors());
        }else{
            $names[]=$request->input(['categories']);
            $this->authorizedUser->username=$request->username;
            $this->authorizedUser->UserPhone=$request->UserPhone;
            $this->authorizedUser->userEmail=$request->userEmail;
            $this->authorizedUser->AcademicLevel=$request->AcademicLevel;
            foreach($names as $value) {
                $this->authorizedUser->papercategories=$value;
            }
            $this->profilePicture=null;
            if ($request->hasFile('UserImage') && $request->file('UserImage')->isValid()) {
                $extension = $request->UserImage->extension();
                $fileName=uniqid().".".$extension;
                if($request->file('UserImage')->move(public_path($this->profileImage),$fileName)){
                    $this->profilePicture = $this->profileImage.$fileName;
                    $this->authorizedUser->UserImage=$this->profilePicture;
                }
            }
            if($this->authorizedUser->save()){
                $data =[
                    'username'=> $this->authorizedUser->username,
                    'UserImage'  =>  $this->authorizedUser->UserImage,
                    'userEmail'         =>    $this->authorizedUser->userEmail,
                    'UserPhone'         =>    $this->authorizedUser->UserPhone,
                    'AcademicLevel'    =>  $this->authorizedUser->AcademicLevel,
                      'categories'    =>  $this->authorizedUser->papercategories
                ];
                ApiResponseBuilder::body(1,'Success',$data,null);
            }else{
                ApiResponseBuilder::body(0,'Failed',null,null);
            }
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function ChangeUserPassword(Request $request)
    {
        $this->authorizedUser=$request->user();
        $rules = [
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8',
            'ConfirmPassword' =>'min:6|required_with:newPassword|same:newPassword'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            if(Hash::check($request->currentPassword,$this->authorizedUser->userpassword)) {
                $this->authorizedUser->userpassword = Hash::make($request->NewPassword);
                if($this->authorizedUser->update()){
                    ApiResponseBuilder::body(1,"Success",null,null);
                }else{
                    ApiResponseBuilder::body(0,"Failed",null,null);
                }
            }else{
                ApiResponseBuilder::body(0,"Incorrect password",null,null);
            }
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }

        public function writersList(Request $request){

            $writer = paperMatesUser::
            select('UserId  as key','username as writerName','userEmail','UserPhone','UserImage','papercategories','AcademicLevel')->where('role','=' ,'writer')->get();
            $params = array();
            foreach ($writer as $writers){
                $this->responseData[] = [
                    'key'=>$writers->key,
                    'writerName'=>$writers->writerName,
                    'userEmail'=>$writers->userEmail,
                    'UserPhone'=>$writers->UserPhone,
                    'UserImage'=>$writers->UserImage,
                    'papercategories' =>$writers->papercategories,
                    'AcademicLevel'=>$writers->AcademicLevel
                ];
                if($this->responseData){
                ApiResponseBuilder::body(1,"Success",$this->responseData,null);
            }else {
                    ApiResponseBuilder::body(0, "Failed", null, null);

                }
            }
            return response()->json(ApiResponseBuilder::getResponse(), 200);
         }
        public function staffList(Request $request){
            $this->authorizedUser=$request->user();
            $staff = paperMatesUser::select('UserId  as key','username as staffName','userEmail','UserPhone','UserImage','papercategories','AcademicLevel')->where('role','=' ,'staff')->get();
            foreach ($staff as $staffs){
                $this->responseData[] = [
                    'key'=>$staffs->key,
                    'staffName'=>$staffs->staffName,
                    'userEmail'=>$staffs->userEmail,
                    'UserPhone'=>$staffs->UserPhone,
                    'UserImage'=>$staffs->UserImage,
                    'papercategories'=>explode(',',$staffs->papercategories),
                    'AcademicLevel'=>explode(',',$staffs->AcademicLevel)
                ];
            }
            if( $this->responseData){
                ApiResponseBuilder::body(1,"Success", $this->responseData,null);
            }else {
                ApiResponseBuilder::body(0, "Failed", null, null);
            }
            return response()->json(ApiResponseBuilder::getResponse(), 200);
        }
        public function studentList(Request $request){
            $student = paperMatesUser::select('UserId  as key','username as studentName','userEmail','UserPhone','UserImage','papercategories','AcademicLevel')
                ->where('role','=' ,'student')->get();
           $r = [];
            foreach ($student as $students){
                $r[] = [
                     'key'=>$students->key,
                    'studentName'=>$students->studentName,
                    'userEmail'=>$students->userEmail,
                    'UserPhone'=>$students->UserPhone,
                    'UserImage'=>$students->UserImage,
                    'papercategories'=>$students->papercategories,
                    'AcademicLevel'=>$students->AcademicLevel
                    ];
                    ApiResponseBuilder::body(1,"Success",$r,null);

            }

            return response()->json(ApiResponseBuilder::getResponse(), 200);
        }

    public function createAcademicLevel(Request $request)
    {
        $this->authorizedUser=$request->user();
        $rules = [
            'AcademicLevel' => 'required|string',
            'price'         => 'required|numeric'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            $academiclevel= [
                'AcademicLevelName'=> $request->AcademicLevel,
                'price' => $request->price
            ];
            $create = paperMatesAcademiclevel::create($academiclevel);
             paperMatesAcademiclevel::where('AcademicLevelId' ,'=', 1)->update(['isActive' => true]);
        if($create){
            ApiResponseBuilder::body(1, "Success", null ,null);
            }else {
                ApiResponseBuilder::body(0, "Failed", null, null);
            }
            return response()->json(ApiResponseBuilder::getResponse(), 200);
        }
    }
    public function AcademicLevels(Request $request)
    {
            $list = paperMatesAcademiclevel::select('AcademicLevelId as key',
            "AcademicLevelName",
            "price",'isActive')->get();
               foreach ($list as $lists){
                   if ($lists->isActive == '1'){
                       $ia=((bool) true) ;
                   }else{
                       $ia=((bool) false) ;
                   }
                   $list_[]= [
                       'AcademicLevelId'=>$lists->key,
                       'price'=>$lists->price,
//                       'isActive'=>$ia
                       'isActive'=> $ia
                   ];
               }

                if($list){
                    ApiResponseBuilder::body(1,"Success",$list,null);
                }else {
                    ApiResponseBuilder::body(0, "Failed", null, null);
                }
            return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function addUser(Request $request)
    {
        $this->authorizedUser=$request->user();
        $rule = [
            'username'=>'required|string',
            'userEmail'=>'required|email',
            'UserPhone'=>'required|',
            'userpassword'=>'required',
            'AcademicLevelName'=>'nullable',
            'Categories'=>'nullable',
            'role'=> 'required|string'
        ];
        $validator = Validator::make($request->all(),$rule);

        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            $names[]=$request->input(['Categories']);
            $AcademicLevelName[]   = $request->input(['AcademicLevelName']);;
             $user = new paperMatesUser();
            $user->username =  $request->username;
              $user->userEmail =  $request->userEmail;
              $user->UserPhone = $request->UserPhone;
              $user->userpassword = Hash::make($request->userpassword);
              foreach ($names as $value){
                  $user->papercategories=$value;

              }
            foreach ($AcademicLevelName as $value1){
                $user->AcademicLevel=$value1;


            }
            $user->role = $request->role;
              $user->save();
                if ($user !== null) {
                    ApiResponseBuilder::body(1, "Success", null, null);
                } else {
                    ApiResponseBuilder::body(0, "Failed", null, null);
            }
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }

    public function countdata()
    {
        $total = paperMatesUser::where('role', '=','writer' )->select(DB::raw('COUNT(*) as total'))->first();
        $tota2 = paperMatesUser::where('role', '=','student' )->select(DB::raw('COUNT(*) as total'))->first();
        $totalacademic = paperMatesUser::where('AcademicLevel', '=','University' )->select(DB::raw('COUNT(*) as total'))->first();
        $totalorders = paperMatesOrder::select(DB::raw('COUNT(*) as total'))->first();

        $data = ['countWriter' =>$total, 'countStudent' => $tota2, 'totalUniversity'=> $totalacademic,'totalorders' =>$totalorders ];
        ApiResponseBuilder::body(1, "Success", $data, null);
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function dashboardCount(Request $request)
    {
        $total = paperMatesUser::where('role', '=','staff' )->select(DB::raw('COUNT(*) as total'))->first();
        $assignmentOrder = paperMatesOrder::select('Papercategory',DB::raw('COUNT(*) as orders'))->groupby('Papercategory')->orderby('orders','DEsc')->get();
        $initial = 1;
        foreach ($assignmentOrder as $items){
            $d[] = ['No' => $initial++,
                'Papercategory'=>$items->Papercategory,
                         'orders'=>$items->orders,
                          ];
        }
        $totalwriter = paperMatesUser::where('role', '=','writer' )->select(DB::raw('COUNT(*) as total'))->first();
        $totalstudent = paperMatesUser::where('role', '=','student' )->select(DB::raw('COUNT(*) as total'))->first();

        $ordercomplete = paperMatesOrder::where('status','=','Completed' )->select(DB::raw('COUNT(*) as total'))->first();
        $orders = paperMatesOrder::select(DB::raw('COUNT(*) as total'))->first();

        $totalcat = paperMatesCategory::select(DB::raw('COUNT(*) as total'))->first();

        $data = ['totalOrders'=>$orders,'totalStudent'=>$totalstudent,'countStaff' => $total,'countCategories'=>$totalcat,'CompletedOrders'=>$ordercomplete,'totalwriter'=>$totalwriter,'MostTopics'=>$d];
        ApiResponseBuilder::body(1, "Success", $data, null);
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function studentDelete(Request $request)
    {
        $this->authorizedUser = $request->user();
        $rules = [
            'studentId' => 'required|numeric',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            $studentIdlist = paperMatesUser::where('userId', '=', $request->studentId)->where('role', '=', 'student')->delete();
        }
        if ($studentIdlist) {
            ApiResponseBuilder::body(1, "Success", null, null);
        } else {
            ApiResponseBuilder::body(0, "Failed", null, null);
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function writerDelete(Request $request)
    {
        $this->authorizedUser = $request->user();
        $rules = [
            'writerId' => 'required|numeric',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            $writerDelete = paperMatesUser::where('userId', '=', $request->writerId)->where('role', '=', 'writer')->delete();
        }
        if ($writerDelete) {
            ApiResponseBuilder::body(1, "Success", null, null);
        } else {
            ApiResponseBuilder::body(0, "Failed", null, null);
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function staffDelete(Request $request)
    {
        $this->authorizedUser = $request->user();
        $rules = [
            'staffId' => 'required|numeric',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            $staffDelete = paperMatesUser::where('userId', '=', $request->staffId)->where('role', '=', 'staff')->delete();
        }
        if ($staffDelete) {
            ApiResponseBuilder::body(1, "Success", null, null);
        } else {
            ApiResponseBuilder::body(0, "Failed", null, null);
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function academicLevelDelete(Request $request)
    {
        $this->authorizedUser = $request->user();
        $rules = [
            'academiclevelId' => 'required|numeric',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            $writerDelete = paperMatesAcademiclevel::where('AcademicLevelId', '=', $request->academiclevelId)->delete();
        }
        if ($writerDelete) {
            ApiResponseBuilder::body(1, "Success", null, null);
        } else {
            ApiResponseBuilder::body(0, "Failed", null, null);
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }

    public function WriterAppliedForOrder(Request $request)
    {
        $isappled = paperMatesOrder::where('orderId',$request->orderId)->update(['isApplied' => 1,'writerId'=> auth::id()]);
        if ($isappled) {
            ApiResponseBuilder::body(1, "Success", null, null);
        } else {
            ApiResponseBuilder::body(0, "Failed", null, null);
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);

    }
    public function forgotPassword(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'email' => "required|email",
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "message" => $validator->errors()->first(), "data" => array());
        } else {
            try {
                $response = Password::sendResetLink($request->only('email'), function (Message $message) {
                    $message->subject($this->getEmailSubject());
                });
                switch ($response) {
                    case Password::RESET_LINK_SENT:
                        return \Response::json(array("status" => 200, "message" => trans($response), "data" => array()));
                    case Password::INVALID_USER:
                        return \Response::json(array("status" => 400, "message" => trans($response), "data" => array()));
                }
            } catch (\Swift_TransportException $ex) {
                $arr = array("status" => 400, "message" => $ex->getMessage(), "data" => []);
            } catch (Exception $ex) {
                $arr = array("status" => 400, "message" => $ex->getMessage(), "data" => []);
            }
        }
        return \Response::json($arr);
    }

}
