<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table="plans";
    protected $primaryKey="plan_id";
    protected $fillable=["trunk","speed","price","priceUnit","code","trunkUnit","speedUnit","createIran","lastUpdate","category"];
}
