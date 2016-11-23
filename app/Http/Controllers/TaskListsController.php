<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Illuminate\Support\Facades\Request;


class TaskListsController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return View
	 */
	public function index()
	{

        $weekAgo = date('c', strtotime('-7 days'));
        $twoWeeksAgo = date('c', strtotime('-14 days'));
        $monthAgo = date('c', strtotime('-30 days'));

        $taskDates = [
            'Created ge ' . $weekAgo => '7 ' . Lang::get('labels.days'),
            'Created ge ' . $twoWeeksAgo => '14 ' . Lang::get('labels.days'),
            'Created ge ' . $monthAgo => '30 ' . Lang::get('labels.days')
        ];

        $taskStatus = [
            'Value eq true' => Lang::get('labels.completed'),
            'Value eq false' => Lang::get('labels.not-completed'),
            "(date(DueTime) eq ".date('Y-m-d')." and DueTime ne null and Value eq false)"=>'Due today',
            "(date(DueTime) lt ".date('Y-m-d')." and DueTime ne null) and Value eq false"=>'Overdue'
        ];

        $usersArr = UsersController::usersList();
        $users =[];
        foreach ($usersArr as $id=>$Name){
            $users["((ParentTaskListId eq null and AssignedTo_Id eq '$id') or (ParentTaskListId ne null and AssignedTo_Id eq '$id' and Parent/AssignedTo_Id ne '$id' and Parent/Value eq false))"] = $Name;
        }

        $creators = UsersController::queryUsersList('CreatedBy_Id');


     return view('taskLists.index',compact('users','taskStatus','taskDates','creators'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return View
	 */
	public function create()
	{
        $users = UsersController::usersList();
        return view('taskLists.create',compact('users'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{

        return null;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function save()
    {
//        $input = Request::all();
//        unset($input['_token']);
//
//        //filter the input
//        foreach($input as $k=>$v)
//        {
//            if($v==""){
//                unset($input[$k]);
//            }
//        }
//        if(isset($input['StartTime'])){ $input['StartTime'] = date('c',strtotime($input['StartTime'])); }// iso date format
//        if(isset($input['DueTime'])) { $input['DueTime'] = date('c', strtotime($input['DueTime']));} // iso date format
//        $cont = new RestController();
//        $res  = $cont->postRequest('TaskLists',$input);
//        if($res instanceof RedirectResponse)
//        {
//            return $res;
//        }
//        Session::flash('message',Lang::get('messages.task-saved'));
        return redirect('tasks');
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
        $task = $cont->getRequest('TaskLists('.$id.')?$expand=Parent($select=Title),CompletedBy($select=FullName),AssignedTo($select=Id,FullName),CreatedBy($select=Id,FullName),Children($expand=AssignedTo($select=FullName))');
        if($task instanceof View)
        {
            return $task;
        }
        $users = UsersController::usersList();
        JavaScriptFacade::put(['users'=>$users,'task'=>$task]);
        return view('taskLists.show',compact('task','users'));
	}

    public function check()
    {
        $cont = new RestController();
        $result = $cont->postRequest('TaskLists/Checked/'.$_POST['id']);
        if($result instanceof RedirectResponse)
        {
            return $result;
        }
        return json_encode(array("success"));
    }

    public function uncheck(){

    }

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$cont = new RestController();
        $task = $cont->getRequest('TaskLists('.$id.')');
        if($task instanceof RedirectResponse)
        {
            return $task;
        }

        $users = UsersController::usersList();

        return view('taskLists.edit',compact('task','users'));
	}

    public function quickEdit($id)
    {
        $cont = new RestController();
        $task = $cont->getRequest('TaskLists/'.$id);
        if($task instanceof RedirectResponse)
        {
            return $task;
        }

        $cont = new RestController();
        $usersRaw = $cont->getRequest("Account/userlist");
        if($usersRaw instanceof RedirectResponse)
        {
            return $usersRaw;
        }
        $users = array();
        foreach($usersRaw as $user)
        {
            $users[$user->Id] = $user->UserName;
        }
        return view('taskLists.editFrame',compact('task','users'));

    }

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$input = Request::all();
        unset($input['_method'],$input['_token']);

        $params = $input;
        $params['Id'] = $id;
        if($params['AssignedTo'] != ""){
            $params['AssignedTo'] = array('Id'=>$params['AssignedTo']);
        }else{
            unset($params['AssignedTo']);
        }
        $cont = new RestController();
        $result = $cont->patchRequest('TaskLists('.$id.')',$params);
        if($result instanceof RedirectResponse)
        {
            return $result;
        }
        Session::flash('message',Lang::get('messages.task-updated-success'));
        return redirect('tasks/show/'.$id);
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


    public function forSideMenu()
    {
        $cont = new RestController();
        $tasks = $cont->getRequest('taskLists');
        if($tasks instanceof RedirectResponse)
        {
          return null;
        }

        //remove completed tasks
        foreach($tasks as $k=>$val){
        if($val->Value== true){
           unset($tasks[$k]);
          }
        }

        //reorder the tasks array
        $tasks = array_values($tasks);

        //sory by due date DESC
        usort($tasks,
        function($a, $b)
        {
            $t1 = strtotime($a->DueTime);
            $t2 = strtotime($b->DueTime);
            return $t2 - $t1;
        });

        //return the 10 soonest tasks
        $tasks = array_only($tasks,[0,1,2,3,4,5,6,7,8,9]);

        $result = "";
        foreach($tasks as $task) {
            $result .= '<div class="contextual-progress">';
            $result .= '<div class="clearfix">';
            $result .= '<div class="progress-title">';
            $result .= '<input style="margin-right:5px;" type="checkbox" id="'.$task->Id.'" onclick=checkTask(this.id,"' . csrf_token() . '");>';
            $result .= '<span class="task-description done">' . $task->Title . '</span></div >';
            $result .= '<div class="progress-percentage"><span class="label"><button id="editTaskIFrame" value="'.url('tasks/quickEdit',$task->Id).'"><i class="fa fa-pencil"></i></button></span></div>';
            $result .= '<hr>';
        }

        return $result;
    }


       public function myTasks()
    {
        $cont = new RestController();
        $tasks = $cont->getRequest('TaskLists');
        if($tasks instanceof RedirectResponse)
        {
            return null;
        }
        //show only tasks that are not completed
        foreach($tasks as $k=>$val){
            if($val->Value== true){
                unset($tasks[$k]);
            }
        }

        //resort the array
        $tasks = array_values($tasks);
        //order by due date
        usort($tasks,
            function($a, $b)
            {
                $t1 = strtotime($a->DueTime);
                $t2 = strtotime($b->DueTime);
                return $t2 - $t1;
            });

        //return the 10 soonest tasks
        $tasks = array_only($tasks,[0,1,2,3,4,5,6,7,8,9]);
        $result = "";
        foreach($tasks as $task) {
            $result .= '<div class="contextual-progress">';
            $result .= '<div class="clearfix">';
            $result .= '<div class="progress-title">';
            $result .= '<input style="margin-right:5px;" type="checkbox" id="'.$task->Id.'" onclick=checkTask(this.id,"' . csrf_token() . '");>';
            $result .= '<span class="task-description done">' . $task->Title . '</span></div >';
            $result .= '<div class="progress-percentage"><span class="label"><button id="editTaskIFrame" value="'.url('tasks/quickEdit',$task->Id).'"><i class="fa fa-pencil"></i></button></span></div>';
            $result .= '<hr>';
        }
        return $result;
    }


    /**
     * deprecated
     *
     * @return string
     */
    public function itemTasks()
    {
        $params  = $_POST;
        $cont = new RestController();
        $tasks = $cont->getRequest('taskLists/'.$params['Model']."/".$params['ModelId']);
        if($tasks instanceof RedirectResponse)
        {
            exit; // just stop
        }
        //show only tasks that are not completed
        foreach($tasks as $k=>$val){
            if($val->Value== true){
                unset($tasks[$k]);
            }
        }

        //resort the array
        $tasks = array_values($tasks);
        //order by due date
        usort($tasks,
            function($a, $b)
            {
                $t1 = strtotime($a->DueTime);
                $t2 = strtotime($b->DueTime);
                return $t2 - $t1;
            });

        //return the 10 soonest tasks
        $tasks = array_only($tasks,[0,1,2,3,4,5,6,7,8,9]);
        $result = "";
        foreach($tasks as $task) {
            $result .= '<div class="contextual-progress">';
            $result .= '<div class="clearfix">';
            $result .= '<div class="progress-title">';
            $result .= '<input style="margin-right:5px;" type="checkbox" id="'.$task->Id.'" onclick=checkTask(this.id,"' . csrf_token() . '");>';
            $result .= '<span class="task-description done">' . $task->Title . '</span></div >';
            $result .= '<div class="progress-percentage"><span class="label"><button id="editTaskIFrame" value="'.url('tasks/quickEdit',$task->Id).'"><i class="fa fa-pencil"></i></button></span></div>';
            $result .= '<hr>';
        }
        return $result;
    }

}

