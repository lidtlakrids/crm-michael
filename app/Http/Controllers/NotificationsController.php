<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;
class NotificationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $creators = UsersController::queryUsersList('Creator_Id');
        $recipients = UsersController::queryUsersList('Recipient_Id');

        $readStatus = [
            'Read ne null'=>"Seen",
            'Read eq null'=>"Not seen yet",
        ];

        return view('notifications.index',compact('creators','recipients', 'readStatus'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = UsersController::usersList();

        return view('notifications.create',compact('users'));
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
        $notification = $cont->getRequest('Notifications('.$id.')?$expand=Recipient($select=FullName),Creator($select=Id,FullName)');
        if($notification instanceof View)
        {
            return $notification;
        }
        $users = UsersController::usersList();
        JavaScriptFacade::put(['users'=>$users,'notification'=>$notification]);

        return view('notifications.show',compact('notification','users'));
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
}
