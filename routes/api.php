<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::middleware('cors')->group(function () {
	Route::any('/online_pay/zfb', 'Api\PaymentzfbController@thirdToMoney');//发起支付
    Route::any('/online_pay_not/zfb', 'Api\PaymentzfbController@notify_res');//异步接口

	Route::any('/online_pay/ymd', 'Api\PaymentymdController@thirdToMoney');//发起支付
    Route::any('/online_pay_not/ymd', 'Api\PaymentymdController@notify_res');//异步接口
    Route::any('/online_df_not/ymd', 'Api\PaymentymdController@notify_df_res');//异步接口

    //第三方充值
    // Route::any('/recharge_online_pay', 'Api\RechargeController@thirdToMoney');//发起支付
    // Route::any('/recharge_online_pay_not', 'Api\RechargeController@notify_res');//异步接口
    //购买 dingbo
    // Route::any('/online_pay/db', 'Api\PaymentdbController@thirdToMoney');//发起支付
    // Route::any('/online_pay_not/db', 'Api\PaymentdbController@notify_res');//异步接口
    //充值 dingbo
    // Route::any('/recharge_online_pay/db', 'Api\RechargedbController@thirdToMoney');//发起支付
    // Route::any('/recharge_online_pay_not/db', 'Api\RechargedbController@notify_res');//异步接口

	/****注册登录模块****/
	Route::any('/login', 'Api\PublicController@login');//登录页面
	Route::any('/register', 'Api\PublicController@register');//注册页面
    Route::any('/forget','Api\PublicController@forget')->middleware('throttle:20,1');//忘记界面
	Route::any('/captcha_img', 'Api\PublicController@captcha');//图文验证码
	Route::any('/new_sendSms', 'Api\PublicController@new_sendMsm');//发送短信
	Route::any('/user/forget', 'Api\PublicController@forget');//忘记密码

    /**/
    Route::any('/getapilink','Api\PublicController@getApiList');//地址列表
    Route::any('/getappversion','Api\PublicController@getAppVersion');//app版本号
    Route::any('/payment', 'Api\JfshopController@change_pay');//支付方式
    Route::any('/uploadImg', 'Api\UserController@uploadImg');//上传凭证

    Route::any('/act/sign', 'Api\ActController@sign');
    Route::any('/act/score', 'Api\ActController@scoreLog');
    Route::any('/act/rewards', 'Api\ActController@rewardList');
    Route::any('/act/lottory', 'Api\ActController@lottory');
    Route::any('/act/luckey', 'Api\ActController@lottoryLog');
    Route::any('/act/luckeymine', 'Api\ActController@MyLottoryLog');
    Route::any('/act/userscore', 'Api\ActController@getuserscore');
    Route::any('/act/address', 'Api\ActController@updateUserAddress');


	/****首页模块****/
	Route::any('index','Api\IndexController@index');//首页
	Route::any('getselltips','Api\IndexController@getselltips');//首页
	Route::any('/projects/{type}','Api\IndexController@projects');//首页基金列表
	Route::any('/treeprojects/{type}','Api\IndexController@treeprojects');//小树盘列表
	Route::any('/project/{id}','Api\IndexController@project');//基金详情
	Route::any('/treeproject/{id}','Api\IndexController@treeproject');//小树盘详情

// 	Route::middleware(['Checklimt'])->group(function () {
        Route::any('/user/money/tender/{type}', 'Api\MoneyController@tender');//购买的基金
        Route::any('/user/money/treetender/{type}', 'Api\MoneyController@treetender');//小树盘购买列表
//     });

    Route::any('/project/buy/{productid}', 'Api\UserController@nowToMoney')->middleware(['checklimit']);//基金购买
    // Route::any('/project/buy2/{productid}','Api\UserController@thirdToMoney');//第三方购买


    // Route::any('/user/money/myProduct/{type}', 'Api\MoneyController@myProduct');//购买的基金
    // Route::any('/user/money/myProduct_detail/{id}', 'Api\MoneyController@myProduct_detail');//购买的基金详情

    Route::any('/user/agreement', 'Api\MoneyController@agreement');//协议
    Route::any('/user/contract', 'Api\MoneyController@contract');//合同
    Route::any('/contact', 'Api\IndexController@contact');//客服列表

	Route::any('/video/index', 'Api\VideosController@index');//视频列表
	Route::any('/video/detail', 'Api\VideosController@detail');//视频详情
	Route::any('/video/like', 'Api\VideosController@like');//视频点赞
	Route::any('/articles/index', 'Api\ArticlesController@index');//资讯列表
	Route::any('/articles/detail', 'Api\ArticlesController@detail');//资讯详情

	Route::any('/user/index','Api\UserController@index');//用户中心
	Route::any('/get_invite_link','Api\IndexController@get_link');//获取邀请链接域名
	Route::any('/user/my', 'Api\UserController@my');//用户资料
	Route::any('/user/myedit','Api\UserController@myedit');//资料修改
	Route::any('/user/banks','Api\UserController@banks');//银行卡包
	Route::any('/user/bankAdd','Api\UserController@bankAdd');//添加银行卡
	Route::any('/user/bankDel','Api\UserController@bankDel');//删除银行卡
	Route::any('/user/addresses','Api\UserController@addresses');//我的收货地址列表
	Route::any('/user/addressEdit','Api\UserController@addressEdit');//收货地址修改
	Route::any('/user/addressAdd','Api\UserController@addressAdd');//收货地址添加
	Route::any('/user/addressDel','Api\UserController@addressDel');
	Route::any('/user/statusEdit','Api\UserController@statusEdit');//默认地址
	Route::any('/user/paypwd','Api\UserController@paypwd');//交易密码修改
	Route::any('/user/mobile', 'Api\UserController@mobile');//绑定手机

	/****会员模块 —— 个人消息****/
	Route::any('/user/msg', 'Api\UserController@msg');//用户消息数
	Route::any('/user/msglist', 'Api\UserController@msglist');//用户消息列表
	Route::any('/user/MsgRead', 'Api\UserController@MsgRead');//消息标记状态
	Route::any('/user/MsgDel', 'Api\UserController@MsgDel');//用户消息删除
    Route::any('/user/myDetail', 'Api\UserController@myDetail');//资金统计
    Route::any('/user/receive','Api\UserController@one_card_receive');//一卡通领取
	/*
	/user/msg   用户消息未读数
	/user/msglist   用户消息列表
	/user/MsgRead   消息标记已读状态   id
	/user/MsgDel    用户消息删除   id
	*/
	/****会员模块 —— 个人记录****/
	Route::any('/user/myteam', 'Api\UserController@myteam');//我的团队
	Route::any('/user/authentication', 'Api\UserController@authentication');//提交个人认证
    Route::any('/user/is_check', 'Api\UserController@is_check');

	Route::any('/user/withdraw', 'Api\MoneyController@withdraw')->middleware(['checklimit']);//提现
    Route::any('/user/withdraws', 'Api\MoneyController@withdraws');//提现记录
	Route::any('/user/recharge', 'Api\MoneyController@recharge');//充值
    Route::any('/user/recharges', 'Api\MoneyController@recharges');//充值记录


	Route::any('/search', 'Api\IndexController@search');//搜索

	/*im推广说明*/
	Route::any('/extension', 'Api\PublicController@extension');//推广说明
    Route::any('/update_download', 'Api\PublicController@update_download');//更新app接口
    Route::any('/user/myProductDetail', 'Api\UserController@myProductDetail');//项目详情
    Route::any('/user/withdra_reminder', 'Api\MoneyController@withdra_reminder');//提现温馨提示
    Route::any('/user/bankEdit', 'Api\UserController@bankEdit');//修改我的银行卡信息

    // Route::any('/user/tamRanking','Api\UserController@tamRanking');//全服排名
    Route::any('/user/queryLevelCode','Api\MoneyController@queryLevelCode');
    Route::any('/checkApiLink','Api\PublicController@checkApiLink');//前端检测域名
    /***新接口***/
    Route::any('/user/teamReport', 'Api\UserController@teamReport');//团队业绩
    Route::any('/user/set_myinfo', 'Api\UserController@set_myinfo');//新更新个人资料
    Route::any('/index/outer_chain', 'Api\IndexController@outer_chain');//外链地址
    Route::any('/user/getImLink', ['as'=>'user.SendCode','uses'=>'Api\UserController@getImLink']);//客服入口
    Route::any('/equity_reminder', ['as'=>'user.SendCode','uses'=>'Api\MoneyController@equity_reminder']);//客服入口

    // Route::any('/index/grpAct/getRedPacket',['as'=>'wap.index','uses'=>'Api\IndexController@get_red_packet']);//领取红包
    // Route::any('/index/grpAct/myRedPacket',['as'=>'wap.index','uses'=>'Api\IndexController@my_red_packet_list']);//我的红包领取记录
    // Route::any('/index/grpAct/getRedPacketInfo',['as'=>'wap.index','uses'=>'Api\IndexController@get_red_packet_info']);//红包页面个人信息
    // Route::any('/index/grpAct/getRedpageInfo',['as'=>'wap.index','uses'=>'Api\PublicController@getRedpageInfo']);//红包页面信息
    // Route::any('/user/money/tender/{id}', ['as'=>'user.agreement','uses'=>'Api\MoneyController@agreement']);//购买的基金详情
    // Route::any('/user/qiandao', ['as'=>'user.qiandao','uses'=>'Api\UserController@qiandao']);//用户签到功能
     Route::any('/user/xj_qiandao', ['as'=>'user.xj_qiandao','uses'=>'Api\UserController@xj_qiandao']);//用户签到功能
     Route::any('/user/newqiandao', ['as'=>'user.newqiandao','uses'=>'Api\UserController@newqiandao']);

    // Route::any('/user/update_tongji', ['as'=>'user.newqiandao','uses'=>'Api\PublicController@update_tongji']);//
    // Route::any('/user/top_tongji', ['as'=>'user.newqiandao','uses'=>'Api\PublicController@top_tongji']);//
    Route::any('/user/sign_log', ['as'=>'user.sign_log','uses'=>'Api\PublicController@sign_log']);//
    // Route::any('/team_rewards', ['as'=>'user.sign_log','uses'=>'Api\IndexController@team_rewards']);//邀请好友页面团队激励说明
    // Route::any('/user/dividend_type', ['as'=>'user.sign_log','uses'=>'Api\UserController@dividend_type']);//股权选择列表
    // Route::any('/user/check_dividend_type', ['as'=>'user.sign_log','uses'=>'Api\UserController@check_dividend_type']);//用户选择股权类型
    Route::any('/user/equity_book', ['as'=>'user.sign_log','uses'=>'Api\UserController@equity_book']);//证书
    Route::any('/user/is_check_id', ['as'=>'user.is_check_id','uses'=>'Api\UserController@is_check_id']);//是否实名认证
    // Route::any('/user/getLevelInfo','Api\MoneyController@getLevelInfo');//等级信息
    // Route::any('/user/getGiftEquity', 'Api\UserController@getGiftEquity');//馈赠股权信息
    // Route::any('/user/benefit_description', 'Api\MoneyController@benefit_description');//会员福利说明
     Route::any('/user/buyVipRecord', 'Api\UserController@buyVipRecord');//我够买的等级记录
    // Route::any('/testuploadpic','Api\TestController@testuploadpic');//接收图片
    // Route::any('/user/getSystemAccount', 'Api\UserController@getSystemAccount');//系统收款账户
    // Route::any('/user/transfer_accounts', 'Api\UserController@transfer_accounts');//余额互转
    // Route::any('/user/transfer_details', 'Api\UserController@transfer_details');//转账明细
    // Route::any('/user/my_collection_code', 'Api\UserController@my_collection_code');//我的收款码
    // Route::any('/user/sendSms', ['as'=>'user.withdraws','uses'=>'Api\PublicController@sendMsm']);//旧发送验证码
    // Route::any('/user/forget', ['as'=>'user.withdraws','uses'=>'Api\PublicController@forget']);//忘记密码
    // Route::any('/user/cloud_merchants', ['as'=>'user.withdraws','uses'=>'Api\UserController@cloud_merchants']);//云商贸用户
    // Route::any('/user/complaint', ['as'=>'user.withdraws','uses'=>'Api\UserController@complaint']);//投诉
    // Route::any('/user/yltjbz', ['as'=>'user.withdraws','uses'=>'Api\UserController@yltjbz']);//如何成为云商户
    // Route::any('/user/get_ylglyid', ['as'=>'user.withdraws','uses'=>'Api\UserController@get_ylglyid']);//如何成为云商户
    // Route::any('/user/ysjj', ['as'=>'user.withdraws','uses'=>'Api\UserController@ysjj']);//云商户简介
    // Route::any('/user/ybhz', ['as'=>'user.withdraws','uses'=>'Api\UserController@ybhz']);//货币互转页面
    // Route::any('/user/collection_list', ['as'=>'user.withdraws','uses'=>'Api\UserController@collection_list']);//我的货币列表
    // Route::any('/money/myProduct/{type}', ['as'=>'user.withdraws','uses'=>'Api\MoneyController@myProduct']);//我购买的项目列表
    // Route::any('/money/myProduct_detail/{id}', ['as'=>'user.withdraws','uses'=>'Api\MoneyController@myProduct_detail']);//我购买的项目列表详情
    // Route::any('/user/transfer_out', ['as'=>'user.withdraws','uses'=>'Api\UserController@transfer_out']);//货币转出
    // Route::any('/user/transfer_out', ['as'=>'user.withdraws','uses'=>'Api\UserController@transfer_out']);//我的项目列表
    Route::any('/currline', ['as'=>'user.withdraws','uses'=>'Api\IndexController@currline']);//货币K线
    Route::any('/update_currline', ['as'=>'user.withdraws','uses'=>'Api\PublicController@update_currline']);//手动更新货币K线   startkey  pid

    Route::any('/user/qd_index', 'Api\UserController@qd_index');//签到页面
    Route::any('/user/huicenter', 'Api\UserController@huicenter');//签到页面
    // Route::any('/user/lxqd', 'Api\UserController@lxqd');//连续签到
    // Route::any('/user/receive', 'Api\UserController@receive');//领取登记
    // Route::any('/user/receive_list', 'Api\UserController@receive_list');//领取登记提交记录
    // Route::any('/user/my_count_down',['as'=>'wap.index','uses'=>'Api\UserController@my_count_down']);//我的倒计时查询
    // Route::any('/pb_type', ['as'=>'user.sign_log','uses'=>'Api\PublicController@pb_type']);//
    // Route::any('/pb_mtype', ['as'=>'user.sign_log','uses'=>'Api\PublicController@pb_mtype']);//运维订单分红方式
    // Route::any('/yunwei_yiaktong', ['as'=>'user.sign_log','uses'=>'Api\PublicController@yunwei_yiaktong']);//运维一卡通多次返利
});

// Route::any('/user/mylink', 'Api\Userontroller@mylink');//我的推广
// Route::any('/user/qiandao', ['as'=>'user.qiandao','uses'=>'UserController@qiandao']);//用户签到功能
// Route::any('/user/password.html', ['as'=>'user.password','uses'=>'UserController@password']);//密码修改
// Route::any('/user/security.html', ['as'=>'user.security','uses'=>'UserController@security']);//安全问题
// Route::any('/user/phone.html', ['as'=>'user.phone','uses'=>'UserController@phone']);//绑定手机
// Route::any('/user/certification.html', ['as'=>'user.certification','uses'=>'UserController@certification']);//安全认证中心
// Route::any('/user/bank.html', ['as'=>'user.bank','uses'=>'UserController@bank']);//我的银行卡
// Route::any('/user/paypwd.html', ['as'=>'user.paypwd','uses'=>'UserController@paypwd']);//交易密码
// Route::any('/user/retrieve.html', ['as'=>'user.paypwd.retrieve','uses'=>'UserController@retrieve']);//找回交易密码
// Route::any('/user/SendCode', ['as'=>'user.SendCode','uses'=>'UserController@SendCode']);//发送短信验证码
// Route::any('/user/SendRZCode', ['as'=>'user.SendRZCode','uses'=>'UserController@SendRZCode']);//发送短信验证码
// Route::any('/user/loginloglist.html', ['as'=>'user.loginloglist','uses'=>'UserController@loginloglist']);//用户登录日志
/**** 会员收益 财务功能 ****/
// Route::any('/user/money/shouyi/{id}.html', ['as'=>'user.shouyi','uses'=>'MoneyController@shouyi']);//收益明细
// Route::any('/user/products.html', ['as'=>'user.products','uses'=>'UserController@products']);//投资项目
// Route::any('/user/QrCodeBg.html', ['as'=>'user.QrCodeBg','uses'=>'UserController@QrCodeBg']);//我的推广二维码
// Route::any('/user/moneylog.html', ['as'=>'user.moneylog','uses'=>'MoneyController@moneylog']);//资金统计
// Route::any('/user/recharge.html', ['as'=>'user.recharge','uses'=>'MoneyController@recharge']);//充值
// Route::any('/user/withdraw.html', ['as'=>'user.withdraw','uses'=>'MoneyController@withdraw']);//提现
// Route::any('/user/recharges.html', ['as'=>'user.recharges','uses'=>'MoneyController@recharges']);//充值记录
// Route::any('/user/withdraws.html', ['as'=>'user.withdraws','uses'=>'MoneyController@withdraws']);//提现记录
// Route::any('/user/offline.html', ['as'=>'user.offline','uses'=>'MoneyController@offline']);//下线分红
// Route::any('/user/budget.html', ['as'=>'user.offline.budget','uses'=>'MoneyController@budget']);//下线收支
// Route::any('/user/payconfig.html', ['as'=>'user.payconfig','uses'=>'MoneyController@payconfig']);//支付方式
// Route::any('/user/nowToMoney', ['as'=>'user.nowToMoney','uses'=>'UserController@nowToMoney']);//项目购买
// Route::any('/user/Memberamount.html', ['as'=>'user.memberamount','uses'=>'UserController@Memberamount']);//帐户余额
/****商品模块****/
// Route::any('/products', 'Api\JfshopController@index');//商品首页
// Route::any('/product/{id}', ['as'=>'product','uses'=>'Api\JfshopController@product']);//商品详情
// Route::any('/order/submit', ['as'=>'wap.shop','uses'=>'Api\JfshopController@submit_order']);//商品订单提交页
// Route::any('/order/detail', ['as'=>'wap.shop','uses'=>'Api\JfshopController@order_details']);//商品订单详情页
// Route::any('/product/pay/{ordernumber}', ['as'=>'wap.jfproduct','uses'=>'Api\JfshopController@exchange']);//商品订单支付
// Route::any('/products/orders', ['as'=>'wap.exchangelog','uses'=>'Api\JfshopController@exchangelog']);//我的商品订单列表
// Route::any('/products/orders/{ordernumber}', ['as'=>'wap.exchangelog','uses'=>'Api\JfshopController@exchangeDetails']);//我的商品订单详情
// Route::any('/orders/cancel/{ordernumber}', ['as'=>'wap.exchangelog','uses'=>'Api\JfshopController@orderCancel']);//订单取消

//首页商品管理
Route::any('/stproducts/{type}','Api\IndexController@stproduct');//实体商品管理
Route::any('/stproductinfo/{id}','Api\IndexController@stproductinfo');//首页商品详情
Route::any('/stproductbuy/list','Api\IndexController@stproductbuy');//首页商品详情 stproductbuyinfo
Route::any('/stproductbuy/stproductbuyinfo','Api\IndexController@stproductbuyinfo');//订单详情
Route::any('/app/getversion','Api\IndexController@getappversion');//获取app版本
Route::any('/checklevel', 'Api\PublicController@checklevel');//登录页面
Route::any('/online_pay/proymd', 'Api\PaymentymdController@prothirdToMoney');//发起支付
Route::any('/stproducts/buy/{productid}', 'Api\UserController@stnowToMoney')->middleware(['checklimit']);//基金购买
Route::any('/act/memeber/address', 'Api\ActController@updateAddres');  //地址修改
Route::any('/act/memeber/addressinfo', 'Api\ActController@Addresinfo');  //地址详情

Route::any('/user/yeindex', ['as'=>'user.yeindex','uses'=>'Api\UserController@yeindex']);  //余额宝详情

Route::any('/user/huicenter', 'Api\UserController@huicenter');  //余额宝详情

Route::any('/index/getarealist', 'Api\IndexController@getarealist');//获取地区列表
Route::any('/index/tgfulilist', 'Api\IndexController@tgfulilist');//推广福利列表
Route::any('/index/gmfulilist', 'Api\IndexController@gmfulilist');//购买福利列表
Route::any('/index/hzpp', 'Api\IndexController@hzpp');//购买福利列表
Route::any('/user/lqrwjijin', 'Api\UserController@lqrwjijin');//修改我的银行卡信息
Route::any('/user/lqmounth', 'Api\UserController@lqmounth');  //领取月工资
Route::any('/user/monthlog', 'Api\UserController@monthlog');  //领取月工资
Route::any('/user/getzctree', 'Api\UserController@getzctree');  //登陆任务领取小树苗
Route::any('/user/treejs', 'Api\UserController@treejs');  //浇水
Route::any('/user/treeaword', 'Api\UserController@treeaword');  //领取将近
Route::any('/user/bigtreejs', 'Api\UserController@bigtreejs');  //大树浇水
Route::any('/user/bigtreeinfo', 'Api\UserController@bigtreeinfo');  //大树基本信息
Route::any('/user/treetask', 'Api\UserController@treetask');  //树木任务
Route::any('/user/getsumfeetree', 'Api\UserController@getsumfeetree');  //购买总数领取树木
Route::any('/user/getlxtree', 'Api\UserController@getlxtree');  //连续签到
Route::any('/user/yuebao', 'Api\UserController@yuebao');  //连续签到
