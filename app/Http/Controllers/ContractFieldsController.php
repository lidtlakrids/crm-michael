<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class contractFieldsController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return View
	 */
	public function index()
	{
		$cont = new RestController();
        $fields = $cont->getRequest('ContractFields');
        if($fields instanceof RedirectResponse)
        {
            return $fields;
        }

        $ordertypes = ProductTypesController::getProductTypesList();
		
        return view('contractFields.index',compact('fields','ordertypes'));
	}

	/**
	 * Show the form for creating a new resource.
	 * todo options
	 * @return View
	 */
	public function create()
	{
        $cont = new RestController();
        $types = ProductTypesController::getProductTypesList();
        $fieldTypes = $cont->getEnumProperties(['OrderFieldType']);
        $fieldTypes = isset($fieldTypes['OrderFieldType']) ? $fieldTypes['OrderFieldType'] : [];

		return view('contractFields.create',compact('fieldTypes','types'));
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


        $cont = new RestController();
        //inser the new field
        $result = $cont->postRequest('ContractFields',$params);
        if($result instanceof RedirectResponse)
        {
            return $result;
        }
        if($orderIds){
        //associate it with forms
            foreach ($orderIds as $id)
            {
               $result = $cont->postRequest('ContractFields('.$result->Id.')/AddTo',
                    [
                     'ProductType_Id'=>$id,
                    ]
                );
            }
        }
        return Redirect::to('contract-fields');
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

        $field = $cont->getRequest("contractFields($id)".'?$expand=OrderFieldOption');
        if($field instanceof View){
            return $field;
        }

        return view('contractFields.show',compact('field'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return View
	 */
	public function edit($id)
	{
		$cont = new RestController();
        $expand = '$expand';
        $field  = $cont->getRequest("ContractFields($id)?$expand=FieldOption");
        if($field instanceof View){
            return $field;
        }

        $fieldTypes = $cont->getEnumProperties(['OrderFieldType']);
        if(isset($fieldTypes['OrderFieldType'])){
            $fieldTypes = $fieldTypes['OrderFieldType'];
        }
        return view('contractFields.edit',compact('field','fieldTypes'));
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
        $result = $cont->patchRequest("contractFields($id)",$input);

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
