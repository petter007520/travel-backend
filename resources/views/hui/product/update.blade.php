@extends('hui.layouts.appupdate')

@section('title', $title)
@section('here')

@endsection
@section('addcss')
    @parent
@endsection
@section('addjs')
    @parent
@endsection

@section("mainbody")
    @parent
@endsection

@section('formbody')




    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">栏目</label>
        <div class="layui-input-inline">
            <select name="category_id"  lay-filter="selctOnchange">
                {!! $tree_option !!}

            </select>

        </div>
    </div>




    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">项目排序</label>

        <div class="layui-input-inline">
            <input type="text" name="sort" placeholder="项目排序" autocomplete="off" class="layui-input" lay-verify="required" value="{{$edit->sort}}">
        </div>
    </div>


    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">项目名称</label>

        <div class="layui-col-md3">
            <input type="text" name="title" lay-verify="required" required placeholder="项目名称" autocomplete="off" class="layui-input" value="{{$edit->title}}">
        </div>
    </div>
<div class="layui-form-item">
    <label class="layui-form-label col-sm-1">是否返本</label>

    <div class="layui-col-md3">
        <select name="is_th">

            <option value="0" @if($edit->is_th==0) selected="selected" @endif>否</option>
            <option value="1" @if($edit->is_th==1) selected="selected" @endif>是</option>
        </select>
    </div>
</div>
<div class="yebstate">
    <div class="layui-form-item">
		<label class="layui-form-label col-sm-1">投资天数</label>
		<div class="layui-input-inline">
			<input type="text" name="th_day" placeholder="投资天数:天"   value="{{$edit->th_day}}" autocomplete="off" class="layui-input">
		</div>
	</div>
	    <div class="layui-form-item">
    		<label class="layui-form-label col-sm-1">年化利率</label>
    		<div class="layui-input-inline">
    			<input type="text" name="nihua" placeholder="年化利率: x%"   value="{{$edit->nihua}}"  autocomplete="off" class="layui-input">
    		</div>
    	</div>

</div>


<div class="state">
    <!--<div class="layui-form-item">-->
    <!--    <label class="layui-form-label col-sm-1">保理机构<font color="#FF0000">*</font></label>-->

    <!--    <div class="layui-col-md3">-->
    <!--        <input type="text" name="bljg" placeholder="保理机构" lay-verify="required" autocomplete="off" class="layui-input" value="{{$edit->bljg}}">-->
    <!--    </div>-->
    <!--</div>-->


    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">项目规模</label>

        <div class="layui-col-md3">
            <input type="text" name="xmgm" placeholder="项目规模 :x万" lay-verify="required" autocomplete="off" class="layui-input" value="{{$edit->xmgm}}">
        </div>
    </div>

    <!--<div class="layui-form-item">-->
    <!--    <label class="layui-form-label col-sm-1">投资进度</label>-->

    <!--    <div class="layui-input-inline">-->
    <!--        <input type="text" name="xmjd" placeholder="投资进度" lay-verify="required" autocomplete="off" class="layui-input" value="{{$edit->xmjd}}">-->
    <!--    </div>-->
    <!--</div>-->

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">发行价</label>

        <div class="layui-input-inline">
            <input type="text" name="qtje" placeholder="金额（发行价）" autocomplete="off" class="layui-input" value="{{$edit->qtje}}">
        </div>
    </div>
	<div class="layui-form-item">
        <label class="layui-form-label col-sm-1">最新价</label>

        <div class="layui-input-inline">
            <input type="text" name="fxj" placeholder="金额（最新价）" autocomplete="off" class="layui-input" value="{{$edit->fxj}}">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">起投数量</label>

        <div class="layui-input-inline">
            <input type="number" name="qtsl" placeholder="起投数量" autocomplete="off" class="layui-input" value="{{$edit->qtsl}}">
        </div>
    </div>

    <!-- <div class="layui-form-item">-->
    <!--    <label class="layui-form-label col-sm-1">市值(货币的涨幅后价格)</label>-->

    <!--    <div class="layui-input-inline">-->
    <!--        <input type="text" name="market_value" placeholder="金额（市值）" autocomplete="off" class="layui-input" value="{{$edit->market_value}}">-->
    <!--    </div>-->
    <!--</div>-->




    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">产品编码</label>

        <div class="layui-input-inline">
            <input type="text" name="equity_code" placeholder="" autocomplete="off" class="layui-input" value="{{$edit->equity_code}}">
        </div>
    </div>


    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">最高金额</label>

        <div class="layui-input-inline">
            <input type="text" name="zgje" placeholder="最高金额  (0为无限制)" autocomplete="off" class="layui-input" value="{{$edit->zgje}}">
        </div>
    </div>

    <!--<div class="layui-form-item">-->
    <!--    <label class="layui-form-label col-sm-1">投资人数</label>-->

    <!--    <div class="layui-input-inline">-->
    <!--        <input type="text" name="tzrs" placeholder="投资人数:x人" autocomplete="off" class="layui-input" value="{{$edit->tzrs}}">-->
    <!--    </div>-->
    <!--</div>-->

<!--     <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">还款方式</label>

        <div class="layui-input-inline">
            <select name="hkfs">
                <option value="0" @if($edit->hkfs==0) selected="selected" @endif>按天付收益，到期还本</option>
                <option value="3" @if($edit->hkfs==3) selected="selected" @endif>按天付收益，等额本息返还</option>
                <option value="2" @if($edit->hkfs==2) selected="selected" @endif>按小时付收益，到期还本</option>
                <option value="1" @if($edit->hkfs==1) selected="selected" @endif>按周期付收益，到期还本</option>
                <option value="4" @if($edit->hkfs==4) selected="selected" @endif>按天收益，到期还本还息</option>
                <option value="5" @if($edit->hkfs==5) selected="selected" @endif>按小时收益，到期还本还息</option>
                <option value="6" @if($edit->hkfs==6) selected="selected" @endif>按周期收益，到期还本还息</option>
            </select>
        </div>
    </div> -->

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">是否重投</label>

        <div class="layui-input-inline">
            <select name="isft">
                <option value="0" @if($edit->isft==0) selected="selected" @endif>不能复投</option>
                <option value="1" @if($edit->isft==1) selected="selected" @endif>可以复投</option>
            </select>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">可投金额</label>

        <div class="layui-input-inline">
            <input type="text" name="ktje" placeholder="可投金额: x元" autocomplete="off" class="layui-input" value="{{$edit->ktje}}">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">赠送投资项目</label>

        <div class="layui-input-inline">
            <input type="number" name="zsje" placeholder="赠送数量" autocomplete="off" class="layui-input" value="{{$edit->zsje}}">


        </div>

        <div class="layui-input-inline">


            <select name="zsje_type">
                <option value="1" @if($edit->zsje_type=='1') selected="selected" @endif>固定数量</option>
                <!--<option value="2" @if($edit->zsje_type=='2') selected="selected" @endif>百分比</option>-->
                <option value="3" @if($edit->zsje_type=='3') selected="selected" @endif>倍数数量(1=1:1赠送,2=1:2赠送)</option>
            </select>
        </div>


        <div class="layui-input-inline">
            <select name="zscp_id">
                <option value="0" selected="selected">请选择产品(无赠送)</option>
                @if($productlist)
                    @foreach($productlist as $v)
                        <option value="{{$v->id}}" @if($edit->zscp_id==$v->id) selected="selected" @endif>{{$v->title}}</option>
                    @endforeach
                @endif

            </select>


        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">日收益率</label>

        <div class="layui-input-inline">
            <input type="text" name="jyrsy" placeholder="日收益率: x%" autocomplete="off" class="layui-input" value="{{$edit->jyrsy}}">
        </div>
    </div>

	<div class="layui-form-item">
        <label class="layui-form-label col-sm-1">日累计收益率</label>

        <div class="layui-input-inline">
            <input type="text" name="jyrsy2" placeholder="日收益率: x%" autocomplete="off" class="layui-input" value="{{$edit->jyrsy2}}">
        </div>
    </div>

    <div class="layui-form-item">

     <!--   <label class="layui-form-label col-sm-1">收益率(救助金)</label>-->
         <label class="layui-form-label col-sm-1">养老救助金</label>
        <div class="layui-input-inline">
            <input type="text" name="hbrsy" placeholder="养老救助金: x%" autocomplete="off" class="layui-input" value="{{$edit->hbrsy}}">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">项目进度%</label>

        <div class="layui-input-inline">
            <input type="text" name="xmjd" placeholder="项目进度(数值)" autocomplete="off" class="layui-input" value="{{$edit->xmjd}}">
        </div>
    </div>
    <!--<div class="layui-form-item">-->
    <!--    <label class="layui-form-label col-sm-1">涨幅比例</label>-->

    <!--    <div class="layui-input-inline">-->
    <!--        <input type="text" name="increase" placeholder="涨幅比例（货币专属）: x% / -x%" autocomplete="off" class="layui-input" value="{{$edit->increase}}">-->
    <!--    </div>-->
    <!--</div>-->

    <!--<div class="layui-form-item">-->
    <!--    <label class="layui-form-label col-sm-1">收益佣金倍数</label>-->

    <!--    <div class="layui-col-md6">-->
    <!--        <input type="text" name="tqsyyj" placeholder="提成收益佣金倍数 (填0本项目无佣金，填1为系统设置下线佣金1倍，填2就是下线佣金在系统设置基础翻倍，依次类推)" autocomplete="off" class="layui-input" value="{{$edit->tqsyyj}}">-->
    <!--    </div>-->
    <!--</div>-->

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">是否返佣</label>

        <div class="layui-input-inline">
            <select name="tqsyyj">
                <option value="1" @if($edit->tqsyyj==1) selected="selected" @endif>反佣金</option>
                <option value="0" @if($edit->tqsyyj==0) selected="selected" @endif>无佣金</option>
            </select>
        </div>
    </div>
	<div class="layui-form-item">
        <label class="layui-form-label col-sm-1">返佣方式</label>

        <div class="layui-input-inline">
            <select name="fy_type">
                <option value="0" @if($edit->fy_type==0) selected="selected" @endif>无返佣</option>
                <option value="1" @if($edit->fy_type==1) selected="selected" @endif>均返佣</option>
                <option value="2" @if($edit->fy_type==2) selected="selected" @endif>充值返，余额不返</option>
                <option value="3" @if($edit->fy_type==3) selected="selected" @endif>余额返，充值不返</option>
            </select>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">项目期限</label>

        <div class="layui-input-inline">
            <input type="text" name="shijian" lay-verify="required" placeholder="项目期限" autocomplete="off" class="layui-input" value="{{$edit->shijian}}">
        </div>

        <div class="layui-input-inline">


            <select name="qxdw">
                <!-- <option value="个交易日" @if($edit->qxdw=='个交易日') selected="selected" @endif>个交易日</option> -->
                <option value="个自然日" @if($edit->qxdw=='个自然日') selected="selected" @endif>个自然日</option>
                <!-- <option value="个小时"   @if($edit->qxdw=='个小时') selected="selected" @endif>个小时</option> -->
            </select>
        </div>
    </div>



    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">投资状态</label>


        <div class="layui-input-inline">
            <select name="tzzt">
                <option value="0" @if($edit->tzzt=='0') selected="selected" @endif>热售中</option>
                <option value="1" @if($edit->tzzt=='1') selected="selected" @endif>已投满</option>
                <option value="2" @if($edit->tzzt=='2') selected="selected" @endif>未发布</option>
                <option value="3" @if($edit->tzzt=='3') selected="selected" @endif>上市中</option>
            </select>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">发布时间</label>


        <div class="layui-input-inline">
            <input type="text" name="created_at" placeholder="发布时间" autocomplete="off" class="layui-input" id="created_at" value="{{$edit->created_at}}">
        </div>
    </div>


 <!--  <div class="layui-form-item">-->
 <!--       <label class="layui-form-label col-sm-1">倒计时</label>-->
 <!--       <div class="layui-input-inline">-->
 <!--           <select name="djs">-->
 <!--               <option value="0" @if($edit->djs==0) selected="selected" @endif>关闭</option>-->
 <!--               <option value="1" @if($edit->djs==1) selected="selected" @endif>开启</option>-->
 <!--           </select>-->
 <!--       </div>-->
 <!--   </div>-->
	<!--<div class="layui-form-item">-->
 <!--       <label class="layui-form-label col-sm-1">倒计时结束</label>-->
 <!--       <div class="layui-input-inline">-->
 <!--           <input type="text" name="djs_at" placeholder="倒计时结束时间" autocomplete="off" class="layui-input" id="djs_at" value="{{$edit->djs_at}}">-->
 <!--       </div>-->
 <!--   </div>-->


    <div class="layui-form-item">

        <label class="layui-form-label col-sm-1">产品图片</label>



        <div class="layui-col-md6">

            <button type="button" class="layui-btn" id="thumb_url" style="float:left;">
                <i class="layui-icon">&#xe67c;</i>上传产品图片
            </button>

            <span class="imgshow" style="float:left;width:100%;margin: 2px;">
                @if($edit->pic!='')
                    <img src="{{$edit->pic}}" width="100" style="float:left;"/>
                    @endif
            </span>

            <input type="text" name="pic" lay-verify="required" value="{{$edit->pic}}" class="layui-input thumb" placeholder="产品图片" style="float:left;width:50%;">


            <script>

                layui.use('upload', function(){


                    var upload = layui.upload;

                    //执行实例
                    var uploadInst = upload.render({
                        elem: '#thumb_url' //绑定元素
                        ,url: '{{route("admin.uploads.uploadimg")}}?_token={{ csrf_token() }}' //上传接口
                        , field:'thumb'
                        ,done: function(src){
                            //上传完毕回调

                            console.log(src);
                            if(src.status==0){
                                layer.msg(src.msg,{time:500},function(){

                                    $(".imgshow").html('<img src="'+src.src+'?t='+new Date()+'" width="100" style="float:left;"/>');

                                    $(".thumb").val(src.src);

                                });
                            }

                        }
                        ,error: function(){
                            //请求异常回调
                        }
                    });

                });



            </script>



        </div>

    </div>





    <script>
    console.log({{$edit->category_id}},"23232")
        if({{$edit->category_id}}==42){
            $(".yebstate").css("display","block")
            $(".state").css("display","none")
        $("input[name='pic']").attr("lay-verify","")
        $("input[name='shijian']").attr("lay-verify","")
        $("input[name='xmgm']").attr("lay-verify","")
        }else{
             $(".yebstate").css("display","none")
             $(".state").css("display","block")
            $("input[name='pic']").attr("lay-verify","required")
            $("input[name='shijian']").attr("lay-verify","required")
            $("input[name='xmgm']").attr("lay-verify","required")
         }
        layui.use('form', function(){
            var form = layui.form;
                form.on('select(selctOnchange)', function (data) {
                  console.log(data.value,"22222222")
                    if(data.value==42){//余额宝
                        $(".yebstate").css("display","block")
                        $(".state").css("display","none")
                        $("input[name='pic']").attr("lay-verify","")
                        $("input[name='shijian']").attr("lay-verify","")
                        $("input[name='xmgm']").attr("lay-verify","")
                    }else{
                        $(".yebstate").css("display","none")
                        $(".state").css("display","block")
                        $("input[name='pic']").attr("lay-verify","required")
                        $("input[name='shijian']").attr("lay-verify","required")
                        $("input[name='xmgm']").attr("lay-verify","required")
                    }
                })
            });

        layui.use(['laydate'], function() {

            var laydate = layui.laydate;
            laydate.render({
                elem: '#created_at' //指定元素
                ,type: 'datetime'
            });

             laydate.render({
                    elem: '#djs_at' //指定元素
                    ,type: 'datetime'
                    ,value: '{{$edit->djs_at}}'
                });
        });
    </script>


<div class="layui-form-item layui-form-text">
        <label class="layui-form-label col-sm-1">描述(项目说明)</label>
        <div class="layui-input-block">
            <textarea placeholder="请填写描述" class="layui-textarea" name="describe">{{$edit->describe}}</textarea>
        </div>
    </div>



    @if(Cache::get('editor')=='markdown')

        <div class="layui-form-item">
            <label class="layui-form-label col-sm-1">产品说明</label>
            <div class="layui-input-block">

                <div id="container" class="editor">
                    <textarea name="content" style="display:none;">{!! $edit->content !!}</textarea>
                </div>

                @include('markdown::encode',['editors'=>['container']])
            </div>
        </div>




    @elseif(Cache::get('editor')=='u-editor')
        <div class="layui-form-item">
            <label class="layui-form-label col-sm-1">分红说明</label>
            <div class="layui-input-block">



            @include('UEditor::head')

            <!-- 加载编辑器的容器 -->
                <script id="container" name="content" type="text/plain">
                    {!! $edit->content !!}
                </script>


                <!-- 实例化编辑器 -->
                <script type="text/javascript">
                    var ue = UE.getEditor('container', {
                        autoHeightEnabled: true,
                        autoFloatEnabled: true,
                        // initialFrameWidth:95,
                        initialFrameHeight:300,

                    });

                    ue.ready(function() {
                        ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');
                    });

                </script>


            </div>
        </div>

    @else

        <div class="layui-form-item editor">

            <label class="layui-form-label col-sm-1">产品说明</label>

            <div class="layui-input-block">

                <textarea name="content" id="container" class="layui-hide" lay-filter="container">{!! $edit->content !!}</textarea>




                <script>

                    var layeditIndex;


                    layui.use(['form','layedit'], function(){

                        var form = layui.form;

                        var layedit = layui.layedit;
                        layedit.set({
                            uploadImage: {
                                url: '{{route("admin.uploads.uploadeditorimg")}}?_token={{ csrf_token() }}'
                                ,type: 'post'
                            }
                        });

                        layeditIndex = layedit.build('container', {
                            tool: [
                                'code',
                                'strong' //加粗
                                ,'italic' //斜体
                                ,'underline' //下划线
                                ,'del' //删除线
                                ,'|' //分割线
                                ,'left' //左对齐
                                ,'center' //居中对齐
                                ,'right' //右对齐
                                ,'link' //超链接
                                ,'unlink' //清除链接
                                ,'image' //图片
                            ],
                            height: 400
                        });


                        function setdescr(){
                            if($('[name="descr"]').val()==''){
                                $('[name="descr"]').val(cutstr(layedit.getText(layeditIndex),200));
                            }
                            setTimeout(function () {
                                setdescr();
                            },5000);
                        }
                        setdescr();


                        $("#layui-btn").click(function(){
                            $("#container").val(layedit.getContent(layeditIndex));

                        });

                        //自定义验证规则

                        form.verify({
                            container: function(value) {
                                layedit.sync(layeditIndex);
                            }
                        });





                        /**
                         * js截取字符串，中英文都能用
                         * @param str：需要截取的字符串
                         * @param len: 需要截取的长度
                         */
                        function cutstr(str, len) {
                            var str_length = 0;
                            var str_len = 0;
                            str_cut = new String();
                            str_len = str.length;
                            for (var i = 0; i < str_len; i++) {
                                a = str.charAt(i);
                                str_length++;
                                if (escape(a).length > 4) {
                                    //中文字符的长度经编码之后大于4
                                    str_length++;
                                }
                                str_cut = str_cut.concat(a);
                                if (str_length >= len) {
                                    str_cut = str_cut.concat("...");
                                    return str_cut;
                                }
                            }
                            //如果给定字符串小于指定长度，则返回源字符串；
                            if (str_length < len) {
                                return str;
                            }
                        }

                    });

                </script>

            </div>

        </div>
</div>
    @endif






@endsection
@section("layermsg")
    @parent
@endsection


@section('form')

@endsection

