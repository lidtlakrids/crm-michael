<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\GoogleCalendar;
use App\User;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class CalendarController extends Controller {

    //
    public function index()
    {
        //$events = $calendar->getEvents(Auth::user()->email);

        return view('calendar.index');
    }

    public function createEvent(){

        $data = Request::all();
        //split the start and end time
        $time = explode(" - ",$data['time']);
        unset($data['time']);
        $data['Start'] = date('c',strtotime($time[0]));
        $data['End'] = date('c',strtotime($time[1]));
//        $data['reminders']= array(
//            'useDefault' => FALSE,
//            'overrides' => array(
//                array('method' => 'email', 'minutes' => 30),
//                array('method' => 'popup', 'minutes' => 10),
//            ),
//        );

        // if we don't set user, make it for the current logged in
        if(!isset($data['User_Id'])){ $data['User_Id'] = Auth::user()->externalId;}

        $cont = new RestController();
        $result = $cont->postRequest('CalendarEvents',$data);

        return json_encode($result);
    }

    /**
     * finds events with specified parameters
     */
    public function getEvents(){
        $data = Request::all();
        $calendar = new GoogleCalendar();
        $result = $calendar->searchEvents($data);
        return $result;
    }

    public function getEventTypes(){
        $cont = new RestController();
        $types = $cont->getEnumProperties(['EventType']);
        // translate them
        $a= [];
        foreach ($types['EventType'] as $k=>$val){
            $a[$val]= Lang::get('labels.'.$val);
        }



        return $a;
    }


}
