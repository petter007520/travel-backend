<?php
namespace App\Http\Controllers\Admin;
use App\CommunityReward;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CommunityRewardController extends BaseController{
    private $table="community_reward";

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index(Request $request){
        return redirect(route($this->RouteController.".lists"));
    }

    public function lists(Request $request){
        $pagesize=10;//默认分页数
        if(Cache::has('pagesize')){
            $pagesize=Cache::get('pagesize');
        }
        $listDB = DB::table($this->table)
            ->select($this->table.'.*')
           ->where(function ($query) {
                $s_key_name=[];
                if(isset($_REQUEST['s_key']) && $_REQUEST['s_key']!=''){
                    $s_key_name[]=[$this->table.".title","like","%".$_REQUEST['s_key']."%"];
                }
                $query->orwhere($s_key_name);
            });
            $list=$listDB->orderBy($this->table.".id")->paginate($pagesize);
        if($request->ajax()){
            if($list){
                return ["status"=>0,"list"=>$list,"pagesize"=>$pagesize];
            }
        }else{
            return $this->ShowTemplate(["list"=>$list,"pagesize"=>$pagesize]);
        }

    }

    /**
     * 新增
     * @param Request $request
     */
    public function store(Request $request){
        if($request->isMethod("post")){
            $data=$request->post();
            unset($data['_token']);
            $data['title']=\App\Formatting::ToFormat($data['title']);
            $data['performance']=\App\Formatting::ToFormat($data['performance']);
            $data['created_at']= Carbon::now();
            DB::table("community_reward")->insert($data);
            if($request->ajax()){
                return response()->json([
                    "msg"=>"添加成功","status"=>0
                ]);
            }else{
                return redirect(route($this->RouteController.'.store'))->with(["msg"=>"添加成功","status"=>0]);
            }
        }else{
            return $this->ShowTemplate();
        }
    }

    /**
     * 编辑
     * @param Request $request
     */
    public function update(Request $request)
    {
        if($request->isMethod("post")){
            $data=$request->post();
            $id = $data['id'];
            unset($data['_token']);
            $data['title']=\App\Formatting::ToFormat($data['title']);
            $data['performance']=\App\Formatting::ToFormat($data['performance']);
            DB::table("community_reward")->where("id",$id)->update($data);
            if($request->ajax()){
                return response()->json([
                    "msg"=>"修改成功","status"=>0
                ]);
            }else{
                return redirect(route($this->RouteController.'.update',["id"=>$request->input("id")]))->with(["msg"=>"修改成功","status"=>0]);
            }
        }else{
            $Model = CommunityReward::find($request->get('id'));
            return $this->ShowTemplate(["edit"=>$Model,"status"=>0]);
        }

    }

    public function delete(Request $request){
          if($request->ajax()) {
            if($request->input("id")){
                $member = DB::table($this->table)
                    ->where(['id' => $request->input("id")])
                    ->first();
                if($member){
                       $delete = DB::table($this->table)->where('id', '=', $request->input("id"))->delete();
                        if ($delete) {
                            return ["status" => 0, "msg" => "删除成功"];
                        } else {
                            return ["status" => 1, "msg" => "删除失败"];
                        }
                }else{
                    return ["status"=>1,"msg"=>"您没有权限删除操作"];
                }
            }else if($request->input("ids")){
                $delete = DB::table($this->table)->whereIn('id',  $request->input("ids"))->delete();
                if ($delete) {
                    return ["status" => 0, "msg" => "删除成功"];
                } else {
                    return ["status" => 1, "msg" => "删除失败"];
                }
            }
        }else{
            return ["status"=>1,"msg"=>"非法操作"];
        }
    }
}
