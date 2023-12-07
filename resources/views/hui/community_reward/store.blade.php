@extends('hui.layouts.appstore')

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
        <label class="layui-form-label col-sm-1">奖励名称</label>
        <div class="layui-input-block">
            <input type="text" name="title" lay-verify="required" required placeholder="奖励名称" autocomplete="off" class="layui-input" value="">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label col-sm-1">奖励条件</label>
        <div class="layui-input-block">
            <input type="text" name="performance" lay-verify="required" required placeholder="奖励条件" autocomplete="off" class="layui-input" value="">
        </div>
    </div>
@endsection
@section("layermsg")
    @parent
@endsection
@section('form')
    @endsection
