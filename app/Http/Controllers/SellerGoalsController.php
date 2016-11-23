<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Http\Requests;
use Illuminate\View\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;

class SellerGoalsController extends Controller
{
    public function index(){

        $get = Request::all();
        //get the seller goals
        $cont = new RestController();
        $year = isset($get['year'])? $get['year']: date('Y');
//        if(isset($get['year'])){
            $month = isset($get['month']) ? "Month+eq+".$get['month']: "Month+eq+".date('n');
//        }else{0
//            $month = "Month+eq+".date('m');
//        }
        // if it's admin, show everything, if not show only own userId
        if(inRole('Administrator')){
            $user  = isset($get['user']) ? "User_Id+eq+'".$get['user']."'":"User_Id+ne+null";
            $userId = isset($get['user']) ? $get['user'] : null;
        }else{
            $user = "User_Id+eq+'".Auth()->user()->externalId."'";
            $userId = Auth()->user()->externalId;
        }

        $goals = $cont->getRequest('SellerGoals?$expand=User($select=FullName)'.'&$orderby=Year,Month+desc&$filter=Year+eq+'.$year.'+and+'.$month.'+and+'.$user);
        if($goals instanceof View){
            return $goals;
        }

        foreach ($goals->value as $k=>$goal){

            $stats = $cont->getRequest("SellerGoals(".$goal->Id.")/Stats");
            if($stats instanceof  View){
                return $stats;
            }
            $goals->value[$k]->CompletedMeetings = 0;
            $goals->value[$k]->CompletedMeetingsDiff = 0;
            $goals->value[$k]->NewSalesCount     = 0;
            $goals->value[$k]->NewSalesCountDiff = 0;
            $goals->value[$k]->NewSalesValue     = 0;
            $goals->value[$k]->NewSalesValueDiff = 0;
            $goals->value[$k]->ReSalesCount      = 0;
            $goals->value[$k]->ReSalesCountDiff  = 0;
            $goals->value[$k]->ReSalesValue      = 0;
            $goals->value[$k]->ReSalesValueDiff  = 0;
            $goals->value[$k]->UpSalesCount      = 0;
            $goals->value[$k]->UpSalesCountDiff  = 0;
            $goals->value[$k]->UpSalesValue      = 0;
            $goals->value[$k]->UpSalesValueDiff  = 0;
            $goals->value[$k]->CallsDiff         = 0;
            $goals->value[$k]->Calls             = 0;
            $goals->value[$k]->Payments          = 0;

            foreach ($stats->value as $s){
                $goals->value[$k]->CompletedMeetings += $s->CompletedMeetings;
                $goals->value[$k]->NewSalesCount    += $s->NewSalesCount;
                $goals->value[$k]->NewSalesValue    += $s->NewSalesValue;
                $goals->value[$k]->ReSalesCount     += $s->ReSalesCount;
                $goals->value[$k]->ReSalesValue     += $s->ReSalesValue;
                $goals->value[$k]->UpSalesCount     += $s->UpSaleCount;
                $goals->value[$k]->UpSalesValue     += $s->UpSaleValue;
                $goals->value[$k]->Calls            += $s->Calls;
                $goals->value[$k]->Payments         += $s->Payments;
            }
            $goals->value[$k]->NewSalesCountDiff = $goals->value[$k]->NewSalesCount - $goals->value[$k]->NewSalesCountGoal;
            $goals->value[$k]->NewSalesValueDiff = $goals->value[$k]->NewSalesValue - $goals->value[$k]->NewSalesGoal;

            $goals->value[$k]->ResalesCountDiff  = $goals->value[$k]->ReSalesCount  - $goals->value[$k]->ReSalesCountGoal;
            $goals->value[$k]->ResalesValueDiff  = $goals->value[$k]->ReSalesValue  - $goals->value[$k]->ReSalesGoal;

            $goals->value[$k]->UpSalesCountDiff  = $goals->value[$k]->UpSalesCount  - $goals->value[$k]->UpSalesCountGoal;
            $goals->value[$k]->UpSalesValueDiff  = $goals->value[$k]->UpSalesValue  - $goals->value[$k]->UpSalesGoal;

            $goals->value[$k]->CallsDiff         = $goals->value[$k]->Calls - $goals->value[$k]->CallsGoal;

            $goals->value[$k]->CompletedMeetingsDiff = $goals->value[$k]->CompletedMeetings - $goals->value[$k]->HealthChecksGoal;
        }
        $goals = $goals->value;
        
        $sellers = UsersController::listByRoles(['Sales']);

        $years  = yearsSelect();
        $months = monthsSelect();
        return view('sellerGoals.index',compact('goals','sellers','years','months','userId','month','year'));
    }

    public function show($id){

        $cont = new RestController();
        $goal = $cont->getRequest("SellerGoals($id)".'?$expand=User($select=FullName,UserName,Id)');
        if($goal instanceof View){
            return $goal;
        }

//      JavaScriptFacade::put(['goal'=>$goal]);
        $days = ['Mon','Tue','Wed','Thu','Fri'];
        $workdays = workWeeksInMonth($goal->Year,$goal->Month);
        $stats = $cont->getRequest("SellerGoals($id)/Stats");
        if($stats instanceof  View){
            return $stats;
        }

        $grouped= [];
        foreach ($stats->value as $s){
            $date = Carbon::parse($s->Date);
            $day = $date->day;
            unset($s->Date);
            $grouped[$day] =$s;
        }
        $workdaysN = [];
        foreach ($workdays as $d) {
            $workdaysN[$d[0]] = $d[0];
        }
        $statDays = array_keys($grouped);
        $missing = array_diff($statDays,$workdaysN);

        foreach ($missing as $m){
            if(isset($workdaysN[$m+1])){
                $grouped[$m+1] = $grouped[$m];
            }elseif(isset($workdaysN[$m-1])){
                $grouped[$m-1] = $grouped[$m];
            }elseif(isset($workdaysN[$m-2])){
                $grouped[$m-2] = $grouped[$m];
            }elseif(isset($workdaysN[$m+2])){
                $grouped[$m+2] = $grouped[$m];
            }
            unset($grouped[$m]);
        }

        $expected = StatisticsController::expectedPaymentsByMonthAndUser($goal->Year,$goal->Month,$goal->User_Id);
        $statistics = [];
        foreach ($workdays as $d) {
            $day = $d[0];
            //whoever maintains it, read it couple of times, it's not complicated, just looks like it
            $statistics['Calls']['Total'][$day]= isset($grouped[$day]) ? $grouped[$day]->Calls : 0;
            $statistics['Calls']['Goal'][$day] = round($goal->CallsGoal/count($workdays));
            $statistics['Calls']['Diff'][$day] = isset($grouped[$day]) ? $grouped[$day]->Calls - round($goal->CallsGoal/count($workdays)) : (-1*$goal->CallsGoal/count($workdays));

            $statistics['HC']['Own'][$day] = isset($grouped[$day]) ? $grouped[$day]->OwnMeetings : 0;
            $statistics['HC']['Booked'][$day] = isset($grouped[$day]) ? $grouped[$day]->BookedMeetings : 0;
            $statistics['HC']['All'][$day] = isset($grouped[$day]) ? $grouped[$day]->HealthChecks : 0;
            $statistics['HC']['Completed'][$day] = isset($grouped[$day]) ? $grouped[$day]->CompletedMeetings : 0;
            $statistics['HC']['Goal'][$day]  = ($goal->HealthChecksGoal/count($workdays));
            $statistics['HC']['Diff'][$day]  =  isset($grouped[$day]) ? $grouped[$day]->CompletedMeetings - ($goal->HealthChecksGoal/count($workdays)) : (-1*$goal->HealthChecksGoal/count($workdays));

            $statistics['New S.']['Count'][$day] = isset($grouped[$day]) ? $grouped[$day]->NewSalesCount:0;
            $statistics['New S.']['Count Goal'][$day] = ($goal->NewSalesCountGoal/count($workdays));
            $statistics['New S.']['Count Diff'][$day] = isset($grouped[$day]) ? $grouped[$day]->NewSalesCount - ($goal->NewSalesCountGoal/count($workdays)) : (-1*$goal->NewSalesCountGoal/count($workdays));
            $statistics['New S.']['Value'][$day] = (isset($grouped[$day]) ? $grouped[$day]->NewSalesValue:0);
            $statistics['New S.']['Goal'][$day] = (($goal->NewSalesGoal/count($workdays)));
            $statistics['New S.']['Diff'][$day] = (isset($grouped[$day]) ? $grouped[$day]->NewSalesValue - ($goal->NewSalesGoal/count($workdays)) : -1*($goal->NewSalesGoal/count($workdays)));

            $statistics['Re S.']['Count'][$day] = isset($grouped[$day]) ? $grouped[$day]->ReSalesCount + $grouped[$day]->UpSaleCount:0;
            $statistics['Re S.']['Count Goal'][$day] = ($goal->ReSalesCountGoal + $goal->UpSalesCountGoal)/count($workdays);
            $statistics['Re S.']['Count Diff'][$day] = isset($grouped[$day]) ? ($grouped[$day]->ReSalesCount+$grouped[$day]->UpSaleCount) - (($goal->ReSalesCountGoal+$goal->UpSalesCountGoal)/count($workdays)) : (-1*($goal->ReSalesCountGoal + $goal->UpSalesCountGoal)/count($workdays));

            $statistics['Re S.']['Value'][$day] = (isset($grouped[$day]) ? $grouped[$day]->ReSalesValue + $grouped[$day]->UpSaleValue:0);
            $statistics['Re S.']['Goal'][$day] = (($goal->UpSalesGoal+$goal->ReSalesGoal)/count($workdays));
            $statistics['Re S.']['Diff'][$day] = (
            isset($grouped[$day]) ? ($grouped[$day]->ReSalesValue + $grouped[$day]->UpSaleValue) - (($goal->UpSalesGoal+$goal->ReSalesGoal)/count($workdays)) : -1*($goal->UpSalesGoal+$goal->ReSalesGoal)/count($workdays));
            $statistics['Paid']['Recurring'][$day] = isset($grouped[$day]) ? $grouped[$day]->Payments - $grouped[$day]->NewSalesPayments:0;
            $statistics['Paid']['New S.'][$day] = isset($grouped[$day]) ? $grouped[$day]->NewSalesPayments:0;
            $statistics['Expected']['Drafts'][$day] = isset($expected[$day])? $expected[$day]:0;
            $statistics['Total']['Paid'][$day] = isset($grouped[$day]) ?  $grouped[$day]->Payments : 0;
            $statistics['Total']['Goal'][$day] = ($goal->UpSalesGoal+$goal->ReSalesGoal+$goal->NewSalesGoal)/count($workdays);
            $statistics['Total']['Diff'][$day] = (
            isset($grouped[$day]) ? ($grouped[$day]->Payments) - (($goal->UpSalesGoal+$goal->ReSalesGoal+$goal->NewSalesGoal)/count($workdays)) : -1*($goal->UpSalesGoal+$goal->ReSalesGoal+$goal->NewSalesGoal)/count($workdays));
        }
//            dd($statistics);
            $startOffset = $workdays[0][1]-1;
            $endOffset =  25 - (count($workdays) + $startOffset);

        // if we the current user does not own the goal,
        // check if he's an admin.
        if($goal->User_Id != Auth::user()->externalId){
            return isAdmin() ?  view('sellerGoals.show',compact('goal','colors','days','goalProps','workdays','statistics','startOffset','endOffset')) :  view('errors.denied');
        } else {
            return view('sellerGoals.show', compact('goal','colors','days','goalProps','workdays'));
        }
    }

    public function isOwner($goal){

    }

}
