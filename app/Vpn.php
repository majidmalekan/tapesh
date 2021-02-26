<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vpn extends Model
{
    protected $primaryKey="vpn_id";
    protected $table="vpns";
    protected $fillable=[
      'customer','id','actual-profile','username','password','disabled','uptime-used','download-used',
        'upload-used','active','incomplete','last-seen','shared-users','user_id'
    ];
}
