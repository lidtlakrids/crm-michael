<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Illuminate\View\View;

class ProductsController extends Controller {



	/**
	 * Display a listing of products
	 *
	 * @return View
	 */
	public function index()
    {
        $cont  = new RestController();
//        $products = $cont->getRequest('Products');
//        if($products instanceof RedirectResponse)
//        {
//            return $products;
//        }
//		$products = $products->value;
        return view('products.index');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return View
	 */
	public function create()
	{
		$departments = ProductDepartmentsController::getDepartmentsList();

		$types = ProductTypesController::getProductTypesList();

		return view('products.create',compact('departments','types'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified product
	 *
	 * @param  int  $id
	 * @return View
	 */
	public function show($id)
	{
        $cont = new RestController();
        $product = $cont->getRequest('Products('.$id.')?$expand=ProductDepartment,ProductType');
		if($product instanceof View)
		{
			return $product;
		}

		$sizes = $cont->getEnumProperties(['PackageSize']);
		$sizes = $sizes['PackageSize'];
		$templates = TaskTemplatesController::templateList();
		JavaScriptFacade::put(['sizes'=>$sizes,'taskTemplates'=>$templates]);

        return view('products.show',compact('product'));
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

		$product = $cont->getRequest("Products($id)".'?$expand=ProductDepartment,ProductType');

        if($product instanceof View)
        {
            return $product;
        }

		$departments = ProductDepartmentsController::getDepartmentsList();

		$types = ProductTypesController::getProductTypesList();

        return view('products.edit',compact('product','departments','types'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
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
     * returns a list of departments
     */
    public static function getProductsList(){

        $cont = new RestController();

        $result = $cont->getRequest('Products?$filter='.urlencode('Active eq true'));

        $products = [];

        if($result instanceof View){
            return $products;
        }

        foreach($result->value as $p){
            $products[$p->Id]=$p->Name;
        }

        return $products;

    }

    /**
     * returns a list of departments
     */
    public static function getAddonsList(){

        $cont = new RestController();

        $result = $cont->getRequest('Products?$filter='.urlencode('Active eq true'));

        $products = [];

        if($result instanceof View){
            return $products;
        }

        foreach($result->value as $p){
            $products[$p->Id]=$p->Name;
        }

        return $products;

    }

    /**
     *Returns products, grouped by their type
     *
     */
	public static function getProductsByType(){
        $cont = new RestController();

        $result = $cont->getRequest('Products?$expand=ProductType&$filter='.urlencode('Active eq true'));

        $products = [];

        if($result instanceof View){
            return $products;
        }

        foreach($result->value as $product){
            if(!isset($products[$product->ProductType->Name]))
            {
                $products[$product->ProductType->Name] =[];
                array_push($products[$product->ProductType->Name],$package);
            }else{
                array_push($products[$product->ProductType->Name],$package);
            }
        }
        return $products;
	}
}
