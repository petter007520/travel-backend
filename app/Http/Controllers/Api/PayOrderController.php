<?php

namespace App\Http\Controllers\Api;

use App\Jobs\CollisionReward;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Memberticheng;
use App\Member;

class PayOrderController extends Controller
{
    /**
     * 订单支付完成(余额|银联|三方)
     * @param $order_id
     */
    public function third_pay_finish_payment($order_id): array
    {
        DB::beginTransaction();
        try {
            $pro_buy_data = DB::table("productbuy")->select('id', 'userid', 'username', 'productid', 'status', 'num', 'amount', 'pay_status', 'category_id', 'ip','pay_type')->where(['id' => $order_id])->first();
            if (!$pro_buy_data) {
                Log::channel('pay')->warning('查无订单(' . $order_id . ')');
                return ["status" => 0, "msg" => "查无订单!"];
            }
            if( $pro_buy_data->status!=1){
                Log::channel('pay')->warning('该订单还未完成支付('.$order_id.')');
                return ["status"=>0,"msg"=>"该订单还未完成支付"];
            }
            $Member = DB::table("member")->select('id', 'username', 'amount', 'level', 'mtype', 'activation', 'integral')->where(['id' => $pro_buy_data->userid])->first();
            $product = DB::table("products")
                ->select('id', 'title', 'category_id', 'qtje', 'isft', 'tzzt', 'hkfs', 'shijian', 'zgje', 'qxdw', 'zsje', 'zsje_type', 'jyrsy', 'qtsl', 'zscp_id', 'fy_type', 'collision_times')
                ->where(['id' => $pro_buy_data->productid])
                ->first();
            //1:固定数量  3:倍数  zscp_id=0不赠送
            $has_hb_zs = DB::table('productbuy')->select('id')->where(['buy_from_id' => $order_id])->first();
            if ($product->zscp_id != 0 && in_array($product->zsje_type, [1, 3]) && !$has_hb_zs) {
                //赠送的产品信息
                $zscp_info = DB::table("products")
                    ->select('id', 'title', 'category_id', 'qtje', 'isft', 'tzzt', 'hkfs', 'shijian', 'zgje', 'qxdw', 'zsje', 'zsje_type', 'jyrsy', 'qtsl', 'zscp_id', 'fy_type')
                    ->where(['id' => $product->zscp_id])
                    ->first();
                if ($product->zsje_type == 3) {
                    $zszsl = $product->zsje * $pro_buy_data->num;//赠送倍数 * 购买数量
                    $zszje = intval($zszsl * $zscp_info->qtje);//赠送总金额
                } else {
                    $zszsl = $product->zsje;
                    $zszje = intval($zszsl * $zscp_info->qtje);
                }

                $useritem_time2 = \App\Productbuy::DateAdd("d", 1, date('Y-m-d 0:0:0', time()));
                if ($zscp_info->qxdw == '个自然日') {
                    $useritem_time2 = \App\Productbuy::DateAdd("d", 1, date('Y-m-d 0:0:0', time()));
                } else if ($zscp_info->qxdw == '个小时') {
                    $useritem_time2 = \App\Productbuy::DateAdd("h", 1, date('Y-m-d H:i:i', time()));
                }

                //赠送项目
                $zscp_log = [
                    "userid" => $Member->id,
                    "username" => $Member->username,
                    "money" => $zszje,
                    "notice" => "参与项目(" . $product->title . "),赠送项目(" . $zscp_info->title . ")",
                    "type" => "赠送项目",
                    "status" => "+",
                    "yuanamount" => $Member->amount,
                    "houamount" => $Member->amount,
                    "ip" => $pro_buy_data->ip,
                    "category_id" => $zscp_info->category_id,
                    "product_id" => $zscp_info->id,
                    "product_title" => $zscp_info->title,
                    'num' => $zszsl,
                    'moneylog_type_id' => '4',
                ];
                \App\Moneylog::AddLog($zscp_log);

                //添加赠送项目订单
                $zscp_hkfs = trim($zscp_info->hkfs);  //还款方式
                $zscp_zhouqi = trim($zscp_info->shijian);//周期

                $zscp['userid'] = $Member->id;
                $zscp['username'] = $Member->username;
                $zscp['level'] = $Member->level;
                $zscp['productid'] = $zscp_info->id;
                $zscp['category_id'] = $zscp_info->category_id;
                $zscp['amount'] = $zszje; //赠送总金额
                $zscp['ip'] = $pro_buy_data->ip;
                $zscp['useritem_time'] = Carbon::now();
                $zscp['useritem_time2'] = $useritem_time2;
                $zscp['sendday_count'] = $pro_buy_data->hkfs == 1 ? 1 : $pro_buy_data->shijian;
                $zscp['status'] = 1;
                $zscp['num'] = $zszsl;//购买数量
                $zscp['unit_price'] = $zscp_info->qtje;//购买时单价
                $zscp['zsje'] = 0;
                $zscp['buy_from_id'] = $order_id;
                $zscp['created_date'] = date('Y-m-d');
                $zscp['order'] = 'JY' . date('YmdHis') . $this->get_random_code(7);
                $zscp['gq_order'] = 'C' . $this->get_random_code(8);

                $zscp['reason'] = "参与项目(" . $product->title . "),赠送项目(" . $zscp_info->title . ")";
                DB::table('productbuy')->insert($zscp);
                DB::table('statistics')->where('user_id', $Member->id)->increment('team_capital_flow', $zszje);//流水统计金额
            }

            //流水统计金额
            $capital_flow = $pro_buy_data->amount;
            //添加个人统计
            DB::table('statistics')->where('user_id', $Member->id)->increment('capital_flow', $capital_flow);
            //添加后台统计
            DB::table('statistics_sys')->where('id', 1)->increment('buy_amount', $capital_flow);
            //统计表end
            $is_return = false;
            if (($pro_buy_data->pay_type == 1 && in_array($product->fy_type, [1, 3])) || (in_array($pro_buy_data->pay_type, [2, 3, 4]) && in_array($product->fy_type, [1, 2]))) {
                $is_return = true;
            }
            if(in_array($pro_buy_data->pay_type,[2,3,4])){
                if($pro_buy_data->pay_type == 2){
                    $type_title = '参与项目,银行卡付款';
                }
                if(in_array($pro_buy_data->pay_type,[3,4])){
                    $type_title = '参与项目,线上支付';
                }
                //购买产品日志
                $log=[
                    "userid"=>$Member->id,
                    "username"=>$Member->username,
                    "money"=>$pro_buy_data->amount,
                    "notice"=>"参与项目(".$product->title.")",
                    "type"=>$type_title,
                    "status"=>"-",
                    "yuanamount"=>$Member->amount,
                    "houamount"=>$Member->amount,
                    "ip"=>$pro_buy_data->ip,
                    "category_id"=>$product->category_id,
                    "product_id"=>$product->id,
                    "product_title"=>$product->title,
                    'num'=>$pro_buy_data->num,
                    'moneylog_type_id'=>'2',
                ];
                \App\Moneylog::AddLog($log);
            }
            if ($is_return && $product->category_id != 42) {
                //上级 是否满足团队奖励
                //插入上家分成,百分比奖励
                //当前用户上家
                $Tichengs = Memberticheng::orderBy("id", "asc")->get();//percent提成比例
                $checkBayong = \App\Productbuy::checkBayong($pro_buy_data->productid);//查返佣比例
                $username = $buyman = $Member->username;
                $now_time = Carbon::now();
                $Member->username = substr_replace($Member->username, '****', 3, 5);

                foreach ($Tichengs as $recent) {
                    $shangjia = \App\Productbuy::checkTjr($username);//上家姓名 username
                    $ShangjiaMember = Member::where("username", $shangjia)->first();
                    if ($ShangjiaMember) {
                        $has_log = DB::table('moneylog')->select('id')->where(['moneylog_userid' => $ShangjiaMember->id, 'from_uid' => $Member->id, 'from_uid_buy_id' => $order_id])->first();
                        if (empty($shangjia) || empty($checkBayong) || $has_log) {
                            break;
                        }
                        //分成钱数
                        $rateMoney = intval($pro_buy_data->amount * $recent->percent * $checkBayong / 100);
                        // 计算本次分成真实金额
                        $rewardMoney = self::get_real_amount($ShangjiaMember->id, $rateMoney);
                        if($rewardMoney > 0) {
                            $title = "尊敬的{$shangjia}会员您好！您的{$recent->name}分成已到账";
                            $content = "您的下线{$buyman}购买项目成功,{$rewardMoney}元已赠送到您的账号,当前的提成比例为" . $recent->percent * $checkBayong . "%";
                            //站内消息
                            $msg = [
                                "userid" => $ShangjiaMember->id,
                                "username" => $ShangjiaMember->username,
                                "title" => $title,
                                "content" => $content,
                                "from_name" => "系统通知",
                                "types" => "下线购买分成",
                            ];
                            \App\Membermsg::Send($msg);

                            $MOamount = $ShangjiaMember->ktx_amount;
                            $ShangjiaMember->increment('ktx_amount', $rewardMoney);
                            $notice = "下线(" . $Member->username . ")购买(" . $product->title . ")项目分成";
                            $log = [
                                "userid" => $ShangjiaMember->id,
                                "username" => $ShangjiaMember->username,
                                "money" => $rewardMoney,
                                "notice" => $notice,
                                "type" => "下线购买分成",
                                "status" => "+",
                                "yuanamount" => $MOamount,
                                "houamount" => $ShangjiaMember->ktx_amount,
                                "ip" => $pro_buy_data->ip,
                                "category_id" => $product->category_id,
                                "product_id" => $product->id,
                                "from_uid" => $Member->id,
                                "from_uid_buy_id" => $order_id,
                                'moneylog_type_id' => '5',
                            ];
                            \App\Moneylog::AddLog($log);

                            $data = [
                                "userid" => $ShangjiaMember->id,
                                "username" => $ShangjiaMember->username,
                                "xxuserid" => $Member->id,
                                "xxusername" => $Member->username,
                                "amount" => $pro_buy_data->amount,
                                "preamount" => $rewardMoney,
                                "type" => "下线分成",
                                "status" => "1",
                                "xxcenter" => $recent->name,
                                "created_at" => $now_time,
                                "updated_at" => $now_time,
                            ];
                            DB::table("membercashback")->insert($data);
                        }
                        $username = $shangjia;
                    }
                }
            }

            //购买累计进入总金额
            $Nowmember = Member::find($Member->id);
            // 异步处理双区对碰奖励
            dispatch(new CollisionReward($Nowmember, $pro_buy_data->amount, $product));
            // 可获得对碰奖励金额  | 购买产品,用户激活
            DB::table("member")->where(['id' => $Member->id])->update(['status'=>1,'collision_amount'=>$pro_buy_data->amount * $product->collision_times]);
            //团队购买累计
            $topid = $Nowmember->top_uid;
            if ($topid != 0) {
                for ($i = 0; $i < 100; $i++) {
                    $topmemeber = Member::find($topid);
                    if (!empty($topmemeber)) {
                        $topmemeber->increment('allxf_fee', $pro_buy_data->amount);
                        $topmemeber->increment('month_allxf', $pro_buy_data->amount);
                        if ($topid == $Nowmember->top_uid) {
                            $topmemeber->increment('zt_sum_fee', $pro_buy_data->amount);
                        }
                        if ($topmemeber->top_uid == 0) {
                            break;
                        } else {
                            $topid = $topmemeber->top_uid;
                        }
                    } else {
                        break;
                    }
                }
            }
            DB::commit();
            return ['status' => 1, 'msg' => '订单完成'];
        }catch (\Exception $e){
            Log::channel('buy')->alert($e->getMessage());
            DB::rollBack();
            return ['status' => 0, 'msg' => $e->getMessage()];
        }
    }

    public static function get_real_amount($user_id,$amount){
        $user_is_out = false;
        // 查看当前用户本次应得返佣(已激活用户才能获得)
        $user = DB::table('member')->where(['id'=>$user_id,'status'=>1])->first(['id','status','username','collision_amount','collision_amount_finsh']);
        if(!$user){
           return 0;
        }
        if($user->collision_amount <= $user->collision_amount_finsh){
            //消息内容
            $content = '您已拿满本局奖励，本轮已出局';
            //站内消息
            $msg = [
                "userid" => $user->id,
                "username" => $user->username,
                "title" => '出局通知',
                "content" => $content,
                "from_name" => "系统通知",
                "types" => "下线购买分成",
            ];
            \App\Membermsg::Send($msg);
            // 用户出局，所有订单标记已结束
            self::finish_order($user_id);
            return 0;
        }
        if(($user->collision_amount - $user->collision_amount_finsh) > $amount){
            DB::table('member')->where(['id'=>$user_id])->increment('collision_amount_finsh',$amount);
            return $amount;
        }
        if(($user->collision_amount - $user->collision_amount_finsh) == $amount){
            //消息内容
            $content = '您已拿满本局奖励，本轮已出局';
            //站内消息
            $msg = [
                "userid" => $user->id,
                "username" => $user->username,
                "title" => '出局通知',
                "content" => $content,
                "from_name" => "系统通知",
                "types" => "下线购买分成",
            ];
            \App\Membermsg::Send($msg);
            // 用户出局，所有订单标记已结束
            self::finish_order($user_id);
            return $amount;
        }
        if(($user->collision_amount - $user->collision_amount_finsh) < $amount){
            $money = $user->collision_amount - $user->collision_amount_finsh;
            //消息内容
            $content = '您已拿满本局奖励，本轮已出局(本次应获得'.$amount.'元,实得'.$money.'元)';
            //站内消息
            $msg = [
                "userid" => $user->id,
                "username" => $user->username,
                "title" => '出局通知',
                "content" => $content,
                "from_name" => "系统通知",
                "types" => "下线购买分成",
            ];
            \App\Membermsg::Send($msg);
            // 用户出局，所有订单标记已结束
            self::finish_order($user_id);
            return $money;
        }
        return 0;
    }

    private static function finish_order($user_id){
        //收益订单结束
        DB::table('productbuy')->where(['userid'=>$user_id])->update(['status'=>0,'reason'=>'用户出局，订单结束']);
        //标记出局
        DB::table('member')->where(['id'=>$user_id])->update(['status'=>2,'collision_amount'=>0,'collision_amount_finsh'=>0]);
    }

    private function get_random_code($num)
    {
        $code_seed = "1234567890";
        $len = strlen($code_seed);
        $ban_num = ($num/2)-3;
        $code = "";
        for ($i = 0; $i < $num; $i++) {
            $rand = rand(0, $len - 1);
            if($i == $ban_num){
                $code .= 'O';
            }else{
                $code .= $code_seed[$rand];
            }
        }
        return $code;
    }
}
