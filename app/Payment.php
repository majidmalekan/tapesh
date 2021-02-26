<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table="payments";
    protected $fillable=["price","user_id","description","Authority","RefID","Message","Status","createPay","orderId"];
    protected $primaryKey="pay_id";
}
