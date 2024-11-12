<?php

namespace App\Http\Controllers;
use DateTime;
use DB;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class WebController extends Controller
{
    function setName($first, $last)
    {
        mb_internal_encoding('UTF-8');
        $setname = mb_substr($first, 1);
        $setlast = mb_substr($last, 1);
        if (str_contains($setname, '\\')) {
            $setname = explode("\\", $setname);
            $setname = $setname[0];
        }
        $name = $setname . " " . $setlast;

        return $name;
    }
    function setAge($dateInput)
    {
        $date = new DateTime($dateInput);
        $now = new DateTime();
        $interval = $now->diff($date);
        $output = $interval->y . ' ปี ' . $interval->m . ' เดือน';

        return $output;
    }
    function setRight($code)
    {
        $config = DB::connection('SSB')->table("DNSYSCONFIG")->where('CtrlCode', '42086')->where('Code', $code)->first();
        $text = $code;
        if ($config !== null) {
            mb_internal_encoding('UTF-8');
            $text = mb_substr($config->LocalName, 1);
        }

        return $text;
    }
    function getWard()
    {
        $wardArr = ["W06","W07","W09","W10","W11","W12","W14","W15","W15B","W16","W16B","W17B"];
        $ward = DB::connection('SSB')
            ->table('DNSYSCONFIG')
            ->where('CtrlCode', '42201')
            ->whereIn('Code', $wardArr)
            ->select('Code','EnglishName')
            ->orderBy('Code','ASC')
            ->get();
        foreach( $ward as $row ) {
            $row->name = mb_substr($row->EnglishName, 1);
        }

        return $ward;
    }
    function getData($filter)
    {
        mb_internal_encoding('UTF-8');
        $data = DB::connection(name: 'SSB')
            ->table('HNIPD_MASTER')
            ->leftjoin('HNPAT_INFO', 'HNIPD_MASTER.HN', '=', 'HNPAT_INFO.HN')
            ->leftjoin('HNPAT_NAME', 'HNPAT_INFO.HN', '=', 'HNPAT_NAME.HN')
            ->leftjoin('HNIPD_RIGHT', 'HNIPD_MASTER.AN', '=', 'HNIPD_RIGHT.AN')
            ->where('HNPAT_NAME.SuffixSmall', 0)
            ->whereNull('DischargeDateTime')
            ->where(function ($query) use ($filter) {
                if($filter->date !== null){
                    $query->whereDate('HNIPD_MASTER.AdmDateTime', $filter->date);
                }
                if($filter->ward !== null){
                    $query->where('HNIPD_MASTER.ActiveWard', $filter->ward);
                }
                if($filter->hn !== null){
                    $query->where('HNIPD_MASTER.HN', $filter->hn);
                }
            })
            ->select(
                'HNIPD_MASTER.AdmDateTime',
                'HNIPD_MASTER.AN',
                'HNIPD_MASTER.HN',
                'HNPAT_INFO.Gender',
                'HNPAT_INFO.BirthDateTime',
                'HNPAT_NAME.FirstName',
                'HNPAT_NAME.LastName',
                'HNPAT_INFO.LineID',
                'HNIPD_MASTER.AdmWard',
                'HNIPD_MASTER.AdmHNBedNo',
                'HNIPD_MASTER.ActiveWard',
                'HNIPD_MASTER.ActiveHNBedNo',
                'HNIPD_RIGHT.RightCode',
                'HNIPD_RIGHT.ARCode',
                // 'HNIPD_MASTER.DefaultRightCode',
            )
            ->orderBy('HNIPD_MASTER.ActiveWard','asc')
            ->orderBy('HNIPD_MASTER.HN','asc')
            ->orderBy('HNIPD_RIGHT.ARCode','asc')
            ->get();

        $output = [];
        $HNarray = [];
        $index = 0;
        $ARdata = DB::connection('BACK')->table('ARMASTER')->get();
        foreach($data as $item){
            $getAR = collect($ARdata)->where('ARCode', $item->ARCode)->first();
            $getTransaction = Transaction::where('hn', $item->HN)->first();
            if($getTransaction !== null && $item->LineID == null){
                if($getTransaction->status == 'cancel'){
                    $setstatus = 'Denied';
                }elseif($getTransaction->status == 'other'){
                    $setstatus = 'Other';
                }
                $memo = $getTransaction->memo;
                $memo_date = $getTransaction->updated_at->format('d/m/y');
            }else{
                $setstatus = ($item->LineID == null) ? false : true;
                $memo = null;
                $memo_date = null;
            }
            if(!in_array($item->HN, $HNarray)){
                $index = $index+ 1;
                $HNarray[] = $item->HN;
                $output[] = [
                    'type' => 1,
                    'index' => $index,
                    'AN' => $item->AN,
                    'HN' => $item->HN,
                    'Bed' => $item->ActiveHNBedNo,
                    'Gender' => ($item->Gender == '1') ?'หญิง':'ชาย',
                    'Name' => $this->setName($item->FirstName, $item->LastName),
                    'Age' => $this->setAge($item->BirthDateTime),
                    'Ward' => $item->ActiveWard,
                    'ARcode' => ($getAR !== null) ? mb_substr($getAR->LocalName, 1) : null,
                    'Right' => $this->setRight($item->RightCode),
                    'Line' => $setstatus,
                    'Memo' => $memo,
                    'MemoDate' => $memo_date
                ];
            }else{
                $output[] = [
                    'type' => 2,
                    'ARcode' => ($getAR !== null) ? mb_substr($getAR->LocalName, 1) : null,
                    'Right' => $this->setRight($item->RightCode),
                    'Line' => $setstatus,
                ];
            }
        }
        if(count($output) == 0 && $filter->hn !== null){
            $data = DB::connection('SSB')
                ->table('HNPAT_INFO')
                ->leftjoin('HNPAT_NAME', 'HNPAT_INFO.HN', '=', 'HNPAT_NAME.HN')
                ->where('HNPAT_NAME.SuffixSmall', 0)
                ->where('HNPAT_INFO.HN' , $filter->hn)
                ->select(
                    'HNPAT_INFO.HN',
                    'HNPAT_INFO.Gender',
                    'HNPAT_INFO.BirthDateTime',
                    'HNPAT_INFO.LineID',
                    'HNPAT_NAME.FirstName',
                    'HNPAT_NAME.LastName'
                    )
                ->first();
            if($data !== null){
                $HNarray[] = $data->HN;
                $getTransaction = Transaction::where('hn', $data->HN)->first();
                if($getTransaction !== null && $data->LineID == null){
                    if($getTransaction->status == 'cancel'){
                        $setstatus = 'Denied';
                    }elseif($getTransaction->status == 'other'){
                        $setstatus = 'Other';
                    }
                    $memo = $getTransaction->memo;
                    $memo_date = $getTransaction->updated_at->format('d/m/y');
                }else{
                    $setstatus = ($data->LineID == null) ? false : true;
                    $memo = null;
                    $memo_date = null;
                }
                $output[] = [
                    'type' => 1,
                    'index' => 1,
                    'AN' => null,
                    'HN' => $data->HN,
                    'Bed' => null,
                    'Gender' => ($data->Gender == '1') ?'หญิง':'ชาย',
                    'Name' => $this->setName($data->FirstName, $data->LastName),
                    'Age' => $this->setAge($data->BirthDateTime),
                    'Ward' => null,
                    'Right' => null,
                    'ARcode' => null,
                    'Line' => $setstatus,
                    'Memo' => $memo,
                    'MemoDate' => $memo_date
                ];
            }
        }
        if($filter->status !== null){
            if($filter->status == 'false'){
                foreach ($output as $key => $value) {
                    if($value['Line'] !== false){
                        unset($output[$key]);
                    }
                }
            }
            elseif($filter->status == 'true'){
                foreach ($output as $key => $value) {
                    if($value['Line'] !== true){
                        unset($output[$key]);
                    }
                }
            }
            elseif($filter->status == 'Denied'){
                foreach ($output as $key => $value) {
                    if($value['Line'] !== 'Denied'){
                        unset($output[$key]);
                    }
                }
            }
            elseif($filter->status == 'Other'){
                foreach ($output as $key => $value) {
                    if($value['Line'] !== 'Other'){
                        unset($output[$key]);
                    }
                }
            }
        }

        return $output;
    }
    function Auth()
    {

        return view('login');
    }
    function AuthCheck(Request $request)
    {
        $userid = $request->userid;
        $password = $request->password;
        $response = Http::withHeaders([
            'token' => env('API_TOKEN_STAFF')
        ])->post('http://172.20.1.12/dbstaff/api/auth', [
            'userid' => $userid,
            'password' => $password,
        ])->object();
        if($response->status == 1){
            session(['userid' => $response->user->userid , 'name' => $response->user->name]);

            $user = User::firstOrCreate([
                'userid' => $response->user->userid,
                'name' => $response->user->name,
            ]);

            if (Auth::loginUsingId($user->id)) {

                return response()->json(['status' => 1 , 'text' => 'Authentication Success!'],200);
            }else{

                return response()->json(['status'=> 0,'text'=> 'Authentication Success , User not found!'],200);
            }
        }

        return response()->json(['status' => 0 , 'text' => 'Authentication Failed!'],200);
    }
    function main()
    {
        $filter = (object)[
            'date' => null,
            'ward' => '-',
            'hn' => null,
            'status' => null
        ];
        $output = $this->getData($filter);
        $ward = $this->getWard();

        return view('index', ['ward' => $ward, 'data' => $output, 'filter' => $filter]);
    }
    function filterData(Request $request)
    {
        $filter = (object)[
            'date' => $request->date,
            'ward' => $request->ward,
            'hn' => $request->hn,
            'status' => $request->status
        ];
        $output = $this->getData($filter);
        $ward = $this->getWard();

        return view('index', ['ward' => $ward, 'data' => $output, 'filter' => $filter]);
    }
    function denialData(Request $request)
    {
        $data = Transaction::where('hn', $request->hn)->first();
        if($data == null){
            $data = new Transaction;
        }
        $data->hn = $request->hn;
        $data->status = $request->status;
        $data->memo = $request->reason;
        $data->save();

        return response()->json(['status' =>'success','data'=> $data]);
    }

    function getList(Request $request)
    {
        $token = $request->header('token');
        if($token !== env('API_TOKEN')){
            return response()->json(['status'=> 'error','message'=> 'api token mismatch!']);
        }

        $filter = (object)[
            'date' => $request->date,
            'ward' => $request->ward,
            'hn' => $request->hn,
            'status' => $request->status
        ];
        $output = $this->getData($filter);
        $ward = $this->getWard();

        return response()->json(['status'=> 'success','list'=> $output, 'ward'=> $ward]);
    }
    function mainOutsite()
    {
        $filter = (object)[
            'date' => null,
            'ward' => '-',
            'hn' => null,
            'status' => null
        ];
        $response = Http::withHeaders([
            'token' =>  env('API_TOKEN'),
        ])->post( 'http://172.20.1.12/w_linecheck/api/getlist', ['date' => null , 'ward' => '-', 'hn' => null, 'status' => null])->json();
        $ward = [];
        foreach ($response['ward'] as $key => $value) {
            $ward[] = (object)[
                'Code' => $value['Code'],
                'name' => $value['name']
            ];
        }

        return view('index', ['ward' => $ward, 'data' => $response['list'], 'filter' => $filter]);
    }
    function mainOutfilter(Request $request)
    {
        $filter = (object)[
            'date' => $request->date,
            'ward' => $request->ward,
            'hn' => $request->hn,
            'status' => $request->status
        ];
        $response = Http::withHeaders([
            'token' =>  env('API_TOKEN'),
        ])->post( 'http://172.20.1.12/w_linecheck/api/getlist', ['date' => $filter->date , 'ward' => $filter->ward, 'hn' => $filter->hn, 'status' => $filter->status])->json();
        $ward = [];
        foreach ($response['ward'] as $key => $value) {
            $ward[] = (object)[
                'Code' => $value['Code'],
                'name' => $value['name']
            ];
        }

        return view('index', ['ward' => $ward, 'data' => $response['list'], 'filter' => $filter]);
    }
}
