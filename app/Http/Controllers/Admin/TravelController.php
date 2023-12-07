<?php


namespace App\Http\Controllers\Admin;
    use App\Travel;
    use App\TravelCategory;
    use Carbon\Carbon;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;

    class TravelController extends BaseController
{

    private $table="travel";


    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->Model=new Travel();
        $list= config('model');
        view()->share("modellist",$list);
        $this->CategoryModel=new TravelCategory();
        $category_id=$request->s_categoryid;
        view()->share("tree_option",$this->CategoryModel->tree_option(0,0,$category_id,0,'travel'));
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
            })
            ->where(function ($query) {
                $s_category_id=[];
                if(isset($_REQUEST['s_categoryid']) && $_REQUEST['s_categoryid']>0){
                    $s_category_id[]=[$this->table.".category_id","=",$_REQUEST['s_categoryid']];
                }
                $query->where($s_category_id);
            });
            $list=$listDB->orderBy($this->table.".sort","desc")->orderBy($this->table.".id","desc")
                ->paginate($pagesize);
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
            unset($data['thumb']);
            $data['category_name']=$this->CategoryModel->where("id",$data['category_id'])->value('name');
            $data['title']=\App\Formatting::ToFormat($data['title']);
            $data['tips']=\App\Formatting::ToFormat($data['tips']);
            $data['content']=\App\Formatting::ToFormat($data['content']??'');
            $data['video_url']=\App\Formatting::ToFormat($data['video_url']);
            $data['create_at']= Carbon::now();
            DB::table("travel")->insert($data);
            if($request->ajax()){
                //删除缓存
                Cache::forget('travel_list');
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
            unset($data['thumb']);
            $data['category_name']=$this->CategoryModel->where("id",$data['category_id'])->value('name');
            $data['title']=\App\Formatting::ToFormat($data['title']);
            $data['tips']=\App\Formatting::ToFormat($data['tips']);
            $data['content']=\App\Formatting::ToFormat($data['content']??'');
            $data['video_url']=\App\Formatting::ToFormat($data['video_url']);
            $data['update_at']=Carbon::now();

            DB::table("travel")->where("id",$id)->update($data);
            if($request->ajax()){
                //删除缓存
                Cache::forget('travelDetail_'.$id);
                Cache::forget('travel_list');
                return response()->json([
                    "msg"=>"修改成功","status"=>0
                ]);
            }else{
                return redirect(route($this->RouteController.'.update',["id"=>$request->input("id")]))->with(["msg"=>"修改成功","status"=>0]);
            }
        }else{

            $Model = $this->Model::find($request->get('id'));
            view()->share("tree_option",$this->CategoryModel->tree_option(0,0,$Model->category_id,0,'travel'));
            view()->share("photos",json_decode($Model->photos));
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
