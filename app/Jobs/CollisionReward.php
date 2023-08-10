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

            if (!empty($topmemeber)) {
                Log::channel('collision_reward')->info('111111111111111');
                //双区 对碰规则 1 .存在大区 2.小区和大区对碰
                if ($region == 1) {
                    //左区业绩增加
                    $topmemeber->increment('left_amount', $integrals);
                    $topmemeber->increment('left_blance', $integrals);

                    //左区的金额小于右区的金额 产生对碰
                    if ($topmemeber->left_blance < $topmemeber->right_blance) {
                        //对碰金额 返给上级
                        $collision_amount = $integrals * $ratio;
                        $topmemeber->increment('ktx_amount', $collision_amount);
                        $log = [
                            "userid" => $topmemeber->id,
                            "username" => $topmemeber->username,
                            "money" => $collision_amount,
                            "notice" => "下线(" . $this->member->username . ")购买(" . $product->title . ")对碰奖励",
                            "type" => "对碰奖励",
                            "status" => "+",
                            "yuanamount" => max($topmemeber->ktx_amount - $collision_amount, 0),
                            "houamount" => $topmemeber->ktx_amount,
                            "ip" => \Request::getClientIp(),
                        ];
                        \App\Moneylog::AddLog($log);

                        $topmemeber->decrement('right_blance', $integrals);                 //减少右区业绩余额
                        $topmemeber->increment('collision_amount_finsh', $collision_amount);//增加用户已完成对碰奖励

                        Log::channel('collision_reward')->info('左区:' . $this->member->username . '购买' . $product->title . '-' .
                            $topmemeber->username . '获得对碰奖励' . $collision_amount);
                    }
                } else if ($region == 2) {
                    //右区业绩增加
                    $topmemeber->increment('right_amount', $integrals);
                    $topmemeber->increment('right_blance', $integrals);

                    //右区的金额小于左区的金额 产生对碰
                    if ($topmemeber->right_blance < $topmemeber->left_blance) {
                        //对碰金额 返给上级
                        $collision_amount = $integrals * $ratio;
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
                    }
                }
                if ($topmemeber->top_uid == 0) {
                    break;
                } else {
                    $topid = $topmemeber->top_uid;
                    $region = $topmemeber->region;
                }
            } else {
                Log::channel('collision_reward')->info('22222222');
                break;
            }
        }
        Log::channel('collision_reward')->info($area . $this->member->username . '购买' . $product->title . '对碰奖励结束');
    }

    public function fail($exception = null)
    {
        Log::error($exception);
    }
}
