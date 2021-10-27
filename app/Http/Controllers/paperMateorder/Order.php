<?php

namespace App\Http\Controllers\paperMateorder;

use App\Helper\ApiResponseBuilder;
use App\Http\Controllers\Controller;
use App\Models\paperMatesCategory;
use App\Models\paperMatesOrder;
use App\Models\paperMatesRole;
use App\Models\paperMatesUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Component\Console\Input\Input;
use Validator;
use function PHPUnit\Framework\isEmpty;

class Order extends Controller
{
    private $responseData;
    private $paperDocuments;
    public function createorders(Request $request)
    {
        $this->authorizedUser = $request->user();
        $rules = [
            'pages' => 'required|numeric',
            'AcademicLevel' => 'required|string',
            'price' => 'required|numeric',
            'Deadline' => 'required',
            'paperTopic' => 'required|string',
            'PaperType'=>'required|string',
            'paperDescription' =>'required|string',
            'ReferenceNo'=> 'required|string'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            $order = [
                'pages' => $request->pages,
                'AcademicLevel'=>$request->AcademicLevel,
                'price' => $request->price,
                'Deadline' => $request->Deadline,
                'paperTopic' => $request->paperTopic,
                'Papercategory'=> $request->PaperType,
                 'paperDescription'   => $request->paperDescription,
                  'userId' => auth::id(),
                'ReferenceNo'=> $request->ReferenceNo
            ];
            $orderCreate = paperMatesOrder::create($order);
            if($orderCreate){
                ApiResponseBuilder::body(1,"Success",$orderCreate,null);
            }else{
                ApiResponseBuilder::body(0,"Failed",null,null);
            }
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);

    }
    public function OrderList(Request $request)
    {
        $orderList = paperMatesOrder
            ::leftjoin('papermatesuser','papermatesuser.UserId','papermatesorder.userId')
            ->select('papermatesuser.username as studentname','papermatesorder.orderId as key','papermatesorder.pages','papermatesorder.Papercategory as paperTypes','papermatesorder.price','papermatesorder.Deadline','papermatesorder.status','papermatesorder.paperTopic','papermatesorder.paperDescription',
               'papermatesorder.created_at','papermatesorder.AcademicLevel')
            ->where('status','In progress')
            ->where('papermatesorder.isApplied' ,'=', 0)
            ->get();
         $orderid = paperMatesOrder
           ::select('username as writername')
            ->leftjoin('papermatesuser','papermatesuser.UserId','papermatesorder.writerId')
             ->where('papermatesorder.isApplied' ,'=', 0)
            ->get();

        for($i=0; $i<count($orderList);$i++){
            $orderList[$i]['writername'] = $orderid[$i]->writername;
        }
            if($orderList){
                ApiResponseBuilder::body(1,"Success",$orderList,null);
            }else{
                ApiResponseBuilder::body(0,"Failed",null,null);
            }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }

    public function OrderListByorderId(Request $request)
    {
        $rules = [
            'key' => 'required|numeric',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            $orderList = paperMatesOrder::select('papermatesuser.username','papermatesorder.orderId as key', 'papermatesorder.Papercategory as paperTypes', 'papermatesorder.pages as Pages', 'papermatesorder.price as Price',
                'papermatesorder.Deadline as Deadline', 'papermatesorder.AcademicLevel','papermatesorder.status', 'papermatesorder.paperTopic as paperTopic', 'papermatesorder.paperDescription')
                ->leftjoin('papermatesuser','papermatesuser.UserId','papermatesorder.userId')
                ->where('orderId', $request->key)->get();
            $orderid = paperMatesOrder
                ::select('username as writername')
                ->leftjoin('papermatesuser','papermatesuser.UserId','papermatesorder.writerId')
                ->where('orderId', $request->key)
                ->get();
            for($i=0; $i<count($orderList);$i++){
                $orderList[$i]['writername'] = $orderid[$i]->writername;
            }
            if ($orderList) {
                ApiResponseBuilder::body(1, "Success", $orderList, null);
            } else {
                ApiResponseBuilder::body(0, "Failed", null, null);
            }
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function paperCategory(Request $request)
    {
        $this->authorizedUser = $request->user();
        $rules = [
            'paperCategory' => 'required|string',
            'AcademicLevel' => 'required|string',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            $categorycreate = [
                'papercategoriesName' => $request->paperCategory,
                'AcademicLevel' => $request->AcademicLevel
            ];
            $create = paperMatesCategory::create($categorycreate);
            if ($create) {
                ApiResponseBuilder::body(1, "Success", null, null);
            } else {
                ApiResponseBuilder::body(0, "Failed", null, null);
            }
            return response()->json(ApiResponseBuilder::getResponse(), 200);

        }
    }
    public function categoryList(Request $request)
    {
        $input = $_GET['AcademicLevel'];
        $rules = [
            'AcademicLevel' => 'Nullable|required|string',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            if($input == 'All' || empty($input)){
                $list = paperMatesCategory::select('papercategoriesId as key', 'papercategoriesName')->get();
                ApiResponseBuilder::body(1, "Success", $list, null);
            }else if ($request->AcademicLevel) {
                $lists = paperMatesCategory::select('papercategoriesId as key', 'papercategoriesName')->where('AcademicLevel',$input)->get();
                ApiResponseBuilder::body(1, "Success", $lists, null);
            }
            else{
                ApiResponseBuilder::body(1, "failed", null, null);
            }
        }
            return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function categoryDelete(Request $request)
    {
        $this->authorizedUser = $request->user();
        $rules = [
            'categoryId' => 'required|numeric',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            ApiResponseBuilder::body(0, ApiResponseBuilder::getMessage($validator), null, $validator->errors());
        } else {
            $categorylist = paperMatesCategory::where('papercategoriesId', '=', $request->categoryId)->delete();
        }
        if ($categorylist) {
            ApiResponseBuilder::body(1, "Success", $categorylist, null);
        } else {
            ApiResponseBuilder::body(0, "Failed", null, null);
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function orderStatusUpdate(Request $request)
    {
        $this->authorizedUser = $request->user();
        $this->paperDocuments=null;
        if ($request->hasFile('paperDocuments') && $request->file('paperDocuments')->isValid()) {

            $extension = $request->paperDocuments->extension();
            $fileName = uniqid() . "." . $extension;
            echo $fileName;
            if ($request->file('paperDocuments')->move(public_path($this->paperDocuments), $fileName)) {
                $this->paperDocuments = $this->paperDocuments.$fileName;
            }
        }
                $order = paperMatesOrder::where('orderId','=',$request->key)->where('writerId',auth::id())->update(['status' =>$request->status,'paperDocuments'=> $this->paperDocuments]);
            if ($order) {
                ApiResponseBuilder::body(1, "Success", $order, null);
            } else {
                ApiResponseBuilder::body(0, "Failed", null, null);
            }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function studentOrders(Request $request)
    {
        $this->authorizedUser = $request->user();
        $orderList = paperMatesOrder
            ::join('papermatesuser','papermatesuser.UserId','papermatesorder.userId')
            ->select('papermatesuser.username as studentname','papermatesorder.orderId as key'
                ,'papermatesorder.pages','papermatesorder.price','papermatesorder.Deadline',
                'papermatesorder.status','papermatesorder.paperTopic','papermatesorder.paperDescription',
                'papermatesorder.Papercategory as paperTypes',
                'papermatesorder.created_at','papermatesorder.AcademicLevel')
            ->where('papermatesorder.userId' ,'=', auth::id())
            ->get();
        foreach($orderList as $orderList_){
            $order[] = $orderList_;
            if($order){
                ApiResponseBuilder::body(1,"Success",$order,null);
            }else{
                ApiResponseBuilder::body(0,"Failed",null,null);
            }
        }
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function writerPendingOrders(Request $request)
    {
        $this->authorizedUser = $request->user();
        $writerorderList = paperMatesOrder
            ::join('papermatesuser','papermatesuser.UserId','papermatesorder.userId')
            ->select('papermatesuser.username as studentName','papermatesorder.orderId as key'
                ,'papermatesorder.pages','papermatesorder.price','papermatesorder.Deadline',
                'papermatesorder.status','papermatesorder.paperTopic','papermatesorder.paperDescription',
                'papermatesorder.Papercategory as paperTypes',
                'papermatesorder.created_at','papermatesorder.AcademicLevel')
            ->where('papermatesorder.writerId','=',auth::id())
            ->where('papermatesorder.status' ,'=', 'In progress')->get();
        $this->responseData = $writerorderList;
        (!empty($this->responseData)) ? ApiResponseBuilder::body(1,"Success",$this->responseData,null) : ApiResponseBuilder::body(0,"Failed",null,null);
        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function writerCompletedOrders(Request $request)
    {
        $this->authorizedUser = $request->user();
        $writerorderList = paperMatesOrder
            ::join('papermatesuser','papermatesuser.UserId','papermatesorder.userId')
            ->select('papermatesuser.username as studentName','papermatesorder.orderId as key'
                ,'papermatesorder.pages','papermatesorder.price','papermatesorder.Deadline',
                'papermatesorder.status','papermatesorder.paperTopic','papermatesorder.paperDescription',
                'papermatesorder.Papercategory as paperTypes',
                'papermatesorder.created_at','papermatesorder.AcademicLevel')
            ->where('papermatesorder.writerId' ,'=', auth::id())
            ->where('papermatesorder.status' ,'=', 'Completed')
            ->get();
        $this->responseData= $writerorderList;
        (!empty($this->responseData)) ? ApiResponseBuilder::body(1,"Success",$this->responseData,null) : ApiResponseBuilder::body(0,"Failed",null,null);

        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function studentpaper(Request $request)
    {
        $this->authorizedUser = $request->user();
        $writerorderList = paperMatesOrder
            ::join('papermatesuser','papermatesuser.UserId','papermatesorder.userId')
            ->select('papermatesuser.username as studentName','papermatesorder.orderId as key'
                ,'papermatesorder.pages','papermatesorder.price','papermatesorder.Deadline',
                'papermatesorder.status','papermatesorder.paperTopic','papermatesorder.paperDescription',
                'papermatesorder.Papercategory as paperTypes',
                'papermatesorder.created_at','papermatesorder.AcademicLevel')
            ->where('papermatesorder.userId' ,'=', auth::id())
            ->where('papermatesuser.role','=','student')
            ->where('papermatesorder.status' ,'=', 'Completed')
            ->get();

        $this->responseData= $writerorderList;

        (!empty($this->responseData)) ? ApiResponseBuilder::body(1,"Success",$this->responseData,null) : ApiResponseBuilder::body(0,"Failed",null,null);

        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }
    public function orderDocument(Request $request)
    {
        $this->authorizedUser = $request->user();
        $documentsList = paperMatesOrder
            ::join('papermatesuser','papermatesuser.UserId','papermatesorder.userId')
            ->select('paperDocuments as Document','papermatesorder.orderId')
            ->where('papermatesorder.status','Completed')
            ->where('papermatesorder.userId' ,'=', auth::id())
            ->where('papermatesorder.orderId' ,'=', $request->orderId)
            ->get();
        $this->responseData= $documentsList;

        (!empty($this->responseData)) ? ApiResponseBuilder::body(1,"Success",$this->responseData,null) : ApiResponseBuilder::body(0,"Failed",null,null);

        return response()->json(ApiResponseBuilder::getResponse(), 200);
    }

}
