<?php

namespace App\Http\Controllers\api\v1;

use App\Average;
use App\Daily;
use App\Extra;
use App\Http\Controllers\Controller;
use App\Payment;
use App\Plan;
use App\Role;
use App\Suggestions;
use App\ueRelation;
use App\upRelation;
use App\User;
use App\Vlan;
use App\Vpn;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SoapClient;
use SoapFault;
use Zarinpal\Zarinpal;
use \RouterOS\Client;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\QueryException;
use \RouterOS\Query;
class UserController extends Controller
{
    protected $client;
    public function __construct()
    {
        try {
            $this->client = new Client([
                // hard code...
                'host' => '213.233.177.228',
                'user' => 'Raspeina',
                'pass' => 'tol0rF@ny',
                'port' => 8728,
            ]);
        } catch (ClientException|QueryException|ConfigException $e) {
            return $e;
        }
    }

    public function register(Request $request)
    {
        $v = new Verta();
        $validData = $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
            'vlanName' => 'required',
            'role_id' => 'required',
        ]);
        $user = User::query()->insertGetId([
            'username' => $validData['username'],
            'password' => bcrypt($validData['password']),
            'api_token' => Str::random(64),
            'role_id' => $validData['role_id'],
            'lastLogin' => $v->formatDatetime(),
            'createIran' => $v->formatDatetime()
        ]);
        $vlan = Vlan::query()->where('vlanName', $validData['vlanName'])->get();
        $updateVlan = Vlan::find($vlan[0]->vlan_id);
        $updateVlan->user_id = $user;
        $updateVlan->save();
        return response(['data' => compact('user', 'vlan'), 'status' => 'success']);
    }

    public function login(Request $request)
    {
        $v = new Verta();
        $validData = $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);
        if (!auth()->attempt($validData)) {
            return response([
                'data' => 'رمز عبور یا نام کاربری شما غلط می باشد',
                'status' => 'error'
            ], 403);
        }
        auth()->user()->update([
            'api_token' => Str::random(64),
            'lastLogin' => $v->formatDatetime()
        ]);
        $user = auth()->user();
        return response(['data' => $user, 'status' => 'success']);
    }

    public function profileFirst(Request $request)
    {
        $validData = $this->validate($request, [
            'phone' => 'required',
            'fullName' => 'required',
            'email' => 'required',
            'companyName' => 'required',
            'companyEmail'=>'required',
            'companyPhone'=>'required',
            'registerNumber'=>'required',
            'address'=>'required',
            'viaEmail' => 'required',
            'viaPhone' => 'required',
        ]);
        $user = User::query()->where('api_token', $validData['api_token'])->get();
        $updateUser = User::find($user[0]->user_id);
        $updateUser->phone = $validData['phone'];
        $updateUser->email = $validData['email'];
        $updateUser->name = $validData['fullName'];
        $updateUser->companyName = $validData['companyName'];
        $updateUser->companyEmail = $validData['companyEmail'];
        $updateUser->companyPhone = $validData['companyPhone'];
        $updateUser->registerNumber = $validData['registerNumber'];
        $updateUser->address = $validData['address'];
        $updateUser->viaPhone = 1;
        $updateUser->viaEmail = 1;
        $updateUser->save();
    }

    public function showPlans()
    {
        $plansOne = Plan::query()->where('category', '=', '1')->get();
        $plansThree = Plan::query()->where('category', '=', '3')->get();
        $extras = Extra::all();

        return response(['data' => compact('plansOne', 'plansThree', 'extras'), 'status' => 'success']);
    }

    public function showExtra()
    {
        $extra = Extra::all();
        return response(['data' => $extra, 'status' => 'success']);
    }

    public function showRoles()
    {
        $role = Role::all();
        return response(['data' => $role, 'status' => 'success']);
    }

    public function addRoles(Request $request)
    {
        $validData = $this->validate($request, [
            'role' => 'required'
        ]);
        $roles = Role::query()->create([
            'role' => $validData['role'],
        ]);
        return response(['data' => $roles, 'status' => 'success']);
    }

    public function showUser(Request $request)
    {
        $validData = $this->validate($request, [
            'api_token' => 'required'
        ]);
        if (User::query()->where('api_token', $validData['api_token'])->count() == 1) {
            $user = User::all();
            return response(['data' => $user, 'status' => 'success']);
        }
    }

    public function userDetail(Request $request)
    {
        $validData = $this->validate($request, [
            'user_id' => 'required',
            'api_token' => 'required'
        ]);
        if (User::query()->where('api_token', $validData['api_token'])->count() == 1) {
            $user = User::query()->where('user_id', $validData['user_id'])->get();
            $vlan = Vlan::query()->where('user_id', $validData['user_id'])->get();
            $up = upRelation::query()->where('user_id', $validData['user_id'])->where('isActive', '=', '1')->get();
            $plan = Plan::query()->where('plan_id', $up[0]->plan_id)->get();
            $payment = Payment::query()->where('user_id', $validData['user_id'])->get();
            return response(['data' => compact('user', 'vlan', 'plan', 'payment')]);
        }
    }

    public function addPlans(Request $request)
    {
        $v = new Verta();
        $validData = $this->validate($request, [
            'code' => 'required',
            'price' => 'required',
            'trunkUnit' => 'required',
            'trunk' => 'required',
            'speed' => 'required',
            'category' => 'required',
            'speedUnit' => 'required',
            'priceUnit' => 'required',
            'api_token' => 'required',
        ]);
        if (User::query()->where('api_token', $validData['api_token'])->count() == 1) {
            $add = Plan::query()->create([
                'code' => $validData['code'],
                'price' => $validData['price'],
                'trunk' => $validData['trunk'],
                'trunkUnit' => $validData['trunkUnit'],
                'speed' => $validData["speed"],
                'category' => $validData['category'],
                'speedUnit' => $validData['speedUnit'],
                'priceUnit' => $validData["priceUnit"],
                'createIran' => $v->formatDatetime(),
                'lastUpdate' => $v->formatDatetime(),
            ]);
            return response(['data' => $add, 'status' => 'success']);
        }
    }

    public function addExtra(Request $request)
    {
        $v = new Verta();
        $validData = $this->validate($request, [
            'code' => 'required',
            'price' => 'required',
            'volume' => 'required',
            'volumeUnit' => 'required',
            'priceUnit' => 'required',
            'api_token' => 'required'
        ]);
        if (User::query()->where('api_token', $validData['api_token'])->count() == 1) {
            $add = Extra::query()->create([
                'code' => $validData['code'],
                'price' => $validData['price'],
                'volume' => $validData['volume'],
                'volumeUnit' => $validData['volumeUnit'],
                'priceUnit' => $validData["priceUnit"],
                'createIran' => $v->formatDatetime(),
                'lastUpdate' => $v->formatDatetime(),
            ]);
            return response(['data' => $add, 'status' => 'success']);
        }

    }

    public function pay(Zarinpal $zarinpal, Request $request)
    {
        $v = new Verta();
        $validData = $this->validate($request, [
            'api_token' => 'required',
            'id' => 'required',
            'status' => 'required'
        ]);
        $user = User::query()->where('api_token', $validData['api_token'])->get();
        date_default_timezone_set('Asia/Tehran');
            $orderId=Str::random(12);
        if ($validData['status'] == '1' || $validData['status'] == '2') {
            $plan = Plan::query()->where('plan_id', $validData['id'])->get();
            $pay = Payment::query()->insertGetId([
                'price' => $plan[0]->price,
                'user_id' => $user[0]->user_id,
                'description' => 'for buying plan',
                'createPay' => $v->formatDatetime(),
                'orderId'=>$orderId
            ]);
            $upRelation=upRelation::query()->create([
                'user_id'=>$pay[0]->user_id,
                'plan_id'=>$plan[0]->plan_id,
                'createUp'=>Carbon::now(),
                'isActive'=>'2',
                'orderId'=>$orderId
            ]);
            $payment = [
                'CallbackURL' => 'http://127.0.0.1:8000/api/v1/verifyPay', // Required
                'Amount' => $plan[0]->price,                    // Required
                'Description' => 'for buying plan',   // Required
                'Email' => $user[0]->email,    // Optional
                'Mobile' => $user[0]->phone            // Optional
            ];
            $response = $zarinpal->request($payment);
            if ($response['Status'] === 100) {
                $payUpdate = Payment::find($pay);
                $payUpdate->Authority = $response['Authority'];
                $payUpdate->save();
                $authority = $response['Authority'];
                return $zarinpal->redirect($authority);
            }
            return 'Error,
    Status: ' . $response['Status'] . ',
    Message: ' . $response['Message'];
        } elseif ($validData['status'] == '3') {
            $extra = Extra::query()->where('extra_id', $validData['id'])->get();
            $pay = Payment::query()->insertGetId([
                'price' => $extra[0]->price,
                'user_id' => $user[0]->user_id,
                'description' => 'for buying extra',
                'createPay' => $v->formatDatetime(),
                'orderId'=>$orderId

            ]);
            $ueRelation=ueRelation::query()->create([
                'user_id'=>$pay[0]->user_id,
                'extra_id'=>$extra[0]->extra_id,
                'consume'=>'0',
                'createUE'=>Carbon::now(),
                'isActive'=>'2',
                'orderId'=>$orderId
            ]);
            $payment = [
                'CallbackURL' => route('verifyPay'), // Required
                'Amount' => $extra[0]->price,                    // Required
                'Description' => 'for buying extra',   // Required
                'Email' => $user[0]->email,    // Optional
                'Mobile' => $user[0]->phone            // Optional
            ];
            $response = $zarinpal->request($payment);
            if ($response['Status'] === 100) {
                $payUpdate = Payment::find($pay);
                $payUpdate->Authority = $response['Authority'];
                $payUpdate->save();
                $authority = $response['Authority'];
                return $zarinpal->redirect($authority);
            }
            return 'Error,
    Status: ' . $response['Status'] . ',
    Message: ' . $response['Message'];
        }
    }

    public function verifyPay(Zarinpal $zarinpal)
    {
        date_default_timezone_set('Asia/Tehran');
        $pay = Payment::query()->where('Authority', $_GET["Authority"])->get();
        $plan=Plan::query()->where('price',$pay[0]->price)->get();
        $payment = [
            'Authority' => $_GET['Authority'],
            'Status' => $_GET['Status'],
            'Amount' => $pay[0]->price
        ];
        $response = $zarinpal->verify($payment);
        if ($response['Status'] === 100) {
            $payUpdate = Payment::find($pay[0]->pay_id);
            $payUpdate->RefID = $response["RefID"];
            $payUpdate->Message = $response['Message'];
            $payUpdate->$response["Status"];
            $payUpdate->save();
            if ($pay[0]->description=='for buying extra'){
                $ueRelation=ueRelation::query()
                    ->where('user_id',$pay[0]->user_id)
                    ->where('plan_id',$plan[0]->plan_id)
                    ->where('isActive','=','2')->get();
                $update=ueRelation::find($ueRelation[0]->ue_id);
                $update->isActive=1;
                $update->save();
            }
            if ($pay[0]->description=='for buying plan'){
                $upRelation=upRelation::query()
                    ->where('user_id',$pay[0]->user_id)
                    ->where('plan_id',$plan[0]->plan_id)
                    ->where('isActive','=','2')->get();
                if (upRelation::query()->where('user_id',$pay[0]->user_id)->where('isActive','=','1')->count()==1){
                    $update=upRelation::find($upRelation[0]->up_id);
                    $update->isActive=3;
                    $update->save();
                }else{
                    if ($plan[0]->category==1){
                        $update=upRelation::find($upRelation[0]->up_id);
                        $update->isActive=1;
                        $update->extension=Carbon::now()->addMonth();
                        $update->save();
                    }elseif ($plan[0]->category==3){
                        $update=upRelation::find($upRelation[0]->up_id);
                        $update->isActive=1;
                        $update->extension=Carbon::now()->addMonths(3);
                        $update->save();
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
                    }

                }

            }
            return 'Payment was successful,
        RefID: ' . $response['RefID'] . ',
        Message: ' . $response['Message'];
        }
        return 'Error,
    Status: ' . $response['Status'] . ',
    Message: ' . $response['Message'];
    }

    public function showPay(Request $request)
    {
        $v = new Verta();
        $arrays = array();
        $array = array();
        $validData = $this->validate($request, [
            'api_token' => 'required'
        ]);
        if (User::query()->where('api_token', $validData['api_token'])->count() == 1) {
            $payment = Payment::all();
            foreach ($payment as $item) {
                $array["pay_id"] = $item->pay_id;
                $array["price"] = $item->price;
                $array["Status"] = $item->Status;
                $array["Authority"] = $item->Authority;
                $array["Message"] = $item->Message;
                $array["RefID"] = $item->RefID;
                $array["createPay"] = $item->createPay;
                $user = User::query()->where('user_id', $item->user_id)->get();
                foreach ($user as $value) {
                    $array["username"] = $value->username;
                }
                $up = upRelation::query()->where('user_id', $item->user_id)->where('isActive', '=', '1')->get();
                $plan = Plan::query()->where('plan_id', $up[0]->user_id)->get();
                $array["code"] = $plan[0]->code;
                $arrays[] = $array;
            }
            return response(['data' => $arrays, 'status' => 'success']);
        }
    }

    public function buy(Request $request)
    {
        $validData = $this->validate($request, [
            'status' => 'required',
            'api_token' => 'required'
        ]);
        $array = array();
        $arrays = array();
        $user = User::query()->where('api_token', $validData['api_token'])->get();
        if ($validData['status'] == '1') {
            $relation = upRelation::query()->where('isActive', '=', '1')->where('user_id', $user[0]->user_id)->get();
            if (count($relation) == '1') {
                $array = $this->getArr($relation[0], $array);
                $arrays[] = $array;
                return response(['data' => $array, 'status' => 'success']);
            } elseif (count($relation) == '0') {
                $relationLast = upRelation::query()->where('user_id', $user[0]->user_id)->latest();
                $array = $this->getArr($relationLast[0], $array);
                array_push($arrays, $array);
                return response(['data' => $array, 'status' => 'success']);
            }
        } elseif ($validData['status'] == '2') {
            $plans = Plan::all();
            foreach ($plans as $plan) {
                $array["trunk"] = $plan->trunk;
                $array["speed"] = $plan->speed;
                $array["category"] = $plan->category;
                $array["price"] = $plan->price;
                $array["priceUnit"] = $plan->priceUnit;
                $array["code"] = $plan->code;
                $array["trunkUnit"] = $plan->trunkUnit;
                $array["speedUnit"] = $plan->speedUnit;
                $array["plan_id"] = $plan->plan_id;
                $arrays[] = $array;
            }
            return response(['data' => $arrays, 'status' => 'success']);

        } elseif ($validData['status'] == '3') {
            $extras = Extra::all();
            foreach ($extras as $extra) {
                $array["volume"] = $extra->volume;
                $array["volumeUnit"] = $extra->volumeUnit;
                $array["price"] = $extra->price;
                $array["priceUnit"] = $extra->priceUnit;
                $array["code"] = $extra->code;
                $array["extra_id"] = $extra->extra_id;
                $arrays[] = $array;
            }
            return response(['data' => $arrays, 'status' => 'success']);
        }

    }

    public function userDashboard(Request $request)
    {
        $validData = $this->validate($request, [
            'api_token' => 'required'
        ]);
        $user = User::query()->where('api_token', $validData['api_token'])->get();
        $upRelation=upRelation::query()->where('user_id',$user[0]->user_id)->where('isActive','=','1')->get();
        if (count($upRelation)==1){
            $plan=Plan::query()->where('plan_id',$upRelation[0]->plan_id)->get();
        }else{
            $plan='0';
        }
        $ueRelation=ueRelation::query()->where('user_id',$user[0]->user_id)->where('isActive','=','1')->orWhere('isActive','=','2')->get();
        if (count($ueRelation)==1){
            $extra=Extra::query()->where('extra_id',$ueRelation[0]->extra_id)->get();
        }else{
            $extra='0';
        }
        $vlan=Vlan::query()->where('user_id',$user[0]->user_id)->get();
        foreach ($vlan as $item){
            // todo: change numbers to constant variable
            $download = number_format($item["rx-byte"] / 1073741824, 3);
            $upload = number_format($item["tx-byte"] / 1073741824, 3);
            $all = $download + $upload;

        }
        return response(['data'=>compact('plan','extra','upRelation','ueRelation','download','user','upload','all','vlan'),'stats'=>'success']);
    }

    public function planDetail(Request $request)
    {
        // todo: logics in controller are weired but validation is awesome :)
        $validData = $this->validate($request, [
            'api_token' => 'required',
            'plan_id' => 'required'
        ]);
        if (User::query()->where('api_token', $validData['api_token'])->count() == 1) {
            $plan = Plan::query()->where('plan_id', $validData['plan_id'])->get();
            return response(['data' => $plan, 'stats' => 'success']);
        }
    }

    public function extraDetail(Request $request)
    {
        $validData = $this->validate($request, [
            'api_token' => 'required',
            'extra_id' => 'required'
        ]);
        if (User::query()->where('api_token', $validData['api_token'])->count() == 1) {
            $extra = Extra::query()->where('extra_id', $validData['extra_id'])->get();
            return response(['data' => $extra, 'stats' => 'success']);
        }
    }

    public function myServices(Request $request){
        $validData=$this->validate($request,[
            'api_token'=>'required'
        ]);
        $array=array();
        $user=User::query()->where('api_token',$validData['api_token'])->get();
        $vlan=Vlan::query()->where('user_id',$user[0]->user_id)->get();
        $array["vlanName"]=$vlan[0]->vlanName;
        $upRelation=upRelation::query()->where('user_id',$user[0]->user_id)->latest()->get();
        $array["expire"]=$upRelation[0]->Extension;
        if ($upRelation[0]->dateExpire==1){
            $array["timeLeft"]='منقضی شده';
        }elseif ($upRelation[0]->dateExpire==0){
            $array["timeLeft"]=Carbon::now()->diffInDays($upRelation[0]->Extension);
        }
        // todo: oh it could be even better
        $statusOptions = [
            1 => 'آنلاین',
            2 => 'آفلاین',
            0 => 'خاموش شده',
        ];
        $statusValue = $upRelation[0]->isActive;
        $array['status'] = $statusOptions[$statusValue];
        return response(['data'=>$array,'stats'=>'success']);
    }

    public function planBills(Request $request){
        $validData=$this->validate($request,[
           'api_token'=>'required'
        ]);
        $array=array();
        $arrays=array();
        $user=User::query()->where('api_token',$validData['api_token'])->get();
        $payments=Payment::query()->where('user_id',$user[0]->user_id)->where('description','for Buying Plan')->where('Status','=','100')->get();
        foreach ($payments as $payment ){
            $array["price"]=number_format($payment->price);
            $array["createPay"]=$payment->createPay;
            $array["orderId"]=$payment->orderId;
            $upRelation=upRelation::query()->where('orderId',$payment->orderId)->get();
            $array["Extension"]=$upRelation[0]->Extension;
            $plan=Plan::query()->where('plan_id',$upRelation[0]->plan_id)->get();
            $array["code"]=$plan[0]->code;
            $array["speed"]=$plan[0]->speed;
            $array["trunk"]=$plan[0]->trunk;
            $array["status"]="موفقیت آمیز";
            if ($plan[0]->category==1){
                $array["category"]='یک ماهه';
            }elseif ($plan[0]->category==3){
                $array["category"]='سه ماهه';
            }
            $arrays[] = $array;
        }
        return response(['data'=>$arrays,'stats'=>'success']);
    }

    public function extraBills(Request $request){
        $validData=$this->validate($request,[
            'api_token'=>'required'
        ]);
        $array=array();
        $arrays=array();
        $user=User::query()->where('user_id',$validData['user_id'])->get();
        $payments=Payment::query()->where('user_id',$user[0]->user_id)->where('description','for Buying extra')->where('Status','=','100')->get();
        foreach ($payments as $payment ){
            $array["price"]=number_format($payment->price);
            $array["createPay"]=$payment->createPay;
            $array["orderId"]=$payment->orderId;
            $ueRelation=ueRelation::query()->where('orderId',$payment->orderId)->get();
            $array["Extension"]=$ueRelation[0]->Extension;
            $extra=Extra::query()->where('extra_id',$ueRelation[0]->extra_id)->get();
            $array["code"]=$extra[0]->code;
            $array["volume"]=$extra[0]->volume;
            $array["status"]="موفقیت آمیز";
            $arrays[] = $array;
        }
        return response(['data'=>$arrays,'stats'=>'success']);
    }
    public function jobQueryDaily(){
        date_default_timezone_set('Asia/Tehran');
        $v=new Verta();
            $vlans=Vlan::all();
            foreach ($vlans as $vlan){
                try {
                    $query =
                        (new Query('/interface/print'))
                            ->where('name', $vlan->vlanName)
                        ->where('type','vlan')
                    ;
                } catch (QueryException $e) {
                    return $e;
                }
                try {
                    $response = $this->client->query($query)->read();
                    foreach ($response as $item){
                        $avg=Daily::query()->where('vlan_id',$vlan->vlan_id)->get();
                        $dayTrunk=0;
                        $downloadTrunk=0;
                        $uploadTrunk=0;
                        foreach ($avg as $value){
                            $downloadDay=$value["rx-byte"];
                            $uploadDay=$value["tx-byte"] ;
                            $downloadTrunk=$downloadTrunk+$downloadDay;
                            $uploadTrunk=$uploadTrunk+$uploadDay;
                        }
                        global $daily;
                        // todo: what a hardcode, think one day you want to change one of them ;)
                        $download= number_format($item["rx-byte"] / 1073741824, 3);
                        $upload= number_format($item["tx-byte"] / 1073741824, 3);
                        $up=upRelation::query()->where('user_id',$vlan->user_id)->where('isActive','=','1')->get();
                        $ue=ueRelation::query()->where('user_id',$vlan->user_id)->where('isActive','=','1')->get();
                        if (count($up)==1){
                            $daily=Daily::query()->insertGetId([
                                'download'=>($download)-($downloadTrunk),
                                'upload'=>($upload)-($uploadTrunk),
                                'vlan_id'=>$vlan->vlan_id,
                                'user_id'=>$vlan->user_id,
                                'date'=>$v->formatDate(),
                                'up_id'=>$up[0]->up_id,
                            ]);
                        }elseif (count($ue)==1){
                            $daily=Daily::query()->insertGetId([
                                'download'=>($download)-$downloadTrunk,
                                'upload'=>($upload)-($uploadTrunk),
                                'vlan_id'=>$vlan->vlan_id,
                                'user_id'=>$vlan->user_id,
                                'date'=>$v->formatDate(),
                                'ue_id'=>$ue[0]->ue_id,
                            ]);
                        }
                        $trunk=$dayTrunk/count($avg);
                        $average=Average::query()->create([
                            'avg'=>$trunk,
                            'vlan_id'=>$vlan->vlan_id,
                            'user_id'=>$vlan->user_id,
                            'day_id'=>$daily
                        ]);
                    }
                } catch (ClientException|QueryException $e) {
                    return $e;
                }
            }
        return response(['data'=>'success','stats'=>'success']);
    }
    public function showDaily(Request $request){
        $validData=$this->validate($request,[
           'api_token'=>'required'
        ]);
        $user=User::query()->where('api_token',$validData['api_token'])->get();
        $daily=Daily::query()->where('user_id',$user[0]->user_id)->get();
        $avg=Average::query()->where('user_id',$user[0]->user_id)->get();
        return response(['data'=>compact('daily','avg'),'stats'=>'success']);
    }

    // why ???
    public function jobQueryTenMinute(){
        $vlans=Vlan::all();
        foreach ($vlans as $vlan) {
                $up = upRelation::query()->where('user_id', $vlan->user_id)->where('isActive', '=', '1')->get();
                if (count($up) == 1) {
                    $query =
                        (new Query('/interface/print'))
                            ->where('name', $vlan->vlanName)
                            ->where('type','vlan')
                    ;
                    $response = $this->client->query($query)->read();
                    foreach ($response as $value) {
                        $download = number_format($value["rx-byte"] / 1073741824, 3);
                        $upload = number_format($vlan["tx-byte"] / 1073741824, 3);
                        $all = $download + $upload;
                        $plan = Plan::query()->where('plan_id', $up[0]->plan_id)->get();
                        $diff = $plan[0]->trunk - $all;
                        if ($diff <= 0) {
                            try {
                                $query =
                                    (new Query('/interface/disable'))
                                        ->where('name', $vlan->vlanName)
                                        ->where('type', 'vlan');
                            } catch (QueryException $e) {
                                return $e;
                            }
                            try {
                                $response = $this->client->query($query)->read();
                                try {
                                    $client = new SoapClient("http://188.0.240.110/class/sms/wsdlservice/server.php?wsdl");
                                    $user = "09125013080";
                                    $pass = "cxuhg88565";
                                    $fromNum = "5000125475";
                                    $toNum = array("09378627222");
                                    $pattern_code = "cp9h3cf042";
                                    $input_data = array(
                                        "name" => $user[0]->username,
                                    );
                                    $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $input_data);
                                } catch (SoapFault $ex) {
                                    return $ex->faultstring;
                                }
                            } catch (ClientException|QueryException $e) {
                                return $e;
                            }
                        } elseif ($diff < 10) {
                            try {
                                $client = new SoapClient("http://188.0.240.110/class/sms/wsdlservice/server.php?wsdl");
                                $user = "09125013080";
                                $pass = "cxuhg88565";
                                $fromNum = "5000125475";
                                $toNum = array("09378627222");
                                $pattern_code = "y3zx242qk5";
                                $input_data = array(
                                    "name" => $user[0]->username,
                                );
                                $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $input_data);
                            } catch (SoapFault $ex) {
                                return $ex->faultstring;
                            }
                        }
                    }
                }
            $query2 =
                (new Query('/interface/print'))
                    ->where('name', $vlan->vlanName)
                    ->where('type','vlan')
            ;
            $response2 = $this->client->query($query2)->read();
            foreach ($response2 as $static){
                $vlanUpdate=Vlan::find($vlan->vlan_id);
                $vlanUpdate->rxByte = $static["rx-byte"];
                $vlanUpdate->txByte = $static['tx-byte'];
                $vlanUpdate->disabled = $static['disabled'];
                $vlanUpdate->running = $static['running'];
                $vlanUpdate->save();
            }
            #trunk
        }
        return response(['data'=>'success','stats'=>'ok']);
    }
    public function jobQueryTime(){
        $vlans=Vlan::all();
        foreach ($vlans as $vlan) {
            $up = upRelation::query()->where('user_id', $vlan->user_id)->where('isActive', '=', '1')->get();
            if (count($up) == 1) {
                $time = Carbon::now();
                $total = new Carbon();
                $detail = strtotime($up[0]->Extension);
                $total->timestamp($detail);
                $day = $total->diffInDays($time);
                if ($day == 0 || $day < 0) {
                    try {
                        $query =
                            (new Query('/interface/enable'))
                                ->where('name', $vlan->vlanName)
                                ->where('type', 'vlan');
                    } catch (QueryException $e) {
                        return $e;
                    }
                    try {
                        $response = $this->client->query($query)->read();
                        try {
                            $client = new SoapClient("http://188.0.240.110/class/sms/wsdlservice/server.php?wsdl");
                            $user = "09125013080";
                            $pass = "cxuhg88565";
                            $fromNum = "5000125475";
                            $toNum = array("09378627222");
                            $pattern_code = "6oevolzp30";
                            $input_data = array(
                                "name" => $user[0]->username,
                            );
                            $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $input_data);
                        } catch (SoapFault $ex) {
                            return $ex->faultstring;
                        }
                    } catch (ClientException|QueryException $e) {
                        return $e;
                    }
                } elseif ($day == 1) {
                    try {
                        $client = new SoapClient("http://188.0.240.110/class/sms/wsdlservice/server.php?wsdl");
                        $user = "09125013080";
                        $pass = "cxuhg88565";
                        $fromNum = "5000125475";
                        $toNum = array("09378627222");
                        $pattern_code = "uqfhfscv4f";
                        $input_data = array(
                            "name" => $user[0]->username,
                        );
                        $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $input_data);
                    } catch (SoapFault $ex) {
                        return $ex->faultstring;
                    }
                } elseif ($day == 3) {
                    try {
                        $client = new SoapClient("http://188.0.240.110/class/sms/wsdlservice/server.php?wsdl");
                        $user = "09125013080";
                        $pass = "cxuhg88565";
                        $fromNum = "5000125475";
                        $toNum = array("09378627222");
                        $pattern_code = "uqfhfscv4f";
                        $input_data = array(
                            "name" => $user[0]->username,
                        );
                        $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $input_data);
                    } catch (SoapFault $ex) {
                        return $ex->faultstring;
                    }
                }
            }
        }
    }
    public function showSuggestion(Request $request){
        $validData=$this->validate($request,[
           'api_token'=>'required'
        ]);
        $user=User::query()->where('api_token',$validData['api_token'])->get();
        $suggestion=Suggestions::query()->where('user_id',$user[0]->user_id)->get();
        return response(['data'=>$suggestion,'stats'=>'ok']);
    }

    /**
     * @param $relationLast
     * @param array $result
     * @return array
     */
    public function getArr($relationLast, array $result): array
    {
        $plans = Plan::query()->where('plan_id', $relationLast->plan_id)->get();
        // todo: what about $plans[0]->toArray()?
        $result["trunk"] = $plans[0]->trunk;
        $result["speed"] = $plans[0]->speed;
        $result["category"] = $plans[0]->category;
        $result["price"] = $plans[0]->price;
        $result["priceUnit"] = $plans[0]->priceUnit;
        $result["code"] = $plans[0]->code;
        $result["trunkUnit"] = $plans[0]->trunkUnit;
        $result["speedUnit"] = $plans[0]->speedUnit;
        $result["plan_id"] = $plans[0]->plan_id;
        return $result;
    }

}
