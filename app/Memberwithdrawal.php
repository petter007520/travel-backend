<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Crypt;
use DB;
use Illuminate\Support\Facades\Log;

class Memberwithdrawal extends Model
{
    protected $table="memberwithdrawal";
    protected $primaryKey="id";
    public $timestamps=true;
    protected $guarded=[];
    protected $fillable = [
        'userid',
        'username',
        'amount',
        'memo',
        'status',
        'ip',
        'sendsms'

        ];
    protected $dates = ['created_at', 'updated_at'];


    protected function AddWithdrawal($userid,$Withdrawalamount,$bankid,$after_amount,$fee,$wtype){
        // protected function AddWithdrawal($userid,$Withdrawalamount,$bankid){
        DB::beginTransaction();
        try{
            $Member = Member::where('id', '=', $userid)->lockForUpdate()->first();
            if(!$Member){
				DB::rollBack();
                return ["status"=>0,"msg"=>"会员不存在"];
            }
            if($Member->rw_level < 8 && $wtype==1){
                DB::rollBack();
                return ["status"=>0,"msg"=>"请先完成任务"];
            }
            if($wtype==0){
                if($Member->ktx_amount<$Withdrawalamount){
    				DB::rollBack();
                    return ["status"=>0,"msg"=>"帐户余额不足"];
                }

            }
            if($wtype==1){
                if($Member->rw_amount<$Withdrawalamount){
    				DB::rollBack();
                    return ["status"=>0,"msg"=>"帐户余额不足"];
                }

            }

            $memberbank_info = DB::table('memberbank')->find($bankid);

            $amount=  $Member->ktx_amount;
            $rwamount=  $Member->rw_amount;


            $Model = new Memberwithdrawal();

            $Model->amount=$Withdrawalamount;


            $Model->userid=$Member->id;
            $Model->username=$Member->username;
            $Model->status='0';
            $Model->ip=\Request::getClientIp();
            $Model->sendsms='0';
            $Model->bankid=$bankid;
            // $Model->created_at="2022-10-10 10:10:11";
            $Model->created_at=Carbon::now();
            $Model->created_date=date('Y-m-d');
            $Model->bankname=$memberbank_info->bankname;
            $Model->bankrealname=$memberbank_info->bankrealname;
            $Model->bankcode=$memberbank_info->bankcode;
            $Model->after_amount=$after_amount;
            $Model->after_amount=$after_amount;
            $Model->fee=$fee;
            $Model->wtype=$wtype;
            // DB::beginTransaction();
            // try{
                $re_da =  $Model->save();
                // DB::commit();
            // }catch(\Exception $exception){
            //     return ['status'=>0,'msg'=>'提交失败，请重试'];
            // }
            // $Model->save();
            // $withdrawal_id = $Model->insertGetId();
            if($wtype==1){
                $Member->decrement('rw_amount',$Withdrawalamount);
                if($Member->rw_amount < 0){
                    // $Member->increment('amount',$Withdrawalamount);
                    // $Model->where(['id'=>$Model->id])->delete();
                    DB::rollBack();
                    return ['status'=>0,'msg'=>'提交失败，请重试!'];
                }
                $msg=[
                    "userid"=>$Member->id,
                    "username"=>$Member->username,
                    "title"=>"提款申请",
                    "content"=>"您的提款申请提交成功(".$Withdrawalamount.")",
                    "from_name"=>"系统审核",
                    "types"=>"提款",
                ];
                \App\Membermsg::Send($msg);

                $log=[
                    "userid"=>$Member->id,
                    "username"=>$Member->username,
                    "money"=>$Withdrawalamount,
                    "notice"=>"提款申请(-)",
                    "type"=>"提款",
                    "status"=>"-",
                    "yuanamount"=>$rwamount,
                    "houamount"=>$Member->rw_amount,
                    "ip"=>\Request::getClientIp(),
                    'bank_id'=>$bankid,
                    'moneylog_type_id'=>'6',
                ];

                \App\Moneylog::AddLog($log);


                if($Member->rw_amount < 0){
                    DB::rollBack();
                    return ['status'=>0,'msg'=>'提交失败，请重试!'];
                }
            }else{
                $Member->decrement('ktx_amount',$Withdrawalamount);
                if($Member->ktx_amount < 0){
                    // $Member->increment('amount',$Withdrawalamount);
                    // $Model->where(['id'=>$Model->id])->delete();
                    DB::rollBack();
                    return ['status'=>0,'msg'=>'提交失败，请重试!'];
                }
                $msg=[
                    "userid"=>$Member->id,
                    "username"=>$Member->username,
                    "title"=>"提款申请",
                    "content"=>"您的提款申请提交成功(".$Withdrawalamount.")",
                    "from_name"=>"系统审核",
                    "types"=>"提款",
                ];
                \App\Membermsg::Send($msg);

                $log=[
                    "userid"=>$Member->id,
                    "username"=>$Member->username,
                    "money"=>$Withdrawalamount,
                    "notice"=>"提款申请(-)",
                    "type"=>"提款",
                    "status"=>"-",
                    "yuanamount"=>$amount,
                    "houamount"=>$Member->ktx_amount,
                    "ip"=>\Request::getClientIp(),
                    'bank_id'=>$bankid,
                    'moneylog_type_id'=>'6',
                ];

                \App\Moneylog::AddLog($log);


                if($Member->ktx_amount < 0){
                    DB::rollBack();
                    return ['status'=>0,'msg'=>'提交失败，请重试!'];
                }
            }



            //添加后台统计
            DB::table('statistics_sys')->where('id',1)->increment('withdrawal_amount',$Withdrawalamount);
            DB::commit();

        }catch(\Exception $exception){
            Log::channel('withdrawal')->alert($exception->getMessage());
            DB::rollBack();
            return ['status'=>0,'msg'=>'提交失败，请重试'];
        }

        return ["status"=>1,"msg"=>"申请成功"];

    }


    protected function CancelWithdrawal($id){

        $Model = Memberwithdrawal::find($id);

        if($Model->status==0){
        DB::beginTransaction();
        try{
            $Model->status='-1';
            $Model->save();

            $Member= Member::find($Model->userid);
            if($Model->wtype==1){
                $amountFiled = 'rw_amount';
            }else{
                $amountFiled = 'ktx_amount';
            }
            $amount=  $Member->$amountFiled;
            // 返还提现金额
            $Member->increment($amountFiled,$Model->amount);

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
                "houamount"=>$Member->$amountFiled,
                "ip"=>\Request::getClientIp(),
                'moneylog_type_id'=>'7',
            ];

            \App\Moneylog::AddLog($log);
            DB::commit();

            }catch(\Exception $exception){
                Log::channel('withdrawal')->alert($exception->getMessage());
                DB::rollBack();
                return ['status'=>0,'msg'=>'提交失败，请重试'];
            }

        }




        return ["status"=>0,"msg"=>"取消成功"];

    }

    protected function ConfirmWithdrawal($id){


        $Model = Memberwithdrawal::find($id);

        if($Model->status==0){
             DB::beginTransaction();
            try{
                $Model->status=1;
                $Model->save();

               $Member= Member::find($Model->userid);
               $amount=  $Member->ktx_amount;
                /*if ($amount < 0) {
					return ['status'=>0,'msg'=>'操作失败，用户余额小于0，请检查'];
				}*/
                $msg=[
                    "userid"=>$Model->userid,
                    "username"=>$Model->username,
                    "title"=>"提款成功",
                    "content"=>"您的提款申请成功",
                    "from_name"=>"系统审核",
                    "types"=>"提款",
                ];
                \App\Membermsg::Send($msg);



                 $log=[
                    "userid"=>$Model->userid,
                    "username"=>$Model->username,
                    "money"=>$Model->fee > 0?$Model->after_amount:$Model->amount,
                    "notice"=>"提款成功",
                    "type"=>"提款成功",
                    "status"=>"+",
                    "yuanamount"=>$amount+$Model->amount,
                    "houamount"=>$amount,
                    "ip"=>\Request::getClientIp(),
                    "withdrawal_id"=>$id,
                    'moneylog_type_id'=>'8',
                 ];

                \App\Moneylog::AddLog($log);

                if($Model->fee > 0){
                    $fee_log=[
                    "userid"=>$Model->userid,
                    "username"=>$Model->username,
                    "money"=>$Model->fee,
                    "notice"=>"提款手续费",
                    "type"=>"提款手续费",
                    "status"=>"-",
                    "yuanamount"=>$amount+$Model->amount,
                    "houamount"=>$amount,
                    "ip"=>\Request::getClientIp(),
                    "withdrawal_id"=>$id,
                    'moneylog_type_id'=>'16',
                 ];

                    \App\Moneylog::AddLog($fee_log);
                }

                $now_statistics_date = date('Y-m-d',time());
                $today_withdrawal = $Model->amount;

                $statistics= statistics::select('user_id','team_total_withdrawal')->find($Member->id);
                // $total = $statistics->team_total_withdrawal;
                DB::table('statistics')->where('user_id',$Member->id)->increment('team_total_withdrawal', $Model->amount);
                DB::table('statistics_sys')->where('id',1)->increment('withdrawal_amount', $Model->amount);

                ///////////////////////////////////////////////////////////
                /// 累计天线
                //团队购买累计
                $Nowmember = DB::table("member")->find($Member->top_uid);

                if(!empty($Nowmember)) {
                    $topid = $Nowmember->id;
                    if($topid !=0){
                    //    var_dump(3333);
                        for($i=0;$i<100;$i++){
                     //       var_dump(44444);
                            $topmemeber = Member::find($topid);
                            if(!empty($topmemeber)){

                                $topmemeber->increment('alltixian',$Model->amount);  //总累计

                                if($topmemeber->top_uid==0){
                                    break;
                                }else{
                                    $topid = $topmemeber->top_uid;
                                }
                            }else{
                                break;
                            }
                        }

                    }
                }


                ///////////////////////////////////////////

                // $member_info = DB::table('member')->select('top_uid','ttop_uid')->where('id',$Model->userid)->first();
                // $has_statistics_date = DB::table('statistics_date')->where(['user_id'=>$Model->userid,'statistics_date'=>$now_statistics_date])->first();
                //     if($has_statistics_date){
                //         DB::table('statistics_date')
                //           ->where(['user_id'=>$Model->userid,'statistics_date'=>$now_statistics_date])
                //             ->increment('today_withdrawal',$today_withdrawal);
                //     }else{
                //         $my_statistics_date = [
                //             'user_id'=>$Model->userid,
                //             'username'=>$Model->username,
                //             'today_withdrawal'=>$today_withdrawal,
                //             'statistics_date'=>$now_statistics_date,
                //             'top_one_uid'=>$member_info->top_uid,
                //             'top_two_uid'=>$member_info->ttop_uid,
                //             ];
                //         DB::table('statistics_date')->insert($my_statistics_date);
                //     }
                DB::commit();

            }catch(\Exception $exception){
                Log::channel('withdrawal')->alert($exception->getMessage());
                DB::rollBack();
                return ['status'=>0,'msg'=>'提交失败，请重试'];
            }


        }
      return ["status"=>0,"msg"=>"操作成功"];
    }



	protected function ConfirmWithdrawalThird($id){


        $Model = Memberwithdrawal::find($id);
		$Member= Member::find($Model->userid);

        if($Model->status==0){
            DB::beginTransaction();
            try{
				$AlipayApiUrl_gettoken = 'http://pay.feihuangteng.com/api/agentpay/apply';
				$callback_url = 'https://ex.135023.com/api/online_df_not/ymd';
				$mchid = '185';
				$secretkey = 'NI5JKY1HWYOBPCN2FCKJNUBYY2M3ELSJDR8IB40IEYRHWEGD4F9HT0KEIEALETXQ7BEKJ0WVFZKZ8YJE1EBUI9BJS0OQFIKXLXJZRG5TLZDJVYWG8WDQEBHV7DBCF5Z4';

				$paramArr = [
					"mchId"			=> $mchid,
					"mchOrderNo"	=> $Model->id,
					"amount"		=> $Model->amount,
					"accountType"   => 1,
					"accountName"   => $Member->realname,
					"accountNo"		=> $Model->bankcode,
					"bankName"		=> $Model->bankname,
					"remark"		=> '代付：' . $Model->amount,
					"notifyUrl"		=> $callback_url,
					"reqTime"		=> date("YmdHis"),
				];

				$paramArr['sign'] = $this->setsign($paramArr, $secretkey);


				$post_res = $this->curl($AlipayApiUrl_gettoken, http_build_query($paramArr));
				if(isset($post_res['retCode']) && $post_res['retCode']== 'SUCCESS'){
					$Model->status = 9;
					$Model->save();
					DB::commit();
					return response()->json(["status"=>1, "msg"=>"代付发起成功"]);
				}else{
					if(isset($post_res['retMsg'])){
						$msg = $post_res['retMsg'];
					}else{
						$msg = '代付失败！';
					}
					return response()->json(["status"=>0, "msg"=>$msg]);
				}
            }catch(\Exception $exception){
                dd($exception);
                DB::rollBack();
                return ['status'=>0,'msg'=>'提交失败，请重试'];
            }
        }
		return ["status"=>0,"msg"=>"操作成功"];
    }

    protected function curl($url, $data){
        $headers = array('Content-Type: application/x-www-form-urlencoded');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        return json_decode($result,true);
    }

	protected function setsign($inputArray,$mkey){
		$singStr = "";
		ksort($inputArray);
		foreach ($inputArray as $key => $value) {
			if ($key == "sign") continue;
			if(!empty($value)){
				if ($singStr == "") {
					$singStr .="{$key}={$value}";
				} else {
					$singStr .="&{$key}={$value}";
				}
			}
		}
		$singStr = $singStr . "&key={$mkey}";
		return strtoupper(md5($singStr));
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

 return ["status"=>0,"msg"=>"操作成功"];
        // if($TixianAmounts>$UserMoneys){

        //     return ["status"=>0,"msg"=>"提现失败,投资还差".sprintf("%.2f",($UserMoneys-$TixianAmounts)).",目前可以提现金额:".sprintf("%.2f",($UserMoneys-$WithdrawalAmounts))];
        // }else{
        //     return ["status"=>1,"msg"=>"操作成功"];
        // }




    }





}
