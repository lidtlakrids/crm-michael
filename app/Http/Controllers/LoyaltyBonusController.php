<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\View\View;

class LoyaltyBonusController extends Controller
{
    public function index(){

        return view('loyaltyBonus.index');
    }

    public function edit($id){
        $cont = new RestController();
        $bonus = $cont->getRequest("LoyaltyBonus($id)");
        if($bonus instanceof View){
            return $bonus;
        }

        return view('loyaltyBonus.edit',compact('bonus'));
    }
}
