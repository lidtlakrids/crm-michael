<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OrderTypesController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        $cont        = new RestController();
        $orderTypes = $cont->getRequest('OrderTypes');
        if($orderTypes instanceof RedirectResponse)
        {
            return $orderTypes;
        }
        $orderTypes = $orderTypes->value;
		return view('orderTypes.index',compact('orderTypes'));
	}

    /**
     * get a list of order types used for select
     *
     * @return string
     */
    public static function getList()
    {
        $cont        = new RestController();
        $orderTypesRaw = $cont->getRequest('OrderTypes?$filter=Type_Id+ne+null');
        if($orderTypesRaw instanceof View)
        {
            $response_array['status'] = 'error';
            return json_encode($response_array);
        }
        $orderTypes = array();
        foreach($orderTypesRaw->value as $ot)
        {
            //filter forms without name
            if($ot->FormName==null){
                continue;
            }
            $orderTypes[$ot->Id] = $ot->FormName;
        }
        return $orderTypes;
    }
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        $types = ProductTypesController::getProductTypesList();

		return view('orderTypes.create',compact('types'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Request::all();

        $cont = new RestController();
        $result = $cont->postRequest('OrderTypes',["FormName"=>$input['FormName'],"Type_Id"=>$input['Type_Id']]);
        if($result instanceof View)
        {
            return $result;
        }

        return redirect('ordertypes');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        $cont = new RestController();

        $orderType = $cont->getRequest('OrderTypes('.$id.')?$expand=OrderTypeOrderField($expand=OrderField($expand=OrderFieldOption))');
        if($orderType  instanceof RedirectResponse)
        {
            return $orderType ;
        }
        usort($orderType->OrderTypeOrderField,
            function($a, $b)
            {
                $t1 = $a->SortOrder;
                $t2 = $b->SortOrder;
                return $t1 - $t2;
            });
        return view('orderTypes.show',compact('orderType'));

	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$cont = new RestController();
        $orderType = $cont->getRequest('OrderTypes('.$id.')?$expand=Type');
        if($orderType instanceof View)
        {
            return $orderType;
        }

        $types = ProductTypesController::getProductTypesList();


        return view('orderTypes.edit',compact('orderType','types'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $input= Request::all();
        $cont = new RestController();

        $result = $cont->patchRequest("OrderTypes($id)",["Id"=>$id,"FormName"=>$input['FormName']]);
        if($result instanceof RedirectResponse)
        {
            return Redirect::back();
        }
        return redirect('ordertypes/show/'.$id);

    }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

    /**
     * add fields to order type view
     * @param $orderType
     * @return View
     */
    public function addFields($orderType){

        $cont = new RestController();

        $result = $cont->getRequest("OrderTypes($orderType)".'/OrderTypeOrderField');
        if(!$result instanceof View){
            $existingFields = [];
            foreach ($result->value as $a){
                if($a->OrderField_Id != null){
                array_push($existingFields,"Id ne ".$a->OrderField_Id);
                }
            }
        }
        JavaScriptFacade::put(['fields'=>$existingFields]);

        return view('orderTypes.addFields',compact('orderType'));
    }






    public function sortFields()
    {

    }

    public function updateFieldOrder(Request $request)
    {
        $input = $request::get('order'); // wtf is this... todo this fucking shit

        // first id is the id of the Order Field, Second id is the id of the Link
       // parse_str($input['order'],$array);
        $sortNumber = 1;
        $cont = new RestController();

        foreach($input as $linkId)
        {
            $result = $cont->patchRequest('OrderTypeOrderFields('.$linkId.')',array('Id'=>$linkId,'SortOrder'=>$sortNumber)); //todo page number
            $sortNumber++;
        }
        echo Lang::get('messages.sort-order-updated');
    }

    public function removeFieldLink()
    {
        $input = Request::only('linkId');
        $cont = new RestController();
        $result = $cont->deleteRequest('OrderTypeOrderFields('.$input['linkId'].')');
        if($result instanceof RedirectResponse)
        {
            return $result;
        }
        Session::flash('message',Lang::get('messages.field-removed'));
        return Redirect::back();
    }

    public function addFieldLink()
    {
        $input = Request::all();
        $cont = new RestController();
        foreach ($input['fieldIds'] as $fieldId=>$val)
        {
            $cont->postRequest('OrderTypeOrderFields',
                [
                    'OrderType_Id'=>$input['OrderType'],
                    'OrderField_Id'=>$fieldId
                ]
            );
        }
        Session::flash('message',Lang::get('messages.fields-added'));
        return Redirect::back();
    }

    public function fieldsList($orderTypeId)
    {
        $cont = new RestController();

        $result = $cont->getRequest('OrderFields');
        if($result instanceof RedirectResponse)
        {
            return $result;
        }
        $fields= '
            <form method="POST" action="http://'.$_SERVER['HTTP_HOST'].'/ordertypes/addFieldLink">
            <input name="_token" type="hidden" value="'.csrf_token().'">
            <input id="orderTypeIdForm" name="OrderType" type="hidden" value="'.$orderTypeId.'">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                      <th></th>
                      <th>'.Lang::get('labels.display-name').'</th>
                      <th>'.Lang::get('labels.value').'</th>
                      <th>'.Lang::get('labels.description').'</th>
                      <th>'.Lang::get('labels.type').'</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($result->value as $field)
        {
            $fields .= "<tr>";
            $fields .= "<td> <input type='checkbox' name='fieldIds[".$field->Id."]'> </td>";
            $fields .= "<td> ".$field->DisplayName." </td>";
            $fields .= "<td> ".$field->ValueName."   </td>";
            $fields .= "<td> ".$field->Description." </td>";
            $fields .= "<td> ".$field->OrderFieldType." </td>";
        }
            $fields .= "</tbody></table>";
            $fields .= "<button class='btn btn-green' type='submit'>".Lang::get('labels.add-fields')."</button> / ";
            $fields .= "<a class='btn btn-primary' href='http://".$_SERVER['HTTP_HOST']."/orderFields/create'>".Lang::get('labels.create-field')."</a>";
            $fields .= "</form>";
        return $fields;
    }
}
