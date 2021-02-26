<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    protected $primaryKey="extra_id";
    protected $table="extras";
    protected $fillable=["volume","volumeUnit","price","priceUnit","code","createIran","lastUpdate"];
}
