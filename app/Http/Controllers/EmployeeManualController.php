<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\View\View;

class EmployeeManualController extends Controller
{
    public function index(){

        return view('employeeManual.index');
    }

    public function show($id){
        $cont = new RestController();

        $manual = $cont->getRequest("EmployeeManuals($id)");
        if($manual instanceof View){
            return $manual;
        }

        $prev = $cont->getRequest('EmployeeManuals?$filter=Id+lt+'.$id.'+and+Published+ne+null&$top=1&$orderby=Id+desc');
        $next = $cont->getRequest('EmployeeManuals?$filter=Id+gt+'.$id.'+and+Published+ne+null&$top=1');

        $prev = $prev instanceof View ? null : !empty($prev->value)? $prev->value[0] : null ;
        $next = $next instanceof View ? null : !empty($next->value)? $next->value[0] : null ;

        return view('employeeManual.show',compact('manual','prev','next'));
    }


    public function edit($id){
        $cont = new RestController();

        $manual = $cont->getRequest("EmployeeManuals($id)");
        if($manual instanceof View){
            return $manual;
        }

        return view('employeeManual.edit',compact('manual'));
    }


}
