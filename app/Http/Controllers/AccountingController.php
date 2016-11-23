<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;

class AccountingController extends Controller {

    //
    public function index()
    {

        $cont = new RestController();
        $statuses = $cont->getEnumQuerySelect('InvoiceStatus','Status');
        $types = $cont->getEnumQuerySelect('InvoiceType','Type');
        $sellers = UsersController::queryListByRoles(['Sales']);

        return view('accounting.index',compact('sellers','statuses','types'));
    }
    public function register()
    {
        return view('accounting.register');
    }

    /**
     * gets the draft that need to be invoiced soon
     */
    public function recurring()
    {
        return view('accounting.recurring');
    }

    public function stat($type = null)
    {
        if($type != null){
            $typeElements = explode('-',$type);

            if(sizeof($typeElements)== 4){
                $type = implode('-',[$typeElements[0],$typeElements[1]]);
                $month = implode('-',[$typeElements[2],$typeElements[3]]);
            }else{
                $type = $typeElements[0];
                $month = implode('-',[$typeElements[1],$typeElements[2]]);
            }

            if($type == 'expected'){
                $stat = new  StatisticsController();
                return $stat->expectedPayments($month);
            }elseif(in_array($type,['newsales-goal','resales-goal','newsales-goal-diff','resales-goal-diff','total-goals','total-goals-diff'])){
                $goals = new SellerGoalsController();

                return $goals->index();
            }

            JavaScriptFacade::put(['type'=>$type,'month'=>$month]);
            return view('accounting.stat-by-type',compact('type'));
        }

        $params = Input::only('year');
        if(isset($params['year']) && $params['year']!=''){
            $year = $params['year'];
        }else{
            $year = date('Y');
        }
        $months = monthsSelect();
        JavaScriptFacade::put(['year'=>$year]);



        return view('accounting.stat',compact('year','months'));
    }

}
