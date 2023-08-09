<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table="articles";
    protected $primaryKey="id";
    public $timestamps=true;
    protected $guarded=[];
    protected $fillable = ['category_id', 'category_name', 'title', 'author', 'keyinfo','descr', 'image', 'content', 'status', 'click_count', 'top_status', 'top_time', 'photos','sort'];


}
