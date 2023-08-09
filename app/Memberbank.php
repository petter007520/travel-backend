<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Crypt;
use DB;

class Memberbank extends Model
{
    protected $table="memberbank";
    protected $primaryKey="id";
    public $timestamps=true;
    protected $guarded=[];
    protected $fillable = [
        'userid',
        'bankname',
        'bankrealname',
        'bankcode',
        'bankaddress',
        'type',
        'status'
        ];
    protected $dates = ['created_at', 'updated_at'];


    protected function AddBank($userid,$bankname,$bankrealname,$bankcode,$bankaddress,$type,$status){

        $Member = Member::find($userid);
        if(!$Member){
            return ["status"=>1,"msg"=>"会员不存在"];
        }

        $Model = new Memberbank();
        $Model->userid=$data['userid'];
        $Model->username=$data['username'];
        $Model->title=$data['title'];
        $Model->content=$data['content'];
        $Model->status=0;
        $Model->from_name=$data['from_name'];
        $Model->types=$data['types'];
        $Model->save();
        
        return ["status"=>0,"msg"=>"添加银行卡成功"];

    }


    protected function CancelWithdrawal($id){

        $Model = Memberwithdrawal::find($id);

        if($Model->status==0){
            $Model->status='-1';
            $Model->save();

            $Member= Member::find($Model->userid);
            $amount=  $Member->amount;
            $Member->increment('amount',$Model->amount);

            $msg=[
                "userid"=>$Model->userid,
                "username"=>$Model->username,
                "title"=>"提款失败",
                "content"=>"您的提款失败(".$Model->amount."元)",
                "from_name"=>"系统审核",
                "types"=>"提款",
            ];
            \App\Membermsg::Send($msg);


            $msg=[
                "userid"=>$Model->userid,
                "username"=>$Model->username,
                "title"=>"提款退回",
                "content"=>"您的提款金额已退回余额中(".$Model->amount."元)",
                "from_name"=>"系统审核",
                "types"=>"提款",
            ];
            \App\Membermsg::Send($msg);



            $log=[
                "userid"=>$Model->userid,
                "username"=>$Model->username,
                "money"=>$Model->amount,
                "notice"=>"提款退回(+)",
                "type"=>"提款",
                "status"=>"+",
                "yuanamount"=>$amount,
                "houamount"=>$Member->amount,
                "ip"=>\Request::getClientIp(),
            ];

            \App\Moneylog::AddLog($log);


        }




        return ["status"=>0,"msg"=>"取消成功"];

    }

    protected function ConfirmWithdrawal($id){


        $Model = Memberwithdrawal::find($id);

        if($Model->status==0){
            $Model->status=1;
            $Model->save();

               $Member= Member::find($Model->userid);
               //$amount=  $Member->amount;
              // $Member->increment('amount',$Model->amount);

                $msg=[
                    "userid"=>$Model->userid,
                    "username"=>$Model->username,
                    "title"=>"提现成功",
                    "content"=>"您的提现申请成功",
                    "from_name"=>"系统审核",
                    "types"=>"充值",
                ];
                \App\Membermsg::Send($msg);


/*
                 $log=[
                            "userid"=>$Model->userid,
                            "username"=>$Model->username,
                            "money"=>$Model->amount,
                            "notice"=>"提现成功",
                            "type"=>"提现",
                            "status"=>"+",
                            "yuanamount"=>$amount,
                            "houamount"=>$Member->amount,
                            "ip"=>\Request::getClientIp(),
                 ];

                \App\Moneylog::AddLog($log);
*/


        }




      return ["status"=>0,"msg"=>"操作成功"];

    }




    //验证提款条件是否符合


    protected function WithdrawalAmount($userid,$Amounts){

        $WithdrawalAmounts = Memberwithdrawal::where("userid",$userid)->whereIn("status",[0,1])->sum("amount");

        $TixianAmounts=  $Amounts+$WithdrawalAmounts;

        $Buymoneys = Moneylog::where("moneylog_userid",$userid)
            ->where("moneylog_status","+")
            ->whereIn("moneylog_type",["每日签到","项目分红","项目本金返款","下线项目分红","下线购买分成"])
            ->sum("moneylog_money");

        $Regmoneys = Moneylog::where("moneylog_userid",$userid)
            ->where("moneylog_status","+")
            ->where("moneylog_type","充值")
            ->where("moneylog_notice","新手礼包(+)")
            ->sum("moneylog_money");

        $UserMoneys=  $Buymoneys+ $Regmoneys;


        if($TixianAmounts>$UserMoneys){

            return ["status"=>1,"msg"=>"提现失败,投资还差".sprintf("%.2f",($UserMoneys-$TixianAmounts)).",目前可以提现金额:".sprintf("%.2f",($UserMoneys-$WithdrawalAmounts))];
        }else{
            return ["status"=>0,"msg"=>"操作成功"];
        }




    }





}
