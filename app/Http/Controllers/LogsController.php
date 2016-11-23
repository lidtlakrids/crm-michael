<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\View\View;

class LogsController extends Controller
{
    
    public function index(){

        return view('logs.index');
    }
    
    public function show($id){

        $cont = new RestController();

        $log = $cont->getRequest("Logs($id)".'?$expand=User($select=FullName)');
        if($log instanceof View){
            return $log;
        }
        return view('logs.show',compact('log'));
    }

    public function detailed(){
        return view('logs.detailed');
    }
}
