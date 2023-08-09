<?php

namespace App\Http\Controllers\Pc;
use App\Auth;
use App\Channel;
use App\Order;
use Carbon\Carbon;
use DB;
use App\Admin;
use App\Club;
use App\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Session;
use App\Ad;

class IndexController
{

    public $cachetime=60;
    public function __construct(Request $request)
    {

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


    }


    /**电脑端首页**/
    public function index(Request $request){

        dd("PC端开发中......");
    }


}


?>
