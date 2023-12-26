<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RewardLog extends Model
{
    protected $table="member_reward_log";
    protected $primaryKey="id";
    public $timestamps=true;
    protected $guarded=[];
    protected $fillable = [
        'user_id',
        'username',
        'title',
        'type',
        'amount',
        'before_amount',
        'after_amount',
        'from_base_username',
        'from_username',
        'created_date',
        'created_at'
    ];

    public static function AddLog($data)
    {
        $logData = [
            'user_id'   => $data['user_id'],
            'username'  => $data['username'],
            'title'     => $data['title'],
            'from'      => $data['from'],
            'type'      => $data['type'],
            'type_title'=> $data['type_title'],
            'amount'    => $data['amount'],
            'before_amount' => $data['before_amount'],
            'after_amount' => $data['after_amount'],
            'from_base_username' => $data['from_base_username'],
            'from_username' => $data['from_username'],
            'created_date' => $data['created_date'],
            'created_at' => date('Y-m-d H:i:s', time())
        ];
        DB::table('member_reward_log')->insert($logData);
    }
}
