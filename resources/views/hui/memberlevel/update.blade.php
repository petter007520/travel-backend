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
        <label class="layui-form-label col-sm-1">等级名称</label>
        <div class="layui-input-inline">
            <input type="text" name="name" lay-verify="required" required placeholder="等级名称" autocomplete="off" class="layui-input" value="{{ $edit->name }}">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">推荐人数</label>
        <div class="layui-input-inline">
            <input type="text" name="tj_num"   lay-verify="required" class="layui-input" placeholder="推荐人数" value="{{ $edit->tj_num }}" >
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">累计消费</label>
        <div class="layui-input-inline">
            <input type="text" name="level_fee"   lay-verify="required" class="layui-input" placeholder="累计消费" value="{{ $edit->level_fee }}" >
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">最小能量值</label>
        <div class="layui-input-inline">
            <input type="number" name="min_nl"   lay-verify="required" class="layui-input" placeholder="最小能量值" value="{{ $edit->min_nl }}" >
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">最大能量值</label>
        <div class="layui-input-inline">
            <input type="number" name="max_nl"   lay-verify="required" class="layui-input" placeholder="最大能量值" value="{{ $edit->max_nl }}" >
        </div>
    </div>
    <div class="layui-form-item zjysg">
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
        <input type="text" name="headurl" lay-verify="required" value="{{$edit->headurl}}" class="layui-input thumb" placeholder="产品图片" style="float:left;width:50%;">
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
@endsection
@section("layermsg")
    @parent
@endsection
@section('form')
@endsection
