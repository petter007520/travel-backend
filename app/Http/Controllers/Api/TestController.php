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

class TestController extends Controller
{
    public function __construct(Request $request)
    {

    }
    
    
    public function testuploadpic(){
        $filename = $_FILES['file']['name'];
        $tmpname = $_FILES['file']['tmp_name'];
        $url = 'upload_cross/';
        if(move_uploaded_file($tmpname,$url.$filename)){
            return response()->json(["status"=>1,"msg"=>"上传成功"]);
        }
        return response()->json(["status"=>0,"msg"=>"上传失败"]);
    }
}

?>
