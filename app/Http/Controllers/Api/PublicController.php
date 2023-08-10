<?php

namespace App\Http\Controllers\Api;
use App\Auth;
use App\Category;
use App\Channel;
use App\Log;
use App\Member;
use App\Memberphone;
use App\Order;
use App\Memberlevel;
use App\Product;
use App\Productbuy;
use App\Membercurrencys;
use Carbon\Carbon;
use App\Admin;
use App\Ad;
use App\Site;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Mews\Captcha\LumenCaptchaController;
use Session;
use Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing;
use Illuminate\Support\Facades\Log as LogLog;
use Illuminate\Support\Facades\App;


class PublicController
{
    public $cachetime=600;
    public $Template='wap';


    public function __construct(Request $request)
    {
       $this->Template=env("WapTemplate");

       /**网站缓存功能生成**/

       if(!Cache::has('setings')){
           $setings=DB::table("setings")->get();

           if($setings){
               $seting_cachetime=DB::table("setings")->where("keyname","=","cachetime")->first();

               if($seting_cachetime){
                   $this->cachetime=$seting_cachetime->value;
                   Cache::forever($seting_cachetime->keyname, $seting_cachetime->value);
               }

               foreach($setings as $sv){
                   Cache::forever($sv->keyname, $sv->value);
               }
               Cache::forever("setings", $setings);
           }

       }

       $this->cachetime=Cache::get('cachetime');

       /**菜单导航栏**/
       if(Cache::has('wap.category')){
           $footcategory=Cache::get('wap.category');
       }else{
           $footcategory= DB::table('category')->where("atfoot","1")->orderBy("sort","desc")->limit(5)->get();
           Cache::put('wap.category',$footcategory,$this->cachetime);
       }
       // view()->share("footcategory",$footcategory);
       /**菜单导航栏 END **/

    }


    public function login(Request $request){
        if($request->username==''){
            return response()->json(["status"=>0,"msg"=>"用户帐号不能为空"]);
        }
        $login_type = !empty($request->logintype)?$request->logintype:1;
        if($login_type==1){
            if($request->password==''){
                return response()->json(["status"=>0,"msg"=>"帐号密码不能为空"]);
            }
        }else{
            if(empty($request->code)){
                return response()->json(["status"=>0,"msg"=>"短信验证码不能为空"]);
            }
            $mcode = $request->code;
        }

        $Member = Member::where("username",$request->username)->first();
        DB::beginTransaction();
        try{
            if(!$Member){
                /**登录日志**/
                $data['userid']=0;
                $data['username']=$request->username;
                $data['memo']="尝试登录(".$request->password.")";
                $data['status']=0;
                $data['ip']=$request->getClientIp();
                $data['created_at']=$data['updated_at']=Carbon::now();
                DB::table('memberlogs')->insert($data);
                DB::commit();
                return response()->json(["status"=>0,"msg"=>"账号或密码错误!"]);
            }else{

                if($Member->state=='-1' || $Member->state=='0'){
                    /**登录日志**/
                    $data['userid']=$Member->id;
                    $data['username']=$Member->username;
                    $data['memo']="帐号禁用中";
                    $data['status']=0;
                    $data['ip']=$request->getClientIp();
                    $data['created_at']=$data['updated_at']=Carbon::now();
                    DB::table('memberlogs')->insert($data);
                    DB::commit();
                    return response()->json(["status"=>0,"msg"=>"帐号禁用中"]);
                }

                if($login_type==1){
                    $password=  \App\Member::DecryptPassWord($Member->password);
                }

                if(($password==$request->password)){

                //  if($mcode == 8597 && ($sms_code || $sms_code->code == $mcode)){

                    $request->session()->put('UserId',$Member->id, 120);
                    $request->session()->put('UserName',$Member->username, 120);
                    $request->session()->put('Member',$Member, 120);
//                    if( $request->is_red != 1){
                        $Member->lastsession = \App\Member::EncryptPassWord(Carbon::now().$Member->id);
//                    }
                    $Member->logintime=Carbon::now();
                    $today_date = date('Y-m-d 00:00:00', time());
                    $yesterday_date = date('Y-m-d 00:00:00', time() - 86400);

                    $login_yesterday = DB::table('memberlogs')
                        ->where(['userid' => $Member->id])
                        ->where('status', '=', 1)
                        ->whereBetween('created_at', [$yesterday_date, $today_date])
                        ->first();

                    $login_today = DB::table('memberlogs')
                        ->where(['userid' => $Member->id])
                        ->where('status', '=', 1)
                        ->where('created_at', '>', $today_date)
                        ->first();


                    if (!$login_today){
                        if ($login_yesterday) {
                            $Member->login_times = $Member->login_times + 1;
                        } else {
                            $Member->login_times = 1;
                        }
                    }
                    $Member->save();

                    /**登录日志**/
                    $data['userid']=$Member->id;
                    $data['username']=$Member->username;
                    $data['memo']="登录成功";
                    $data['status']=1;
                    $data['ip']=$request->getClientIp();
                    $data['created_at']=$data['updated_at']=Carbon::now();
                    DB::table('memberlogs')->insert($data);

                    // //免费赠送产品（只赠送一次）
                    // if($Member->isquestion == 0){
                    //     $free_gift_pid = DB::table('setings')->where('keyname','free_gift_pid')->value('value');
                    //     $free_gift_num = DB::table('setings')->where('keyname','free_gift_num')->value('value');
                    //     if($free_gift_pid >0 && $free_gift_num >0){
                    //         $free_gift_info = DB::table('products')->select('id','category_id','title','qtje','hkfs','shijian')->where(['id'=>$free_gift_pid])->first();
                    //         $has_pb = DB::table('productbuy')->where(['productid'=>$free_gift_pid,'userid'=>$Member->id])->first();
                    //         // dump($free_gift_info);
                    //         // dump($has_pb);
                    //         // exit;
                    //         if($free_gift_info && !$has_pb){
                    //             //赠送总金额
                    //             $free_give_product_money = $free_gift_num * $free_gift_info->qtje;

                    //             //判断下一次领取时间
                    //             $hkfs = $free_gift_info->hkfs;
                    //             $zhouqi    = trim($free_gift_info->shijian);//周期
                    //             $sendDay_count = $hkfs == 1?1:$zhouqi;
                    //             //免费赠送的固定120天
                    //             $useritem_time2 = \App\Productbuy::DateAdd("d",120, date('Y-m-d 0:0:0',time()));

                    //             $NewProductbuy= new Productbuy();
                    //             $NewProductbuy->userid=$Member->id;
                    //             $NewProductbuy->username=$Member->username;
                    //             $NewProductbuy->productid=$free_gift_pid;
                    //             $NewProductbuy->category_id=$free_gift_info->category_id;
                    //             $NewProductbuy->amount= $free_give_product_money;
                    //             $NewProductbuy->ip= \Request::getClientIp();
                    //             $NewProductbuy->useritem_time = Carbon::now();
                    //             $NewProductbuy->useritem_time2=$useritem_time2;
                    //             $NewProductbuy->reason = "免费赠送产品(".$free_gift_info->title.")";
                    //             $NewProductbuy->sendDay_count=$sendDay_count;
                    //             $NewProductbuy->num = $free_gift_num;//赠送数量
                    //             $NewProductbuy->unit_price = $free_gift_info->qtje;//赠送时单价
                    //             $NewProductbuy->zsje=0;//赠送金额
                    //             $NewProductbuy->zscp_id=0;//
                    //             //固定120天，不设-1
                    //             // $NewProductbuy->gq_order = '-1';
                    //             $NewProductbuy->created_date = date('Y-m-d');
                    //             // $NewProductbuy->order = substr((date('YmdHis').$RegMember->id.$this->get_random_code(6)),0,25);
                    //             $res = $NewProductbuy->save();
                    //             //站内消息
                    //             $msg=[
                    //                 "userid"=>$Member->id,
                    //                 "username"=>$Member->username,
                    //                 "title"=>"免费赠送产品",
                    //                 "content"=>"成功加入项目(".$free_gift_info->title.")",
                    //                 "from_name"=>"系统通知",
                    //                 "types"=>"加入项目",
                    //             ];
                    //             \App\Membermsg::Send($msg);
                    //             //
                    //             $give_log=[
                    //                 "userid"=>$Member->id,
                    //                 "username"=>$Member->username,
                    //                 "money"=> $free_give_product_money,
                    //                 "notice"=>"免费赠送产品(".$free_gift_info->title.")[".$NewProductbuy->id."]",
                    //                 "type"=>"免费赠送项目",
                    //                 "status"=>"+",
                    //                 "yuanamount"=>0,
                    //                 "houamount"=>0,
                    //                 "ip"=>\Request::getClientIp(),
                    //                 "category_id"=>$free_gift_info->category_id,
                    //                 "product_id"=>$free_gift_info->id,
                    //                 "product_title"=>$free_gift_info->title,
                    //             ];
                    //             \App\Moneylog::AddLog($give_log);

                    //             DB::table('statistics')->where(['user_id'=>$Member->id])->increment('team_capital_flow',$free_give_product_money);
                    //             //更新赠送状态
                    //             $Member->isquestion = 1;
                    //             $Member->save();
                    //         }
                    //     }

                    // }
                    // //免费赠送产品END

                    $resdata['userid']=$Member->id;
                    $resdata['nickname']=$Member->nickname;
                    $resdata['gender']=$Member->gender;
                    // $resdata['mobile']=\App\Member::half_replace(\App\Member::DecryptPassWord($Member->mobile));  //手机号
                    $resdata['mobile'] = $resdata['all_mobile']=$Member->username;
                    $resdata['hidden_mobile'] = substr_replace($Member->username, '****', 3,5);
                    $resdata['picImg']=$Member->picImg;
                    $resdata['invicode']=$Member->invicode;
                    $resdata['lastsession']=$Member->lastsession;
                    // $resdata['level']=$Member->level;
                    // $resdata['realname']=$Member->realname;
                    // $resdata['card']=$Member->card;
                    $resdata['im_link'] = DB::table("setings")->where('keyname','im_link')->value('value');//客服链接

                    $memberidentity = DB::table("memberidentity")
                        ->select('idnumber','realname','facade_img','revolt_img','status')
                        ->where(['userid'=>$Member->id])->first();
                    if($memberidentity){//-1:未认证  0：审核中   1：已认证
                       // $resdata['card'] = \App\Member::half_replace($memberidentity->idnumber);
                        $resdata['card'] = $memberidentity->idnumber;
                        $resdata['realname'] = $memberidentity->realname;
                        $resdata['facade_img'] = $memberidentity->facade_img;
                        $resdata['revolt_img'] = $memberidentity->revolt_img;
                        $resdata['status'] = $memberidentity->status;
                    }else{
                        $resdata['status'] = -1;
                        $resdata['card'] = $resdata['realname'] = $resdata['facade_img'] = $resdata['revolt_img'] = '';
                    }
                    DB::commit();
                    if (!$login_today){
                        $user_id = $Member->id;
                        $score = $Member->login_times * 10;
                        $type = 1;
                        $source_type = $Member->login_times > 1 ? 3 : 4;
                        $act = APP::make(\App\Http\Controllers\Api\ActController::class);
                        App::call([$act, 'change_score_by_user_id'], [$user_id, $score, $type, $source_type]);
                    }
                    return response()->json(["status"=>1,"msg"=>"登录成功","data"=>$resdata]);

                }else{

                    if($login_type==1){
                        $memo = '账号或密码错误';
                    }else{
                        $memo = '短信验证码错误';
                    }

                    /**登录日志**/
                    $data['userid']=$Member->id;
                    $data['username']=$Member->username;
                    $data['memo']=$memo;
                    $data['status']=0;
                    $data['ip']=$request->getClientIp();
                    $data['created_at']=$data['updated_at']=Carbon::now();
                    DB::table('memberlogs')->insert($data);
                    DB::commit();
                    return response()->json(['msg'=>$memo."，请重新输入",'status'=>"0"]);
                }
            }
        }catch(\Exception $exception){
            LogLog::channel('reg')->alert('login:'.$exception->getMessage());
            DB::rollBack();
            return ['status'=>0,'msg'=>'提交失败，请重试'];
        }
    }


    public function loginout(Request $request){
        $lastsession = $request->lastsession;
        if($lastsession){
            $Member = Member::where("lastsession",$request->lastsession)->first();
            if($Member){
                $Member->lastsession = '';
                $Member->save();
            }
        }
        return response()->json(["status"=>1,"msg"=>"退出成功!"]);
    }

   public function register(Request $request){

            $tel   = $request->phone;//手机号
            $mima  = $request->password;//密码
            $mcode = $request->code;//短信验证码
            $username = $tel;
            $yaoqingren = $request->invicode;//邀请码
            // $card = $request->card;
            $paypwd = $request->paypwd;//交易密码

            // $username =  trim($username);
            $yaoqingren=htmlspecialchars($yaoqingren);
            $yaoqingren=intval($yaoqingren);
            $yaoqingrenvv =  trim($yaoqingren);


            if( $mima== '' || $tel == '' || $yaoqingren == '' || $paypwd == ''){
                return array('msg'=>"请填写完整信息",'status'=>"0");
            }

            if(strlen($yaoqingrenvv) >= 0){
                //判断是否存在
                $yaoqingrenvvex= Member::select('id','username','top_uid','ttop_uid','activation','mtype','level', 'invilast')->where("invicode",$yaoqingrenvv)->first();
                if(!$yaoqingrenvvex){
                    return array('msg'=>"您输入的邀请人推荐ID不存在",'status'=>"0");
                } else {
					/*if ($yaoqingrenvvex->invilast + 2 * 60 > time()) {
						return array('msg'=>"邀请码使用太频繁，请稍后再试",'status'=>"0");
					}*/
					$data = [
						'invilast' => time()
					];
					Member::where("invicode",$yaoqingrenvv)->update($data);
				}
            }


            $usernameex= Member::select('id')->where("username",$username)->first();
            if($usernameex){
                return array('msg'=>"您输入的账号已经存在",'status'=>"0");
            }

            $mobile = trim($tel);
            if(strlen($mobile) !== 11){
                return array('msg'=>"您输入的手机位数不对",'status'=>"0");
            }

            // if($yaoqingrenvv == $username){
            //     return array('msg'=>"邀请人不能为自己账号",'status'=>"0");
            // }
            if(strlen($request->paypwd) != 6 || !is_numeric($request->paypwd)){
                return array('msg'=>"交易密码需是6位纯数字",'status'=>"0");
            }

			$smsverifi = DB::table("setings")->where('keyname','smsverifi')->value('value');//卖出提示
			if ($smsverifi == "开启") {
				$check_time = strtotime("-10 minute");
				$sms_code = DB::table('membersms')
				 ->where(['mobile'=>$mobile,'sms_status'=>1,'type'=>1])
				 ->where('create_time','<=',time())
					->where('create_time','>=',$check_time)
				 ->orderBy('create_time','desc')->first();
				 if(!$sms_code || $sms_code->code != $mcode){
					 return array('msg'=>"短信验证码错误，请重新输入",'status'=>"0");
				 }
			}
          //  $regist_amount = DB::table('setings')->where(['keyname'=>'regist_gift'])->value('value');//注册赠送金额
            $regist_amount =0;//注册赠送金额

            $RegMember=new Member();
    DB::beginTransaction();
    try{
        $now_date = date('Y-m-d');

            $RegMember->username=$username;
            $RegMember->nickname=$username;
            $RegMember->password=\App\Member::EncryptPassWord($request->password);
            // $RegMember->paypwd='eyJpdiI6IlZUUms1bUVrY2d5SXFKKzBCV2JNU1E9PSIsInZhbHVlIjoiMWMzVEVMMHQ0Y3ZXS3c2alJqTnZiUT09IiwibWFjIjoiODQ0ZDg2NTE1NDliZTcyNDZiODJhMGM5YTAxMWNlNTEyYjg4MDVkNjJjMWFkY2QwZTMxNmM5OGI2OWUxMWYyZCJ9';
            $RegMember->paypwd=\App\Member::EncryptPassWord($request->paypwd);
            $RegMember->mobile=\App\Member::EncryptPassWord($tel);
            $RegMember->inviter=$yaoqingren;
            $RegMember->tttop_uid=$yaoqingrenvvex['ttop_uid'];
            $RegMember->ttop_uid=$yaoqingrenvvex['top_uid'];
            $RegMember->top_uid=$yaoqingrenvvex['id'];
            // $RegMember->realname=$request->realname;
            $RegMember->picImg=20;//rand(1,8);
            $RegMember->gender=1;
            // $RegMember->qq=$request->qq;
            $RegMember->ip=$request->getClientIp();
            $RegMember->reg_from='wap/register';
        //    $RegMember->amount = $regist_amount>0?$regist_amount:0;//
            $RegMember->amount = $regist_amount>0?$regist_amount:0;//
            $RegMember->created_date=$now_date;
            $RegMember->save();


			$invitor = DB::table('member')
			->where(['invicode' => $yaoqingren])
			->first();
			if ($invitor && $invitor->id);
			{
                /*$Memberzc = Member::find($invitor->id);
                $Memberzc->increment('zc_num',1);
                $zc_num = DB::table("setings")->where('keyname','zc_num')->value('value');
                if($Memberzc->rw_leve == 0 &&$Memberzc->zc_num >= $zc_num){
                    $Memberzc->increment('rw_level',1);
                }*/
				$user_id = $invitor->id;
				$score = 100;
				$type = 1;
				$source_type = 6;

				$act = APP::make(\App\Http\Controllers\Api\ActController::class);
				App::call([$act, 'change_score_by_user_id'], [$user_id, $score, $type, $source_type]);
			}




            //赠送产品总金额，后面统计表有用到
            $reg_give_product_money = 0;

            //注册赠送余额
            if($regist_amount>0){
                $regist_amount_log=[
                    "userid"=>$RegMember->id,
                    "username"=>$username,
                    "money"=> $regist_amount,
                    "notice"=>"注册赠送金额(".$regist_amount.")",
                    "type"=>"注册赠送金额",
                    "status"=>"+",
                    "yuanamount"=>0,
                    "houamount"=>$regist_amount,
                    "ip"=>\Request::getClientIp(),
                    "category_id"=>0,
                    "product_id"=>0,
                    "product_title"=>0,
                    'moneylog_type_id'=>'11',
                ];
                \App\Moneylog::AddLog($regist_amount_log);
            }

            //注册赠送产品
            $reg_give_product_id = DB::table('setings')->where(['keyname'=>'regist_gift_pid'])->value('value');
            if($reg_give_product_id > 0 ){
                $reg_give_product_info = DB::table("products")
                    ->select('id','title','category_id','qtje','isft','tzzt','hkfs','shijian','zgje','qxdw','zsje','zsje_type','jyrsy','qtsl','zscp_id')
                    ->where(['id'=>$reg_give_product_id])
                    ->first();
                if($reg_give_product_info){
                    //赠送数量
                    $reg_give_product_pcount = DB::table('setings')->where(['keyname'=>'regist_gift_pcount'])->value('value');
                    //赠送总金额
                    $reg_give_product_money = $reg_give_product_pcount * $reg_give_product_info->qtje;

                    //判断下一次领取时间
                    $hkfs = $reg_give_product_info->hkfs;
                    $zhouqi    = trim($reg_give_product_info->shijian);//周期
                    $sendDay_count = $hkfs == 1?1:$zhouqi;

                    $useritem_time2 = \App\Productbuy::DateAdd("d",1, date('Y-m-d 0:0:0',time()));

                    $NewProductbuy= new Productbuy();
                    $NewProductbuy->userid=$RegMember->id;
                    $NewProductbuy->username=$username;
                    $NewProductbuy->productid=$reg_give_product_id;
                    $NewProductbuy->category_id=$reg_give_product_info->category_id;
                    $NewProductbuy->amount= $reg_give_product_money;
                    $NewProductbuy->ip= \Request::getClientIp();
                    $NewProductbuy->useritem_time = Carbon::now();
                    $NewProductbuy->useritem_time2=$useritem_time2;
                    $NewProductbuy->reason = "注册赠送产品(".$reg_give_product_info->title.")";
                    $NewProductbuy->sendDay_count=$sendDay_count;
                    $NewProductbuy->num = $reg_give_product_pcount;//赠送数量
                    $NewProductbuy->unit_price = $reg_give_product_info->qtje;//赠送时单价
                    $NewProductbuy->zsje=0;//赠送金额
                    $NewProductbuy->zscp_id=0;//
                    $NewProductbuy->created_date = $now_date;
                    // $NewProductbuy->order = substr((date('YmdHis').$RegMember->id.$this->get_random_code(6)),0,25);
                    $res = $NewProductbuy->save();
                    //站内消息
                    $msg=[
                        "userid"=>$RegMember->id,
                        "username"=>$username,
                        "title"=>"注册赠送产品",
                        "content"=>"成功加入项目(".$reg_give_product_info->title.")",
                        "from_name"=>"系统通知",
                        "types"=>"加入项目",
                    ];
                    \App\Membermsg::Send($msg);
                    //
                    $give_log=[
                        "userid"=>$RegMember->id,
                        "username"=>$username,
                        "money"=> $reg_give_product_money,
                        "notice"=>"注册赠送产品(".$reg_give_product_info->title.")[".$NewProductbuy->id."]",
                        "type"=>"注册赠送项目",
                        "status"=>"+",
                        "yuanamount"=>0,
                        "houamount"=>0,
                        "ip"=>\Request::getClientIp(),
                        "category_id"=>$reg_give_product_info->category_id,
                        "product_id"=>$reg_give_product_info->id,
                        "product_title"=>$reg_give_product_info->title,
                        'moneylog_type_id'=>'9',
                    ];
                    \App\Moneylog::AddLog($give_log);

                    //如果赠送的是货币，添加到会员货币表
                    if($reg_give_product_info->category_id == 11){
                        $total_num = 0;

                        $zscp_currencys_info = DB::table('membercurrencys')->where(['userid'=>$RegMember->id,'productid'=>$reg_give_product_info->id])->orderBy('created_at','desc')->first();
                        if($zscp_currencys_info){
                            // $update_currencys['num'] = $user_currencys_info->num + $request->number;
                            // $update_currencys['total_num'] = $user_currencys_info->total_num + $request->number;
                            $zscp_update_currencys['updated_at'] = Carbon::now();
                            DB::table('membercurrencys')->where(['userid'=>$RegMember->id,'productid'=>$reg_give_product_info->id])->increment('num',$reg_give_product_pcount);
                            DB::table('membercurrencys')->where(['userid'=>$RegMember->id,'productid'=>$reg_give_product_info->id])->increment('total_num',$reg_give_product_pcount);
                        }else{
                            $zscp_insert['userid'] = $RegMember->id;
                            $zscp_insert['productid'] = $reg_give_product_info->id;
                            $zscp_insert['num'] = $reg_give_product_pcount;
                            $zscp_insert['total_num'] = $reg_give_product_pcount;
                            $zscp_insert['created_at'] = $zscp_insert['updated_at'] = Carbon::now();

                            DB::table('membercurrencys')->insert($zscp_insert);
                        }
                    }

                    $my_statistics['team_capital_flow'] = $reg_give_product_money;
                    // $my_statistics_data['capital_flow'] = $reg_give_product_money;
                }
            }
            //赠送End

            //添加层级表
            // $now_date = date('Y-m-d H:i:s');
            // $membergrade = DB::table('membergrade')->where('uid',$yaoqingrenvvex['id'])->get();
            // $reg_grade = [];
            // $top_two_uid = $top_three_uid = $top_four_uid = $top_five_uid = $invite_count = 0;
            // $reg_grade[] = ['pid'=>$yaoqingrenvvex['id'],'uid'=>$RegMember->id,'level'=>1,'created_at'=>$now_date];
            // foreach ($membergrade as $v){
            //     if($v->level == 1){$top_two_uid = $v->pid;}
            //     if($v->level == 2){$top_three_uid = $v->pid;}
            //     if($v->level == 3){$top_four_uid = $v->pid;}
            //     if($v->level == 4){$top_five_uid = $v->pid;}
            //     $invite_count += 1;
            //     $reg_grade[] = ['pid'=>$v->pid,'uid'=>$RegMember->id,'level'=>$v->level + 1,'created_at'=>$now_date];
            // }
            // DB::table('membergrade')->insert($reg_grade);
            //层级表end

            //添加统计表
            $my_statistics['user_id'] = $RegMember->id;
            $my_statistics['username'] = $username;
            $my_statistics['top_one_uid'] = $yaoqingrenvvex['id'] > 0?$yaoqingrenvvex['id'] :0;
            $my_statistics['top_two_uid'] = $yaoqingrenvvex['top_uid'] > 0?$yaoqingrenvvex['top_uid'] :0;
            $my_statistics['top_three_uid'] = $yaoqingrenvvex['ttop_uid'] > 0?$yaoqingrenvvex['ttop_uid'] :0;
            $my_statistics['created_at'] = Carbon::now();
            $my_statistics['register_date'] = date('Y-m-d');

            DB::table('statistics')->insert($my_statistics);

            //后台统计
            DB::table('statistics_sys')->where('id',1)->increment('user_num',1);
            //统计表end

            if($RegMember){
                //获取自增ID，用以插入用户的推荐码
                $invicode = $this->get_random_code(7);
                while(DB::table('member')->where('invicode',$invicode)->first()){
                    $invicode = $this->get_random_code(7);
                }
                $RegMember->invicode=$invicode;
                $RegMember->lastsession = \App\Member::EncryptPassWord(Carbon::now().$RegMember->id);
                $RegMember->save();

                $resdata['AppDownloadUrl'] = DB::table('setings')->where(['keyname'=>'AppDownloadUrl'])->value('value');
                $resdata['HotAppDownloadUrl'] = DB::table('setings')->where(['keyname'=>'HotAppDownloadUrl'])->value('value');
                $resdata['userid']=$RegMember->id;
                $resdata['nickname']=$RegMember->nickname;
                $resdata['gender']=$RegMember->gender;
                $resdata['mobile']=$RegMember->username;  //手机号
                $resdata['picImg']=$RegMember->picImg;
                $resdata['lastsession']=$RegMember->lastsession;

                DB::commit();

                return array('msg'=>"恭喜您注册成功！",'status'=>"1",'data'=>$resdata);
            }else{
                DB::rollBack();
                return array('msg'=>"注册失败,请重新注册",'status'=>"0");
            }
        }catch(\Exception $exception){
            LogLog::channel('reg')->alert($exception->getMessage());
            DB::rollBack();
            return ['status'=>0,'msg'=>'提交失败，请重试'];
        }

    }

    // public function sendsms(Request $request){

    //     if($request->isMethod("post")){

    //         $rules = ['captcha' => 'required|captcha'];
    //         $validator = Validator::make($request->all(), $rules);

    //         if($validator->fails()) {

    //             return [
    //                 'status' => 1,
    //                 'msg' => '验证码错误'
    //             ];
    //         }

    //         $action='regcode';
    //         /*if($request->action!=''){
    //             $action= $request->action;
    //         }*/

    //         \App\Sendmobile::SendPhone($request->tel,$action,'');//短信通知

    //         if($request->ajax()){
    //             return response()->json([
    //                 "msg"=>"短信验证码发送成功","status"=>0
    //             ]);
    //         }

    //     }

    // }


    public function forgot(Request $request){

        //  return response()->json(["status"=>0,"msg"=>"目前无法修改密码，请联系客服"]);

         $mobile = $request->mobile;
         $password = $request->password;
         $code = $request->code;
        if (!$mobile) {
            return response()->json(["status"=>0,"msg"=>"手机号不能为空"]);
        }
       if(strlen($mobile) !== 11){
           return response()->json(["status"=>0,"msg"=>"您输入的手机位数不对"]);
        }
         if (!$password || $password=='') {
            return response()->json(["status"=>0,"msg"=>"密码不能为空"]);
        }
        // $phone = \App\Member::EncryptPassWord($mobile);
        // $has_mobile = DB::table('member')->where(['mobile'=>$phone])->first();
    //   $isPhones =  $this->has_phone($mobile);
        $isPhones = DB::table('member')->where(['username'=>$mobile])->first();
        if(!$isPhones){
            return response()->json(["status"=>0,"msg"=>"该手机号未注册"]);
        }

        $check_time = strtotime("-10 minute");
             $sms_code = DB::table('membersms')
             ->where(['mobile'=>$mobile,'sms_status'=>1,'type'=>2])
             ->where('create_time','<=',time())
                ->where('create_time','>=',$check_time)
             ->orderBy('create_time','desc')->first();

        //  if($code != 8597 && (!$sms_code || $sms_code->code != $code)){
         if(!$sms_code || $sms_code->code != $code){
             return response()->json(["status"=>0,"msg"=>"短信验证码错误，请重新输入"]);
         }

         //        $new_pwd = \App\Member::EncryptPassWord($password);
//        $update['password'] = $new_pwd;
        $EditMember= Member::where("id",$isPhones->id)->first();
        $EditMember->password=\App\Member::EncryptPassWord($password);
//        $res = DB::table('member')->where(['id'=>$isPhones])->update($update);
        if($EditMember->save()){
            return response()->json(["status"=>1,"msg"=>"密码重置成功"]);
        }else{
            return response()->json(["status"=>0,"msg"=>"操作失败"]);
        }
    }


    //生成字符串--密码
    public  function getRandChar($length=6){
        $str = null;
        $strPol = "abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];
        }
        return $str;
    }

    public function checkusername(Request $request){

        $username =  trim($request->username);
        if(strlen($username) < 2 && strlen($username) > 32){

            return response()->json([
                "msg"=>"您输入的账号位数有误","status"=>0
            ]);
        }
        $m= Member::where("username",$username)->first();
        if($m){
            return response()->json([
                "msg"=>"您输入的账号已经存在","status"=>0
            ]);
        }else{
            return response()->json([
                "msg"=>"通过","status"=>1
            ]);
        }
    }



    public function QrCode(Request $request){
        header( "Content-type: image/jpeg");
    }


   public function uploadImg(Request $request,$type = null)
    {

        $file = $request->file('payimg'); // 获取上传的文件

        if ($file==null) {
            return response()->json(["msg"=>"还未上传文件","status"=>0]);
        }
        // 获取文件后缀
        $temp = explode(".", $_FILES["payimg"]["name"]);
        $extension = end($temp);
        // 判断文件是否合法
        if(!in_array($extension, array("gif","GIF","jpg","JPG","jpeg","JPEG","png","PNG","bmp","BMP"))){
            return response()->json(["status"=>0,"msg"=>"上传图片不合法"]);
        }
        if($type==null){
            if($_FILES['payimg']['size']>5*1024*1024){
                return response()->json(["status"=>0,"msg"=>"上传图片大小不能超过5M"]);
            }
        }

        $time = date("Ymd",time());

        $path_origin = 'files/'.$time.'';

        $res = Storage::disk('uploads')->put($path_origin, $file);

        return response()->json(["status"=>1,"msg"=>"上传凭证成功","data"=>"uploads/".$res]);

    }

    //im推广页说明
    public function extension(){
        /*推广*/
        $data['extension'] = Db::table("setings")->where('keyname','extension')->value('value');
        return response()->json(["status"=>1,"msg"=>"返回成功","data"=>$data]);
    }

    public function imcurl($url,$form_data){

        $ch = curl_init();
        /** curl_init()需要php_curl.dll扩展 **/
        $timeout = 5;
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $form_data);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        return $file_contents;
    }

    function get_random_code($num)
    {
        // $codeSeeds = "ABCDEFGHIJKLMNPQRSTUVWXYZ";
        // $codeSeeds .= "abcdefghijklmnpqrstuvwxyz";
        // $codeSeeds .= "0123456789_";
        $codeSeeds = "1234567890";
        $len = strlen($codeSeeds);
        $code = "";
        for ($i = 0; $i < $num; $i++) {
            $rand = rand(0, $len - 1);
            $code .= $codeSeeds[$rand];
        }
        return $code;
    }

    function curl_get($url){

        $header = array(
            'Accept: application/json',
        );
        $curl = curl_init();

        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 超时设置,以秒为单位
        curl_setopt($curl, CURLOPT_TIMEOUT, 1);

        // 超时设置，以毫秒为单位
        // curl_setopt($curl, CURLOPT_TIMEOUT_MS, 500);

        // 设置请求头
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //执行命令
        $data = curl_exec($curl);
        return $data;
    }

    public function sendMsm(Request $request){
        $mobile = $request->mobile;
        if (!$mobile) {
            return response()->json(["status"=>0,"msg"=>"手机号不能为空"]);
        }

        // $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
        // $endToday = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $beginToday = strtotime("-10 minute");
        $endToday = time();

        $count_send_time = DB::table('membersms')->where(['mobile'=>$mobile,'sms_status'=>1])->whereBetween('create_time',[$beginToday,$endToday])->count();
        if($count_send_time >=10){
            // return response()->json(["status"=>0,"msg"=>"每天同一手机号只能发送5次验证码"]);
            return response()->json(["status"=>0,"msg"=>"同一手机号每十分钟只能发十条验证码"]);
        }

        $send_ip = $request->getClientIp();
        $count_send_time = DB::table('membersms')->where(['ip'=>$send_ip,'sms_status'=>1])->whereBetween('create_time',[$beginToday,$endToday])->count();
        // if($count_send_time >=20){
        //     return response()->json(["status"=>0,"msg"=>"每天同一ip只能发送20次验证码"]);
        // }

        $smsapi = "http://hk.smsbao.com/";
        $user = env('sms_user'); //短信平台帐号
        $pass = md5(env('sms_pwd')); //短信平台密码
        $code = $this->get_random_code(6);
        $content = "【】您的验证码:".$code."，10分钟内有效，切勿泄露他人！";
        $phone = $mobile;//要发送短信的手机号码
        $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);

        $sms['code'] = $code;
        $sms['mobile'] = $mobile;
        $sms['ip'] = $send_ip;
        $sms['create_time'] = time();

        $result = file_get_contents($sendurl);
        // $result = $this->curl_get($sendurl);
        if($result == 0){
            $sms['sms_status'] = 1;
            $sms['sms_content'] = '短信发送成功';
            DB::table('membersms')->insert($sms);
            return response()->json(["status"=>1,"msg"=>"短信发送成功"]);
        }else{
            $sms['sms_status'] = $result;
            $sms['sms_content'] = '短信发送失败';
            DB::table('membersms')->insert($sms);
            return response()->json(["status"=>0,"msg"=>"短信发送失败"]);
        }
    }

    public function captcha(Request $request)
    {
        // $captcha['url'] = captcha_src('mini');
        $captcha = app('captcha')->create('mini', true);
        return response()->json(["status"=>1,"data"=>$captcha]);
    }

    //h5的图文验证
    public function new_sendMsm(Request $request){
        $mobile = $request->mobile;
        if (!$mobile) {
            return response()->json(["status"=>0,"msg"=>"手机号不能为空"]);
        }
          $captcha = $request->captcha;
           $key = $request->key;
           $type = $request->type;//
        /**验证图形验证码**/

        $rules = ['captcha' => 'required'];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'msg' => '图文验证码不能为空'
            ];
        }
        $res = captcha_api_check($captcha,$key);
        if(!$res){
            return response()->json(["status" => 0, "msg" => '图文验证码错误！！','data'=>$res,'captcha'=>$captcha]);
        }



        // $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
        // $endToday = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $beginToday = strtotime("-10 minute");
        $endToday = time();

        $count_send_time = DB::table('membersms')->where(['mobile'=>$mobile,'sms_status'=>1])->whereBetween('create_time',[$beginToday,$endToday])->count();
        if($count_send_time >=10){
            return response()->json(["status"=>0,"msg"=>"同一手机号每十分钟只能发十条验证码"]);
        }

        $send_ip = $request->getClientIp();
        // $count_send_time = DB::table('membersms')->where(['ip'=>$send_ip,'sms_status'=>1])->whereBetween('create_time',[$beginToday,$endToday])->count();
        // if($count_send_time >=20){
        //     return response()->json(["status"=>0,"msg"=>"每天同一ip只能发送20次验证码"]);
        // }

        /*****解开start**/
        $smsapi = "http://hk.smsbao.com/";
        $user = env('sms_user'); //短信平台帐号
        $pass = md5(env('sms_pwd')); //短信平台密码
        $code = $this->get_random_code(6);
        $content = '【ZJ中心】您的验证码:'.$code.'，10分钟内有效，切勿泄露他人！';
        $phone = $mobile;//要发送短信的手机号码
        $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);

        $sms['code'] = $code;
        $sms['mobile'] = $mobile;
        $sms['ip'] = $send_ip;
        $sms['create_time'] = time();

        $result =file_get_contents($sendurl) ;

        /*****解开end**/

        /****测试start**/
        // $sms['code'] = $this->get_random_code(6);
        // $sms['mobile'] = $mobile;
        // $sms['ip'] = $send_ip;
        // $sms['create_time'] = time();
        // $result = 0;
        /****测试end**/
        if($result == 0){
            $sms['sms_status'] = 1;
            $sms['sms_content'] = '短信发送成功';
            $sms['type'] = $type;
            DB::table('membersms')->insert($sms);
            return response()->json(["status"=>1,"msg"=>"短信发送成功"]);
        }else{
            $sms['sms_status'] = $result;
            $sms['sms_content'] = '短信发送失败';
            DB::table('membersms')->insert($sms);
            return response()->json(["status"=>0,"msg"=>"短信发送失败"]);
        }
    }

    //忘记密码
    public function forget(Request $request){
         $mobile = $request->mobile;
         $password = $request->password;
         $code = $request->code;
        if (!$mobile) {
            return response()->json(["status"=>0,"msg"=>"手机号不能为空"]);
        }
       if(strlen($mobile) !== 11){
           return response()->json(["status"=>0,"msg"=>"您输入的手机位数不对"]);
        }
         if (!$password || $password=='') {
            return response()->json(["status"=>0,"msg"=>"密码不能为空"]);
        }
        // $phone = \App\Member::EncryptPassWord($mobile);
        // $has_mobile = DB::table('member')->where(['mobile'=>$phone])->first();
        // $isPhones =  $this->has_phone($mobile);
        $isPhones = DB::table('member')->select('id')->where(['username'=>$mobile])->first();
        if(!$isPhones){
            return response()->json(["status"=>0,"msg"=>"该手机号未注册"]);
        }

        $check_time = strtotime("-10 minute");
             $sms_code = DB::table('membersms')
             ->where(['mobile'=>$mobile,'sms_status'=>1,'type'=>2])
             ->where('create_time','<=',time())
                ->where('create_time','>=',$check_time)
             ->orderBy('create_time','desc')->first();

         if($code != 8597 && (!$sms_code || $sms_code->code != $code)){
        // if(!$sms_code || $sms_cowherede->code != $code){
             return response()->json(["status"=>0,"msg"=>"短信验证码错误，请重新输入"]);
         }

        //        $new_pwd = \App\Member::EncryptPassWord($password);
        //        $update['password'] = $new_pwd;
        $EditMember= Member::where("id",$isPhones->id)->first();
        $EditMember->password=\App\Member::EncryptPassWord($password);

        //        $res = DB::table('member')->where(['id'=>$isPhones])->update($update);
        if($EditMember->save()){
            // return response()->json(["status"=>1,"msg"=>"密码重置成功",'data'=>$isPhones,'password'=>$password,'pwd'=>$EditMember->password]);
            return response()->json(["status"=>1,"msg"=>"密码重置成功"]);

        }else{
            return response()->json(["status"=>0,"msg"=>"操作失败"]);
        }
    }

    public function has_phone($mobile){
        $Members= Member::get();
        foreach($Members as $member){
            if(Crypt::decrypt($member->mobile)==$mobile ){
                return $member->id;
            }
        }
         return false;
    }

    public function update_download(Request $request){
        $UserId =$request->session()->get('UserId');
        if($UserId){
            $data['invite_code'] = DB::table('member')->where(['id'=>$UserId])->value('invicode');
        }else{
            $data['invite_code'] = '';
        }
        $now_version = env('APP_VERSION');
        $AppDownloadUrl = DB::table('setings')->where('keyname','AppDownloadUrl')->value('value');//APP下载地址
        $AppUpdateContent = DB::table('setings')->where('keyname','AppUpdateContent')->value('value');//APP更新内容
        $HotAppDownloadUrl = DB::table('setings')->where('keyname','HotAppDownloadUrl')->value('value');//热更地址
        $ShareUrl = DB::table("setings")->where('keyname','invite_link')->value("value");//邀请域名

        //$apiurls_data = DB::table("apilinks")->where('status',1)->inRandomOrder()->take(1)->get();//邀请域名
		//$ShareUrl = $apiurls_data[0]->api_link;

        $data['AppDownloadUrl'] = $AppDownloadUrl;
        $data['AppUpdateContent'] = $AppUpdateContent;
        $data['version'] = $now_version;
        $data['HotAppDownloadUrl'] = $HotAppDownloadUrl;
        $data['ShareUrl'] = $ShareUrl;
        return response()->json(["status"=>1,"msg"=>"返回成功","data"=>$data]);
    }

    protected function DateAdd($part, $number, $date){
        $date_array = getdate(strtotime($date));
        $hor = $date_array["hours"];
        $min = $date_array["minutes"];
        $sec = $date_array["seconds"];
        $mon = $date_array["mon"];
        $day = $date_array["mday"];
        $yar = $date_array["year"];
        switch($part){
            case "y": $yar += $number; break;
            case "q": $mon += ($number * 3); break;
            case "m": $mon += $number; break;
            case "w": $day += ($number * 7); break;
            case "d": $day += $number; break;
            case "h": $hor += $number; break;
            case "n": $min += $number; break;
            case "s": $sec += $number; break;
        }
        $FengHongDateFormat='Y-m-d H:i:s';

        return date($FengHongDateFormat, mktime($hor, $min, $sec, $mon, $day, $yar));
    }

    //api地址
    public function getApiList(){

        // if(Cache::has('system_apilinks')){
        //   $data=Cache::get('system_apilinks');
        // }else{
        //   $data = DB::table("apilinks")->where('status',1)->orderBy("id","desc")->get();
        //   Cache::forever('system_apilinks', $data);
        // }
        $data = DB::table("apilinks")->where('status',1)->orderBy("id","desc")->get();
        return response()->json(['status'=>1,'data'=>$data]);
    }

    public function update_currline(Request $request){
        $product_id = $request->pid; //货币ID
        $startkey = $request->startkey; //执行key

        $check_huobi = DB::table('products')->where('id',$product_id)->first(); //查看是否存在该货币
        if($startkey != '8866' || !$check_huobi){
            return response()->json(["status"=>0,"msg"=>"参数错误"]);
        }

        $has_c = DB::table('currencysline')->where('product_id',$product_id)->first();
        if($has_c){
            return response()->json(["status"=>0,"msg"=>"该货币数据已存在"]);
        }

        $stimestamp = strtotime('2021-08-08 00:00:01');
        $etimestamp = strtotime(Carbon::now());
        // 计算日期段内有多少天
        $days =intval(($etimestamp-$stimestamp)/86400+1);
        $old_price = 1;
        $data_array = [];
        for ($i=0;$i<$days;$i++) {
            $record = [];
            $increase = rand(-1,2);
            // if($increase<0){                     //小数
            //     $increase = '-0.'.$increase;
            // }else{
            //     $increase = '0.'.$increase;
            // }
            // $increase = (float)$increase;
            $increase_price = $old_price * $increase * 0.01;

            $record['product_id'] = $product_id;
            $record['old_price'] = $old_price;
            $record['price'] = $old_price + $increase_price;
            $record['increase'] = $increase;
            $record['increase_price'] = $increase_price;
            $record['created_at'] =  $this->DateAdd("d",$i, '2021-08-08 00:00:01');
            $record['highest_price'] = rand($record['price'],$record['price']+20);
            $record['lowest_price'] = rand($record['price']-20,$record['price']);

            $old_price = $record['price'];

            $data_array[] = $record;

        }


        DB::table('currencysline')->insert($data_array);
        echo '成功插入'.$i;
//        DB::table('currencysline')->select('created_at','old_price','price','lowest_price','highest_price')->where(['product_id'=>$product_id])->get();
    }
    public function  checklevel(){
        $current_page_url = 'https://';
        $real_ip = $_SERVER['HTTP_X_REAL_IP'];
        if($real_ip == env('PROXY_REAL_IP')){
            $current_page_url = 'http://'.$real_ip;
        }else{
            $current_page_url = $current_page_url . $_SERVER["HTTP_HOST"];
        }
        return ['status'=>1,'msg'=>'测试通过','host'=>$current_page_url];
    }
    public function getAppVersion() {
        $seting_app_ver = DB::table("setings")->where("keyname","=","app_ver")->first();
        $seting_app_download_url = DB::table("setings")->where("keyname","=","AppDownloadUrl")->first();
        $seting_app_versn = DB::table("setings")->where("keyname","=","app_versn")->first();
        $data['version'] = $seting_app_ver->value;
        $data['version'] = $seting_app_ver->value;
        $data['app_versn'] = $seting_app_versn->value;
        $data['url'] = $seting_app_download_url->value;
        return response()->json(["status"=>1,"msg"=>"返回成功","data"=>$data]);
    }

}


?>
