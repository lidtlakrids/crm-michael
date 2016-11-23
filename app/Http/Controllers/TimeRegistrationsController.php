<?php namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TimeRegistrationsController extends Controller {


    /**
     * @return string
     */
    public function checkIn()
    {
        $cont = new RestController();
        $checkedIn = $cont->postRequest('TimeRegistrations/action.CheckIn');
        if($checkedIn instanceof View)
        {
            return json_encode(['status'=>'error']);
        }
        Session::put('status','CheckedIn');
        return json_encode(['status'=>'CheckedIn']);
    }

    /**
     * begin work
     */
    public function beginWork(){
        $cont = new RestController();

        $status = session('status');
        if(in_array($status,['CheckedOut','Absent','Sick','Vacation','error'])){
            $checkedIn = $cont->postRequest('TimeRegistrations/action.CheckIn');
            if($checkedIn instanceof View)
            {
                Session::put('status','error');
                return json_encode(['status'=>'error']);

            }else{
                Session::put('status','CheckedIn');

                return json_encode(['status'=>'CheckedIn']);
            }
        }elseif($status =="Break"){
            $checkedIn = $cont->postRequest('TimeRegistrations/action.EndBreak');
            if($checkedIn instanceof View)
            {
                Session::put('status','error');
                return json_encode(['status'=>'error']);
            }else{
                Session::put('status','CheckedIn');
                return json_encode(['status'=>'CheckedIn']);
            }
        }
        //if we end up here, it's obviously error
        Session::put('status','error');
        return json_encode(['status'=>'error']);
    }

    /**
     * @return string
     */
    public function checkOut()
    {
        $cont = new RestController();
        $checkedIn = $cont->postRequest('TimeRegistrations/action.CheckOut');
        if($checkedIn instanceof View)
        {
            return json_encode(['status'=>'error']);
        }
        Session::put('status','CheckedOut');
        return json_encode(['status'=>'CheckedOut']);
    }

    /**
     * @return string
     */
    public function beginBreak()
    {
        $cont = new RestController();
        $checkedIn = $cont->postRequest('TimeRegistrations/action.BeginBreak');
        if($checkedIn instanceof View)
        {
            return json_encode(['status'=>'error']);
        }
        Session::put('status','Break');
        return json_encode(['status'=>'Break']);
    }


    /**
     * @return string
     */
    public function endBreak()
    {
        $cont = new RestController();
        $checkedIn = $cont->postRequest('TimeRegistrations/action.EndBreak');
        if($checkedIn instanceof View)
        {
            return json_encode(['status'=>'error']);
        }
        Session::put('status','CheckedIn');

        return json_encode(['status'=>'CheckedIn']);
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return View
	 */
	public function index()
	{
		$cont = new RestController();

        $checkedIn = $cont->getRequest('TimeRegistrations/UserStatusList');
        if($checkedIn instanceof View)
        {
            return json_encode(['status'=>'error']);
        }

        return view('timeRegistrations.index',compact('checkedIn'));
	}

    /**
     * Time logs for time intervals
     *
     * @return \Illuminate\View\View
     */
    public function timeLogs()
    {
        $params = Request::all();
        $cont  = new RestController();
        $role = null;
        $time = date('Y-m-d').' - '.date('Y-m-d');

        $roles = AclController::listRoles();
        if(!isset($params['Role']) || $params['Role'] == ''){
            $users = UsersController::usersList();
        }else{
            $users = UsersController::listByRoles([$roles[$params['Role']]]);
            $role = $params['Role'];
        }
        if(isset($params['time']) && $params['time'] !== ''){
            $time = $params['time'];
        }
        // list of users that should not be considered in the time logs
        $exclude =['136','128','135','155','122','4c662517-ebf5-41c6-92e1-c179da3d2b2f','68']; // see comments on http://crm.gcm.nu/tasks/show/6386



        JavaScriptFacade::put(['users'=>$users]);
        return view('timeRegistrations.timeLogs',compact('role','roles','exclude','users','time'));
    }

    /**
     * @return \Illuminate\View\View|null
     */
    public function screen()
    {
        $cont = new RestController();

        $result = $cont->getRequest('TimeRegistrations/action.UserStatusList?$expand=User($select=Id,UserName,FullName;$filter='.urlencode('Active eq true)'));
        if($result instanceof View)
        {
            return $result;
        }

        //Users that shoult not be on the screen
        $exclude = ['135','110','103','73','acd0fd2e-8c0c-46b6-9260-ecb45ed8d3d0'];

        // create users array and  group them by their status
        $users = [];
        $users['CheckedIn']  = [];
        $users['CheckedOut'] = [];
        $users['Break']      = [];
        foreach($result->value as $user)
        {
            if(in_array($user->User->Id,$exclude)) continue;

            switch($user->Status)
            {
                case "CheckedIn":
                 array_push($users['CheckedIn'],$user);
                    break;
                case "Break":
                array_push($users['Break'],$user);
                    break;
                case "CheckedOut":
                case "Absent":
                case "Sick":
                case "Vacation":
                    array_push($users['CheckedOut'],$user);
            }
        }

//        $today = date('Y-m-d');
//        $o = $cont->getRequestAnon('Orders/OrderCountToday'));
        $orders = 0;
//        if(!$o instanceof View){
//            $orders = $o;
//        }


        return view('timeRegistrations.screen',compact('users','orders'));
    }


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
