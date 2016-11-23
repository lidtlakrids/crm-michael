<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class ClientRatesController extends Controller
{
    /**
     *shows a list with all of them
     */
    public function index(){
        return view('clientRates.index');
    }

    public function create(){

        $salaryGroups = SalaryGroupsController::getList();


        return view('clientRates.create',compact('salaryGroups'));
    }

    public function edit($id){

        $cont = new RestController();

        $clientRate  = $cont->getRequest("ClientRates($id)".'?$expand=SalaryGroup');

        if($clientRate instanceof View){
            return $clientRate;
        }

        $salaryGroups = SalaryGroupsController::getList();

        return view('clientRates.edit',compact('clientRate','salaryGroups'));
    }

    public function show($id){
        $con = new RestController();

        $clientRate = $con->getRequest("ClientRates($id)".'?$expand=SalaryGroup');
        if($clientRate instanceof View){
            return $clientRate;
        }

        return view('clientRates.show',compact('clientRate'));
    }
}
