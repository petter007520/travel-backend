<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $table="version";
    protected $primaryKey="id";
    public $timestamps=true;
    protected $guarded=[];
}
