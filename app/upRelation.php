<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class upRelation extends Model
{
    protected $table="upRelations";
    protected $primaryKey="up_id";
    protected $fillable=[
      "user_id","plan_id","createUP","Extension","dateExpire","isActive","orderId"
    ];
}
