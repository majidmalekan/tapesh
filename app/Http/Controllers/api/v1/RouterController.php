<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Payment;
use App\Plan;
use App\Suggestions;
use App\upRelation;
use App\User;
use App\Vlan;
use App\Vpn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use \RouterOS\Client;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\QueryException;
use \RouterOS\Query;

class RouterController extends Controller
{
    protected $client;
    public function __construct()
    {
        try {
            $this->client = new Client([
                // todo: change this hard code.
                'host' => '213.233.177.228',
                'user' => 'Raspeina',
                'pass' => 'tol0rF@ny',
                'port' => 8728,
            ]);
        } catch (ClientException $e) {
            return $e;
        } catch (ConfigException $e) {
            return $e;

        } catch (QueryException $e) {
            return $e;

        }
    }
    public function vlans(){
        // todo: better naming?
            $array=array();
            $arrays=array();
        try {
            $query =
                (new Query('/interface/print'))
                    ->where('type', 'vlan');
        } catch (QueryException $e) {
            return $e;
        }
        try {
            $response = $this->client->query($query)->read();
        } catch (ClientException|QueryException $e) {
            return $e;
        }
        foreach ($response as $item){
               $array["name"]=$item['name'];
               $array["id"]=$item[".id"];
               $array["mac-address"]=$item["mac-address"];
               $array["rx-byte"]=$item["rx-byte"];
               $array["tx-byte"]=$item["tx-byte"];
               $array["disabled"]=$item['disabled'];
               $array["running"]=$item['running'];
               if (Vlan::query()->where('vlanName',$item['name'])->where('id',$item['.id'])->doesntExist()) {
                   $vlans = Vlan::query()->create([
                       'vlanName' => $item['name'],
                       'id' => $item['.id'],
                       'mac-address' => $item['mac-address'],
                       'rxByte' => $item['rx-byte'],
                       'txByte' => $item['tx-byte'],
                       'disabled' => $item['disabled'],
                       'running' => $item['running'],
                   ]);
               }
            array_push($arrays,$array);
        }
        return response(['data'=>$arrays,'status'=>'success']);
    }
    public function vpns(){
        // todo: better naming one more time
        $array=array();
        $arrays=array();
        try {
            $query = (new Query('/tool/user-manager/user/print'));
        } catch (QueryException $e) {
            return $e;
        }
        try {
            $response = $this->client->query($query)->read();
        } catch (ClientException|QueryException $e) {
            return $e;

        }
        // todo: review php magic methods __get() and __set()
        foreach ($response as $item){
            $array["customer"]=$item['customer'];
            $array["id"]=$item[".id"];

            $array["username"]=$item["username"];
            $array["password"]=$item["password"];
            if (array_key_exists('actual-profile',$item)){
                $array["actual-profile"]=$item["actual-profile"];
            }else{
                // remove it or add some logic to it
            }
            if ( array_key_exists('uptime-used',$item) && array_key_exists('download-used',$item) && array_key_exists('upload-used',$item) ){
                $array["uptime-used"]=$item['uptime-used'];
                $array["download-used"]=$item['download-used'];
                $array["upload-used"]=$item['upload-used'];
                $array["actual-profile"]='';
                if(Vpn::query()->where('username',$item['username'])->where('id',$item['.id'])->doesntExist()) {
                    $vpn = Vpn::query()->create([
                        'customer' => $item['customer'],
                        'id' => $item['.id'],
                        'actual-profile' =>  $array["actual-profile"],
                        'username' => $item['username'],
                        'password' => $item['password'],
                        'last-seen' => $item['last-seen'],
                        'uptime-used' => $item['uptime-used'],
                        'download-used' => $item['download-used'],
                        'upload-used' => $item['upload-used'],
                        'active' => $item['active'],
                        'incomplete' => $item['incomplete'],
                        'disabled' => $item['disabled'],
                        'shared-users' => $item['shared-users']
                    ]);
                }
            }else {
                $array["uptime-used"] = '';
                $array["download-used"] = '';
                $array["upload-used"] = '';

                if (Vpn::query()->where('username', $item['username'])->where('.id', $item['.id'])->doesntExist()) {
                    $vpn = Vpn::query()->create([
                        'customer' => $item['customer'],
                        'id' => $item['.id'],
                        'actual-profile' =>  $array["actual-profile"],
                        'username' => $item['username'],
                        'password' => $item['password'],
                        'last-seen' => $item['last-seen'],
                        'active' => $item['active'],
                        'incomplete' => $item['incomplete'],
                        'disabled' => $item['disabled'],
                        'shared-users' => $item['shared-users']
                    ]);
                }
            }
            $array["last-seen"]=$item['last-seen'];
            $array["active"]=$item['active'];
            $array["incomplete"]=$item['incomplete'];
            $array["disabled"]=$item['disabled'];
            $array["shared-users"]=$item['shared-users'];
            $arrays[] = $array;
        }
        return response(['data'=>$arrays,'status'=>'success']);

    }
    public function showVlan(Request $request){
        // todo: create a new request class and validate requests on that class
        $validData=$this->validate($request,[
           'api_token'=>'required'
        ]);
        // todo: i don't think that's style
        $array=array();
        $arrays=array();
        if (User::query()->where('api_token',$validData['api_token'])->count() == 1){
            $vlans=Vlan::all();
            foreach ($vlans as $vlan){
                $array["vlan_id"]=$vlan->vlan_id;
                $array["vlanName"]=$vlan->vlanName;
                $array["id"]=$vlan->id;
                $array["trunk"]=$vlan->rxByte + $vlan->txByte;
                $array["running"]=$vlan->running;
                $user=User::query()->where('user_id',$vlan->user_id)->get();
                $array["username"]=$user[0]->username;
                $upRelation=upRelation::query()->where('user_id',$vlan->user_id)->where('isActive','=','1')->get();
                $plan=Plan::query()->where('plan_id',$upRelation[0]->plan_id)->get();
                $array["volume"]=$plan[0]->trunk;
                $array["period"]=$plan[0]->period;
                array_push($arrays,$array);
            }
            return response(['data'=>$arrays,'status'=>'success']);
        }
    }
    public function showVlanOption(Request $request){
        $validData=$this->validate($request,[
            'api_token'=>'required'
        ]);
        if (User::query()->where('api_token',$validData['api_token'])->count() == 1){
            $vlans=Vlan::all();
            return response(['data'=>$vlans,'status'=>'success']);
        }
    }
    public function adminDashboard(Request $request){
        $validData=$this->validate($request,[
           'api_token'=>'required'
        ]);
        $countUser=User::query()->count();
        $countVlan=Vlan::query()->count();
        $countPay=Payment::query()->where('Status','=','100');
        $payment=0;
        foreach ($countPay as $item){
            $payment=$payment+$item->price;
        }
        $pay=Payment::query()->orderByDesc('pay_id')->limit(10)->get();
        $user=User::query()->orderByDesc('user_id')->limit(10)->get();
        $vlan=Vlan::query()->orderByDesc('txByte')->limit(10)->get();
        return response(['data'=>compact('pay','user','vlan','countUser','countVlan','payment'),'status'=>'success']);
    }
    public function enable(){
        try {
            $query =
                (new Query('/interface/enable'))
                    ->where('name','vlan13-test')
                ->where('type','vlan')
            ;
        } catch (QueryException $e) {
            return $e;
        }
        try {
            $response = $this->client->query($query)->read();
            var_dump($response);
        } catch (ClientException $e) {
            return $e;
        } catch (QueryException $e) {
            return $e;
        }
    }
    public function makeSuggest(Request $request)
    {
        date_default_timezone_set('Asia/Tehran');
        $validData = $this->validate($request, [
            'api_token' => 'required',
            'text' => 'required',
            'description' => 'required',
            'whatFor' => 'required',
            'date' => 'required',
            'user_id' => 'required'
        ]);
        $user = User::query()->where('api_token', $validData['api_token'])->get();
        if ($user[0]->role_id == 2) {
            $suggest = Suggestions::query()->create([
                'text' => $validData["text"],
                'description' => $validData['description'],
                'whatFor' => $validData['whatFor'],
                'date' => Carbon::now(),
                'user_id' => $validData['user_id']
            ]);
            return response(['data'=>$suggest,'status'=>'ok']);
        }else{
            return response(['data'=>'شما مدیر نیستید','status'=>'ok']);
        }
    }
}
