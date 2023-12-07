<?php


namespace App\Http\Controllers\Admin;
    use App\Smstmp;
    use DB;
    use App\Category;
    use Illuminate\Http\Request;
    use Session;
    use Cache;


class SmstmpController extends BaseController
{

    private $table="smstmp";


    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->Model=new Smstmp();



    }



    public function index(Request $request){

        return redirect(route($this->RouteController.".lists"));

    }




    public function lists(Request $request){


        $adminAuthID =$request->session()->get('adminAuthID');
        $adminID =$request->session()->get('adminID');



        $pagesize=10;//默认分页数
        if(Cache::has('pagesize')){
            $pagesize=Cache::get('pagesize');
        }



        if($request->ajax()){
            $listDB = DB::table($this->table)
                ->select($this->table.'.*');

            $list=$listDB->orderBy($this->table.".id","asc")
                ->paginate($pagesize);

            if($list){
                return ["status"=>0,"list"=>$list,"pagesize"=>$pagesize];
            }
        }else{



            return $this->ShowTemplate([]);
        }

    }

    public function store(Request $request){

        if($request->isMethod("post")){



            $Model = $this->Model;

            $Model->sms_txtname = $request->input('sms_txtname');
            $Model->sms_type = $request->input('sms_type');
            $Model->sms_content = $request->input('sms_content');

            $Model->save();


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






    public function update(Request $request)
    {
        if($request->isMethod("post")){



            $Model = $this->Model::find($request->input('id'));
            $Model->sms_txtname = $request->input('sms_txtname');
            $Model->sms_type = $request->input('sms_type');
            $Model->sms_content = $request->input('sms_content');

            $Model->save();



            if($request->ajax()){
                return response()->json([
                    "msg"=>"修改成功","status"=>0
                ]);
            }else{
                return redirect(route($this->RouteController.'.update',["id"=>$request->input("id")]))->with(["msg"=>"修改成功","status"=>0]);
            }


        }else{


            $Model = $this->Model::find($request->get('id'));

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


            }


        }else{
            return ["status"=>1,"msg"=>"非法操作"];
        }

    }



}
