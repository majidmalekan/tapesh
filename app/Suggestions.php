<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Suggestions extends Model
{
    protected $primaryKey="suggest_id";
    protected $fillable=["user_id","text","description","whatFor","date","status","feedback"];
    protected $table="suggestions";
}
