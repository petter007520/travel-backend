<?php

namespace App\Jobs;

use App\Http\Controllers\App\BankCardStatementController;
use App\Mail\PcSendMailUtil;
use App\Mail\SendMailUtil;
use App\Member;
use App\Models\Bank;
use App\Models\BankCardStatement;
use App\Models\BankCardStatementExport;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 对碰奖励发放队列
 */
class CollisionReward implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $member = [];
    public $productInfo = [];
    public $amount = 0;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($member, $integrals, $product)
    {
        $this->member = $member;
        $this->amount = $integrals;
        $this->productInfo = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $topid = $this->member->top_uid;    //上级ID
        $integrals = $this->amount;       //金额
        $Member = $this->member;          //用户信息
        $product = $this->productInfo;    //产品信息
        $region = $this->member->region;  //区域

        $area = '左区';
        if ($region == 2) {
            $area = '右区';
        }

        Log::channel('collision_reward')->info($area . $this->member->username . '购买' . $product->title . '对碰奖励开始');

        $ratio = DB::table("setings")->where("keyname", "dp_reward_ratio")->value("value");

        for ($i = 0; $i < 200; $i++) {

            $topmemeber = Member::find($topid);

            //不存在大小区
            if ($topmemeber->left_blance == 0 && $topmemeber->right_blance == 0) {
                $topid = $topmemeber->top_uid;
                $region = $topmemeber->region;
                continue;
            }

            //用户的小区
            $user_region = $topmemeber->left_blance > $topmemeber->right_blance ? 1 : 2;

            //存在上级
            if (!empty($topmemeber)) {
                //用户为激活状态
                if ($topmemeber->status == 1) {

                    //用户原本金额
                    $yuanmoney = $topmemeber->ktx_amount;

                    //用户还能获利金额 1
                    $total_amount = $topmemeber->collision_amount - $topmemeber->collision_amount_finsh;

                    //双区 对碰规则 1 .存在大区 2.小区和大区对碰
                    if ($region == 1) {

                        //左区业绩增加
                        $topmemeber->increment('left_amount', $integrals);
                        $topmemeber->increment('left_blance', $integrals);

                        //左区的金额小于右区的金额 产生对碰
                        if ($topmemeber->left_blance < $topmemeber->right_blance) {

                            //对碰金额 返给上级
                            $collision_amount = round($integrals * $ratio, 2);
                            if ($collision_amount >= $total_amount) {
                                $collision_amount = $total_amount;
                                //用户出局

                            }
                            $topmemeber->increment('ktx_amount', $collision_amount);
                            $log = [
                                "userid" => $topmemeber->id,
                                "username" => $topmemeber->username,
                                "money" => $collision_amount,
                                "notice" => "下线(" . $this->member->username . ")购买(" . $product->title . ")对碰奖励",
                                "type" => "对碰奖励",
                                "status" => "+",
                                "yuanamount" => $yuanmoney,
                                "houamount" => $topmemeber->ktx_amount,
                                "ip" => \Request::getClientIp(),
                            ];
                            \App\Moneylog::AddLog($log);

                            $topmemeber->decrement('right_blance', $integrals);                 //减少右区业绩余额
                            $topmemeber->increment('collision_amount_finsh', $collision_amount);//增加用户已完成对碰奖励

                            Log::channel('collision_reward')->info('左区:' . $this->member->username . '购买' . $product->title . '-' .
                                $topmemeber->username . '获得对碰奖励' . $collision_amount);
                        } else {
                            //左区的业绩余额大于右区的业绩余额 并且右区业绩大于0
                            if ($topmemeber->right_blance > 0) {
                                $integrals = $topmemeber->left_blance - $topmemeber->right_blance;
                                $collision_amount = round($integrals * $ratio, 2);
                                if ($collision_amount > $total_amount) {
                                    $collision_amount = $total_amount;
                                }
                                $topmemeber->increment('ktx_amount', $collision_amount);
                                $log = [
                                    "userid" => $topmemeber->id,
                                    "username" => $topmemeber->username,
                                    "money" => $collision_amount,
                                    "notice" => "下线(" . $this->member->username . ")购买(" . $product->title . ")对碰奖励",
                                    "type" => "对碰奖励",
                                    "status" => "+",
                                    "yuanamount" => $yuanmoney,
                                    "houamount" => $topmemeber->ktx_amount,
                                    "ip" => \Request::getClientIp(),
                                ];
                                \App\Moneylog::AddLog($log);

                                $topmemeber->decrement('left_blance', $integrals);                   //减少左区业绩余额
                                $topmemeber->decrement('right_blance', $integrals);                  //右区业绩为0
                                $topmemeber->increment('collision_amount_finsh', $collision_amount); //增加用户已完成对碰奖励
                            }
                        }
                    } else if ($region == 2) {
                        //右区业绩增加
                        $topmemeber->increment('right_amount', $integrals . "ddd");
                        $topmemeber->increment('right_blance', $integrals);

                        //右区的金额小于左区的金额 产生对碰
                        if ($topmemeber->right_blance < $topmemeber->left_blance) {
                            //对碰金额 返给上级
                            $collision_amount = round($integrals * $ratio, 2);
                            if ($collision_amount > $total_amount) {
                                $collision_amount = $total_amount;
                            }
                            $topmemeber->increment('ktx_amount', $collision_amount);
                            $log = [
                                "userid" => $topmemeber->id,
                                "username" => $Member->username,
                                "money" => $collision_amount,
                                "notice" => "下线(" . $this->member->username . ")购买(" . $product->title . ")对碰奖励",
                                "type" => "对碰奖励",
                                "status" => "+",
                                "yuanamount" => max($topmemeber->ktx_amount - $collision_amount, 0),
                                "houamount" => $topmemeber->ktx_amount,
                                "ip" => \Request::getClientIp(),
                            ];
                            \App\Moneylog::AddLog($log);

                            $topmemeber->decrement('left_blance', $integrals);                    //减少左区业绩余额
                            $topmemeber->increment('collision_amount_finsh', $collision_amount);  //增加用户已完成对碰奖励

                            Log::channel('collision_reward')->info('右区:' . $this->member->username . '购买' . $product->title . '-' .
                                $topmemeber->username . '获得对碰奖励' . $collision_amount);
                        } else {
                            //右区的业绩余额大于左区的业绩余额 并且左区业绩大于0
                            if ($topmemeber->left_blance > 0) {
                                $integrals = $topmemeber->right_blance - $topmemeber->left_blance;
                                $collision_amount = round($integrals * $ratio, 2);
                                if ($collision_amount > $total_amount) {
                                    $collision_amount = $total_amount;
                                }

                                $topmemeber->increment('ktx_amount', $collision_amount);

                                $log = [
                                    "userid" => $topmemeber->id,
                                    "username" => $topmemeber->username,
                                    "money" => $collision_amount,
                                    "notice" => "下线(" . $this->member->username . ")购买(" . $product->title . ")对碰奖励",
                                    "type" => "对碰奖励",
                                    "status" => "+",
                                    "yuanamount" => $yuanmoney,
                                    "houamount" => $topmemeber->ktx_amount,
                                    "ip" => \Request::getClientIp(),
                                ];
                                \App\Moneylog::AddLog($log);

                                $topmemeber->decrement('right_blance', $integrals);                   //减少右区业绩余额
                                $topmemeber->decrement('left_blance', $integrals);                   //左区业绩为0
                                $topmemeber->increment('collision_amount_finsh', $collision_amount); //增加用户已完成对碰奖励
                            }
                        }
                    }
                }

                //双区中小区业绩
                $small_amount = $topmemeber->left_amount >= $topmemeber->right_amount ? $topmemeber->right_amount : $topmemeber->left_amount;
                $Nolevel = DB::table("memberlevel")->find($topmemeber->level);
                $levellist = DB::table("memberlevel")->orderBy('id', 'ASC')->get()->toArray();
                $lid = 0;
                if (!empty($Nolevel)) {
                    $lid = $Nolevel->id;
                }
                //社区简行VIP升级
                foreach ($levellist as $key => $value) {
                    if ($small_amount >= $value->level_fee && $value->id > $lid) {
                        $data1['level'] = $value->id;
                        DB::table("member")->where('id', $topmemeber->id)->update($data1);
                    }
                }


                //用户发放启航之星任务奖励,享受小区业绩增加百分比奖励
                if ($region == $user_region) {
                    if ($lid > 0) {
                        foreach ($levellist as $key => $value) {
                            if ($lid == $value->id) {
                                $vip_reward = rand($integrals * $value->rate, 2);
                                if ($vip_reward > $topmemeber->collision_amount - $topmemeber->collision_amount_finsh) {
                                    $vip_reward = $topmemeber->collision_amount - $topmemeber->collision_amount_finsh;
                                }
                                $topmemeber->increment('ktx_amount', $vip_reward);
                                $topmemeber->increment('collision_amount_finsh', $vip_reward);

                                $log = [
                                    "userid" => $topmemeber->id,
                                    "username" => $topmemeber->username,
                                    "money" => $collision_amount,
                                    "notice" => "下线(" . $this->member->username . ")购买(" . $product->title . ")启航之星奖励",
                                    "type" => "启航之星奖励",
                                    "status" => "+",
                                    "yuanamount" => $yuanmoney,
                                    "houamount" => $topmemeber->ktx_amount,
                                    "ip" => \Request::getClientIp(),
                                ];
                                \App\Moneylog::AddLog($log);
                            }
                        }
                    }
                }

                //双区小区业绩达标送旅行大礼包
                $travellist = DB::table("memberlevel")->orderBy('id', 'ASC')->get()->toArray();
                foreach ($travellist as $key => $value) {
                    if ($small_amount >= $value->level_fee) {
                        $hsa_log = DB::table("travellog")->where('userid', $topmemeber->id)->where('userid', $topmemeber->id)->first();
                        if (!$hsa_log) {
                            $travel_data = [
                                'userid' => $topmemeber->id,
                                'username' => $topmemeber->username,
                                'travel_id' => $value->id,
                                'travel_name' => $value->name,
                            ];
                            DB::table('travellog')->insert($travel_data);
                        }
                    }
                }

                if ($topmemeber->top_uid == 0) {
                    break;
                } else {
                    $topid = $topmemeber->top_uid;
                    $region = $topmemeber->region;
                }
            } else {
                break;
            }
        }
        Log::channel('collision_reward')->info($area . $this->member->username . '购买' . $product->title . '对碰奖励结束');
    }

    public function fail($exception = null)
    {
        Log::channel('collision_reward')->error($exception);
    }
}
