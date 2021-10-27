<?php

namespace App\Http\Controllers\paperMateReview;

use App\Helper\ApiResponseBuilder;
use App\Http\Controllers\Controller;
use App\Models\paperMatesOrder;
use App\Models\paperMatesReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
class Review extends Controller
{
    public function CreateReview(Request $request)
    {
        $this->authorizedUser = $request->user();
        $rules = [
            'reviews' => 'required|string',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            $order = [
                'reviews' => $request->reviews,
                'userId' => auth::id(),
                'reply'=>$request->reply
            ];
            $orderCreate = paperMatesReview::create($order);
            if($orderCreate){
                ApiResponseBuilder::body(1,"Success",$orderCreate,null);
            }else{
                ApiResponseBuilder::body(0,"Failed",null,null);
            }
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);

    }
    public function userReview(Request $request){

        $orderList = paperMatesReview::select('papermatesreview.reviews','papermatesuser.username','reply','papermatesuser.UserImage as Image')->leftjoin('papermatesuser','papermatesuser.UserId','papermatesreview.userId')
            ->orderBy('papermatesreview.userId','DESC')
            ->simplepaginate(3);
        if($orderList){
            ApiResponseBuilder::body(1,"Success",$orderList,null);
        }else{
            ApiResponseBuilder::body(0,"Failed",null,null);
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }


}
