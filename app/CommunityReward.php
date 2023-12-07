<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommunityReward extends Model
{
    protected $table="community_reward";
    protected $primaryKey="id";
    public $timestamps=true;
    protected $guarded=[];
    protected $fillable = ['title', 'performance','create_at'];


}
