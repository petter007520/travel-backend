<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PayOrderController;
use App\Member;
use App\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BonusSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:send{id=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '分红计划';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = $this->argument('id');
        $where=[];
        //单独跑productbuy某一条数据
        if($id>0){
            $where=[["id","=",$id]];
        }
        $now_time = date('Y-m-d H:i:s');
        $now_date = date('Y-m-d');
        $now_datetime = date('Y-m-d H:i');
        //获取所有可收益数据
		$count = DB::table("productbuy")
            ->where($where)
            ->where('num','>',0)
            ->where('amount','>',0)
            ->where('category_id','<>',11)
            ->where(['status'=>1])
            ->where(function ($q) {  //闭包返回的条件会包含在括号中
                return $q->where("useritem_time2", "=", DATE_FORMAT(NOW(), 'Y-m-d 00:00:00'))
                    ->orWhere([
                        ["useritem_time2", "<", DATE_FORMAT(NOW(), 'Y-m-d H:i:00')]
                    ]);
            })
            ->orderBy('useritem_time2')
            ->count();
        if ($count <= 0) {
            echo '暂无可返佣任务'."\n";
            return '暂无可返佣任务';
        }
        $task_num = intval(ceil($count / 8000));
        for ($ta = 0; $ta < $task_num; $ta++) {
            echo '本次分红任务次数：' . $task_num . '次' . '--当前执行第' . ($ta + 1) . '次' . "\n";
            $ProductbuyList = DB::table("productbuy")
                ->where($where)
                ->where('num', '>', 0)
                ->where('amount', '>', 0)
                ->where('category_id', '<>', 11)
                ->where(['status' => 1])
                ->where(function ($q) {  //闭包返回的条件会包含在括号中
                    return $q->where("useritem_time2", "=", DATE_FORMAT(NOW(), 'Y-m-d 00:00:00'))
                        ->orWhere([
                            ["useritem_time2", "<", DATE_FORMAT(NOW(), 'Y-m-d H:i:00')]
                        ]);
                })
                ->orderBy('useritem_time2')
                ->limit(8000)
                ->get();
            if (count($ProductbuyList) < 1) {
                echo '查无返佣计划';
                return '查无返佣计划';
            }

            //获取所有项目，并用项目id做键值对数组
            $Products = Product::get();
            foreach ($Products as $Product) {
                $this->Products[$Product->id] = $Product;
            }

            $i = 0;//当前返佣总人数
            $j = 0;//当前未到返佣时间人数
            $z = 0;
            foreach ($ProductbuyList as $value) {
                $userid = $value->userid;        //投注用户ID
                $pid = $value->productid;        //项目ID
                $buyid = $value->id;             //投注表ID。
                $created_date = '';
                if (isset($this->Products[$pid])) {
                    $i++;
                    $BuyMember = Member::find($userid);
                    $hsa_log = true;
                    if ($this->Products[$pid]->qxdw == "个小时") {
                        $hsa_log = DB::table('moneylog')
                            ->select('id')
                            ->where(['moneylog_userid' => $userid, 'buy_id' => $buyid, 'moneylog_type' => '项目分红'])
                            ->where('created_date', $now_datetime)
                            ->first();
                        $created_date = $now_datetime;
                    } else if ($this->Products[$pid]->qxdw == "个自然日") {
                        $hsa_log = DB::table('moneylog')
                            ->select('id')
                            ->where(['moneylog_userid' => $userid, 'buy_id' => $buyid, 'moneylog_type' => '项目分红'])
                            ->where('created_date', $now_date)
                            ->first();
                        $created_date = $now_date;
                    }
                    //今日是否已返还过收益
                    /***会员存在且未收益过***/
                    if ($BuyMember && !$hsa_log) {
                        $nowcishu = (int)$value->useritem_count;//收益次数
                        //判断 收益次数是否大于项目到期天数，当前时间是否小于下次收益时间
                        if (false && ($nowcishu >= $shijian || $useritem_time4 < $useritem_time2)) {
                            $j++;
                        } else {
                            //计算日收益
                            $money = floatval($this->Products[$pid]->jyrsy * $value->amount / 100);  //每日分红
                            //计算实际应得静态收益
                            $money = PayOrderController::get_real_amount($userid,$money);
                            if($money > 0) {
                                DB::beginTransaction();
                                try {
                                    if ($this->Products[$pid]->qxdw == '个自然日') {
                                        $useritem_time2 = \App\Productbuy::DateAdd("d", 1, $value->useritem_time2);
                                    } else if ($this->Products[$pid]->qxdw == '个小时') {
                                        $useritem_time2 = \App\Productbuy::DateAdd("h", 1, $value->useritem_time2);
                                    }
                                    $data['useritem_time1'] = $now_time;//今日收益时间
                                    $data['useritem_time2'] = $useritem_time2;
                                    $data['useritem_count'] = $nowcishu + 1;//收益次数+1
                                    if ($value->category_id != 42 && $data['useritem_count'] >= (int)$this->Products[$pid]->shijian) {
                                        DB::table("productbuy")->where('id', $value->id)->update(['status'=>0]);
                                    }
                                    // 产品分红
                                    if ($value->category_id == '13') {
                                        //更新项目分红时间
                                        DB::table("productbuy")->where("id", $buyid)->update($data);
                                        //金额记录日志
                                        $projectName = $this->Products[$pid]->title;
                                        $notice = '项目收益-(' . $projectName . ')';
                                        $amountFH = round($money, 2);//日收益金额 加日志

                                        $BuyMember_id = $BuyMember->id;
                                        $BuyMember_username = $BuyMember->username;
                                        $Mamount = $BuyMember->ktx_amount;
                                        $ip = \Request::getClientIp();
                                        /**************************收益金额加入 用户余额*****************************/
                                        $BuyMember->increment('ktx_amount', $amountFH);

                                        //添加金额log表
                                        $log = [
                                            "userid" => $BuyMember_id,
                                            "username" => $BuyMember_username,
                                            "money" => $amountFH,
                                            "notice" => $notice,
                                            "type" => '项目分红',
                                            "status" => '+',
                                            "yuanamount" => $Mamount,
                                            "houamount" => $BuyMember->ktx_amount,
                                            "ip" => $ip,
                                            "product_id" => $pid,
                                            "category_id" => $this->Products[$pid]->category_id,
                                            "product_title" => $projectName,
                                            "buy_id" => $buyid,
                                            "moneylog_type_id" => '10_' . $buyid . '_' . $now_date,
                                            'created_at' => $now_time,
                                            'created_date' => $created_date
                                        ];
                                        \App\Moneylog::AddLog($log);


                                        if ($data['useritem_count'] >= (int)$this->Products[$pid]->shijian) {
                                            $Mamount = $BuyMember->ktx_amount;
                                            $amountFB = round($value->amount, 2);
                                            //退还本金
                                            $BuyMember->increment('ktx_amount', $amountFB);
                                            $notice = '项目返本-(' . $projectName . ')';
                                            $log = [
                                                "userid" => $BuyMember->id,
                                                "username" => $BuyMember->username,
                                                "money" => $amountFB,
                                                "notice" => $notice,
                                                "type" => '项目返本',
                                                "status" => '+',
                                                "yuanamount" => $Mamount,
                                                "houamount" => $BuyMember->ktx_amount,
                                                "ip" => $ip,
                                                "product_id" => $pid,
                                                "category_id" => $this->Products[$pid]->category_id,
                                                "product_title" => $projectName,
                                                "buy_id" => $buyid,
                                                "moneylog_type_id" => '21_' . $buyid . '_' . $now_date,
                                                'created_at' => $now_time,
                                                'created_date' => $created_date
                                            ];
                                            \App\Moneylog::AddLog($log);
                                        }
                                    }
                                    //添加check_money 表
                                    $check_money = [
                                        'uid' => $BuyMember->id,
                                        'username' => $BuyMember->username,
                                        'money' => $money,
                                        'type' => 2,
                                        'created_date' => $created_date,
                                        'from_id' => $value->id,
                                        'created_at' => $now_time,
                                    ];
                                    DB::table('check_money')->insert($check_money);

                                    //添加后台统计
                                    DB::table('statistics_sys')->where('id', 1)->increment('release_amount', $money);
                                    $z++;
                                    DB::commit();
                                } catch (\Exception $exception) {
                                    Log::channel('pf')->alert($exception->getMessage());
                                    DB::rollBack();
                                }
                            }
                        }
                    }
                }
            }
            /**循环结束**/
            // $peo = $i - $j;
            $rmsg = "反佣成功。返佣" . $i . "人，成功" . $z . "人，" . $j . "人时间未到！";
            echo $rmsg;
        }
    }
}
