<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
use Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use App\Memberticheng;
use App\Productbuy;
use App\Member;

class PaymentController extends Controller
{
    public $AlipayApiUrl = 'http://api.shangdubook.com/Api/Pay/unionOrder';
    public $callback_url = 'http://www.yugdh.com/api/online_pay_not';//异步接口
    public $out_uid = 4648216;//商户客户端用户ID
   
    
    public function postAlipayApi(Request $request)
    {   
        // $data = [
        //     'merchantNum'   => $merchantNum,            //商户号(商户号，由平台提供)
        //     'orderNo'       => $request->order,         //商户订单号(仅允许字母或数字类型,建议不超过32个字符，不要有中文)
        //     'amount'        => $request->amount,        //支付金额(请求的价格(单位：元) 可以0.01元)
        //     'notifyUrl'     => $request->notifyUrl,     //异步通知地址(异步接收支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数。)
        //     'returnUrl'     => $request->returnUrl,     //同步通知地址(支付成功后跳转到的地址，不参与签名。)
        //     'payType'       => 'llzfb',                 //请求支付类型
        //     'payFrom'       => $request->realname,
        //     'ip'            => '12.12.12.12'
        // ];
        
        $data = [
            'merchantNum'   => $this->merchantNum,          //商户号(商户号，由平台提供)
            'orderNo'       => '12345678909',               //商户订单号(仅允许字母或数字类型,建议不超过32个字符，不要有中文)
            'amount'        => 20,                          //支付金额(请求的价格(单位：元) 可以0.01元)
            'notifyUrl'     => 'http://kad.kmzgb.com/api/papanr',                 //异步通知地址(异步接收支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数。)
            'returnUrl'     => 'https://www.baidu.com/',                 //同步通知地址(支付成功后跳转到的地址，不参与签名。)
            'payType'       => 'llzfb',                     //请求支付类型
            'payFrom'       => 'xxx',
            'ip'            => '12.12.12.12',
        ];
        //签名【md5(商户号+商户订单号+支付金额+异步通知地址+商户秘钥)】
        $data['sign'] = md5($data['merchantNum'].$data['orderNo'].$data['amount'].$data['notifyUrl'].$this->merchantKey); 
        $res = $this->curl($this->AlipayApiUrl,$data);
        
        $res_arr = json_encode($res,JSON_UNESCAPED_UNICODE);
        
        if($res->code != 200){
            Log::channel('pay')->warning($res_arr);
            return response()->json(["status"=>0, "msg"=>$res->msg]);
        }
        
        Log::channel('pay')->info($res_arr);
        return response()->json(["status"=>1, "msg"=>"返回结果",'data'=>$res,'order_data'=>'']);
    }
    
    /* LOG
    * notice 提交参数
    * info 支付返回
    * debug  支付失败
    * alert 异步返回数据
    * warning  异步数据
    */
    //代收异步结果通知（接收）
    public function notify_res(Request $request){
        // Log::channel('pay')->alert($request);
        
        $platform_product_id = $request->platform_product_id;//平台产品ID
        $status = $request->status;//订单状态 1.待处理 3.完成 4.失败
        $pay_order_id = $request->pay_order_id;//平台订单号
        $out_uid = $request->out_uid;//
        $out_trade_no = $request->out_trade_no;//商户订单号
        $amount = $request->amount;//订单金额 元/两位小数
        $real_amount = $request->real_amount;//实付金额 元/两位小数
        $cny_rate = $request->cny_rate;//人民币汇率
        $exchange_rate = $request->exchange_rate;//兑换比例
        $notify_time = $request->notify_time;//通知时间
        $sign = $request->sign;
        
        $paramArr = [
            'platform_product_id'=>$platform_product_id,
            'status'=>$status,
            'pay_order_id'=>$pay_order_id,
            'out_uid'=>$this->out_uid,
            'out_trade_no'=>$out_trade_no,
            'amount'=>$amount,
            'real_amount'=>$real_amount,
            'cny_rate'=>$cny_rate,
            'exchange_rate'=>$exchange_rate,
            'notify_time'=>$notify_time,
            ];
        
        $has_productbuy = DB::table('productbuy')
            ->where(['order'=>$out_trade_no,'pay_type'=>3,'third_party_order'=>$pay_order_id,'pay_status'=>0,'status'=>2])
            ->first();
        if(!$has_productbuy){
            $request['msg'] = '查无订单';
            Log::channel('pay')->warning($request);
            return response()->json(["status"=>0, "msg"=>"查无订单"]);
        }
        
        $check_sign = $this->setsign($paramArr,ENV('pay_app_key'));
        
        if($check_sign != $sign){
            $request['msg'] = '签名错误';
            Log::channel('pay')->warning($request);
            return response()->json(["status"=>0, "msg"=>"签名错误"]);
        }
        
        if($status == 3){//1.待处理 3.完成 4.失败
            DB::table('productbuy')
                ->where(['order'=>$out_trade_no,'pay_type'=>3,'third_party_order'=>$pay_order_id,'pay_status'=>0])
                ->update(['pay_status'=>1,'status'=>1,'pay_order_check_time'=>Carbon::now()]);
            $this->succ_post($has_productbuy->id);
        }else{
            DB::table('productbuy')
                ->where(['order'=>$orderNo,'pay_type'=>3,'third_party_order'=>$platformOrderNo,'pay_status'=>0])
                ->update(['pay_status'=>$status,'status'=>3,'pay_order_check_time'=>Carbon::now()]);
        }
        Log::channel('pay')->alert('[success]'.$out_trade_no.'-'.$pay_order_id);
        
        return 'success';
    }
    
    //代收结果查询接口
    public function return_res(Request $request){
        // $orderNo = $request->orderNo;
        // $param = [
        //     'merchantNum'=>$this->merchantNum,
        //     'merchantOrderNo'=>$orderNo,
        //     'sign'=>($this->merchantNum.$orderNo.$this->merchantKey),//md5(商户号+商户订单号+商户秘钥)
        //     ];
        // $res = $this->paycurl($this->return_res_api,$param);
        // return response()->json(["status"=>1, "msg"=>"",'data'=>$res]);
    }
    
    public function curl($url,$data){ 
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url); 
        curl_setopt($curl, CURLOPT_POST, 1); 
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT,'TEST'); 
        $result = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        return json_decode($result);
    }
    
    function setsign($param,$appSecret){
        $params = array_filter($param);
        ksort($params);
    
    
        $dataStr = http_build_query($params);
        $dataStr = urldecode($dataStr);
    
        $dataStr .= $appSecret;
        $sign = md5($dataStr);
        return $sign;
    }
    
    function get_random_code($num)
    {
        $codeSeeds = "ABCDEFGHIJKLMNPQRSTUVWXYZ";
        // $codeSeeds .= "abcdefghijklmnpqrstuvwxyz";
        // $codeSeeds .= "0123456789_";
        $codeSeeds .= "123456789";
        $len = strlen($codeSeeds);
        $code = "";
        for ($i = 0; $i < $num; $i++) {
            $rand = rand(0, $len - 1);
            $code .= $codeSeeds[$rand];
        }
        return $code;
    }
    
    public function thirdToMoney(Request $request){
            
        $lastsession = $request->lastsession;
        if(!$lastsession){
            return response()->json(["status"=>-1,"msg"=>"请先登录！"]);
        }else{
            $Member = Member::where("lastsession",$request->lastsession)->first();
            if(!$Member){
                return response()->json(["status"=>-1,"msg"=>"请先登录！"]);
            }else{
                $request->session()->put('UserId',$Member->id, 120);
                $request->session()->put('UserName',$Member->username, 120);
                $request->session()->put('Member',$Member, 120);
            }
        }

        $UserId = $request->session()->get('UserId');

        $this->Member = Member::find($UserId);
        if(!$this->Member){
           return response()->json(["status"=>-1,"msg"=>"请先登录!"]);
        }
        if($this->Member->state == '0' || $this->Member->state == '-1'){
             return response()->json(["status"=>0,"msg"=>"帐号禁用中"]);
        }
        /*判断登陆*/    
            
            
        $pay_type = $request->pay_type;//付款方式
        if(!in_array($pay_type,[3,4])){
            return response()->json(["status"=>0,"msg"=>"支付方式错误"]);
        }
        $payment_id = $pay_type==3?1:2;// 3、支付宝  4、微信
        $payment_info =  DB::table('payment')->select('bankcode','enabled')->where(['id'=>$payment_id])->first();
        if($payment_info->enabled != 1){
            return response()->json(["status"=>0,"msg"=>"通道未开启"]);
        }
        $agent_code = $payment_info->bankcode;
        
        //购买项目
        $UserId =$request->session()->get('UserId');
        if($UserId<1){
            return response()->json(["status"=>-1,"msg"=>"请先登录！"]);
        }
        $checkSM = DB::table("member")->select('realname','card')->where(['id'=>$UserId])->first();
        if(empty($checkSM->realname) || empty($checkSM->card)){
            return response()->json(["status"=>0,"msg"=>"请先完成实名后进行购买"]);
        }
        
        if(!$request->productid || !is_numeric($request->productid)){
            return response()->json(["status"=>0,"msg"=>"项目不存在或已下架！！"]);
        }
        if($request->number<1 || !is_numeric($request->number)){
            return response()->json(["status"=>0,"msg"=>"购买项目数量错误！"]);
        }

        $product=DB::table("products")
            ->select('id','title','category_id','qtje','isft','tzzt','hkfs','shijian','zgje','qxdw','zsje','zsje_type','jyrsy','qtsl','zscp_id')
            ->where(['id'=>$request->productid])
            ->first();
        
        if(!$product){
            return response()->json(["status"=>0,"msg"=>"项目不存在或已下架！"]);
        }
        
        if((int)$request->number < (int)$product->qtsl){
            return response()->json(["status"=>0,"msg"=>"低于项目最低起投数量"]);
        }

        $Member= Member::select('id','username','amount','paypwd','state','realname','mobile','level')->where('state',1)->find($UserId);

        $integrals= $product->qtje*$request->number;
        
        $hkfs      = trim($product->hkfs);  //还款方式
        $zhouqi    = trim($product->shijian);//周期
        // if($product->category_id == 12){
        //     $hkfs = 4;
        // }
        //判断项目是否停止
        if($product->tzzt != 0){
            return response()->json(["status"=>0,"msg"=>"该项目已售罄"]);
        }
        //判断起投数量
        if($product->qtje > $integrals){
            return response()->json(["status"=>0,"msg"=>"您购买项目起投金额为".$product->qtje]);
        }
        //判断最高投
        if((int)$product->zgje !== 0){
            if($integrals > $product->zgje){
                return response()->json(["status"=>0,"msg"=>"您购买项目最高投入金额为".$product->zgje]);
            }
        }
        //判断投资是否投过
        if($product->isft == 0){
            $Productbuy= Productbuy::where("productid",$request->productid)->where("userid",$this->Member->id)->where('status','<>',3)->first();
            if($Productbuy){
                return response()->json(["status"=>0,"msg"=>"抱歉，该项目只允许投一次"]);
            }
        }
        
        //判断下一次领取时间
        $useritem_time2 = \App\Productbuy::DateAdd("d",1, date('Y-m-d 0:0:0',time()));

        $ip = $request->getClientIp();
        // $notice = "加入项目(".$product->title.")(-)";
            
        // $log=[
        //     "userid"=>$this->Member->id,
        //     "username"=>$this->Member->username,
        //     "money"=>$integrals,
        //     "notice"=>$notice,
        //     "type"=>"参与慈善项目,第三方付款(支付宝)",
        //     "status"=>"-",
        //     "yuanamount"=>$Member->amount,
        //     "houamount"=>$Member->amount,
        //     "ip"=>\Request::getClientIp(),
        //     "category_id"=>$product->category_id,
        //     "product_id"=>$product->id,
        //     "product_title"=>$product->title,
        // ];
        // \App\Moneylog::AddLog($log);


        $sendDay_count = $hkfs == 1?1:$zhouqi;
        $NewProductbuy= new Productbuy();

        //赠送金额
        if($product->zsje_type == 2 ){
            $product->zsje = intval($integrals * (zsje * 0.01));
        }
        $order = date('YmdHis').$this->get_random_code(8);

        $NewProductbuy->userid=$Member->id;
        $NewProductbuy->username=$Member->username;
        $NewProductbuy->level=$Member->level;
        $NewProductbuy->productid=$request->productid;
        $NewProductbuy->category_id=$product->category_id;
        $NewProductbuy->amount=$integrals;
        $NewProductbuy->ip=$ip;
        $NewProductbuy->useritem_time=Carbon::now();
        $NewProductbuy->useritem_time2=$useritem_time2;
        $NewProductbuy->sendday_count=$sendDay_count;
        $NewProductbuy->status = 2;               //未审核
        $NewProductbuy->num = $request->number; //购买数量
        $NewProductbuy->unit_price = $product->qtje;//购买时单价
        $NewProductbuy->zsje=$product->zsje;
        $NewProductbuy->zscp_id=$product->zscp_id?$product->zscp_id:0;
        $NewProductbuy->order = $order;
        $NewProductbuy->gq_order = 'C'.$request->productid.($Member->id+2354);
        
        $NewProductbuy->pay_type = 3;           //支付宝支付
        $NewProductbuy->pay_status = 0;
        // if($product->category_id == 12 ){
            // $NewProductbuy->gq_order = 'Y'.$this->get_random_code(9);
            // while(DB::table('productbuy')->where('gq_order',$NewProductbuy->gq_order)->first()){
                // $NewProductbuy->gq_order = 'Y'.$this->get_random_code(9);
            // }
        // }
        $res = $NewProductbuy->save();
        
        if(!$res){
            return response()->json(["status"=>0,"msg"=>"投资失败，请重新操作"]);
        }else{
            $time = time();
            $paramArr = [
                'app_id'=>ENV('pay_app_id'),
                'agent_code'=>$agent_code,//通道编码
                'out_uid'=>$this->out_uid,
                'out_trade_no'=>$order,
                'amount'=>sprintf("%.2f",$integrals),//订单金额 单位：元 保留2位小数
                // 'amount'=>1.00,
                'currency'=>2,//货币类型 ，固定值 2
                'timestamp'=>$time,
                'callback_url'=>$this->callback_url,
                'create_ip'=>'61.174.243.16'
            ];
            //签名
            $paramArr['sign'] = $this->setsign($paramArr,ENV('pay_app_key'));
            Log::channel('pay')->notice(json_encode($paramArr,JSON_UNESCAPED_UNICODE));

            $post_res = $this->curl($this->AlipayApiUrl,$paramArr);
            $post_res_str = json_encode($post_res,JSON_UNESCAPED_UNICODE);//放log用

            if($post_res->status == 200){
                Log::channel('pay')->info($post_res_str);
                DB::table('productbuy')
                ->where(['id'=>$NewProductbuy->id])
                ->update(['third_party_order'=>$post_res->data->platform_order_no]);
                return response()->json(["status"=>1, "msg"=>"跳转支付",'payUrl'=>$post_res->data->pay_url,'data'=>$post_res]);
            }else{
                Log::channel('pay')->error('['.$order.']'.$post_res_str);
                return response()->json(["status"=>0, "msg"=>$post_res->msg]);
            }
        }
        
    }
    
    public function succ_post($pro_buy_id){
       
        $pro_buy_data = DB::table("productbuy")->select('id','userid','username','productid','status','num','amount','pay_status')->where(['id'=>$pro_buy_id,'pay_status'=>1])->first();
        
        if(!$pro_buy_data){
            Log::channel('pay')->warning('查无订单('.$pro_buy_id.')');
            return response()->json(["status"=>0,"msg"=>"查无订单!"]);
        }
        if( $pro_buy_data->status!=1 || $pro_buy_data->pay_status != 1){
            Log::channel('pay')->warning('该订单还未完成支付('.$pro_buy_id.')');
            return response()->json(["status"=>0,"msg"=>"该订单还未完成支付"]);
        }
        $Member =  DB::table("member")->select('id','username','amount','level')->where(['id'=>$pro_buy_data->userid])->first();
        /*支付成功添加moneylog*/
        $product_info = DB::table('products')->select('title','category_id')->where(['id'=>$pro_buy_data->productid])->first();
        $notice = "参与慈善项目(".$product_info->title.")";
        $money_log=[
            "userid"=>$Member->id,
            "username"=>$Member->username,
            "money"=>$pro_buy_data->amount,
            "notice"=>$notice,
            "type"=>"参与慈善项目,第三方付款(支付宝)",
            "status"=>"-",
            "yuanamount"=>$Member->amount,
            "houamount"=>$Member->amount,
            "ip"=>\Request::getClientIp(),
            "category_id"=>$product_info->category_id,
            "product_id"=>$pro_buy_data->productid,
            "product_title"=>$product_info->title,
        ];
        \App\Moneylog::AddLog($money_log);
        
        //站内消息
        $msg=[
            "userid"=>$Member->id,
            "username"=>$Member->username,
            "title"=>"参与慈善项目",
            "content"=>"成功参与慈善项目(".$product_info->title.")",
            "from_name"=>"系统通知",
            "types"=>"加入项目",
        ];
        \App\Membermsg::Send($msg);
        /*****/
        
        /**************支付成功赠送产品*****************/
        $product=DB::table("products")
            ->select('id','title','category_id','qtje','isft','tzzt','hkfs','shijian','zgje','qxdw','zsje','zsje_type','jyrsy','qtsl','zscp_id')
            ->where(['id'=>$pro_buy_data->productid])
            ->first();
        if($product->zscp_id != 0 && in_array($product->zsje_type,[1,3])){
            //赠送的产品信息
            $zscp_info = DB::table("products")
                ->select('id','title','category_id','qtje','isft','tzzt','hkfs','shijian','zgje','qxdw','zsje','zsje_type','jyrsy','qtsl','zscp_id')
                ->where(['id'=> $product->zscp_id])
                ->first();
                
            $zscp_id = $product->zscp_id;//赠送产品id
            //判断下一次领取时间
            $useritem_time2 = \App\Productbuy::DateAdd("d",1, date('Y-m-d 0:0:0',time()));
            $sendDay_count = $product->shijian;//周期
           
            if($product->zsje_type == 3){
                $zszsl = $product->zsje * $pro_buy_data->num;//赠送倍数 * 购买数量
                $zszje = intval($zszsl * $zscp_info->qtje);//赠送总金额
            }else{
                $zszsl = $product->zsje;
                $zszje = intval($zszsl * $zscp_info->qtje);
            }
                
            $ip = \Request::getClientIp();
                
            //赠送项目
            $zscp_log=[
                "userid"=>$Member->id,
                "username"=>$Member->username,
                "money"=> $zszje,
                "notice"=>"参与慈善项目(".$product->title."),赠送项目(".$zscp_info->title.")",
                "type"=>"赠送项目",
                "status"=>"+",
                "yuanamount"=>$Member->amount,
                "houamount"=>$Member->amount,
                "ip"=>$ip,
                "category_id"=>$zscp_info->category_id,
                "product_id"=>$zscp_info->id,
                "product_title"=>$zscp_info->title,
            ];
            \App\Moneylog::AddLog($zscp_log);

            $zscp['userid'] = $Member->id;
            $zscp['username'] = $Member->username;
            $zscp['level'] = $Member->level;
            $zscp['productid'] = $zscp_info->id;
            $zscp['category_id'] = $zscp_info->category_id;
            $zscp['amount'] = $zszje; //赠送总金额
            $zscp['ip'] = $ip;
            $zscp['useritem_time'] = Carbon::now();
            $zscp['useritem_time2'] = $useritem_time2;
            $zscp['sendday_count'] = $sendDay_count;
            $zscp['status'] = 1;
            $zscp['num'] = $zszsl;//购买数量
            $zscp['unit_price'] = $zscp_info->qtje;//购买时单价
            $zscp['zsje'] = 0;
            $zscp['buy_from_id'] = $pro_buy_data->id;
            
            $zscp['reason'] = "参与慈善项目(".$product->title."),赠送项目(".$zscp_info->title.")";
            DB::table('productbuy')->insert($zscp);
            
        }
        
        
        /**************支付成功返佣金*****************/
        $Tichengs= Memberticheng::orderBy("id","asc")->get();//percent提成比例
        $checkBayong = \App\Productbuy::checkBayong($product->id);//查返佣比例
        $username = $buyman = $Member->username;
        
        foreach ($Tichengs as $recent){
            $shangjia = \App\Productbuy::checkTjr($username);//上家姓名 username

            $ShangjiaMember= Member::where("username",$shangjia)->first();
           
           $has_log = DB::table('moneylog')->select('id')->where(['moneylog_userid'=>$ShangjiaMember->id,'from_uid'=>$pro_buy_data->userid,'from_uid_buy_id'=>$pro_buy_data->id])->first();
            if (empty($shangjia) || empty($checkBayong) || $has_log) {
                break;
            }
            //分成钱数
            $integrals= $pro_buy_data->amount;
            $rewardMoney = intval($integrals * $recent->percent * $checkBayong / 100);
//加buy_from_uid_id
           $title = "尊敬的{$shangjia}会员您好！您的{$recent->name}分成已到账";
           $content = "您的下线{$buyman}购买项目成功,{$rewardMoney}元已赠送到您的账号,当前的提成比例为".$recent->percent * $checkBayong."%";
           //站内消息
           $msg=[
               "userid"=>$ShangjiaMember->id,
               "username"=>$ShangjiaMember->username,
               "title"=>$title,
               "content"=>$content,
               "from_name"=>"系统通知",
               "types"=>"下线购买分成",
           ];
           \App\Membermsg::Send($msg);

           $MOamount=$ShangjiaMember->amount;

           $ShangjiaMember->increment('amount',$rewardMoney);

           $notice = "下线(".$Member->username.")参与慈善(".$product->title.")项目分成";

           $log=[
               "userid"=>$ShangjiaMember->id,
               "username"=>$ShangjiaMember->username,
               "money"=>$rewardMoney,
               "notice"=>$notice,
               "type"=>"下线购买分成",
               "status"=>"+",
               "yuanamount"=>$MOamount,
               "houamount"=>$ShangjiaMember->amount,
               "ip"=>\Request::getClientIp(),
               "category_id"=>$product->category_id,
               "product_id"=>$product->id,
               "from_uid"=>$Member->id,
               "from_uid_buy_id"=>$pro_buy_data->id,
           ];
           \App\Moneylog::AddLog($log);

           $data=[
               "userid"=>$ShangjiaMember->id,
               "username"=>$ShangjiaMember->username,
               "xxuserid"=>$Member->id,
               "xxusername"=>$Member->username,
               "amount"=>$integrals,
               "preamount"=>$rewardMoney,
               "type"=>"下线分成",
               "status"=>"1",
               "xxcenter"=>$recent->name,
               "created_at"=>Carbon::now(),
               "updated_at"=>Carbon::now(),
           ];
           DB::table("membercashback")->insert($data);

           $username=$shangjia;
        }
    }
    
}


?>
