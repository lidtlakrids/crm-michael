<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

use App\Http\Requests;
use Illuminate\View\View;

class SalariesController extends Controller
{

    /**
     * returns a list with all users and their bonuses
     */
    public function index(){
        $months = querySellerPeriods('StartDate','EndDate',['separate'=>true]);
        $sellers = UsersController::listByRoles(['Sales']);
        return view('salaries.index',compact('months','sellers'));
    }

    /**
     * payments overview and requesting bonus
     * @param $userId
     * @return View|null
     * @internal param null $period
     */
    public function payments($userId = null){


        $sellers = UsersController::listByRoles(['Sales']);
        $periods = past12months();
        $cont = new RestController();
        if(!isset($_GET['period'])){
            $period = current($periods);
        }else{
            $period = $periods[$_GET['period']];
        }

        $periods = array_keys($periods);
        //make iso-9000 date format for backend request
        $startDate = $period['start'];
        $endDate   = $period['end'];
        $userId = inRole('Accounting')? $userId : Auth::user()->externalId;
        $user = null;
        if($userId != null){
            $userId = inRole('Accounting')? $userId : Auth::user()->externalId;
            $user = UsersController::getUserNameById($userId);

            $payments = $cont->postRequest('Salaries/action.PaidOverview',['StartDate'=>$startDate,"EndDate"=>$endDate,'User_Id'=>$userId]);
            if($payments instanceof View){
                return $payments;
            }
            // we can call this from js
            if(Request::ajax()){
                $sum = 0;
                $result=$payments->value;
                foreach($result as $value){
                    if(isset($value->NetValue))
                        $sum += $value->NetValue;
                }
                return json_encode($sum);
            }else {
                $grouped = [];
                //group them by invoice
                foreach ($payments->value as $payment) {
                    if (isset($grouped[$payment->Invoice_Id])) {
                        $grouped[$payment->Invoice_Id]->NetValue += $payment->NetValue;
                        $grouped[$payment->Invoice_Id]->Commission += $payment->Commission;
                    } else {
                        $grouped[$payment->Invoice_Id] = $payment;
                    }
                }
                $payments = $grouped;
            }
        }else{ // user id is null ,get all of the user payments
            foreach ($sellers as $id=>$name) {

                $res = $cont->postRequest('Salaries/action.PaidOverview',['StartDate'=>$startDate,"EndDate"=>$endDate,'User_Id'=>"$id"]);
                if(!$res instanceof View) {

                }
                $grouped =[];

                foreach ($res->value as $payment) {
                    if (isset($grouped[$payment->Invoice_Id])) {
                        $grouped[$payment->Invoice_Id]->NetValue += $payment->NetValue;
                        $grouped[$payment->Invoice_Id]->Commission += $payment->Commission;
                    } else {
                        $grouped[$payment->Invoice_Id] = $payment;
                    }
                }

                $data[$id]= $grouped;
            }

        }

        return view('salaries.payments',compact('payments','sellers','user','periods','period','data'));
    }

    public function missingPayments($userId = null){

        $cont = new RestController();

        // get all missing payments
        if($userId == null){
            $result = $cont->getRequest(
                'Invoices?$filter='.urlencode("Type eq 'Invoice' and (Status eq 'Reminder' or Status eq 'Sent' or Status eq 'Overdue' or Status eq 'Created')").
                    '&$select=Created,Due,NetAmount,Payed,Name,ClientAlias_Id,Id,InvoiceNumber,Status&$expand=User($select=FullName),ClientAlias($expand=User($select=FullName);$select=Name)&$orderby=Created+desc'
            );
            if($result instanceof View){
                return $result;
            }
            $grouped = [];
            $today = new Carbon();
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
            return view('salaries.missingPayments',compact('total','user','users','userId'));
        }

        $payments = $cont->postRequest('Salaries/action.PendingOverview',['User_Id'=>$userId]);
        if($payments instanceof View){
            return $payments;
        }
        $grouped = [];
        //group them by invoice
        foreach ($payments->value as $payment){
            if(Carbon::parse($payment->DueDate)->year < 2016 ) continue; // Fix until we update the invoice statuses

            if(isset($grouped[$payment->Invoice_Id])){
                $grouped[$payment->Invoice_Id]->NetValue += $payment->NetValue;
                $grouped[$payment->Invoice_Id]->Commission += $payment->Commission;
            }else{
                $grouped[$payment->Invoice_Id] = $payment;
//                $res = $cont->getRequest("ClientAlias($payment->ClientAlias_Id)".'?$select=PhoneNumber');
//                if(!$res instanceof View){
//                    $grouped[$payment->Invoice_Id]->PhoneNumber = $res->PhoneNumber;
//                }

                // determine the css class for each one of them
                $today = new Carbon();
                $due     = Carbon::parse($payment->DueDate);
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
                        $payment->Class = "warning";
                        break;
                }
            }
        }

        $payments = $grouped;
        $user = UsersController::getUserNameById($userId);
        $users = UsersController::listByRoles(['Sales']);

        return view('salaries.missingPayments',compact('payments','user','users','userId'));

    }

    /**
     * shows the salary for single user
     * @param null $bonusId
     * @return null
     * @internal param $id
     */
    public function show($bonusId)
    {

        $cont = new RestController();

        $salary = $cont->postRequest('Salaries(' . $bonusId . ')/action.Bonus');
        if ($salary instanceof View) {
            return $salary;
        }
        return view('salaries.show', compact('salary'));
    }

}

