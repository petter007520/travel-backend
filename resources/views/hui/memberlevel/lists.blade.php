@extends('hui.layouts.applists')



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



        <xblock>

            <button class="layui-btn" onclick="store()">
                <i class="layui-icon download">&#xe654;</i>
                添加</button>

        </xblock>

             <table class="layui-table x-admin layui-form">

            <colgroup>

               <!-- <col width="50">
                <col width="100">
                <col width="100">
                <col width="110">

                <col width="110">
                <col width="80">
                <col width="80">-->

            </colgroup>

            <thead>

            <tr>
                <th>ID</th>
                <th>会员等级</th>
              <!--  <th>购买价格</th>-->
               <!-- <th>等级返利</th>-->
                <th>推荐人数</th>
                <th>累计消费</th>
                <th>最小能量值</th>
                <th>最大能量值</th>
                <!--<th>每日玩大转盘数</th>-->
                <th>头像</th>
                <!--<th>发展同级下线数</th>-->
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
    {{--//'pay_code','pay_name','pay_bank','pay_pic','pay_desc','enabled'--}}
    <script id="demo" type="text/html">


        <%#  layui.each(d.data, function(index, item){ %>

        <tr>
            <td>
                <% item.id %>
            </td>



            <td><% item.name %></td>

           <!-- <td><% item.price %></td>-->

            <!-- <td><% item.rate %>%</td>-->
             <td><% item.tj_num %></td>
             <td><% item.level_fee %></td>
             <td><% item.min_nl %></td>
             <td><% item.max_nl %></td>
            <td><img src=<% item.headurl %> width="20"></td>

             <!--<td><% item.inte %></td>
             <td><% item.wheels %></td>
             <td><% item.offlines %></td> -->

            <td class="td-manage">

                <a title="编辑"  onclick="update(<% item.id %>,<% d.current_page %>)" href="javascript:;">
                    <i class="layui-icon">&#xe642;</i>
                </a>

            </td>
        </tr>






        <%#  }); %>
        <%#  if(d.length === 0){ %>
        无数据
        <%#  } %>

    </script>




    <script>



       layui.use(['form'], function(){

        var form = layui.form;

        });



    </script>

@endsection



