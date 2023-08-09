<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Session;

class Admin extends Model
{
    protected $table="admins";
    protected $primaryKey="id";
    public $timestamps=true;
    protected $guarded=[];
    protected $fillable = ['username','password','email','authid','name','phone','clubid','consume','offdate'];
    protected $dates = ['created_at', 'updated_at', 'disabled_at'];


}
