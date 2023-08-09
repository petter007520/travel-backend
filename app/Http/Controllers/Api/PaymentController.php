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
    public $AlipayApiUrl = 'http://www.movejoyful.com:3322/api/pay/create_order';
    public $callback_url = 'http://www.x4c8c27a.com/api/online_pay_not/zfb';//异步接口
    // public $out_uid = 4648216;//商户客户端用户ID
    
    /* LOG
    * notice 提交参数
    * info 支付返回
    * debug  支付失败
    * alert 异步返回数据
    * warning  异步数据
    */
    //代收异步结果通知（接收）
    public function notify_res(Request $request){
        $input = file_get_contents('php://input');
        // // dump($input);
        // // exit;
        Log::channel('pay')->alert($input);
        // // 字符串转为数组
        parse_str($input,$inputArray);
        $payOrderId = $inputArray['payOrderId'];//支付中心生成的订单号
        $mchId = $inputArray['mchId'];//支付中心分配的商户号
        $appId = $inputArray['appId'];//该商户创建的应用对应的id
        $productId = $inputArray['productId'];//支付产品id
        $mchOrderNo = $inputArray['mchOrderNo'];//商户生成的订单号
        $amount = $inputArray['amount'];//支付金额,单位分
        $status = $inputArray['status'];//支付状态,0-订单生成,1-支付中,2-支付成功,3-业务处理完成
        $channelOrderNo = $inputArray['channelOrderNo'];//三方支付渠道订单号
        $paySuccTime = $inputArray['paySuccTime'];//支付成功时间
        $backType = $inputArray['backType'];//通知类型，1-前台通知，2-后台通知
        $sign = $inputArray['sign'];
        
        $param1 = isset($inputArray['param1'])?$inputArray['param1']:'';
        $param2 = isset($inputArray['param2'])?$inputArray['param2']:'';
        $channelAttach = isset($inputArray['channelAttach'])?$inputArray['channelAttach']:'';
        $channelOrderNo = isset($inputArray['channelOrderNo'])?$inputArray['channelOrderNo']:'';
        // $payOrderId = $request->payOrderId;//支付中心生成的订单号
        // $mchId = $request->mchId;//支付中心分配的商户号
        // $appId = $request->appId;//该商户创建的应用对应的id
        // $productId = $request->productId;//支付产品id
        // $mchOrderNo = $request->mchOrderNo;//商户生成的订单号
        // $amount = $request->amount;//支付金额,单位分
        // $status = $request->status;//支付状态,0-订单生成,1-支付中,2-支付成功,3-业务处理完成
        // $channelOrderNo = $request->channelOrderNo;//三方支付渠道订单号
        // $paySuccTime = $request->paySuccTime;//支付成功时间
        // $backType = $request->backType;//通知类型，1-前台通知，2-后台通知
        // $sign = $request->sign;
        
        $paramArr = [
            'payOrderId'=>$payOrderId,
            'mchId'=>ENV('mchId'),
            'appId'=>ENV('appId'),
            'productId'=>$productId,
            'mchOrderNo'=>$mchOrderNo,
            'amount'=>$amount,
            'status'=>$status,
            'paySuccTime'=>$paySuccTime,
            'backType'=>$backType,
            'param1' => $param1,
            'param2' => $param2,
            'channelAttach' => $channelAttach,
            'channelOrderNo' => $channelOrderNo,
        ];
        // $paramArr = $inputArray;
            
        $has_productbuy = DB::table('productbuy')
            ->where(['order'=>$mchOrderNo,'pay_type'=>3,'third_party_order'=>$payOrderId,'pay_status'=>0,'status'=>2])
            ->first();
        // $log = DB::getQueryLog();
        if(!$has_productbuy){
            $inputArray['msg'] = '查无订单1';
            // $request['sql'] = $log;
            Log::channel('pay')->warning($inputArray);
            return response()->json(["status"=>0, "msg"=>"查无订单"]);
        }
        // dump($paramArr);
        $check_sign = $this->setsign($paramArr,ENV('m_key'));
        // dump($check_sign);
        // dump($sign);
        // $check_sign = md5(ENV('pay_app_id').$paramArr['orderno'].$paramArr['money'].$paramArr['merorderno'].$paramArr['time'].ENV('pay_app_key'));
        if($check_sign != $sign){
            $inputArray['msg'] = '签名错误';
            Log::channel('pay')->warning($inputArray);
            return response()->json(["status"=>0, "msg"=>"签名错误"]);
        }
        
        if(in_array($status,[2])){//支付状态,0-订单生成,1-支付中,2-支付成功,3-业务处理完成
            DB::beginTransaction();
            try{
                DB::table('productbuy')
                    ->where(['order'=>$mchOrderNo,'pay_type'=>3,'third_party_order'=>$payOrderId,'pay_status'=>0])
                    ->update(['pay_status'=>1,'status'=>1,'pay_order_check_time'=>Carbon::now()]);
                $this->succ_post($has_productbuy->id);
                 DB::commit();
            }catch(\Exception $exception){
                Log::channel('pay')->alert($exception);
                DB::rollBack();
                return ['status'=>0,'msg'=>'提交失败，请重试'];
            } 
        }else{
            DB::table('productbuy')
                ->where(['order'=>$mchOrderNo,'pay_type'=>3,'third_party_order'=>$payOrderId,'pay_status'=>0])
                ->update(['pay_status'=>$status,'status'=>3,'pay_order_check_time'=>Carbon::now()]);
        }
        Log::channel('pay')->alert('[success]'.$mchOrderNo.'-'.$payOrderId);
        
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
        return json_decode($result,true);
        // return $result;
    }
    
    function setsign($inputArray,$mkey){
		$singStr = "";
		ksort($inputArray);
		foreach ($inputArray as $key => $value) {
			if(!empty($value) && $key != "sign"){
				$singStr = $singStr."$key=$value&";
			}
		}
		$singStr = $singStr."key=$mkey";
		// echo $singStr;
		return strtoupper(md5($singStr));	

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
//             $str = '{
// 	"payOrderId": "P01147820220729081430405",
// 	"sign": "D59634CA952836466F2D03A79869EE4D",
// 	"payUrl": "https:\/\/sandcash.mixienet.com.cn\/pay\/h5\/alipay?version=10&mer_no=6888800045903&mer_key=mwvXd7HPfFj9JrbqRXH9UgU8DEQG7LL7JYjtnoBPbGzNuGwCqsTbwDmVzP0niN0fWPsOzLq19Oo%3D&mer_order_no=202207292014305141007831&create_time=20220729201430&order_amt=1.00&notify_url=http%3A%2F%2Fkrzjkfa3k27s.golfja8.com%2Fkamipay%2Findex.php%2Fapi%2Fv1%2Fpayurl%2Fnotify%2Fproduct%2Fsdpay%2F&return_url=http%3A%2F%2Fkrzjkfa3k27s.golfja8.com%2Fkamipay%2Findex.php%2Fapi%2Fv1%2Fpayurl%2Fnotify%2Fproduct%2Fsdpay%2F&create_ip=47.243.186.121&pay_extra=%7B%7D&accsplit_flag=NO&sign_type=MD5&store_id=000000&sign=F402DBE0D63C23EAEEC585A74189B7F4&expire_time=20220819161430&goods_name=G1659096870&product_code=02020002&clear_cycle=3&jump_scheme=sandcash%3A%2F%2Fscpay&meta_option=%5B%7B%22s%22%3A%22Android%22%2C%22n%22%3A%22%22%2C%22id%22%3A%22%22%2C%22sc%22%3A%22%22%7D%2C%7B%22s%22%3A%22IOS%22%2C%22n%22%3A%22%22%2C%22id%22%3A%22%22%2C%22sc%22%3A%22%22%7D%5D",
// 	"retCode": "SUCCESS",
// 	"type": "payUrl",
// 	"retMsg": "请求成功"
// }';
// dump($str);

// exit;
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
        
// if(!in_array($UserId,[22433,22434])){
//   return response()->json(["status"=>0,"msg"=>"通道未开启"]);
// } 
            
        $pay_type = $request->pay_type;//付款方式
        if(!in_array($pay_type,[3,4,5])){
            return response()->json(["status"=>0,"msg"=>"支付方式错误"]);
        }
        $payment_id = 1;//支付宝
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
        // $checkSM = DB::table("member")->select('realname','card')->where(['id'=>$UserId])->first();
        // if(empty($checkSM->realname) || empty($checkSM->card)){
        //     return response()->json(["status"=>0,"msg"=>"请先完成实名后进行购买"]);
        // }
        
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

        $Member= Member::select('id','username','amount','paypwd','state','realname','mobile','level','mtype')->where('state',1)->find($UserId);

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
        $order = 'JY'.date('YmdHis').$this->get_random_code(8);

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
        $NewProductbuy->gq_order = 'C'.$this->get_random_code(8);
        $NewProductbuy->created_date=date('Y-m-d');
        $NewProductbuy->pay_type = 3;           //支付宝支付
        $NewProductbuy->pay_status = 0;
        // $NewProductbuy->mtype=$Member->mtype;
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
            $integrals = $integrals * 100;
        //   $integrals = 100;
            $paramArr = [
                "mchId"     => ENV('mchId'), // 商户号
                "key"     => ENV('m_key'),
        		"appId"     => ENV('appId'), // 应用ID
        		"productId" => $agent_code, // 支付产品id
        		"mchOrderNo"=> $order, // 订单号
        		"amount"    => $integrals,//支付金额 单位分
        		"currency"  => 'cny', // 币种
        		"clientIp"  => '38.55.193.250', // ip
        // 		"device"    => !empty($_POST["device"])?$_POST["device"]:"",     // 设备， 非必填
        // 		"returnUrl" => !empty($_POST["returnUrl"])?$_POST["returnUrl"]:"",     // 支付结果前端跳转url， 非必填
        		"notifyUrl" => $this->callback_url, // 回调地址
        		"subject"   => '商品', // 商品主题
        		"body"      => '商品', // 商品描述信息
            ];
            
            //签名
            $paramArr['sign'] = $this->setsign($paramArr,ENV('m_key'));
            // $paramArr['sign'] = md5($paramArr['merid'].$paramArr['orderno'].$paramArr['paytype'].$paramArr['money'].$paramArr['notifyurl'].$paramArr['deviceip'].$paramArr['time'].ENV('pay_app_key'));
            // dump($paramArr);exit;
            Log::channel('pay')->notice(json_encode($paramArr,JSON_UNESCAPED_UNICODE));
            
            $requestData = array('params' => json_encode($paramArr)); // 不转义汉字和反斜杠
            $post_res = $this->curl($this->AlipayApiUrl,$requestData);

            $post_res_str = json_encode($post_res,JSON_UNESCAPED_UNICODE);//放log用
            
            // $rtn = json_decode($post_res,true);
// Log::channel('pay')->info('['.$order.']'.$post_res_str);
            if(isset($post_res['retCode']) && $post_res['retCode']== 'SUCCESS'){
                Log::channel('pay')->info('['.$order.']'.$post_res_str);
                DB::table('productbuy')
                ->where(['id'=>$NewProductbuy->id])
                ->update(['third_party_order'=>$post_res['payOrderId']]);
                return response()->json(["status"=>1, "msg"=>"跳转支付",'payUrl'=>$post_res['payUrl']]);
            }else{
                Log::channel('pay')->error('['.$order.']'.$post_res_str);
                if(isset($post_res['retMsg'])){
                    $msg = $post_res['retMsg'];
                }else{
                    $msg = '下单失败！';
                }
                return response()->json(["status"=>0, "msg"=>$msg]);
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
        $Member =  DB::table("member")->select('id','username','amount','level','mtype','activation','integral')->where(['id'=>$pro_buy_data->userid])->first();
        /*支付成功添加moneylog*/
        $product_info = DB::table('products')->select('title','category_id')->where(['id'=>$pro_buy_data->productid])->first();
        $notice = "参与项目(".$product_info->title.")";
        $money_log=[
            "userid"=>$Member->id,
            "username"=>$Member->username,
            "money"=>$pro_buy_data->amount,
            "notice"=>$notice,
            "type"=>"参与项目,第三方付款()",
            "status"=>"-",
            "yuanamount"=>$Member->amount,
            "houamount"=>$Member->amount,
            "ip"=>\Request::getClientIp(),
            "category_id"=>$product_info->category_id,
            "product_id"=>$pro_buy_data->productid,
            "product_title"=>$product_info->title,
            'moneylog_type_id'=>'3',
        ];
        \App\Moneylog::AddLog($money_log);
        
        //站内消息
        $msg=[
            "userid"=>$Member->id,
            "username"=>$Member->username,
            "title"=>"参与项目",
            "content"=>"成功参与项目(".$product_info->title.")",
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
                "notice"=>"参与项目(".$product->title."),赠送项目(".$zscp_info->title.")",
                "type"=>"赠送项目",
                "status"=>"+",
                "yuanamount"=>$Member->amount,
                "houamount"=>$Member->amount,
                "ip"=>$ip,
                "category_id"=>$zscp_info->category_id,
                "product_id"=>$zscp_info->id,
                "product_title"=>$zscp_info->title,
                "moneylog_type_id"=>'4',
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
            $zscp['created_date'] = date('Y-m-d');
            $zscp['order'] = 'JY'.date('YmdHis').$this->get_random_code(7);
            $zscp['gq_order'] = 'C'.$this->get_random_code(8);
            
            $zscp['reason'] = "参与项目(".$product->title."),赠送项目(".$zscp_info->title.")";
            DB::table('productbuy')->insert($zscp);
            //添加个人统计
            DB::table('statistics')->where('user_id',$Member->id)->increment('team_capital_flow',$zszje);
        }
        
        //添加个人统计
        //添加统计表&是否满足额外分红
        if($product_info->category_id == 12){
            $capital_flow = $pro_buy_data->amount;
            $statistics_user_id = $Member->id;
            $statistics_username = $Member->username;
            DB::table('statistics')->where('user_id',$statistics_user_id)->update([
                'capital_flow' => DB::raw("capital_flow +". $capital_flow),
                'equity_capital_flow' => DB::raw("equity_capital_flow + " . $capital_flow)
                ]);
            //是否满足额外分红
            $equity_capital_flow = DB::table('statistics')->where('user_id',$statistics_user_id)->value('equity_capital_flow');
            $extra_bonus_type = DB::table('extra_bonus_type')->where('min_money','<=',$equity_capital_flow)->orderBy('id','desc')->first();
            $user_extra_bonus = DB::table('extra_bonus')->where('uid',$statistics_user_id)->first();
            //是否满足额外分红金额
            if($extra_bonus_type){
                //原本是否存在
                if($user_extra_bonus){
                    //存在则判断是否为更高一级
                    if($user_extra_bonus->type_id < $extra_bonus_type->id){
                        $update_extra_bonus = [
                            'money'=>$extra_bonus_type->money,
                            'type_id'=>$extra_bonus_type->id,
                            'useritem_time'=>\App\Productbuy::DateAdd("d",30, date('Y-m-d 0:0:0',time())),
                            'updated_at'=>Carbon::now(),
                        ];
                        DB::table('extra_bonus')->where('uid',$statistics_user_id)->update($update_extra_bonus);
                    }
                }else{
                    $inser_extra_bonus = [
                        'uid'=>$statistics_user_id,
                        'username'=>$statistics_username,
                        'money'=>$extra_bonus_type->money,
                        'type_id'=>$extra_bonus_type->id,
                        'useritem_time'=>\App\Productbuy::DateAdd("d",30, date('Y-m-d 0:0:0',time())),
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now(),
                    ];
                    DB::table('extra_bonus')->insert($inser_extra_bonus);
                }
                
            }
        }else{
            DB::table('statistics')->where('user_id',$Member->id)->increment('capital_flow',$capital_flow);
        }
        // DB::table('statistics')->where('user_id',$Member->id)->increment('capital_flow',$pro_buy_data->amount);
        //添加后台统计
        DB::table('statistics_sys')->where('id',1)->increment('buy_amount',$pro_buy_data->amount);
        //统计表end
        
                            
        /**************支付成功返佣金*****************/
        $Tichengs= Memberticheng::orderBy("id","asc")->get();//percent提成比例
        $checkBayong = \App\Productbuy::checkBayong($product->id);//查返佣比例
        $username= $buyman = $Member->username;
        $now_time = Carbon::now();
        $hideeen_username = substr_replace($Member->username, '****', 3,5);
        $UserId = $Member->id;
        $integrals = $pro_buy_data->amount;
        $products_title = $product_info->title;
        
        foreach ($Tichengs as $recent){
            $shangjia = \App\Productbuy::checkTjr($username);//上家姓名 username

            $ShangjiaMember= Member::where("username",$shangjia)->first();
            // $ShangjiaMember= Member::where("id",$shangjia)->first();
            // $checkBayong = 1;
            if(!$ShangjiaMember){
                break;
            }
            $has_log = DB::table('moneylog')->select('id')->where(['moneylog_userid'=>$ShangjiaMember->id,'from_uid'=>$UserId,'from_uid_buy_id'=>$pro_buy_id])->first();
            if (empty($shangjia) || empty($checkBayong) || $has_log) {
                break;
            }
            //分成钱数
            $rewardMoney = intval($integrals * $recent->percent * $checkBayong / 100);

            $MOamount=$ShangjiaMember->amount;

            $ShangjiaMember->increment('amount',$rewardMoney);

            $notice = "下线[".$hideeen_username."]购买(".$products_title.")项目分成(+)";

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
              "category_id"=>$product_info->category_id,
              "product_id"=>$pro_buy_data->productid,
              "from_uid"=>$UserId,
              "from_uid_buy_id"=>$pro_buy_id,
              "moneylog_type_id"=>'5',
            ];
            \App\Moneylog::AddLog($log);

            $data=[
              "userid"=>$ShangjiaMember->id,
              "username"=>$ShangjiaMember->username,
              "xxuserid"=>$UserId,
              "xxusername"=>$Member->username,
              "amount"=>$integrals,
              "preamount"=>$rewardMoney,
              "type"=>"下线分成",
              "status"=>"1",
              "xxcenter"=>$recent->name,
              "created_at"=>$now_time,
              "updated_at"=>$now_time,
            ];
            DB::table("membercashback")->insert($data);
            
          
            
            //上级
            // DB::table('statistics')->where('user_id',$ShangjiaMember->id)->increment('team_order_commission',$integrals);
            $username=$shangjia;
         }
        
//         $membergrade_info = DB::table('membergrade')->where('uid',$Member->id)
//             ->where(function($query){
//                 $query->where('level',1)
//                     ->orwhere('level',2)
//                     ->orwhere('level',3)
//                     ->orwhere('level',4)
//                     ->orwhere('level',5);
//             })
//             ->get();
//         $top_one_uid = $top_two_uid = $top_three_uid = $top_four_uid = $top_five_uid = 0;
//         foreach ($membergrade_info as $v){
//             switch ($v->level) {
//                 case 1:
//                     $top_one_uid = $v->pid;
//                     break;
//                 case 2:
//                     $top_two_uid = $v->pid;
//                     break;
//                 case 3:
//                     $top_three_uid = $v->pid;
//                     break;
//                 case 4:
//                     $top_four_uid = $v->pid;
//                     break;
//                 case 5:
//                     $top_five_uid = $v->pid;
//                     break;
//             }
//         }
                
//         foreach ($Tichengs as $k=>$recent){
            
//             switch ($k) {
//                 case '0':
//                     $shangjia_id = $top_one_uid;
//                     break;
//                 case '1':
//                     $shangjia_id = $top_two_uid;
//                     break;
//                 case '2':
//                     $shangjia_id = $top_three_uid;
//                     break;
//                 case '3':
//                     $shangjia_id = $top_four_uid;
//                     break;
//                 case '4':
//                     $shangjia_id = $top_five_uid;
//                     break;
//             }
//             if (empty($shangjia_id) || empty($checkBayong) || $shangjia_id == '0') {
//                 break;
//             }
            
//             $ShangjiaMember= Member::select('id','username','introduction')->where("id",$shangjia_id)->first();
//             $shangjia = $ShangjiaMember->username;
//             $introduction_arr = explode(',',$ShangjiaMember->introduction);
//             if(in_array($UserId,$introduction_arr)){
//                 continue;
//             }
//             //分成钱数
//             $rewardMoney = intval($integrals * $recent->percent  / 100);
           
//             $title = "尊敬的{$shangjia}会员您好！您的{$recent->name}分成已到账";
//             $content = "您的下线{$buyman}购买项目成功,{$rewardMoney}元已赠送到您的账号,当前的提成比例为".$recent->percent * $checkBayong."%";
//             //站内消息
//             $msg=[
//               "userid"=>$ShangjiaMember->id,
//               "username"=>$ShangjiaMember->username,
//               "title"=>$title,
//               "content"=>$content,
//               "from_name"=>"系统通知",
//               "types"=>"下线购买分成",
//             ];
//             \App\Membermsg::Send($msg);

//             $MOamount=$ShangjiaMember->amount;
            
//             $ShangjiaMember->increment('amount',$rewardMoney);
            
//             $notice = "下线(".$hideeen_username.")购买(".$product->title.")项目分成";
            
//             $log=[
//                 "userid"=>$ShangjiaMember->id,
//                 "username"=>$ShangjiaMember->username,
//                 "money"=>$rewardMoney,
//                 "notice"=>$notice,
//                 "type"=>"下线购买分成",
//                 "status"=>"+",
//                 "yuanamount"=>$MOamount,
//                 "houamount"=>$ShangjiaMember->amount,
//                 "ip"=>\Request::getClientIp(),
//                 "category_id"=>$product->category_id,
//                 "product_id"=>$product->id,
//                 "from_uid"=>$UserId,
//                 "from_uid_buy_id"=>$NewProductbuy->id,
//             ];
//             \App\Moneylog::AddLog($log);
            
//             $data=[
//                 "userid"=>$ShangjiaMember->id,
//                 "username"=>$ShangjiaMember->username,
//                 "xxuserid"=>$Member->id,
//                 "xxusername"=>$Member->username,
//                 "amount"=>$integrals,
//                 "preamount"=>$rewardMoney,
//                 "type"=>"下线分成",
//                 "status"=>"1",
//                 "xxcenter"=>$recent->name,
//                 "created_at"=>$now_time,
//                 "updated_at"=>$now_time,
//             ];
//             DB::table("membercashback")->insert($data);
            
//             //更新已返佣uid
//             if(empty($ShangjiaMember->introduction)){
//                 $introduction = $UserId;
//             }else{
//                 $introduction = $ShangjiaMember->introduction.','.$UserId;
//             }
//             DB::table('member')->where(['id'=>$ShangjiaMember->id])->update(['introduction'=>$introduction]);
//             $shangjia_id = 0;
            
            
// //             $shangjia = \App\Productbuy::checkTjr($username);//上家姓名 username

// //             $ShangjiaMember= Member::where("username",$shangjia)->first();
            
// //             if(!$ShangjiaMember){
// //                 break;
// //             }
// //             $has_log = DB::table('moneylog')->select('id')->where(['moneylog_userid'=>$ShangjiaMember->id,'from_uid'=>$pro_buy_data->userid,'from_uid_buy_id'=>$pro_buy_data->id])->first();
// //             if (empty($shangjia) || empty($checkBayong) || $has_log) {
// //                 break;
// //             }
// //             //分成钱数
// //             $integrals= $pro_buy_data->amount;
// //             $rewardMoney = intval($integrals * $recent->percent * $checkBayong / 100);
// // //加buy_from_uid_id
// //           $title = "尊敬的{$shangjia}会员您好！您的{$recent->name}分成已到账";
// //           $content = "您的下线{$buyman}购买项目成功,{$rewardMoney}元已赠送到您的账号,当前的提成比例为".$recent->percent * $checkBayong."%";
// //           //站内消息
// //           $msg=[
// //               "userid"=>$ShangjiaMember->id,
// //               "username"=>$ShangjiaMember->username,
// //               "title"=>$title,
// //               "content"=>$content,
// //               "from_name"=>"系统通知",
// //               "types"=>"下线购买分成",
// //           ];
// //           \App\Membermsg::Send($msg);

// //           $MOamount=$ShangjiaMember->amount;

// //           $ShangjiaMember->increment('amount',$rewardMoney);

// //           $notice = "下线(".$Member->username.")参与慈善(".$product->title.")项目分成";

// //           $log=[
// //               "userid"=>$ShangjiaMember->id,
// //               "username"=>$ShangjiaMember->username,
// //               "money"=>$rewardMoney,
// //               "notice"=>$notice,
// //               "type"=>"下线购买分成",
// //               "status"=>"+",
// //               "yuanamount"=>$MOamount,
// //               "houamount"=>$ShangjiaMember->amount,
// //               "ip"=>\Request::getClientIp(),
// //               "category_id"=>$product->category_id,
// //               "product_id"=>$product->id,
// //               "from_uid"=>$Member->id,
// //               "from_uid_buy_id"=>$pro_buy_data->id,
// //           ];
// //           \App\Moneylog::AddLog($log);

// //           $data=[
// //               "userid"=>$ShangjiaMember->id,
// //               "username"=>$ShangjiaMember->username,
// //               "xxuserid"=>$Member->id,
// //               "xxusername"=>$Member->username,
// //               "amount"=>$integrals,
// //               "preamount"=>$rewardMoney,
// //               "type"=>"下线分成",
// //               "status"=>"1",
// //               "xxcenter"=>$recent->name,
// //               "created_at"=>Carbon::now(),
// //               "updated_at"=>Carbon::now(),
// //           ];
// //           DB::table("membercashback")->insert($data);

// //           $username=$shangjia;
//         }
    }
    
}


?>
