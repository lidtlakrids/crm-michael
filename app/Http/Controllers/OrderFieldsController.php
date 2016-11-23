<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OrderFieldsController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$cont = new RestController();
        $fields = $cont->getRequest('OrderFields');
        if($fields instanceof RedirectResponse)
        {
            return $fields;
        }

        $ordertypes = OrderTypesController::getList();
		
        return view('orderFields.index',compact('fields','ordertypes'));
	}

	/**
	 * Show the form for creating a new resource.
	 * todo options
	 * @return View
	 */
	public function create()
	{
        $cont = new RestController();
        $result = $cont->getRequest('OrderTypes');
        if($result instanceof RedirectResponse)
        {
            return $result;
        }
        $orderTypes = [];
        foreach ($result->value as $orderType) {
            $orderTypes[$orderType->Id] = $orderType->FormName;
        }
        $fieldTypes = $cont->getEnumProperties(['OrderFieldType']);
        $fieldTypes = isset($fieldTypes['OrderFieldType']) ? $fieldTypes['OrderFieldType'] : [];
		return view('orderFields.create',compact('fieldTypes','orderTypes'));
	}

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
	public function store()
	{
        $params = Request::input();
        unset($params['_token']);
        $orderIds = array_pull($params,'OrderIds');

        $params['Active']   = isset($params['Active'])?     true:false;
        $params['Required'] = isset($params['Required'])?   true:false;
        $params['Special']  = isset($params['Special'])?   true:false;


        $cont = new RestController();
        //inser the new field
        $result = $cont->postRequest('OrderFields',$params);
        if($result instanceof RedirectResponse)
        {
            return $result;
        }
        if($orderIds){
        //associate it with forms
            foreach ($orderIds as $id)
            {
                //todo error handling
                $cont->postRequest('OrderTypeOrderFields',
                    [
                     'OrderType_Id'=>$id,
                     'OrderField_Id'=>$result->Id
                    ]
                );
            }
        }
        return Redirect::to('order-fields');
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

        $field = $cont->getRequest("OrderFields($id)".'?$expand=OrderFieldOption');
        if($field instanceof View){
            return $field;
        }

        return view('orderFields.show',compact('field'));
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
        $expand = '$expand';
        $field  = $cont->getRequest("OrderFields($id)?$expand=OrderFieldOption".urlencode('($orderby=SortOrder desc)'));
        if($field instanceof View){
            return $field;
        }

        //sort the options, since we can't do it in the query

        usort($field->OrderFieldOption, function($a, $b)
        {
            return $a->SortOrder - $b->SortOrder;
        });
        $fieldTypes = $cont->getEnumProperties(['OrderFieldType']);
        if(isset($fieldTypes['OrderFieldType'])){
            $fieldTypes = $fieldTypes['OrderFieldType'];
        }
        return view('orderFields.edit',compact('field','fieldTypes'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$input = Request::all();
        unset($input['_token']);
        $input['Active']= ($input['Active'] =="on")? true:false;
        $input['Required']= ($input['Required'] =="on")? true:false;
        $cont = new RestController();
        $result = $cont->patchRequest("OrderFields($id)",$input);

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

}
