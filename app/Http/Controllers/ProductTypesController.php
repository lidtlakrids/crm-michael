<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
class ProductTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('productTypes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('productTypes.create');
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

        $productType = $cont->getRequest("ProductTypes($id)?".'?$expand=ContractFieldLink');
        if($productType instanceof View){
            return $productType;
        }

        $fields = $cont->getRequest('ContractFields?$filter='.urlencode("FieldLinks/any(d:d/ProductType_Id eq $id)").'&$expand=FieldOption');
        if($fields instanceof View){
            $fields = [];
        }else{
            $fields = $fields->value;
        }

        return view('productTypes.show',compact('productType','fields'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $cont = new RestController();

        $productType = $cont->getRequest("ProductTypes($id)");
        if($productType instanceof View){
            return $productType;
        }

        return view('productTypes.edit',compact('productType'));
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

    /**
     * returns a list of departments
     */
    public static function getProductTypesList(){
        $cont = new RestController();

        $result = $cont->getRequest('ProductTypes');

        $types = [];

        if($result instanceof View){
            return $types;
        }

        foreach($result->value as $type){
            $types[$type->Id] = $type->Name;
        }

        return $types;
    }


    /**
     * returns a select, used to query data
     *
     * @param $property if we set this, it will overwrite the default : User_Id
     * @return array
     */
    public static function queryTypesList($property = null){
        $cont = new RestController();
        $types = $cont->getRequest('ProductTypes?$orderby=Name');
        if($types instanceof View){
            return [];
        }
        $typesList = [];
        if($property == null){
            $property = "Type_Id";
        }

        foreach($types->value as $type){
            $typesList[$property.' eq '.$type->Id]=$type->Name;
        }
        return $typesList;
    }

    public function addFields($id){


        return view('productTypes.addFields',compact('id'));
    }

}
