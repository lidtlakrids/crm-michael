<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ClientAliasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $sellers = UsersController::queryListByRoles(['Sales','Administrator']);
        $statuses = [
            "Contract/any(d:d/Status eq 'Active' or d/Status eq 'Standby')" => 'With active contracts'
        ];
        return view('clientAlias.index',compact('sellers','statuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $ClientId
     * @return View
     */
    public function create($ClientId)
    {
        $users = UsersController::usersList(); // todo get users
        $countries = CountriesController::countriesList(); // todo get them from the api
        return view('clientAlias.create',compact('users','ClientId','countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return View
     */
    public function store()
    {
        $input = Request::all();
        unset($input['_token']);


        $clientid = $input['Client_Id'];
        // initialize data for the post request
        $data            = [];
        $data['Client']  = ['Id'=>array_pull($input,'Client_Id')];
        $data['Country_Id'] = ['Id'=>array_pull($input,'Country')];
//        $data['User']    = ['Id'=>array_pull($input,'SellerId')];
        $data['User_Id']    = "7e38624c-c31d-4766-8a0e-30ec037cc67b";
        $data= array_merge($input,$data);
        $con = new RestController();
        $result = $con->postRequest('Clients('.$clientid.')/ClientAlias',$data);

        if($result instanceof View)
        {
            return $result;
        }
        Session::flash('message',Lang::get('messages.client-created'));
        return Redirect::to('clients/showAlias/'.$result->Id);
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
        $clientAlias = $cont->getRequest('ClientAlias('.$id.')?$expand=Invoice,User($select=FullName),Client($select=Id,CINumber,ClientManager_Id;$expand=ClientManager($select=FullName,UserName)),'.
            'Country,Contact($filter=MainContact+eq+true),Contract($expand=Product($select=Name,SalePrice),'.
            'Country($select=CountryCode),Manager($select=FullName),User($select=UserName),Children;$orderby=Id+desc)');
        if ($clientAlias instanceof View)
        {
            return $clientAlias;
        }

        if(!$this->isOwner($clientAlias)){
            return view('errors.denied');
        }
        $clientManagers = UsersController::listByRoles(['Client Manager']);

        return view('clientAlias.show', compact('clientAlias','clientManagers'));
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
        $clientAlias = $cont->getRequest('ClientAlias('.$id.')?$expand=Contact,Country,User($select=Id,UserName,FullName),Client($select=CINumber,ClientManager_Id),Taxonomy,Contract($select=Manager_Id)');
        if($clientAlias instanceof View)
        {
            return $clientAlias;
        }

        if(!$this->isOwner($clientAlias)){
            return view('errors.denied');
        }
        $countries = CountriesController::countriesList();
        $users = UsersController::usersList();
        $teams = UsersController::teamsList();

        return view('clientAlias.edit',compact('clientAlias','countries','users','teams'));

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

        $cont = new RestController();

        $result = $cont->patchRequest('ClientAlias('.$id.')',$input);


        //todo why the fuck doesn't it get the post
        //todo fuck this, made it with js instead
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
     * Depricated but pretty. Let it stay
     * d
     * @param $aliasId
     * @return string
     */
    public function aliasInvoices($aliasId)
    {
        $cont = new RestController();
        $result = $cont->getRequest('ClientAlias('.$aliasId.')?$select=Id&$expand=Invoice($select=Id,InvoiceNumber,Name,Created,Payed,Due,Address,ZipCode,City,NetAmount)');
        if ($result instanceof RedirectResponse) {
            return json_encode(array('status'=>'error'));
        }
        $invoices = "";
        $invoices = " <table class='table table-bordered' id='dataTableInvoices'>
                                <thead>
                                <tr>
                                    <th>" . Lang::get('labels.invoice-number') . "</th>
                                    <th>" . Lang::get('labels.name') . "</th>
                                    <th>" . Lang::get('labels.created') . "</th>
                                    <th>" . Lang::get('labels.paid') . "</th>
                                    <th>" . Lang::get('labels.due') . "</th>
                                    <th>" . Lang::get('labels.address') . "</th>
                                    <th>" . Lang::get('labels.net-amount') . "</th>
                                </tr>
                                </thead>
                                <tbody id='ordersBody'>";

        // $result is oData response
        foreach ($result->Invoice as $invoice) {

            if($invoice->Payed){
                $payed = Carbon::parse($invoice->Payed)->format('d-m-Y H:i');
            }else{$payed = "---";}


            $invoices .= "<tr>";
            $invoices .= "<td> <a href='" . url('invoices/show', $invoice->Id) . "'>" . $invoice->InvoiceNumber . " </a></td>";
            $invoices .= "<td>" . $invoice->Name . "</td>";
            $invoices .= "<td>" . Carbon::parse($invoice->Created)->format('d-m-Y H:i') . "</td>";
            $invoices .= "<td>" . $payed. "</td>";
            $invoices .= "<td>" . Carbon::parse($invoice->Due)->format('d-m-Y H:i') . "</td>";
            $invoices .= "<td>" . $invoice->Address . ", " . $invoice->ZipCode . ", " . $invoice->City . "</td>";
            $invoices .= "<td>" . number_format($invoice->NetAmount ,2,',','.') . "</td>";
            $invoices .= "</tr>";

        }
        $invoices .= "<span style='display: none;' id='invoicesLoaded'></span>";
        $invoices .= " </tbody> </table>";
        return $invoices;
    }

    /**
     * Depricated but pretty. Let it stay
     * d
     * @param $aliasId
     * @return string
     */
    public function aliasOrders($aliasId)
    {
        $cont = new RestController();
        $result = $cont->getRequest('ClientAlias('.$aliasId.')?$select=Id&$expand=Order($select=Id,Created,Approved,Confirmed,Archived;$expand=OrderType($select=FormName),User($select=UserName))');
        if ($result instanceof RedirectResponse) {
            return json_encode(array('status'=>'error'));
        }
        $orderController = new OrdersController();
        $orders = "";
        $orders = " <table class='table table-bordered' id='dataTableOrders'>
                                <thead>
                                <tr>
                                    <th>" . Lang::get('labels.order-number') . "</th>
                                    <th>" . Lang::get('labels.order-type') . "</th>
                                    <th>" . Lang::get('labels.created-date') . "</th>
                                    <th>" . Lang::get('labels.order-status') . "</th>
                                    <th>" . Lang::get('labels.seller') . "</th>
                                </tr>
                                </thead>
                                <tbody id='ordersBody'>";

        // $result->value is because oData
        foreach ($result->Order as $order) {
            $orders .= "<tr>";
            $orders .= "<td> <a href='" . url('orders/show', $order->Id) . "' >" . $order->Id . " </a></td>";
            $orders .= "<td>" . $order->OrderType->FormName . "</td>";
            $orders .= "<td>" . Carbon::parse($order->Created)->format('d-m-Y H:i') . "</td>";
            $status = $orderController->orderStatusIcons($order);
            $orders .= "<td>" . $status . "</td>";
            $orders .= "<td>" . $order->User->UserName . "</td>";
            $orders .= "</tr>";
        }
        $orders .= "<span style='display: none;' id='ordersLoaded'></span>";
        $orders .= " </tbody> </table>";

        return $orders;
    }


    /**
     * search trough clientAlias
     * @return string
     * @internal param $str
     */
    public function search()
    {
        $input = Request::input('term');
        $cont = new RestController();
        $str = trim($input);
        $result = $cont->getRequest('ClientAlias?$filter='.urlencode("indexof(tolower(Name), '$str') ge 0 or indexof(tolower(Homepage), '$str') ge 0"));
        if($result instanceof View){
            return json_encode(['Error']);
        }
        $clients= [];
        foreach($result->value as $k){
            array_push($clients,$k->Name);
        }
        return json_encode($clients);
    }

    /**
     * Function that groups the contracts with their children contracts
     *
     * @param array $contracts
     * @return array
     *
     */
    public static function groupClientContracts(array $contracts){

        //initialize the array
        $groupedContracts = [];

        //add each contract to the parentId in the array
        foreach($contracts as $contract)
        {
            if($contract->Parent_Id == null && !array_key_exists($contract->Parent_Id,$groupedContracts)){ //null means this is parent contract
                $groupedContracts[$contract->Id] = $contract;
                $groupedContracts[$contract->Id]->Children = [];

            }elseif($contract->Parent_Id != null && isset($groupedContracts[$contract->Parent_Id])){
                array_push($groupedContracts[$contract->Parent_Id]->Children,$contract);
            }
        }
        return $groupedContracts;
    }


    /**
     * Display stat of client Alias.
     *
     * @return View
     */

    public function stat()
    {
        return view('clientAlias.stat');
    }
    /**
     * Display graph of client Alias for testing.
     *
     * @return View
     */
    public function graph()
    {
        return view('clientAlias.graph');
    }

    /**
     * checks for entity ownership depending on a role
     * @param $item
     * @return bool
     */
    public function isOwner($item)
    {
        $roles = Session::get('roles');
        $userId = Auth::user()->externalId;

        $result = !empty(array_intersect($roles, ['Administrator','Accounting','Developer']));
        if ($result ||  in_array($userId,['34',"55",'155','128','119'])) { // 34 Allow Mikker / Andreas Wewer /Michael Posberg/ Kristoffer(seo)/ charlotte to see all clients
            return true;
        }
        //remove the user role. we don't care about it
        $roles = array_diff($roles, array('User'));
        $roles = array_values($roles);

        switch ($roles[0]){
            case "Client Manager":
                return $item->Client->ClientManager_Id == null? true: Auth::user()->externalId ? true:false;
                break;
            case "Adwords":
                if(isset($item->Contract)){
                    foreach ($item->Contract as $contract){
                        if(in_array($contract->ContractType_Id,[2,4,18,19,20,17])){ return true;};
                    }
                }
                break;
            case "SEO":
                if(isset($item->Contract)){
                    foreach ($item->Contract as $contract){
                        if(in_array($contract->ContractType_Id,[2,3,8,18,19,20])){ return true;};
                    }
                }
                break;
            case "Sales":
                return $item->User_Id == Auth::user()->externalId ? true:false;
                break;
            default :
                return $item->User_Id == Auth::user()->externalId ? true:false;
                break;
        }
        //default, we deny.
        return false;
    }
}
