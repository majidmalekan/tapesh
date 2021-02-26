<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Daily extends Model
{
    protected $primaryKey="day_id";
    protected $table="daily";
    protected $fillable=["upload","download","date","user_id","vlan_id","up_id","ue_id","status"];
}
