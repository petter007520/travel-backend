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

                    <input type="text" name="s_key"  placeholder="请输入会员账号" autocomplete="off" class="layui-input" value="@if(isset($_REQUEST['s_key'])){{$_REQUEST['s_key']}}@endif">

                </div>
                
               <div class="layui-input-inline">

                    <select name="s_grptype" lay-filter="grptype">
                        
                        <option value="">红包类型</option>
                
                        <option value="1" @if(isset($_REQUEST['s_grptype']) && $_REQUEST['s_grptype']==1) selected="selected" @endif> 红包</option>
                        
                        <option value="3" @if(isset($_REQUEST['s_grptype']) && $_REQUEST['s_grptype']==3) selected="selected" @endif> 实物</option>
                        
                    </select>

                </div>


                <div class="layui-input-inline">

                    <button class="layui-btn" lay-submit lay-filter="go">查询</button>

                </div>

            </form>
        </div>
        


            <table class="layui-table x-admin layui-form">
                <colgroup>
                    <col width="150">
                    <col width="200">
                    <col width="200">
                    <col width="200">
                    <col width="200">
                    <col width="200">
                    <col width="200">
                    <col>
                </colgroup>
                <thead>
                <tr>
                    <th><div class="layui-unselect header layui-form-checkbox" lay-skin="primary"><i class="layui-icon">&#xe605;</i></div></th>
                    <th>用户</th>
                    <th>红包金额</th>
                    <th>类型</th>
                    <th>奖品名称</th>
                    <th>是否领取到</th>
                    <th>领取日期</th>
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
                    <span style=""></span>
                </td>
                <td ><% item.username%></td>
                <td><% item.value %></td>
                <td>
                <%# if(item.type==1){ %>
                红包
                <%# }else{ %>
                实物
                <%# } %>
                </td>
                <td><% item.grp_name %></td>
                <td>
                <%# if(item.status==1){ %>
                已领取
                <%# }else{ %>
                未领取
                <%# } %>
                </td>
                
                <td><% item.created_at %></td>
            </tr>



            <%#  }); %>
            <%#  if(d.length === 0){ %>
            无数据
            <%#  } %>

    </script>

    <script>


        layui.use('form', function(){
            var form = layui.form;


        //     form.on('select(grptype)', function(data){
        //         //console.log(data.elem); //得到select原始DOM对象
        //         //console.log(data.value); //得到被选中的值
        //         // console.log(data.othis); //得到美化后的DOM对象
        //         var obj={
        //             s_key:$("[name='s_key']").val(),
        //             s_grptype:data.value
        //         };
        //         lists(1,obj);
        //     });

        // });

    </script>
    
@endsection

