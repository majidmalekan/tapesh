<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ueRelation extends Model
{
    protected $table="ueRelations";
    protected $primaryKey="ue_id";
    protected $fillable=[
        "user_id","extra_id","createUE","consume","isActive","orderId"
    ];
}
