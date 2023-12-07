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
        <label class="layui-form-label col-sm-1">名称</label>

        <div class="layui-input-block">
            <input type="text" name="name" lay-verify="required" required placeholder="名称" autocomplete="off" class="layui-input" value="{{$edit->name}}">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">最大显示数</label>
        <div class="layui-input-inline">
            <input type="text" name="maxnum" class="layui-input" lay-verify="required" required placeholder="最大显示数" value="{{$edit->maxnum}}">
        </div>
    </div>






    <div class="layui-form-item">

        <label class="layui-form-label col-sm-1">效果预览</label>



        <div class="layui-input-block">

            <button type="button" class="layui-btn" id="thumb_url" style="float:left;">
                <i class="layui-icon">&#xe67c;</i>上传图片
            </button>

            <span class="imgshow" style="float:left;width:100%;margin: 2px;">

                <img src="{{$edit->thumb_url}}" width="100" style="float:left;"/>
            </span>

            <input type="text" value="{{$edit->thumb_url}}" name="thumb_url" lay-verify="required" class="layui-input thumb" placeholder="缩略图" style="float:left;width:50%;">


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



    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">广告模板</label>

        <div class="layui-input-block">
            <input type="text" name="modelname" placeholder="广告模板" lay-verify="required"  autocomplete="off" class="layui-input" value="{{$edit->modelname}}">
        </div>
    </div>


    <div class="layui-form-item layui-form-text">
        <label class="layui-form-label col-sm-1">排序</label>
        <div class="layui-input-block">
            <input type="text" name="sort" placeholder="排序" lay-verify="required"  autocomplete="off" class="layui-input" value="{{$edit->sort}}">

        </div>
    </div>

    <div class="layui-form-item layui-form-text">
        <label class="layui-form-label col-sm-1">说明</label>
        <div class="layui-input-block">
            <textarea placeholder="请填写描述" class="layui-textarea" name="extention">{{$edit->extention}}</textarea>
        </div>
    </div>






@endsection
@section("layermsg")
    @parent
@endsection


@section('form')

@endsection
