<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ProductPackagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('productPackages.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $departments = ProductDepartmentsController::getDepartmentsList();

        $types = ProductTypesController::getProductTypesList();

        $products = ProductsController::getProductsList();

        $cont = new RestController();
        $paymentTerms = $cont->getEnumProperties(['ContractTerms']);
        $paymentTerms = (isset($paymentTerms['ContractTerms'])? $paymentTerms['ContractTerms']:[]);
        $sizes = $cont->getEnumProperties(['PackageSize']);
        $sizes = $sizes['PackageSize'];
        $taskTempaltes = TaskTemplatesController::templateList();

        return view('productPackages.create',compact('departments','types','products','paymentTerms','sizes','taskTemplates'));
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

        $package = $cont->getRequest("ProductPackages($id)?".'$expand=Product,Products($expand=Product($select=Name))');

        if($package instanceof View){
            return $package;
        }

        return view('productPackages.show',compact('package'));
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

        $package = $cont->getRequest("ProductPackages($id)?".'$expand=Product,Products($expand=Product)');

        if($package instanceof View){
            return $package;
        }
        $allowedProducts = [];

        foreach($package->Products as $p){
            array_push($allowedProducts,$p->Product_Id);
        }


        $paymentTerms = $cont->getEnumProperties(['ContractTerms']);
        $paymentTerms = (isset($paymentTerms['ContractTerms'])? $paymentTerms['ContractTerms']:[]);
        $products = ProductsController::getProductsList();
        $sizes = $cont->getEnumProperties(['PackageSize']);
        $sizes = $sizes['PackageSize'];
        return view('productPackages.edit',compact('package','products','allowedProducts','paymentTerms','sizes'));
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
     * Returns a list of active packages
     */
    public static function getPackageList(){

        $cont= new RestController();

        $packagesRaw = $cont->getRequest('ProductPackages?$expand=Product&$filter='.urlencode('Active eq true'));

        if($packagesRaw instanceof View){
            return [];
        }

        return $packagesRaw->value;
    }

    /**
     * returns packages grouped by type
     *
     * @return array
     */
    public static function getPackagesByType(){
        $cont = new RestController();

        $packagesRaw = $cont->getRequest('ProductPackages?$expand=Product($expand=ProductType)&$filter='.urlencode('Active eq true and Product ne null').'&$orderby=Product/SalePrice');
        if($packagesRaw instanceof View){
            return [];
        }
        $packages= [];

        foreach($packagesRaw->value as $package){
            if(!isset($packages[$package->Product->ProductType->Name]))
            {
               $packages[$package->Product->ProductType->Name] =[];
                array_push($packages[$package->Product->ProductType->Name],$package);
            }else{
                array_push($packages[$package->Product->ProductType->Name],$package);
            }
        }

        return $packages;
    }
}
