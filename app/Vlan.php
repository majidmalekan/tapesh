<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vlan extends Model
{
    protected $primaryKey="vlan_id";
    protected $table='vlans';
    protected $fillable=[
      'vlanName','id','macAddress','rxByte','txByte','disabled','running','user_id',
    ];
}
