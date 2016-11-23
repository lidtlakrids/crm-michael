<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use App\Http\Requests;
use Illuminate\View\View;

class PartnersController extends Controller
{
    public function index(){

        return view('partners.index');
    }

    public function create(){
        $users = UsersController::usersList();
        $countries = CountriesController::countriesList();
        return view('partners.create',compact('users','countries'));
    }

    public static function partnersList(){
        $cont = new RestController();
        $partners = [];

        $partnersRaw = $cont->getRequest('Partners?$select=Name,Id,Homepage');
        if($partnersRaw instanceof View)
        {
            return $partners;
        }

        foreach($partnersRaw->value as $partner)
        {
            $partners[$partner->Id] = $partner->Name." - ".$partner->Homepage;
        }

        if(Request::ajax()){
            return json_encode($partners);
        }
        return $partners;
    }

    public function show($id){

        $cont = new RestController();

        $partner = $cont->getRequest('Partners('.$id.')?$expand=Leads,ClientAlias($expand=Contract($expand=Product,InvoiceLines($expand=Invoice))),User($select=FullName),Country($select=CountryCode)');
        if($partner instanceof View){
            return $partner;
        }
        return view('partners.show',compact('partner'));
    }
    public function edit($id){

        $cont = new RestController();

        $partner = $cont->getRequest('Partners('.$id.')?$expand=Leads,ClientAlias($expand=Contract($expand=Product,InvoiceLines($expand=Invoice))),User($select=FullName),Country($select=CountryCode)');
        if($partner instanceof View){
            return $partner;
        }
        $users = UsersController::usersList();
        $countries = CountriesController::countriesList();
        return view('partners.edit',compact('partner','users','countries'));
    }
}
