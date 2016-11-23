<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Lang;
use Illuminate\View\View;

class InformationSchemesController extends Controller
{

    public function index(){

        $clientManagers  = UsersController::queryListByRoles(['Client Manager']);
        $types = ProductTypesController::queryTypesList();
        return view('informationSchemes.index', compact('clientManagers', 'types' ));
    }


    /**
     * All information schemes with special fields need to be approved
     */
    public function needApproval(){

        $users = UsersController::queryUsersList();
        $weekAgo = date('c', strtotime('-7 days'));
        $twoWeeksAgo = date('c', strtotime('-14 days'));
        $monthAgo = date('c', strtotime('-30 days'));

        $orderDates = [
            'Created ge ' . $weekAgo => '7 ' . Lang::get('labels.days'),
            'Created ge ' . $twoWeeksAgo => '14 ' . Lang::get('labels.days'),
            'Created ge ' . $monthAgo => '30 ' . Lang::get('labels.days'),
        ];

        $types = ProductTypesController::queryTypesList();

        return view('informationSchemes.needApproval',compact('users','orderDates','types'));
    }

    /**
     * show the information scheme and all its information
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id){

        $cont = new RestController();
        $infoScheme = $cont->getRequest("InformationSchemes($id)".'?$expand=Contract($expand=ClientAlias,ContractType,Product),FieldValue($expand=OrderField),Products($select=Id;$expand=Product($select=Name))');
        if($infoScheme instanceof View){
            return $infoScheme;
        }

        $infoScheme->FieldValue = OrdersController::groupOrderFieldValues($infoScheme->FieldValue);
        return view('informationSchemes.show',compact('infoScheme'));
    }

    public function edit($id){

        $cont = new RestController();

        $infoScheme = $cont->getRequest('InformationSchemes('.$id.')?$expand=FieldValue($expand=OrderField($expand=OrderFieldOption)),User($select=Id)');
        if($infoScheme instanceof View){
            return $infoScheme;
        }

        return view('informationSchemes.edit',compact('infoScheme'));
    }
    
}
