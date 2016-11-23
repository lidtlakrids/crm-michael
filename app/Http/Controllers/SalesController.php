<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SalesController extends Controller {

    //
    public function index()
    {

        return view('sales.index');
    }

    public function clientsToCall(){
        $users = UsersController::queryListByRoles(['Sales']);

        $bookers = UsersController::queryUsersList('Booker_Id');

        return view('sales.clientsToCall',compact('bookers','users'));
    }
}
