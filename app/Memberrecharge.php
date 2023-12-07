<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Session;
use Cache;

class Memberrecharge extends Model
{
    protected $table="memberrecharge";
    protected $primaryKey="id";
    public $timestamps=true;
    protected $guarded=[];
    protected $fillable = [
        'ordernumber',
        'userid',
        'username',
        'amount',
        'usdt_amount',
        'memo',
        'paymentid',
        'status',
        'paytime',
        'payimg',
        'ip',
        'bank',
        'accNo',
        'sendsms',
        'type',
        'recharge_type'
        ];
    protected $dates = ['created_at', 'updated_at'];


    protected function Recharge($Data){
      $Rec=  new Memberrecharge();
      $Rec->ordernumber= $Data['ordernumber'] ?? Carbon::now()->format("YmdHis") . rand(10000000, 99999999);
      $Rec->userid=$Data['userid'];
      $Rec->username= $Data['username'] ?? DB::table("member")->where("id", $Data['userid'])->value("username");
      $Rec->amount=$Data['amount'];
      $Rec->usdt_amount=$Data['usdt_amount'];
      $Rec->ip=$Data['ip'];
      $Rec->type=$Data['type'];
      $Rec->recharge_type=$Data['recharge_type'] ?? 1;
      $Rec->paymentid=$Data['paymentid'];
      if(isset($Data['payimg'])){
        $Rec->payimg=$Data['payimg'];
        }
      $Rec->memo=$Data['memo'];
      $Rec->status= $Data['status'] ?? 0;
      $Rec->sendsms=0;
      $Rec->save();
     return ["status"=>0,"data"=>$Rec];
    }

    protected function ConfirmRecharge($id,$status){
        $Model = Memberrecharge::find($id);
        if($Model->status==0){
            $Model->status=$status;
            $Model->paytime==date('Y-m-d H:i:s',time());
            $Model->save();
            $type = 1;
            if($status==1){
                $Member= Member::find($Model->userid);
                $amount =  0;
                $houamount = 0;
                $money = 0;
                $notice = '';
                if($Model->recharge_type ==1){
                    $amount=  $Member->ktx_amount;
                    $notice = '充值成功(CNY)';
                    $money = $Model->amount;
                    $Member->increment('ktx_amount',$money);
                    $houamount = $Member->ktx_amount;
                }
                if($Model->recharge_type == 2){
                    $notice = '充值成功(USDT)';
                    $amount=  $Member->usdt_amount;
                    $money = $Model->usdt_amount;
                    $Member->increment('usdt_amount',$money);
                    $houamount = $Member->usdt_amount;
                }
                $Member->save();
                $log=[
                    "userid"=>$Model->userid,
                    "username"=>$Model->username,
                    "money"=>$money,
                    "notice"=>$notice,
                    "type"=>"充值",
                    "status"=>"+",
                    "yuanamount"=>$amount,
                    "houamount"=>$houamount,
                    "ip"=>\Request::getClientIp(),
                    "recharge_id"=>$id,
                ];
                \App\Moneylog::AddLog($log);

                $msg=[
                    "userid"=>$Model->userid,
                    "username"=>$Model->username,
                    "title"=>"充值订单",
                    "content"=>"您的充值成功(".$Model->amount.")",
                    "from_name"=>"系统审核",
                    "types"=>"充值",
                ];
                \App\Membermsg::Send($msg);

                //添加个人统计
                DB::table('statistics')->where('user_id',$Model->userid)->increment('team_total_recharge',$Model->amount);
                //添加后台统计
                DB::table('statistics_sys')->where('id',1)->increment('recharge_amount',$Model->amount);
            }
        }

      return ["status"=>0];

    }


}
