<?php

namespace App\Http\Controllers\Api;
use App\Auth;
use App\Category;
use App\Channel;
use App\Http\Controllers\Controller;
use App\Member;
use Carbon\Carbon;
use DB;
use App\Admin;
use App\Ad;
use App\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Intervention\Image\Facades\Image;
use Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ArticlesController extends Controller
{
    public $cachetime=600;
    public $Template='wap';
    //public function __construct(Request $request)
     public function aa(Request $request)
    {

        $this->Template=env("WapTemplate");
        $this->middleware(function ($request, $next) {
            //dd($request->session()->all());
            //$request->session()->put('UserId',502, 120);
           $lastsession = $request->lastsession;
            if(!$lastsession){
                return response()->json(["status"=>-1,"msg"=>"请先登录！"]);
            }else{
                $Member = Member::where("lastsession",$request->lastsession)->first();
                if(!$Member){
                    return response()->json(["status"=>-1,"msg"=>"请先登录！"]);
                }else{
                    $request->session()->put('UserId',$Member->id, 120);
                    $request->session()->put('UserName',$Member->username, 120);
                    $request->session()->put('Member',$Member, 120);
                }
            }

            $UserId = $request->session()->get('UserId');

            if($UserId<1){
                return response()->json(["status"=>-1,"msg"=>"请先登录!"]);
            }else{
              $this->Member = Member::find($UserId);
              if(!$this->Member){
                 return response()->json(["status"=>-1,"msg"=>"请先登录!"]);
              }
            }


            return $next($request);
        });


        /**网站缓存功能生成**/

        if(!Cache::has('setings')){
            $setings=DB::table("setings")->get();

            if($setings){
                $seting_cachetime=DB::table("setings")->where("keyname","=","cachetime")->first();

                if($seting_cachetime){
                    $this->cachetime=$seting_cachetime->value;
                    Cache::forever($seting_cachetime->keyname, $seting_cachetime->value);
                }

                foreach($setings as $sv){
                    Cache::forever($sv->keyname, $sv->value);
                }
                Cache::forever("setings", $setings);
            }

        }

        $this->cachetime=Cache::get('cachetime');

        if(Cache::has('memberlevel.list')){
            $memberlevel=Cache::get('memberlevel.list');
        }else{
            $memberlevel= DB::table("memberlevel")->orderBy("id","asc")->get();
            Cache::get('memberlevel.list',$memberlevel,Cache::get("cachetime"));
        }

        $memberlevelName=[];
        foreach($memberlevel as $item){
            $memberlevelName[$item->id]=$item->name;
        }

        $this->memberlevelName=$memberlevelName;

    }

    /***资讯列表***/
    public function index(Request $request){
        $UserId = $request->session()->get('UserId');
        $category_id = $request->get('type');
        $page = $request->get('page',"1");
        $pagesize = $request->get('pageSize',"10");
       // $pagesize=6;

        $res =  DB::table("articles")->select('id','title','image','updated_at','content','click_count')->where(['status'=>2,'category_id'=>$category_id])->orderBy("top_status","desc")
            ->paginate($pagesize, ['*'], 'page', $page);
        foreach($res as $k=>$v){
            $v->updated_at = substr($v->updated_at,0,16);
        }
        /*视频 常见问题加视频*/
        if($category_id == 32){
            if(Cache::has("video_id_7")){
                $video = Cache::get("video_id_7");
            }else{
                $video = DB::table('videos')->select('videos_name','video_url','thumb_url')->where('id',7)->first();
                Cache::forever("video_id_7",$video);
            }
            $data['video'] = $video;
        }
        $data['list'] = $res;
        return response()->json(['status'=>1,'data'=>$data]);
    }

    /***资讯详情***/
    public function detail(Request $request){

        $UserId = $request->session()->get('UserId');
        $articleId = $request->id;
        if(!$articleId){
          return response()->json(['status'=>0,'msg'=>'参数错误']);
        }
        if(Cache::has("articles_detail_".$articleId)){
            $datas = Cache::get("articles_detail_".$articleId);
        }else{
            $datas =  DB::table("articles")->select('category_id','content','image','title','keyinfo','updated_at','descr','click_count')->where(['id'=>$articleId,'status'=>2])->first();
            $pregRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
           // $datas->content = preg_replace($pregRule, '<img src="' . ENV('FILE_URL') . '${1}" style="width:100%">', $datas->content);
//            $url = "http://".$_SERVER ['HTTP_HOST'];
//            $datas->content = str_replace("<img src=\"/","<img src=\"".$url."/",$datas->content);
//            $datas->content = str_replace("<img src=\"h","<img src=\"h",$datas->content);
            $datas->updated_at = substr($datas->updated_at,0,16);
            Cache::forever("articles_detail_".$articleId,$datas);
        }
        return response()->json(['status'=>1,'data'=>$datas]);
    }

}


?>
