@extends(env('Template').'.layouts.applists')
@section('title', $title)
@section('here')
@endsection
@section('addcss')
    @parent
@endsection
@section('addjs')
    @parent
@endsection
@section('formbody')
    <div class="x-body">
        <div class="layui-row">
            <form class="layui-form layui-col-md12 x-so" action="{{ route($RouteController.".lists") }}" method="get">
                <div class="layui-input-inline">
                    <input type="text" name="s_key" placeholder="请输入标题" autocomplete="off" class="layui-input"
                           value="@if(isset($_REQUEST['s_key'])){{$_REQUEST['s_key']}}@endif">
                </div>
                <div class="layui-input-inline">
                    <select name="s_categoryid" lay-filter="category_id">
                        <option value="0">旅行分类</option>
                        {!! $tree_option !!}
                    </select>
                </div>
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit lay-filter="go">查询</button>
                </div>
            </form>
        </div>
        <xblock>
            <button class="layui-btn" onclick="store()"><i class="layui-icon download">&#xe654;</i>添加</button>
            <button class="layui-btn layui-btn-danger" onclick="delAll()"><i class="layui-icon">&#xe640;</i>批量删除</button>
        </xblock>
        <table class="layui-table x-admin layui-form">
            <colgroup>
                <col>
                <col>
                <col>
                <col>
                <col>
                <col>
                <col>
                <col>
                <col>
                <col>
                <col>
            </colgroup>
            <thead>
            <tr>
                <th>
                    <div class="layui-unselect header layui-form-checkbox" lay-skin="primary"><i class="layui-icon">&#xe605;</i></div>ID
                </th>
                <th>名称</th>
                <th>分类</th>
                <th>图片</th>
                <th>排序</th>
                <th>添加日期</th>
                <th>更新日期</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody id="view">
            </tbody>
        </table>
        <div id="layer_pages"></div>
    </div>
@endsection
@section("layermsg")
    @parent
@endsection
@section('form')
    <script id="demo" type="text/html">
        <%#  layui.each(d.data, function(index, item){ %>
        <tr>
            <td>
                <div class="layui-unselect layui-form-checkbox" lay-skin="primary" data-id='<% item.id %>'><i class="layui-icon">&#xe605;</i></div>
                <span style=""><% item.id %></span>
            </td>
            <td class="title_<% item.id %>"><% item.title %></td>
            <td><% item.category_name %></td>
            <td id="photo-front-<% item.id %>"><img style="max-width: 15px;" src=<% item.img?item.img:'' %>
                width="15" onclick="openFrontPhotos(<% item.id %>)">
            </td>
            <td><% item.sort %></td>
            <td><% item.create_at %></td>
            <td><% item.update_at %></td>
            <td class="td-manage">
                <a title="编辑" onclick="update(<% item.id %>,<% d.current_page %>)" href="javascript:;">
                    <i class="layui-icon">&#xe642;</i>
                </a>
                <%# if(item.category_id!=29 && item.category_id!=30 && item.category_id!=31 && item.category_id!=27){ %>
                <a title="删除" onclick="del(<% item.id %>,<% d.current_page %>)" href="javascript:;">
                    <i class="layui-icon">&#xe640;</i>
                </a>
                <%# } %>
            </td>
        </tr>
        <%#  }); %>
        <%#  if(d.length === 0){ %>
        无数据
        <%#  } %>

    </script>
    <script>
        layui.use('form', function () {
            var form = layui.form;
            form.on('select(category_id)', function (data) {
                var obj = {
                    s_key: $("[name='s_key']").val(),
                    s_categoryid: data.value,
                };
                lists(1, obj);
            });
        });
        function openFrontPhotos(id) {
            layer.photos({
                photos: '#photo-front-' + id
                , shift: 0
            });
        }

    </script>
@endsection

