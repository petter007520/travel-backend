<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Articledynamic extends Model
{
    protected $table="articlesdynamic";
    protected $primaryKey="id";
    public $timestamps=true;
    protected $guarded=[];
    protected $fillable = ['category', 'title', 'type', 'content','upload_url','sort'];


}
