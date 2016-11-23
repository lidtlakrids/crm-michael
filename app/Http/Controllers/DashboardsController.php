<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;

class DashboardsController extends Controller {

	//
    public function index()
    {
        //redirect if user is not logged or we don't have session with auth token
        if (!Auth::check() || !Session::has('Bearer'))
        {
          return redirect('auth/login');
        }
        // Redirect depending on role

        if(isDev()){
            return view('dashboard.devDashboard');
        }elseif (inRoleNeutral('Administrator')){
            return view('dashboard.adminDashboard');
        } elseif(inRoleNeutral('Sales')){
            return redirect('sales');
        } elseif(inRoleNeutral('Adwords')){
            return view('adwords.dashboard');
        } elseif(inRoleNeutral('SEO')){
            return view('dashboard.seo');
        } elseif(inRoleNeutral('Client Manager')){
            return view('dashboard.clientManager');
        }elseif(inRoleNeutral('Meet Booking')){
            return view('dashboard.bookingDashboard');
        }elseif(inRoleNeutral('User')){
            return view('dashboard.home');
        }

        return view('dashboard.index');
    }
    public function home()
    {

        return view('dashboard.home');
    }
    public function admin()
    {

        return view('dashboard.adminDashboard');
    }

    public function accounting1()
    {


        return view('dashboard.accounting');
    }
    public function stat()
    {
        return view('dashboard.stat');
    }

    public function appointments(){

//        $cont = new RestController();
//
//        $userQuery =
//
//        $userQuery = "User_Id eq '".Auth::user()->externalId."'";
//
//        $appointments = $cont->getRequest('CalendarEvents'.urlencode('?$filter='.$userQuery));
//        if($appointments instanceof View){
//            return $appointments;
//        }
//        $appointments = $appointments->value;

        return view('dashboard.appointments');
    }

    public function unpaidInvoices(){


//        $cont = new RestController();
//
//        $userQuery =
//
//        $userQuery = "User_Id eq '".Auth::user()->externalId."'";
//
//        $invoices = $cont->getRequest('Invoices?$filter=Status eq webapi.Models.InvoiceStatus\'Sent\' or Status eq webapi.Models.InvoiceStatus\'Overdue\' and '.$userQuery);
//        if($invoices instanceof View){
//            return $invoices;
//        }
//        $invoices = $invoices->value;

        return view('dashboard.unpaid');
    }

    public function unconfirmedOrders(){

        return view('dashboard.unconfirmedOrders');
    }

    public function optimizations(){
        $input = Input::all();
        $today = dayStartEnd();
        $periods =[
            ''=>'Select Period',
            "(NextOptimize lt ".$today['DayEnd'].' and NextOptimize gt '.$today['DayStart'].')' => "Today",
            "NextOptimize lt ".$today['DayStart'] =>"Overdue optimizations",
            "NextOptimize gt ".$today['DayEnd'] => "Future optimizations",
        ];

        $managers = UsersController::queryUsersList('Manager_Id');
        $selected = null;

        return view('dashboard.optimizations',compact('periods','selected','managers'));
    }

    public function activeClients(){

        $con = new RestController();
        $alias = $con->postRequest('ClientAlias/action.ActiveClientAlias?$expand=User($select=FullName),Contact');
        if($alias instanceof View){
            return $alias;
        }

        $alias = $alias->value;

        return view('dashboard.activeClients',compact('alias'));

    }

    /**
     * contracts that will end within 3 months
     * @return View|null
     */
    public function contractRenewal(){

        $cont = new RestController();

        $contracts = $cont->postRequest('Contracts/action.RenewalOverview?$expand='.
            'ClientAlias($select=Name;$expand=Client($select=Id;$expand=ClientManager($select=FullName))),'.
            'Product($select=Name),Country($select=CountryCode),Manager($select=FullName)');
        if($contracts instanceof View){
            return $contracts;
        }

        $contracts = $contracts->value;
        return view('dashboard.contractRenewal',compact('contracts'));
    }


    /**
     * overdue invoices and reminders
     *
     */
    public function overdueInvoices(){

        $cont = new RestController();

        $overdues = $cont->postRequest('Salaries/action.OverDueOverview');
        if($overdues instanceof View){
            return $overdues;
        }

        $grouped = [];
        //group them by invoice
        foreach ($overdues->value as $payment){
            if(isset($grouped[$payment->Invoice_Id])){
                $grouped[$payment->Invoice_Id]->NetValue += $payment->NetValue;
                $grouped[$payment->Invoice_Id]->Commission += $payment->Commission;
            }else{
                $grouped[$payment->Invoice_Id] = $payment;
                // determine the css class for each one of them
                $today = new DateTime();
                $due     = new DateTime($payment->DueDate);
                $daysoverdue = $due->diff($today)->format('%a');

                //determine the color of the payment
                switch ($payment->Status){
                    case "Sent":
                        $payment->Class = $daysoverdue >0 ? "warning":"success";
                        break;
                    case "Overdue" :
                    case  "Reminder" :
                        $payment->Class = $daysoverdue > 30 ? "danger": "warning";
                        break;
                    case "DebtCollection":
                    case "None":
                        $payment->Class = "danger";
                }
            }
        }

        $overdues = $grouped;

        return view('dashboard.overdueInvoices',compact('overdues'));
    }

    /**
     * contracts that ended but didn't get renewed
     */
    public function winback(){

        $cont = new RestController();

        $endedContracts = $cont->postRequest('Contracts/action.WinBackOverview?$expand='.
            'ClientAlias($select=Name;$expand=Client($select=Id;$expand=ClientManager($select=FullName))),'.
            'Product($select=Name),Country($select=CountryCode),Manager($select=FullName)');
        if($endedContracts instanceof View){
            return $endedContracts;
        }
        $endedContracts = $endedContracts->value;
        return view('dashboard.winback',compact('endedContracts'));
    }

    public function ongoingOptimizations($userid = null){


        $users = UsersController::queryListByRoles(['Adwords','Seo'],'Manager_Id');
        $admin = isAdmin();
        if($userid != null){
            $userid = $admin ? $userid : UsersController::activeUserId();
        }
        return view('dashboard.ongoingOptimizations',compact('users','userid','admin'));
    }



}
