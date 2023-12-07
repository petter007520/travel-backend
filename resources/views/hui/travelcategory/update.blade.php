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
        <label class="layui-form-label col-sm-1">分类名称</label>

        <div class="layui-input-block">
            <input type="text" name="name" lay-verify="required" required placeholder="分类名称" autocomplete="off" class="layui-input" value="{{$edit->name}}">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">副标题</label>

        <div class="layui-input-block">
            <input type="text" name="tips" placeholder="副标题" autocomplete="off" class="layui-input" value="{{$edit->tips}}">
        </div>
    </div>


    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">排序</label>
        <div class="layui-input-block">
            <input type="text" name="weight" placeholder="排序" autocomplete="off" class="layui-input" value="{{$edit->weight}}">
        </div>
    </div>
@endsection
@section("layermsg")
    @parent
@endsection


@section('form')

@endsection
