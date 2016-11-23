<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;

class TaskTemplatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('taskTemplates.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = UsersController::usersList();

        return view('taskTemplates.create',compact('users'));
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
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cont = new RestController();

        $taskTemplate = $cont->getRequest("TaskListTemplates($id)".'?$expand=Author($select=Id,FullName,UserName),Children($expand=AssignedTo($select=FullName)),AssignedTo($select=FullName)');
        if($taskTemplate instanceof View){
            return $taskTemplate;
        }

        $users  = UsersController::usersList();
        JavaScriptFacade::put(['users'=>$users]);
        return view('taskTemplates.show',compact('taskTemplate','users'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $cont = new RestController();

        $taskTemplate = $cont->getRequest("TaskListTemplates($id)".'?$expand=Author($select=Id,FullName,UserName)');
        if($taskTemplate instanceof View){
            return $taskTemplate;
        }
        $users = UsersController::usersList();

        return view('taskTemplates.edit',compact('taskTemplate','users'));
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


    public static function templateList(){

        $cont = new RestController();
        $taskTemplates = $cont->getRequest('TaskListTemplates');
        $templates = [];
        if($taskTemplates instanceof View){
            return $templates;
        }else{
            foreach($taskTemplates->value as $temp){
                $templates[$temp->Id] = $temp->Title;
            }
        }
        return $templates;
    }
}
