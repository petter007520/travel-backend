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
        <label class="layui-form-label col-sm-1">支付标识</label>
        <div class="layui-input-inline">
            <label>
                <input type="text" name="pay_code" lay-verify="required" class="layui-input" placeholder="名称" value="{{$edit->pay_code}}">
            </label>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">支付名称</label>
        <div class="layui-input-inline">
            <label>
                <input type="text" name="pay_name" lay-verify="required" class="layui-input" placeholder="名称" value="{{$edit->pay_name}}">
            </label>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">支付类型</label>
        <div class="layui-input-inline">
            <label>
                <select name="type">
                    <option @if($edit->type=='online') selected="selected" @endif value="online">在线支付</option>
                    <option @if($edit->type=='offline') selected="selected" @endif value="offline">银联支付</option>
                    <option @if($edit->type=='wallet') selected="selected" @endif value="wallet">余额支付</option>
                    <option @if($edit->type=='chain') selected="selected" @endif value="chain">USDT支付</option>
                </select>
            </label>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">银行名称</label>
        <div class="layui-input-inline">
            <label>
                <input type="text" name="bankname" class="layui-input" placeholder="银行名称" value="{{$edit->bankname}}">
            </label>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">持卡人姓名</label>
        <div class="layui-input-inline">
            <label>
                <input type="text" name="bankrealname" class="layui-input" placeholder="持卡人姓名" value="{{$edit->bankrealname}}">
            </label>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">支行</label>
        <div class="layui-input-inline">
            <label>
                <input type="text" name="bank_type" class="layui-input" placeholder="支行" value="{{$edit->bank_type}}">
            </label>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">银行卡号</label>
        <div class="layui-input-inline">
            <label>
                <input type="text" name="bankcode" class="layui-input" placeholder="持卡人姓名" value="{{$edit->bankcode}}">
            </label>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">图标</label>
        <div class="layui-input-block">
            <button type="button" class="layui-btn" id="thumb_url" style="float:left;">
                <i class="layui-icon">&#xe67c;</i>上传图片
            </button>
            <span class="imgshow wxerw" style="float:left;width:100%;margin: 2px;">
                 <img src="{{$edit->pay_pic}}" width="100" style="float:left;"/>
            </span>
            <label>
                <input type="text" name="pay_pic"  value="{{$edit->pay_pic}}" class="layui-input thumb" placeholder="图标" style="float:left;width:50%;">
            </label>
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

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">渠道类型</label>
        <div class="layui-input-inline">
            <label>
                <select name="pay_type">
                    <option @if($edit->pay_type=='0') selected="selected" @endif value="0">请选择</option>
                    <option @if($edit->pay_type=='1') selected="selected" @endif value="1">余额</option>
                    <option @if($edit->pay_type=='2') selected="selected" @endif value="2">银行卡</option>
                    <option @if($edit->pay_type=='3') selected="selected" @endif value="3">支付宝</option>
                    <option @if($edit->pay_type=='4') selected="selected" @endif value="4">微信</option>
                    <option @if($edit->pay_type=='5') selected="selected" @endif value="5">USDT(trc20)</option>
                </select>
            </label>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">说明</label>
        <div class="layui-input-block">
            <label>
                <textarea name="pay_desc" class="layui-textarea">{{$edit->pay_desc}}</textarea>
            </label>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">支付场景</label>
        <div class="layui-input-block">
            <input type="checkbox" name="scenes[]" value="order" title="订单支付" @if($edit->scenes && in_array('order',$edit->scenes)) checked @endif>
            <input type="checkbox" name="scenes[]" value="recharge" title="充值" @if($edit->scenes && in_array('recharge',$edit->scenes)) checked @endif>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">状态</label>
        <div class="layui-input-block">
            <input type="radio" name="enabled" value="1" title="启用" @if($edit->enabled==1)checked="checked"@endif>
            <input type="radio" name="enabled" value="0" title="禁用" @if($edit->enabled==0)checked="checked"@endif>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">默认</label>
        <div class="layui-input-block">
            <input type="radio" name="is_default" value="1" title="默认" @if($edit->is_default==1)checked="checked"@endif>
            <input type="radio" name="is_default" value="0" title="不默认" @if($edit->is_default==0)checked="checked"@endif>
        </div>
    </div>
@endsection

@section("layermsg")

    @parent

@endsection
@section('form')

    <script>



        var s_classify='{{$edit->parent}}';

        layui.use('form', function(){

            var form = layui.form;


            @if(Cache::get('editor')=='markdown')
            if($("[name='model']").val()=='singlepages'){
                $('.editor').show();
            }else{
                $('.editor').hide();
            }

            @else
            if($("[name='model']").val()=='singlepages'){
                $('.editor').show();
            }else{
                $('.editor').hide();
            }
            @endif



            if($("[name='model']").val()=='links'){
                $('.links').text('外链地址(URL)');
                $("[name='links']").attr({'placeholder':'外链地址(URL)'});
            }else{
                $('.links').text('目录地址(英文)');
                $("[name='links']").attr({'placeholder':'目录地址(英文)'});
            }

            //各种基于事件的操作，下面会有进一步介绍



            //自定义验证规则

            form.verify({





            });



            //监听提交

            form.on('submit(go)', function(data){

                return true;

            });



            form.on('select(s_model)', function(data){

                @if(Cache::get('editor')=='markdown')
                    if(data.value=='singlepages'){
                        $('.editor').show();
                    }else{
                        $('.editor').hide();
                    }
                @else
                    if(data.value=='singlepages'){
                        $('.editor').show();
                    }else{
                        $('.editor').hide();
                    }
                @endif


                if($("[name='model']").val()=='links'){
                    $('.links').text('外链地址(URL)');
                    $("[name='links']").attr({'placeholder':'外链地址(URL)'});
                }else{
                    $('.links').text('目录地址(英文)');
                    $("[name='links']").attr({'placeholder':'目录地址(英文)'});
                }



            });







            var storeid =$("[name='storeid']").val();

            if(storeid>0){

              //  getdatas(storeid);

            }



            function  getdatas(storeid){







            }





            var classify_html='';

            function set_html(classify,index_i=0){

                if(index_i==0){

                    classify_html='';

                }

                var listkeys='';

                for(var ki=0;ki<index_i;ki++){

                    listkeys+='┕';

                }



                for(var i in classify){

                    if(s_classify==classify[i].id){

                        var selected=' selected="selected"';

                    }else{

                        var selected='';

                    }

                    classify_html+='<option value="'+classify[i].id+'" '+selected+'>'+listkeys+classify[i].name+'</option>';



                    if(classify[i].parents.length>0){

                        index_i++;

                        set_html(classify[i].parents,index_i);

                    }

                }

                $(".s_classify").html(classify_html);



                form.render(); //更新全部

            }







        });





    </script>

    @endsection





