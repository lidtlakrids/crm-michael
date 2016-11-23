<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\View\View;

class OrderFieldOptionsController extends Controller
{

    /**
     * shows the menu for option editing
     *
     * @param $id
     * @return null
     */
    public function edit($id){

        //get the option
        $cont = new RestController();

        $option = $cont->getRequest('OrderFieldOptions('.$id.")");
        if($option instanceof View){
            return $option;
        }

        return view('orderFieldOptions.edit',compact('option'));
    }
}
