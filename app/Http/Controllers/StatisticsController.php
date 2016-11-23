<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;

class StatisticsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {

        return view('statistics.index');
    }

    /**
     * returns a list with amount of contracts of each user
     * @param $role
     * @return array
     */
    public static function userContractsCount($role){

        $cont = new RestController();
        $result = $cont->getRequest('Contracts?$filter='.urlencode('Manager ne null and Manager/Active eq true and Manager/Roles/any(d:d/Role/Name eq \''.$role.'\') and Status eq \'Active\'').'&$expand=Manager($select=FullName,UserName,Active)');

        if($result instanceof View ){
            return [];
        }
        $stats = [];
        foreach($result->value as $k){
            if(isset($stats[$k->Manager->FullName])){
                $stats[$k->Manager->FullName]++;
            }else{
                $stats[$k->Manager->FullName] = 1;
            }
        }
        arsort($stats);
        return $stats;
    }


    /** meetings by month per year
     * @param $year
     * @return mixed
     */
    public function meetingsForTheYear($year = null){

        if($year == null){
            $start = Carbon::now()->startOfYear();
            $end   = Carbon::now();
        }else{
            $start = Carbon::create($year)->startOfYear();
            $end   = $start->endOfYear();
        }

        $months = getMonthListFromDates($start,$end);

        $cont = new RestController();
        foreach ($months as $k=>$val){
            $d = explode('-',$val);
            $month = Carbon::create($d[0],$d[1]);
            $start = $month->startOfMonth()->toAtomString();
            $end =   $month->endOfMonth()->toAtomString();
            $thisMonth = "(Created le ".$end.' and Created ge '.$start.')';
            $bookedLeads = $cont->getRequest('CalendarEvents?$select=ModelId&$filter=Model+eq+\'Lead\'+and+Booker_Id+ne+null+and+'.urlencode($thisMonth." and (EventType eq 'HealthCheck')")); // fuck this query

            if($bookedLeads instanceof View){
                $periods[$val]= 'error';
            }else{
                $periods[$val] = count($bookedLeads->value);
            }
        }
        if(Request::ajax()){
            return $periods;
        }else{
            return $periods;
        }
    }


    /**
     * Stats over Meet Booking calendar events,
     * @return View
     * @internal param null $userId
     */
    public function meetingsStatistics(){
        $params = Input::all();

        if(!isset($params['Booker_Id'])){
            $userId = Auth::user()->externalId;
        }else{
            if(!isAdmin()){
                $userId = Auth::user()->externalId;
            }else{
                $userId = $params['Booker_Id'] != '' ? $params['Booker_Id'] : Auth::user()->externalId;
            }
        }
        $cont = new RestController();
        // this month by default;
        $months = queryMonthPeriods("Created","Created");
        // this month is always the first key that comes from the function
        // array_values returns the first value, then we search the key for this value. Stupid but works
        if(isset($params['Created'])){
            $thisMonth = $params['Created'];
            $thisMonthName = $months[$params['Created']];
        }else{
            $thisMonthName = array_values($months)[0];
            $thisMonth = array_search($thisMonthName,$months);
        }
        $bookedLeads = $cont->getRequest('CalendarEvents?$select=Id,ModelId&$filter=Model+eq+\'Lead\'+and+Booker_Id+eq+\''.$userId.'\'+and+User_Id+ne+\''.$userId.'\'+and+'.urlencode('('.$thisMonth.") and (EventType eq 'HealthCheck')")); // fuck this query
        if($bookedLeads instanceof View){
            return $bookedLeads;
        }
        $aliasIds = array_map(function($val){return $val->ModelId;},$bookedLeads->value);
        JavaScriptFacade::put(['aliasIds'=>$aliasIds,'userId'=>$userId,'thisMonth'=>$thisMonth,'leadIds'=>$bookedLeads->value]);
        $bookers = UsersController::listByRoles(['Meet Booking']);
        $userName = UsersController::getUserNameById($userId);
        return view('statistics.meetings',compact('months','bookers','thisMonthName','thisMonth','months','userName','userId'));
    }

    function deadSellers(){

        $sellers = UsersController::listByRoles(['Sales']);

        $cont = new RestController();

        JavaScriptFacade::put(['sellers'=>$sellers]);

        $res = $cont->getRequest('Users?$orderby=FullName&$filter='.urlencode('Active eq false'));
        $userList= [];
        foreach($res->value as $user){
                $userList[ 'User_Id eq \'' . $user->Id . "'"] = $user->FullName;
        }

        $contractStatus = [
            "Status ne 'Suspended'"=>"Select",
            'Status eq \'Active\''=>Lang::get('labels.active'),
            'Status eq \'Standby\''=>Lang::get('labels.standby'),
            'Status eq \'Suspended\''=>Lang::get('labels.suspended'),
            'Status eq \'Completed\''=>Lang::get('labels.completed'),
            'Status eq \'Cancelled\''=>'Cancelled',
            'not ClientAlias/Invoice/any()'=>'No payment info.'
        ];
        return view('statistics.deadSellers',compact('userList','contractStatus','sellers'));
    }

    /**
     *
     * @return View
     */
    public function contractsValue($id=null){
        $params = Input::all();
        $cont = new RestController();
        // this month by default;
        $months = queryMonthPeriods("StartDate","EndDate",['separate'=>true]);
        // this month is always the first key that comes from the function
        // array_values returns the first value, then we search the key for this value. Stupid but works
        if(isset($params['Time'])){
            $thisMonth = $params['Time'];
            $thisMonthName = $months[$params['Time']];
            $times = explode(',',$thisMonth);
            $startDate = $times[0];
            $endDate = $times[1];
        }else{
            $thisMonthName = array_values($months)[0];
            $thisMonth = array_search($thisMonthName,$months);
            $times = explode(',',$thisMonth);
            $startDate = $times[0];
            $endDate = $times[1];
        }

        if($id == null) {
            $roles = ['Adwords', 'Seo'];
            $role = null;
            if (isset($params['Role']) && $params['Role'] != '') {
                $roles = [$roles[$params['Role']]];
                $role = $params['Role'];
            }
            $users = UsersController::listByRoles($roles,'all');
            $roles = ['Adwords', 'Seo']; // quick and dirty
            $stats = [];
            $stats['total'] = 0;
            foreach ($users as $id => $name) {
                $res = $cont->postRequest('ContractDailyStats/Summary', ['StartDate' => $startDate, 'EndDate' => $endDate, 'User_Id' => "$id"]);
                if (!$res instanceof View) {
                    $stats[$id] = $res->value;
                    $stats['total'] += $res->value;
                }
            }
            // Sort
            uasort($stats, function ($a, $b) {
                if ($a == $b) {
                    return 0;
                }
                return ($a < $b) ? 1 : -1;
            });
            return view('statistics.contractValues', compact('months', 'users', 'stats', 'role', 'roles', 'thisMonthName', 'thisMonth', 'months', 'userName'));
        } else {
            $stats = [];
            $res = $cont->getRequest('ContractDailyStats?$filter='.urlencode("DateOfStat le ".$endDate.' and DateOfStat ge '.$startDate).'&$select=DateOfStat,DailyValue&$expand=Contract($select=Id;$expand=Product($select=Name),ClientAlias($select=Name))');
            if($res instanceof View){
                return $res;
            }

            foreach ($res->value as $stat){
                $date = toDate($stat->DateOfStat);
                if(!isset($stats[$date])){
                    $stats[$date] = [];
                }
                array_push($stats[$date],$stat);
            }

            $dates = array_keys($stats);


            return view('statistics.contractValuesUser',compact('stats'));
        }
    }

    public function expectedPayments($month = null){

        $user = Request::input('user');

        if($month == null) {
            $months = next12months();
        }else{
            $month = date($month.'-01');
            $monthStart = date('Y-m-01',strtotime($month));
            $monthEnd   = date('Y-m-t',strtotime($month));
            $months[date('Y-n',strtotime($monthStart))]['start']= $monthStart;
            $months[date('Y-n',strtotime($monthEnd))]['end']= $monthEnd;
        }
        $cont = new RestController();

        $userfilter = '';
        if($user !== null && $user !== ''){
            $userfilter = " and User_Id eq '$user'";
        }

        foreach ($months as $k=>$val){
            $start = toIsoDateString($val['start']);
            $end =   endOfDay($val['end']);
            $test = $cont->getRequest('ClientAlias?$select=Id&'.
                '$filter='.urlencode("Draft/any(d:d/NoticeAccountant lt $end and d/NoticeAccountant gt $start) or Invoice/any(i:i/Created le $end and i/Created ge $start)").
                '&$expand='.
                'Draft('.
                    '$expand=DraftLine('.
                        '$select=Quantity,UnitPrice;'.
                            '$expand=Product($select=SalePrice)'.
                '),ClientAlias($select=Name)'.
                ';$select=Id,NoticeAccountant,User_Id;'.
                '$filter='.urlencode("NoticeAccountant le $end and NoticeAccountant ge $start".$userfilter.' and (Status ne \'Deleted\' and Status ne \'Invoice\')),').
            'Invoice($expand=ClientAlias($select=Name);$filter='.urlencode('(Status eq \'Created\' or Status eq \'Sent\' or Status eq \'Overdue\' or Status eq \'Reminder\') and Type eq \'Invoice\' and '.
                'Created le '.$end.' and Created ge '.$start.$userfilter.';$select=Id,Created,InvoiceNumber,NetAmount,User_Id)'
            ));

//            $result = $cont->getRequest('Drafts?$expand=DraftLine($expand=Product($select=SalePrice);$select=Id)&$select=Id,NoticeAccountant&$filter='.
//                urlencode('NoticeAccountant le '.toIsoDateString($val['end']).' and NoticeAccountant ge '.toIsoDateString($val['start'])));
            if($test instanceof View){
                $periods[$k]= 'error';
            }else{
                $stats[$k] = [];
                $stats[$k]['drafts'] = [];
                $stats[$k]['invoices'] = [];
                $stats[$k]['draftSum'] = 0;
                $stats[$k]['invoiceSum'] = 0;
                foreach ($test->value as $client){
                    if(!empty($client->Draft))   array_push($stats[$k]['drafts'],$client->Draft);
                    if(!empty($client->Invoice)) array_push($stats[$k]['invoices'],$client->Invoice);
                    $stats[$k]['draftSum']   += draftLinesSum($client->Draft);
                    $stats[$k]['invoiceSum'] += sumProperties($client->Invoice,'NetAmount');
                }

                $stats[$k]['drafts'] = array_flatten($stats[$k]['drafts']);
                $stats[$k]['invoices'] = array_flatten($stats[$k]['invoices']);
                $periods[$k] = $stats[$k];
            }
        }
        if(Request::ajax()){
            return $periods;
        }

        $sellers = UsersController::listByRoles(['Sales','Administrator']);
        $users = UsersController::usersList(false);
        return view('statistics.expectedPayments',compact('periods','months','month','users','user','sellers'));
    }

    public function clients(){

        $start = Carbon::create(2016,8,1);
        $end   = Carbon::now();

        $months = getMonthListFromDates($start,$end);
        $cont = new RestController();

        $periods =[];

        foreach($months as $k=>$val){
            $ym   = explode('-',$val);
            $year = $ym[0];
            $month = $ym[1];
            $result = $cont->getRequest('ClientDailyStats?$filter='.urlencode("year(DayOfStat) eq $year and month(DayOfStat) eq $month").'&$expand=ClientAlias($select=Name),Seller($select=FullName)');
            if($result instanceof View){
                $periods[$val]= 'error';
            }else{
                $periods[$val]['Lost']      = [];
                $periods[$val]['NewConfirmed'] = [];
                $periods[$val]['NewPayed']     = [];
                $periods[$val]['Winback']   = [];
                foreach ($result->value as $index=>$s){
                    if(isset($periods[$val][$s->State])){
                        array_push($periods[$val][$s->State],$s);
                    }
                }
            }
        }

        return view('statistics.clients',compact('months','periods'));
    }


    public function periodization(){

        return view('statistics.periodization');
    }

    public function clientsByType(){

        return view('statistics.by-type');
    }

    public function activeClients(){

        return view('statistics.activeClients');
    }



    public function optimizations($userId = null){
        $params = Input::all();
        $cont = new RestController();
        // this month by default;
        $months = queryMonthPeriods("StartDate","EndDate",['separate'=>true]);
        // this month is always the first key that comes from the function
        // array_values returns the first value, then we search the key for this value. Stupid but works
        if(isset($params['Time'])){
            $thisMonth = $params['Time'];
            $thisMonthName = $months[$params['Time']];
            $times = explode(',',$thisMonth);
            $startDate = $times[0];
            $endDate = $times[1];
        }else{
            $thisMonthName = array_values($months)[0];
            $thisMonth = array_search($thisMonthName,$months);
            $times = explode(',',$thisMonth);
            $startDate = $times[0];
            $endDate = $times[1];
        }

        $currentUser = UsersController::activeUserId();
        $role = null;
        $user = null;
        if($userId !== null){
            $users = [isAdmin() ? $userId : $currentUser => 'name'];
            $user = UsersController::getUserNameById($userId);
        }else{
            if(!isAdmin()){
                $users = [isAdmin() ? $userId : $currentUser => 'name'];
                $user = UsersController::getUserNameById($currentUser);
            }else {
                $roles = ['Adwords', 'Seo'];
                if (isset($params['Role']) && $params['Role'] != '') {
                    $roles = [$roles[$params['Role']]];
                    $role = $params['Role'];
                }
                $users = UsersController::listByRoles($roles);
            }
        }
        $roles = ['Adwords', 'Seo']; // quick and dirty

        $stats = [];
        $statsTotalMinutes= 0;
        $statsTotalOptimizations = 0;
        $statsAverageDaily = 0;
        $statsAverageTime = 0;
        foreach ($users as $id=>$name){
            $res = $cont->postRequest('Contracts/OptimizeStats',['StartDate'=>$startDate,'EndDate'=>$endDate,'User_Id'=>"$id"]);
            if(!$res instanceof View){
                $stats[$id]=$res;
                $statsTotalMinutes       += $res->TotalMinutesOnOptimize;
                $statsTotalOptimizations += $res->OptimizationsDone;
                $statsAverageDaily       += $res->OptimizationsDoneAverageDaily;
                $statsAverageTime        += $res->AverageOptimizeTime == 'NaN' ? 0 : $res->AverageOptimizeTime;
            }
        }
        // Sort
        uasort($stats, function ($a, $b) {
            if ($a->OptimizationsDone == $b->OptimizationsDone) {
                return 0;
            }
            return ($a->OptimizationsDone < $b->OptimizationsDone) ? 1 : -1;
        });

        return view('statistics.optimizations',
            compact('months','users','stats','role','roles','thisMonthName','thisMonth','months','userName',
                'statsTotalMinutes','statsTotalOptimizations','statsAverageDaily','statsAverageTime','user'));
    }

    public function sellersOverview($id = null){
        $period = Input::all('Period');
        $sellers = UsersController::listByRoles(['Sales']);
        JavaScriptFacade::put(['sellers'=>$sellers]);
        $months = past12months();
        $periods = array_keys($months);
        $selected = null;
        if(isset($period['Period'])){
            $selected = $period['Period'];
            $period = $periods[$period['Period']];// why did I do it like that......
        }else{
            $period = $periods[0];
        }
        JavaScriptFacade::put(['monthPeriod'=>$period]);

        return view('statistics.sellersOverview',compact('sellers','periods','period','selected'));
    }


    public function sellerScreen(){

        $period = Input::all('Period');
        $sellers = UsersController::listByRoles(['Sales']);
        JavaScriptFacade::put(['sellers'=>$sellers]);
        $months = past12months();
        $periods = array_keys($months);
        $selected = null;
        if(isset($period['Period'])){
            $selected = $period['Period'];
            $period = $periods[$period['Period']];// why did I do it like that......
        }else{
            $period = $periods[0];
        }
        JavaScriptFacade::put(['monthPeriod'=>$period]);

        return view('statistics.sellerScreen',compact('sellers','periods','period','selected'));
    }

    /**
     * returns the expected payments for a month and year and user, grouped day by day
     *
     * @param $year
     * @param $month
     * @param $userId
     * @return array
     */
    public static function expectedPaymentsByMonthAndUser($year,$month,$userId){

        $cont = new RestController();

        $result = $cont->getRequest('Drafts?$filter='.
            urlencode("year(NoticeAccountant) eq $year and month(NoticeAccountant) eq $month and User_Id eq '$userId' 
                and (Status eq 'None' or Status eq 'System') and Type eq 'Invoice'"
                ).'&$expand=DraftLine($select=UnitPrice,Quantity,Discount)&$select=NoticeAccountant');

        if($result instanceof View){
            return [];
        }

        $data = [];
        foreach ($result->value as $d){
            $date = Carbon::parse($d->NoticeAccountant);
            $day = $date->day;
            if(isset($data[$day])){
                $data[$day] += draftLinesSum([$d]);

            }else{
                $data[$day]=  draftLinesSum([$d]);
            }
        }

        return $data;
    }


    /**
     * @param null $id
     * @return View
     */
    function debtCollection($id = null){

        $cont = new RestController();
        $userQuery = $id == null ? "User_Id ne null": "User_Id eq '$id'";
        $result = $cont->getRequest(
            'Invoices?$filter='.urlencode("Type eq 'Invoice' and Status eq 'DebtCollection' and ".$userQuery).
            '&$select=Created,Due,NetAmount,Payed,Name,ClientAlias_Id,Id,InvoiceNumber,Status&$expand=User($select=FullName),ClientAlias($expand=User($select=FullName);$select=Name)&$orderby=Created+desc'
        );
        if($result instanceof View){
            return $result;
        }

        $grouped = [];
        //group them by invoice
        foreach ($result->value as $payment){
            if(Carbon::parse($payment->Due)->year < 2016 ) continue; // Fix until we update the invoice statuses

            if(isset($grouped[$payment->Id])){
                $grouped[$payment->Id]->NetValue += $payment->NetValue;
            }else{
                $grouped[$payment->Id] = $payment;
//                $res = $cont->getRequest("ClientAlias($payment->ClientAlias_Id)".'?$select=PhoneNumber');
//                if(!$res instanceof View){
//                    $grouped[$payment->Invoice_Id]->PhoneNumber = $res->PhoneNumber;
//                }
                // determine the css class for each one of them
                $today = new Carbon();
                $due     = Carbon::parse($payment->Due);
                $daysoverdue = $today->diffInDays($due,false);
                //determine the color of the payment
                switch ($payment->Status){
                    case "Sent":
                    case "Created":
                        $payment->Class = $daysoverdue < 0 ? "warning":"success";
                        break;
                    case "Overdue" :
                    case  "Reminder" :
                        $payment->Class = $daysoverdue < -30 ? "danger": "warning";
                        break;
                    case "DebtCollection":
                    default:
                        $payment->Class = "danger";
                        break;
                }
            }
        }

        $total = $grouped;
        $users = UsersController::listByRoles(['Sales']);
        $user  = UsersController::getUserNameById($id);
        $userId = $id;
        return view('statistics.debtCollection',compact('user','users','total','userId'));
    }




}
