<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;

class AppointmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $users = UsersController::queryUsersList();

        $bookers = UsersController::queryUsersList('Booker_Id');

        $today = dayStartEnd();

        $appointmentDates = [
            "not Activity/any(d:d/ActivityType eq 'Completed' or d/ActivityType eq 'Cancel') and date(Start) eq ".date('Y-m-d',time())=>"Appointments Today",
            'not Activity/any(d:d/ActivityType eq \'Completed\' or d/ActivityType eq \'Cancel\') and Start ge ' . date('c',strtotime('now')) =>Lang::get('labels.future-appointments'),
            'Start le ' . date('c',strtotime('now')) =>"Past appointments",
            'not Activity/any(d:d/ActivityType eq \'Completed\' or d/ActivityType eq \'Cancel\') and End le ' . date('c',strtotime('now')) . ' and not Activity/any(d:d/ActivityType eq \'Cancel\' or d/ActivityType eq \'Completed\')' => 'Overdue',

        ];
        $cont = new RestController();
        $types = $cont->getEnumQuerySelect('EventType','EventType');
        //translate it the stupid way
        foreach ($types as $k=>$val){
            $types[$k] = Lang::get('labels.'.$val);
        }
        return view('appointments.index',compact('users','appointmentDates','bookers','types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return View
     */
    public function show($id)
    {
        $cont = new RestController();
        $appointment = $cont->getRequest("CalendarEvents($id)".'?$expand=User($select=FullName,UserName),Booker($select=FullName,UserName),Attendees,Source,'.
                            'Activity($expand=User($select=FullName),Comment($select=Id,Message);$orderby=Created+desc)');
//        dd($appointment);
        if($appointment instanceof View){
            return $appointment;
        }
        if(!$this->isOwner($appointment)){
            return view('errors.denied');
        }

        JavaScriptFacade::put(['appointment'=>$appointment]);

        return view('appointments.show',compact('appointment'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    /**
     * checks for entity ownership depending on a role
     * @param $item
     * @return bool
     */
    public function isOwner($item)
    {
        $roles = Session::get('roles');
        $userId = Auth::user()->externalId;

        $result = !empty(array_intersect($roles, ['Administrator','Accounting','Developer']));
        if ($result) {
            return true;
        }
        // In leads, role doesn't matter, you either created or are assigned to the lead, otherwise you can't see it
        return in_array($userId,[$item->User_Id,$item->Booker_Id]);

    }

}
