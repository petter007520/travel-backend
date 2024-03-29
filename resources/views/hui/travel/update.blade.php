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
        <label class="layui-form-label col-sm-1">分类</label>
        <div class="layui-input-inline">
            <select name="category_id">
                {!! $tree_option !!}
            </select>
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">名称</label>

        <div class="layui-input-block">
            <input type="text" name="title" lay-verify="required" required placeholder="标题" autocomplete="off" class="layui-input" value="{{$edit->title}}">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">副标题</label>

        <div class="layui-input-block">
            <input type="text" name="tips" placeholder="副标题" autocomplete="off" class="layui-input" value="{{$edit->tips}}">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">视频地址</label>

        <div class="layui-input-block">
            <input type="text" name="video_url" placeholder="视频地址" autocomplete="off" class="layui-input" value="{{$edit->video_url}}">
        </div>
    </div>

     <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">缩略图</label>
        <div class="layui-input-block">
            <button type="button" class="layui-btn" id="thumb_url" style="float:left;">
                <i class="layui-icon">&#xe67c;</i>上传图片
            </button>
            <span class="imgshow" style="float:left;width:100%;margin: 2px;">
                @if($edit->img!='')
                <img src="{{$edit->img}}" width="100" style="float:left;"/>
                    @endif
            </span>
            <input type="text" name="img"  class="layui-input thumb" placeholder="缩略图" style="float:left;width:50%;" value="{{$edit->img}}">
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
        <label class="layui-form-label col-sm-1">排序</label>
        <div class="layui-input-inline">
            <input type="number" name="sort"  class="layui-input" placeholder="排序" value="{{$edit->sort}}">
        </div>
    </div>

    <div class="layui-form-item">

        <label class="layui-form-label col-sm-1">状态</label>
        <div class="layui-input-inline">
            <input type="radio" name="status" value="1" title="草稿" @if($edit->status==1) checked="checked" @endif>
            <input type="radio" name="status" value="2" title="发布" @if($edit->status==2) checked="checked" @endif>

        </div>


    </div>
    @if(Cache::get('editor')=='markdown')
        <div id="container" class="editor">
            <textarea name="content" style="display:none;">{{$edit->content}}</textarea>
        </div>
        @include('markdown::encode',['editors'=>['container']])

    @elseif(Cache::get('editor')=='u-editor')

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

    @else

        <div class="layui-form-item editor">

            <label class="layui-form-label col-sm-1">内容</label>

            <div class="layui-input-block">

                <textarea name="content" id="container" class="layui-hide" lay-filter="container">{{$edit->content}}</textarea>




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


                        $("#layui-btn").click(function(){
                            $("#container").val(layedit.getContent(layeditIndex));
                        });

                        //自定义验证规则

                        form.verify({
                            container: function(value) {
                                layedit.sync(layeditIndex);
                            }
                        });


                    });

                </script>

            </div>

        </div>

    @endif
@endsection
@section("layermsg")
    @parent
@endsection
@section('form')
@endsection
