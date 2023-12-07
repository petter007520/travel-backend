<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TravelCategory extends Model
{
    protected $table="travel_category";
    protected $primaryKey="id";
    public $timestamps=true;
    protected $guarded=[];
    protected $fillable = ['parent','name', 'tips', 'weight', 'create_at'];

    public function tree_option($parentid=0,$cj=0,$selected=0,$current=0,$model='',$whereArr=[])
    {
        if($model!=''){
            $category =TravelCategory::where('parent',$parentid)->where($whereArr)->orderBy("weight","desc")->get();
        }else{
            $category =TravelCategory::where('parent',$parentid)->orderBy("weight","desc")->get();
        }
        //第一次做的时候get()后面加了toArray()，页面遍历数据时报错遍历的不是对象，去掉后可行
        $cjstr='';
        $arr = '';
        if (sizeof($category) !=0){
            $cj++;
            for($i=1;$i<$cj;$i++){
                $cjstr.=$i==1?'|-':'-';
            }
            foreach ($category as $datum) {
                $selectedstr='';
                if($datum['id']==$selected){
                    $selectedstr='selected="selected"';
                }
                if($datum['id']!=$current){
                    $arr.='<option value="'.$datum['id'].'" '.$selectedstr.'>'.$cjstr.$datum['name'].'</option>';
                }
                $arr.=$this->tree_option($datum['id'],$cj,$selected,$current,$model);
            }
        }
        return $arr;
    }
}
