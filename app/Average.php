<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Average extends Model
{
    protected $fillable=["day_id","user_id","vlan_id","avg"];
    protected $primaryKey="avg_id";
    protected $table="averages";
}
