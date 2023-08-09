<?php


namespace App\Http\Controllers\Admin;
    use App\Member;
    use App\Memberlevel;
    use App\Product;
    use App\statistics;
    use App\Productbuy;
    use App\Category;
    use Carbon\Carbon;
    use DB;
    use Illuminate\Http\Request;
    use Session;
    use Cache;
    use App\Membercurrencys;
    use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\App;
    use App\Bigtree;

class ProductbuyController extends BaseController
{

    private $table="productbuy";


    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->Model=new Productbuy();

        $Products= Product::get();
        foreach ($Products as $Product){
            $this->Products[$Product->id]=$Product;
        }

        $this->CategoryModel=new Category();
        $category_id=$request->s_categoryid;
        view()->share("tree_option",$this->CategoryModel->tree_option(0,0,$category_id,0,'products'));

      $Memberlevels= Memberlevel::get();

        foreach ($Memberlevels as $Memberlevel){
            $this->Memberlevels[$Memberlevel->id]=$Memberlevel;
        }

				$totalAmount = DB::table($this->table)
                    ->where(['status'=>1])
                    // ->where(['category_id'=>13,'status'=>1])
                    ->where(function ($query) {
                        $s_siteid=[];
                        if(isset($_REQUEST['s_key']) && $_REQUEST['s_key']!=''){
                            $s_siteid[]=[$this->table.".username","=",$_REQUEST['s_key']];
                        }

                        $query->where($s_siteid);
                    })
                    ->where(function ($query) {
                        $date_s=[];
                        if(isset($_REQUEST['date_s']) && $_REQUEST['date_s']!=''){

                            $query->whereDate("useritem_time",">=",$_REQUEST['date_s']." 00:00:00");
                        }
                    })
                    ->where(function ($query) {
                        $date_s=[];
                        if(isset($_REQUEST['date_e']) && $_REQUEST['date_e']!=''){

                            $query->whereDate("useritem_time","<=",$_REQUEST['date_e']." 23:59:59");
                        }
                    })
                    ->where(function ($query) {
                        $s_categoryid=[];
                        if(isset($_REQUEST['s_categoryid']) && $_REQUEST['s_categoryid']>0){
                            $s_categoryid[]=[$this->table.".category_id","=",$_REQUEST['s_categoryid']];
                        }

                        $query->where($s_categoryid);
                    })
                    ->where(function ($query) {
                        $s_status=[];
                        if(isset($_REQUEST['s_status']) && $_REQUEST['s_status']!=''){
                            $s_status[]=[$this->table.".status","=",$_REQUEST['s_status']];
                        }

                        $query->where($s_status);
                    })
                    ->sum('amount');

        view()->share("totalAmount",$totalAmount);

        $startdata = date('Y-m-d 00:00:00', time());
        $enddata = date('Y-m-d 23:59:59', time());


        $today_amount= DB::table($this->table)
                    // ->where(['category_id'=>13])
                    ->where(function ($query) {
                        if(!isset($_REQUEST['date_s'])){
                             $query->where('updated_at','>=',date('Y-m-d 00:00:00', time()))
                             ->where('updated_at','<=',date('Y-m-d 23:59:59', time()));
                        }

                    })
                    // ->where('updated_at','>=',$startdata)
                    // ->where('updated_at','<=',$enddata)
                    ->where(function ($query) {
                        $s_siteid=[];
                        if(isset($_REQUEST['s_key']) && $_REQUEST['s_key']!=''){
                            $s_siteid[]=[$this->table.".username","=",$_REQUEST['s_key']];
                        }

                        $query->where($s_siteid);
                    })
                    ->where(function ($query) {
                        $date_s=[];
                        if(isset($_REQUEST['date_s']) && $_REQUEST['date_s']!=''){

                            $query->whereDate("useritem_time",">=",$_REQUEST['date_s']." 00:00:00");
                        }
                    })
                    ->where(function ($query) {
                        $date_s=[];
                        if(isset($_REQUEST['date_e']) && $_REQUEST['date_e']!=''){

                            $query->whereDate("useritem_time","<=",$_REQUEST['date_e']." 23:59:59");
                        }
                    })
                    ->where(function ($query) {
                        $s_categoryid=[];
                        if(isset($_REQUEST['s_categoryid']) && $_REQUEST['s_categoryid']>0){
                            $s_categoryid[]=[$this->table.".category_id","=",$_REQUEST['s_categoryid']];
                        }

                        $query->where($s_categoryid);
                    })
                    ->where(function ($query) {
                        $s_status=[];
                        if(isset($_REQUEST['s_status']) && $_REQUEST['s_status']!=''){
                            $s_status[]=[$this->table.".status","=",$_REQUEST['s_status']];
                        }

                        $query->where($s_status);
                    })
					->where(function ($query) {
						$s_pay_type=[];
						if(isset($_REQUEST['s_pay_type']) && $_REQUEST['s_pay_type']!=''){
							$s_pay_type[]=[$this->table.".pay_type","=",$_REQUEST['s_pay_type']];
						}

						$query->where($s_pay_type);
					})
                    ->sum('amount');

        view()->share("today_amount",$today_amount);

        $today_amount_ok= DB::table($this->table)
                    // ->where(['category_id'=>13,'status'=>1])
                    ->where(['status'=>1])
                    ->where(function ($query) {
                        if(!isset($_REQUEST['date_s'])){
                             $query->where('updated_at','>=',date('Y-m-d 00:00:00', time()))
                             ->where('updated_at','<=',date('Y-m-d 23:59:59', time()));
                        }

                    })
                    // ->where('updated_at','>=',$startdata)
                    // ->where('updated_at','<=',$enddata)
                    ->where(function ($query) {
                        $s_siteid=[];
                        if(isset($_REQUEST['s_key']) && $_REQUEST['s_key']!=''){
                            $s_siteid[]=[$this->table.".username","=",$_REQUEST['s_key']];
                        }

                        $query->where($s_siteid);
                    })
                    ->where(function ($query) {
                        $date_s=[];
                        if(isset($_REQUEST['date_s']) && $_REQUEST['date_s']!=''){

                            $query->whereDate("useritem_time",">=",$_REQUEST['date_s']." 00:00:00");
                        }
                    })
                    ->where(function ($query) {
                        $date_s=[];
                        if(isset($_REQUEST['date_e']) && $_REQUEST['date_e']!=''){

                            $query->whereDate("useritem_time","<=",$_REQUEST['date_e']." 23:59:59");
                        }
                    })
                    ->where(function ($query) {
                        $s_categoryid=[];
                        if(isset($_REQUEST['s_categoryid']) && $_REQUEST['s_categoryid']>0){
                            $s_categoryid[]=[$this->table.".category_id","=",$_REQUEST['s_categoryid']];
                        }

                        $query->where($s_categoryid);
                    })
                    ->where(function ($query) {
                        $s_status=[];
                        if(isset($_REQUEST['s_status']) && $_REQUEST['s_status']!=''){
                            $s_status[]=[$this->table.".status","=",$_REQUEST['s_status']];
                        }

                        $query->where($s_status);
                    })
					->where(function ($query) {
						$s_pay_type=[];
						if(isset($_REQUEST['s_pay_type']) && $_REQUEST['s_pay_type']!=''){
							$s_pay_type[]=[$this->table.".pay_type","=",$_REQUEST['s_pay_type']];
						}

						$query->where($s_pay_type);
					})
                    ->sum('amount');

        view()->share("today_amount_ok",$today_amount_ok);
    }



    public function index(Request $request){

        return redirect(route($this->RouteController.".lists"));

    }




    public function lists(Request $request){



      //  \App\Memberwithdrawal::AddWithdrawal(1,5000);
        $pagesize=10;//默认分页数
        if(Cache::has('pagesize')){
            $pagesize=Cache::get('pagesize');
        }



        if($request->ajax()){
            $listDB = DB::table($this->table)
				->leftJoin('member', 'member.id', '=', 'productbuy.userid')
                ->select($this->table.'.*')
            ->where(function ($query) {
                $s_siteid=[];
                if(isset($_REQUEST['s_key']) && $_REQUEST['s_key']!=''){
                    $s_siteid[]=[$this->table.".username","=",$_REQUEST['s_key']];
                }

                $query->where($s_siteid);
            })
			->where(function ($query) {
                $top_uid = 0;
                if(isset($_REQUEST['top_uid']) && $_REQUEST['top_uid']!=''){
                    $user_info = DB::table('member')
						->where(['username' => $_REQUEST['top_uid']])
						->first();
					$top_uid = $user_info->id;
                }
				if ($top_uid > 0) {
					$query->where('member.top_uid', '=', $top_uid);
				}
            })
            ->where(function ($query) {
                $s_status=[];
                if(isset($_REQUEST['s_status']) && $_REQUEST['s_status']!=''){
                    $s_status[]=[$this->table.".status","=",$_REQUEST['s_status']];
                }

                $query->where($s_status);
            })
                ->where(function ($query) {
                $s_pay_type=[];
                if(isset($_REQUEST['s_pay_type']) && $_REQUEST['s_pay_type']!=''){
                    $s_pay_type[]=[$this->table.".pay_type","=",$_REQUEST['s_pay_type']];
                }

                $query->where($s_pay_type);
            })
               ->where(function ($query) {
                $s_categoryid=[];
                if(isset($_REQUEST['s_categoryid']) && $_REQUEST['s_categoryid']>0){
                    $s_categoryid[]=[$this->table.".category_id","=",$_REQUEST['s_categoryid']];
                }

                $query->where($s_categoryid);
            })
            ->where(function ($query) {
                    $date_s=[];
                    if(isset($_REQUEST['date_s']) && $_REQUEST['date_s']!=''){

                        $query->whereDate("useritem_time",">=",$_REQUEST['date_s']." 00:00:00");
                    }
                })

            ->where(function ($query) {
                $date_s=[];
                if(isset($_REQUEST['date_e']) && $_REQUEST['date_e']!=''){

                    $query->whereDate("useritem_time","<=",$_REQUEST['date_e']." 23:59:59");
                }
            });


            $list=$listDB->orderBy($this->table.".id","desc")
                ->paginate($pagesize);

            if($list){
                $total_amount =  $ok_total_amount = 0;
                foreach ($list as $item){

                    $item->product=  isset($this->Products[$item->productid])?$this->Products[$item->productid]->title:'0';
                    $item->rate=isset($this->Memberlevels[$item->level])?$this->Memberlevels[$item->level]->rate:'0';

                    if(isset($this->Products[$item->productid])){

                        $moneyCount = $this->Products[$item->productid]->jyrsy * $item->amount/100;
                        $item->moneyCount= sprintf("%.2f",$moneyCount);

                        $elseMoney = $item->rate * $item->amount/100;
                        $item->elseMoney= sprintf("%.2f",$elseMoney);

                    }else{
                        $item->moneyCount=0;
                    }

                    if($item->useritem_time2<=Carbon::now() && $item->useritem_count < $item->sendday_count){
                        $item->fh=1;
                    }else{
                        $item->fh=0;
                    }

                    $item->timenow=Carbon::now()->format("Y-m-d H:i:s");

                    $item->payimg = json_decode($item->payimg);

                }

                return ["status"=>0,"list"=>$list,"pagesize"=>$pagesize];
            }
        }else{



            return $this->ShowTemplate([]);
        }

    }

    public function store(Request $request){



    }






    public function update(Request $request)
    {
        if($request->isMethod("post")){

            $Model = $this->Model::find($request->get('id'));
            $products_info = DB::table("products")->where(['id'=>$Model->productid])->first();
            $products_title = $products_info->title;
            $Member= Member::where('state',1)->find($Model->userid);

            if($request->status!='8' && $request->status!='9'){
                if($Model->status==2){
                    DB::beginTransaction();
                 //   try{
                        if($request->status=='1'){
	                        $integrals = $Model->amount;
                            //当前产品信息
                            $buy_product_info = DB::table("products")
                                    ->select('id','title','category_id','qtje','isft','tzzt','hkfs','shijian','zgje','qxdw','zsje','zsje_type','jyrsy','qtsl','zscp_id','fy_type')
                                    ->where(['id'=>$Model->productid])
                                    ->first();

                            //购买流水
                            $log=[
                                "userid"=>$Member->id,
                                "username"=>$Member->username,
                                "money"=>$Model->amount,
                                "notice"=>"参与项目(".$buy_product_info->title.")",
                                "type"=>"参与项目,银行卡付款",
                                "status"=>"-",
                                "yuanamount"=>$Member->amount,
                                "houamount"=>$Member->amount,
                                "ip"=>\Request::getClientIp(),
                                "category_id"=>$buy_product_info->category_id,
                                "product_id"=>$buy_product_info->id,
                                "product_title"=>$buy_product_info->title,
                                'num'=>$Model->num,
                                'moneylog_type_id'=>'2',
                            ];
                            \App\Moneylog::AddLog($log);

                            $has_zs = DB::table('productbuy')->where(['buy_from_id'=>$Model->id])->first();
                            	if($buy_product_info->category_id==12){
                				    $Member->increment('sum_gqfee',$Model->amount);
                				}else if($buy_product_info->category_id==13){
                				    $Member->increment('sum_jjfee',$Model->amount);
                				}else if($buy_product_info->category_id==42){
                				    $Member->increment('sum_yeb',$Model->amount);
                				}
                            //当前统计时间
                            $now_statistics_date = date('Y-m-d',time());
                            $capital_flow = $Model->amount;
                            $repeat = 0;
                            $UserId = $Model->userid;
                            //赠送产品
                            if($Model->zscp_id != 0 && in_array($buy_product_info->zsje_type,[1,3]) && !$has_zs){

                                //赠送的产品信息
                                $zscp_info = DB::table("products")
                                    ->select('id','title','category_id','qtje','isft','tzzt','hkfs','shijian','zgje','qxdw','zsje','zsje_type','jyrsy','qtsl','zscp_id')
                                    ->where(['id'=>$Model->zscp_id])
                                    ->first();
                                $user_info = DB::table('member')->where('id',$Model->userid)->first();

                                if($buy_product_info->zsje_type == 3){
                                    // $product->zsje = intval($products_info->zsje * 0.01 * $Model->num);//购买的数量的百分比
                                    //赠送总数量
                                    $zszsl = $Model->num * $buy_product_info->zsje;
                                    //赠送总金额
                                    $zszje = intval($zszsl * $zscp_info->qtje);
                                }else{
                                    $zszsl = $buy_product_info->zsje;
                                    $zszje = intval($zszsl * $zscp_info->qtje);
                                }
                                if($buy_product_info->qxdw =='个自然日'){
                                    $useritem_time2 = \App\Productbuy::DateAdd("d",1, date('Y-m-d 0:0:0',time()));
                                }else if($buy_product_info->qxdw =='个小时'){
                                   // echo '111';
                                    $useritem_time2 = \App\Productbuy::DateAdd("h",1, date('Y-m-d H:i:s',time()));
                                  //  echo $useritem_time2;
                                }



                                //赠送项目
                                $zscp_log=[
                                    "userid"=>$Model->userid,
                                    "username"=>$Model->username,
                                    "money"=> $zszje,
                                    "notice"=>"加入项目(".$products_title.")[".$Model->id."](-),赠送项目(".$zscp_info->title.")",
                                    "type"=>"赠送项目",
                                    "status"=>"+",
                                    "yuanamount"=>0,
                                    "houamount"=>0,
                                    "ip"=>\Request::getClientIp(),
                                    "category_id"=>$zscp_info->category_id,
                                    "product_id"=>$zscp_info->id,
                                    "product_title"=>$zscp_info->title,
                                    'num'=>$zszsl,
                                    'moneylog_type_id'=>'4',
                                ];
                                \App\Moneylog::AddLog($zscp_log);

                                $zscp['userid'] = $Model->userid;
                                $zscp['username'] = $Model->username;
                                // $zscp['level'] = $user_info->level;
                                $zscp['productid'] = $zscp_info->id;
                                $zscp['category_id'] = $zscp_info->category_id;
                                // $zscp['amount'] = $Model->zsje * $zscp_info->qtje; //赠送数量 * 起投金额
                                $zscp['amount'] = $zszje; //赠送总金额
                                $zscp['ip'] = $Model->ip;
                                $zscp['useritem_time'] = Carbon::now();
                                $zscp['useritem_time2'] = $useritem_time2;
                                $zscp['sendday_count'] = 99999;
                                $zscp['status'] = 1;
                                // $zscp['num'] = $Model->zsje;//购买数量
                                $zscp['num'] = $zszsl;//赠送总数量
                                $zscp['unit_price'] = $zscp_info->qtje;//购买时单价
                                $zscp['zsje'] = 0;
                                $zscp['updated_at'] = Carbon::now();
                                $zscp['buy_from_id'] = $Model->id;
                                $zscp['created_date'] = date('Y-m-d');
                                $zscp['order'] = 'JY'.date('YmdHis').$this->get_random_code(7);
                                $zscp['gq_order'] = 'C'.$this->get_random_code(8);

                                $zscp['reason'] = "加入项目(".$products_title.")[".$Model->id."](-),赠送项目(".$zscp_info->title.")";
                                //如果赠送的是货币，添加到会员货币表
                                if($zscp_info->category_id == 11){
                                    // $currencys= new Membercurrencys();
                                    $total_num = 0;
                                    // $zscp_currencys_info = $currencys::where(['userid'=>$Model->userid,'productid'=>$Model->zscp_id])->orderBy('created_at','desc')->first();
                                    $zscp_currencys_info = DB::table('membercurrencys')->where(['userid'=>$Model->userid,'productid'=>$Model->zscp_id])->orderBy('created_at','desc')->first();
                                    if($zscp_currencys_info){
                                        // $update_currencys['num'] = $user_currencys_info->num + $request->number;
                                        // $update_currencys['total_num'] = $user_currencys_info->total_num + $request->number;
                                        $zscp_update_currencys['updated_at'] = Carbon::now();
                                        DB::table('membercurrencys')->where(['userid'=>$Model->userid,'productid'=>$Model->zscp_id])->increment('num',$Model->zsje);
                                        DB::table('membercurrencys')->where(['userid'=>$Model->userid,'productid'=>$Model->zscp_id])->increment('total_num',$Model->zsje);
                                    }else{
                                        $zscp_insert['userid'] = $Model->userid;
                                        $zscp_insert['productid'] = $Model->zscp_id;
                                        $zscp_insert['num'] = $Model->zsje;
                                        $zscp_insert['total_num'] = $total_num + $Model->zsje;
                                        $zscp_insert['created_at'] = $zscp_insert['updated_at'] = Carbon::now();

                                        DB::table('membercurrencys')->insert($zscp_insert);
                                    }
                                }

                                DB::table('productbuy')->insert($zscp);
                                DB::table('statistics')->where('user_id',$Model->userid)->increment('team_capital_flow',$zszje);//流水统计金额
                                $repeat = 1;//防止重复统计
                            }
                            //赠送产品End

                            //如果是货币，添加到会员货币表
                            if($buy_product_info->category_id == 11){
                                $now_time = Carbon::now();
                                $currencys= new Membercurrencys();
                                $total_num = 0;
                                $user_currencys_info = $currencys::where(['userid'=>$Model->userid,'productid'=>$Model->productid])->orderBy('created_at','desc')->first();
                                if($user_currencys_info){
                                    // $update_currencys['num'] = $user_currencys_info->num + $request->number;
                                    // $update_currencys['total_num'] = $user_currencys_info->total_num + $request->number;
                                    $update_currencys['updated_at'] = $now_time;
                                    $currencys::where(['userid'=>$Model->userid,'productid'=>$Model->productid])->increment('num',$Model->num);
                                    $currencys::where(['userid'=>$Model->userid,'productid'=>$Model->productid])->increment('total_num',$Model->num);
                                }else{
                                    $currencys->userid = $Model->userid;
                                    $currencys->productid = $Model->productid;
                                    $currencys->num = $Model->num;
                                    $currencys->total_num = $Model->num;
                                    $currencys->created_at = $now_time;
                                    $currencys->updated_at = $now_time;
                                    $currencys_res = $currencys->save();
                                }
                            }

                            //添加统计表&是否满足额外分红
                            if($buy_product_info->category_id == 12){
                                $statistics_user_id = $Model->userid;
                                $statistics_username = $Model->username;
                                DB::table('statistics')->where('user_id',$statistics_user_id)->update([
                                    'capital_flow' => DB::raw("capital_flow +". $capital_flow),
                                    'equity_capital_flow' => DB::raw("equity_capital_flow + " . $capital_flow)
                                    ]);
                                //是否满足额外分红
                                $equity_capital_flow = DB::table('statistics')->where('user_id',$statistics_user_id)->value('equity_capital_flow');
                                if ($equity_capital_flow != null) {
                                    $extra_bonus_type = DB::table('extra_bonus_type')->where('min_money','<=',$equity_capital_flow)->orderBy('id','desc')->first();
                                    $user_extra_bonus = DB::table('extra_bonus')->where('uid',$statistics_user_id)->first();

                                } else {
                                    $extra_bonus_type = false;
                                }
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
                            // DB::table('statistics')->where('user_id',$UserId)->increment('capital_flow',$capital_flow);
                            //添加后台统计
                            DB::table('statistics_sys')->where('id',1)->increment('buy_amount',$capital_flow);
                            //统计表end

							$is_return = false;
							if ($buy_product_info->fy_type == 2 || $buy_product_info->fy_type == 1){
								$is_return = true;
							}
							//增加总金额
                            $Member->increment('sum_fee',$Model->amount);
                            $Member->increment('dh_sumfee',$Model->amount);
							if ($is_return && $Model->category_id!=42) {
								// $shangji_id = DB::table('membergrade')->where(['uid'=>$Model->userid,'level'=>1])->value('pid');
								$shangji_id = $Member->top_uid;
								$shangji_info = DB::table('member')->select('level','mtype','username','activation','integral','amount')->where('id',$shangji_id)->first();


								$Tichengs= \App\Memberticheng::orderBy("id","asc")->get();//percent提成比例
								$checkBayong = \App\Productbuy::checkBayong($Model->productid);//查返佣比例
							//	$checkBayong = 1;//查返佣比例
								$username = $buyman = $Model->username;
								$now_time = Carbon::now();
								$hidden_username = substr_replace($Model->username, '****', 3,5);

								foreach ($Tichengs as $recent){
									$shangjia = \App\Productbuy::checkTjr($username);//上家姓名 username
                                  //  echo 111;
									$ShangjiaMember= Member::where("username",$shangjia)->first();
									// $ShangjiaMember= Member::where("id",$shangjia)->first();
									// $checkBayong = 1;
									if(!$ShangjiaMember){
                                    //    echo 22222;
										break;
									}
									$has_log = DB::table('moneylog')->select('id')->where(['moneylog_userid'=>$ShangjiaMember->id,'from_uid'=>$Model->userid,'from_uid_buy_id'=>$Model->id])->first();
									if (empty($shangjia) || empty($checkBayong) || $has_log) {
                                    //  echo 33333333;
										break;
									}
									//分成钱数
									$rewardMoney = intval($integrals * $recent->percent * $checkBayong / 100);
                                    //     echo 444444;
									$MOamount=$ShangjiaMember->ktx_amount;

									$ShangjiaMember->increment('ktx_amount',$rewardMoney);

									$notice = "下线[".$hidden_username."]购买(".$products_title.")项目分成(+)";

									$log=[
									  "userid"=>$ShangjiaMember->id,
									  "username"=>$ShangjiaMember->username,
									  "money"=>$rewardMoney,
									  "notice"=>$notice,
									  "type"=>"下线购买分成",
									  "status"=>"+",
									  "yuanamount"=>$MOamount,
									  "houamount"=>$ShangjiaMember->ktx_amount,
									  "ip"=>\Request::getClientIp(),
									  "category_id"=>$Model->category_id,
									  "product_id"=>$Model->productid,
									  "from_uid"=>$Model->userid,
									  "from_uid_buy_id"=>$Model->id,
									  'moneylog_type_id'=>'5',
									];
									\App\Moneylog::AddLog($log);

									$data=[
									  "userid"=>$ShangjiaMember->id,
									  "username"=>$ShangjiaMember->username,
									  "xxuserid"=>$Model->userid,
									  "xxusername"=>$Model->username,
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
							}

                            //购买累计进入总金额
                            $Nowmember = Member::find($Member->id);
                            $Nolevel = DB::table("memberlevel")->find($Member->level);
                            $gNolevel = DB::table("membergrouplevel")->find($Member->glevel); //会员团队级别
                            $levellist = DB::table("memberlevel")->orderBy('id','ASC')->get()->toArray();
                            $glevellist = DB::table("membergrouplevel")->orderBy('id','ASC')->get()->toArray(); //团队级别
                            $lid = 0;
                            if(!empty($Nolevel)){
                                $lid = $Nolevel->id;
                            }
                            //会员级别调整
                            foreach ($levellist as $key=>$value){
                                if(($value->tj_num <= $Nowmember->sum_tg && $value->level_fee <= $Nowmember->sum_fee) && $value->id > $lid){
                                    $data1['level'] = $value->id;
                                    DB::table("member")->where('id',$Member->id)->update($data1);
                                }
                            }

                            //团队购买累计
                            $topid = $Nowmember->top_uid;
                            if($Nowmember->is_gm==0){
                                 $topmemeber1 = Member::find($topid);
                                $Nowmember->increment('is_gm',1);
                                if(!empty($topmemeber1)){
                                    Member::where('id',$Member->top_uid)->increment('sum_tg',1);

                                    ///////////////////////////////////////////////////////////
                                    $lid1 = 0;
                                    $Nowmember1 =Member::find($topmemeber1->id);
                                    $Nolevel1 = DB::table("memberlevel")->find($Nowmember1->level);
                                    if(!empty($Nolevel1)){
                                        $lid1 = $Nolevel1->id;
                                    }
                                    foreach ($levellist as $key=>$value){
                                        if(($value->tj_num <= $Nowmember1->sum_tg && $value->level_fee <= $Nowmember1->sum_fee) && $value->id > $lid1){
                                            $datatop1['level'] = $value->id;
                                            DB::table("member")->where('id',$Nowmember1->id)->update($datatop1);

                                        }
                                    }
                                    ///////////////////////////////////////////////////////////
                                }
                            }
                            if($topid !=0){
                                $topmemeber1 = Member::find($topid);
                                if($Nowmember->is_cj==0){
                                    $cjgtfee = DB::table('setings')->where(['keyname'=>'cj_gtfee'])->value('value');//注册赠送金额
                                    if($Nowmember->sum_fee >= $cjgtfee){
                                        $topmemeber1->increment("cj_num",1);
                                        $Nowmember->increment("is_cj",1);
                                    }

                                }
                                for($i=0;$i<100;$i++){
                                    $topmemeber = Member::find($topid);
                                    if(!empty($topmemeber)){

                                        $topmemeber->increment('allxf_fee',$integrals);  //总累计
                                        $topmemeber->increment('month_allxf',$integrals);   //
                                        if($topid==$Nowmember->top_uid){
                                            $topmemeber->increment('zt_sum_fee',$integrals);
                                        }

                                        //////////////////////////////////////////////////////////////////////////////
                                        /// 校验团队等级
                                        $topmemeber1 = $topmemeber;
                                        $gNolevel = DB::table("membergrouplevel")->find($topmemeber1->glevel); //会员团队级别
                                        $lid = 0;
                                        if(!empty($gNolevel)){
                                            $lid = $gNolevel->id;
                                        }
                                        foreach ($glevellist as $k=>$v){
                                            //该等级级下会员数


                                         //   if(($v->tj_num <= $topmemeber1->sum_tg && $v->level_fee <= $topmemeber1->zt_sum_fee) && $v->id > $lid && $v->zt_num<=$countgl){
                                            if(($v->tj_num <= $topmemeber1->sum_tg && $v->level_fee <= $topmemeber1->zt_sum_fee) && $v->id > $lid ){
                                                $data2['glevel'] = $v->id;
                                                DB::table("member")->where('id',$topmemeber1->id)->update($data2);


                                            }
                                        }
                                        if($topmemeber->top_uid==0){
                                            break;
                                        }else{
                                            $topid = $topmemeber->top_uid;
                                        }
                                        /////////////////////////////////////////////////////////////////////////////
                                    }else{
                                        break;
                                    }
                                }

                            }

                            //升级
							$user_id = $Model->userid;
							$score = $capital_flow;
							$type = 1;
							$source_type = 5;

							$act = APP::make(\App\Http\Controllers\Api\ActController::class);
							App::call([$act, 'change_score_by_user_id'], [$user_id, $score, $type, $source_type]);


                            //修改任务


                            $Model->status = $request->status;
                            $Model->save();
                            //上级任务级别判断
                            //能量金购买管理
                            $nlgm = DB::table('setings')->where(['keyname'=>'nl_gm'])->value('value');//注册赠送金额
                            if((int)$nlgm > 0){
                                $gmnlfee = $integrals*$nlgm;
                                $yuannl = $Member->nl_fee;
                                $bigtree = Bigtree::find(1);
                                $bigtree->increment("nl",$gmnlfee);
                                $Member->increment("nl_fee",(int)$gmnlfee);
                                $notice = "购买产品获取希望资金";
                                $log=[
                                    "userid"=>$Member->id,
                                    "username"=>$Member->username,
                                    "money"=>$gmnlfee,
                                    "notice"=>$notice,
                                    "type"=>"购买产品获取希望资金",
                                    "status"=>"+",
                                    "yuanamount"=>$yuannl,
                                    "houamount"=>$Member->nl_fee,
                                    "ip"=>\Request::getClientIp(),
                                    "category_id"=>$Model->category_id,
                                    "product_id"=>$Model->productid,
                                    "from_uid"=>$Model->userid,
                                    "from_uid_buy_id"=>$Model->id,
                                    'moneylog_type_id'=>'32',
                                ];
                                \App\Moneylog::AddLog($log);
                            }


                            $data = ["status"=>0,"msg"=>"确认通过成功"];
                        }else if($request->status=='3'){
                            // $integral = DB::table('productbuy')->where(['id'=>$Model->id])->value('integral');
                            // if($integral > 0){
                            // $Member->increment('integral',$integral);

                            // }
                            $data = ["status"=>0,"msg"=>"确认未通过成功"];
                            $Model->status = $request->status;
                            $Model->reason = $request->reason;
                            $Model->save();
                        }

                    DB::commit();
                    try{
                    }catch(\Exception $exception){
                        Log::channel('buy')->alert($exception);
                        DB::rollBack();
                        return ['status'=>0,'msg'=>'提交失败，请稍后重试'];
                    }
                    if($request->ajax()){
                       return response()->json($data);
                    }

                }
            }else{
                if($Model->ft_amount!=0){
                    if($request->status=='8'){
                        $Model->amount = $Model->amount + $Model->ft_amount;
                        $Model->ft_amount = 0;
                        $data = ["status"=>0,"msg"=>"确认复投通过成功"];
                    }else if($request->status=='9'){
                        $Model->ft_reason = $request->ft_reason;
                        $data = ["status"=>0,"msg"=>"确认未通过成功"];
                    }
                    $Model->save();

                    if($request->ajax()){
                       return response()->json($data);
                    }

                }
            }




        }

    }


    public function sendsms(Request $request)
    {
        if($request->isMethod("post")){



            $Model = $this->Model::find($request->get('id'));

            if($Model->sendsms==0){
                $Model->sendsms=1;
                $Model->save();
            }

            \App\Sendmobile::SendUid($Model->userid,'txcg');//短信通知




            if($request->ajax()){
                return response()->json([
                    "msg"=>"操作成功","status"=>0
                ]);
            }


        }

    }





    public function delete(Request $request){

          if($request->ajax()) {
              if(is_array($request->input("ids"))){
                if(count($request->input("ids"))>0){

                    // $admins = DB::table($this->table)
                    //     ->whereIn('id',  $request->input("ids"))
                    //     ->count();
                    // if($admins>0){
                    //     return ["status" => 1, "msg" => "系统用户组不允许删除"];
                    // }

                    $delete = DB::table($this->table)->whereIn('id', $request->input("ids"))->delete();
                    if ($delete) {
                        return ["status" => 0, "msg" => "批量删除成功"];
                    } else {
                        return ["status" => 1, "msg" => "批量删除失败"];
                    }
                }

              }
            if($request->input("id")){


                $Model = $this->Model::find($request->get('id'));
                $member = DB::table($this->table)
                    ->where(['id' => $request->input("id")])
                    ->first();
                    // dump($member);
                    // exit();


                if($member){

                      $delete = DB::table($this->table)->where('id', '=', $request->input("id"))->delete();
                        if ($delete) {





                            return ["status" => 0, "msg" => "删除成功"];
                        } else {
                            return ["status" => 1, "msg" => "删除失败"];
                        }


                }else{
                    return ["status"=>1,"msg"=>"您没有权限删除操作"];
                }


            }


        }else{
            return ["status"=>1,"msg"=>"非法操作"];
        }

    }

    function get_random_code($num)
    {
        // $codeSeeds = "ABCDEFGHIJKLMNPQRSTUVWXYZ";
        // $codeSeeds .= "abcdefghijklmnpqrstuvwxyz";
        // $codeSeeds .= "0123456789_";
        $codeSeeds = "1234567890";
        $len = strlen($codeSeeds);
        $ban_num = ($num/2)-3;
        $code = "";
        for ($i = 0; $i < $num; $i++) {
            $rand = rand(0, $len - 1);
            if($i == $ban_num){
                $code .= 'O';
            }else{
                $code .= $codeSeeds[$rand];
            }
        }
        return $code;
    }

}
