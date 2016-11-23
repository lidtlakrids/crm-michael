<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\View\View;

class SalaryGroupsController extends Controller
{
    /**
     * shows a list with all salary bonuses
     */
    public function index(){

        return view('salaryGroups.index');
    }

    public function create(){


        return view('salaryGroups.create');
    }

    public function edit($id){

        $cont = new RestController();

        $sg = $cont->getRequest("SalaryGroups($id)");
        if($sg instanceof View){
            return $sg;
        }

        return view('salaryGroups.edit',compact('sg'));

    }


    public function show($id){

        $cont = new RestController();

        $sg = $cont->getRequest("SalaryGroups($id)".'?$expand=Rates');
        if($sg instanceof View){
            return $sg;
        }

        return view('salaryGroups.show',compact('sg'));
    }

    public static function getList(){
        $cont = new RestController();

        $res = $cont->getRequest('SalaryGroups?$orderby=Name');
        if($res instanceof View){
            return [];
        }

        $salaryGroups = [];

        foreach($res->value as $sg){
            $salaryGroups[$sg->Id] = $sg->Name;
        }

        return $salaryGroups;

    }


}
