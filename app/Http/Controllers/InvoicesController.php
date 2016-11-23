<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;

class InvoicesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $cont = new RestController();
        $statuses = $cont->getEnumQuerySelect('InvoiceStatus','Status');
        $types = $cont->getEnumQuerySelect('InvoiceType','Type');
        $sellers = UsersController::queryListByRoles(['Sales']);

        return view('invoices.index', compact('sellers','statuses','types'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $users = UsersController::usersList();

        $countries = [1=>'DK',2=>'NO']; // todo get them from the api

        return view('clients.createAlias',compact('users','ClientId','countries'));
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
     * Display the specified resource.
     *
     * @param  int $id
     * @return View
     */
    public function show($id)
    {
        $cont = new RestController();
        $invoice = $cont->getRequest('Invoices('.$id.')?$expand=InvoiceLine($expand=Product($select=Name,Description),Contract($select=Manager_Id)),ClientAlias($expand=Client($select=CINumber,ClientManager_Id),Country),User($select=FullName)');
        if($invoice instanceof View)
        {
            return $invoice;
        }
        if(!$this->isOwner($invoice)){
            return view('errors.denied');
        }
        $statuses = $cont->getEnumProperties(['InvoiceStatus']);
        $statuses = isset($statuses['InvoiceStatus'])?$statuses['InvoiceStatus']:[];
        JavaScriptFacade::put(['statuses'=>$statuses]);
        return view('invoices.show', compact('invoice','statuses'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Creates a draft for a contract
     *
     * @param $contractId
     * @return \Illuminate\View\View|null
     */
    public function draftForContract($contractId)
    {
        $cont = new RestController();

        $draft = $cont->postRequest('Drafts/ByContractId/'.$contractId);
        if($draft instanceof RedirectResponse)
        {
            return $draft;
        }
        //get a list of users for the select
        $users = UsersController::usersList();
        $userSelect=[];
        foreach($users as $id=>$name)
        {
            array_push($userSelect,array('label'=>$name,'value'=>$id));
        }
        /// add empty option for the users select
        array_unshift($userSelect,['label'=>Lang::get('labels.select-user'),'value'=>'']);
        $userSelect = json_encode($userSelect,true);

        return view('invoices.draft',compact('draft','userSelect'));
    }

    public function invoiceForContract($contractId)
    {
        $cont = new RestController();

        $invoice = $cont->postRequest('Drafts/ByContractId/'.$contractId);
        if($invoice instanceof View)
        {
            return $invoice;
        }

        return view('invoices.show',compact('invoice'));
    }


    public function invoicePdf($invoiceHash)
    {
        $cont = new RestController();

        $result = $cont->getRequest("Publics('$invoiceHash')");
        if($result instanceof View){
            return $result;
        }
        $headers = array(
            'Content-type' => 'application/pdf',
        );
        return Response::make($result, 200, $headers);
    }

    public function updateDraftLine()
    {
        $input = Request::all();
        //initialize post data
        $data=[];
        $cont = new RestController();

        switch($input['action']){
            case 'create':
                //user Id
                $userId = array_pull($input['data'],'User');
                $data['User']= ['Id'=>$userId];
                $data = array_merge($data,$input['data']);
                break;

            case 'edit':
                //draft line id
                $data['Id']=$input['id'];

                //user Id
                $userId = array_pull($input['data'],'User');
                $data['User']= ['Id'=>$userId];

                //add the rest of the variables
                $data = array_merge($data,$input['data']);

                $result = $cont->putRequest('DraftLines/'.$data['Id'],$data);
                break;
            case 'remove':
                $ids = $input['id'];
                foreach($ids as $id) {
                    $result = $cont->deleteRequest('DraftLines/'.$id);
                }
                    break;

            default: break;
        }

        if($result instanceof RedirectResponse)
        {
            return $result;
        }
        return json_encode(array('status'=>'success'));
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
        if ($result || in_array($userId,['34','155'])) { // Allow Mikker to see everything
            return true;
        }
        //remove the user role. we don't care about it
        $roles = array_diff($roles, array('User'));
        $roles = array_values($roles);

        switch ($roles[0]){
            case "Client Manager":
                return $item->ClientAlias->Client->ClientManager_Id == null? true: $userId ? true:false;
                break;
            case "Adwords":
            case "SEO":
                if(isset($item->InvoiceLine)){
                    foreach ($item->InvoiceLine as $line){
                        if($line->Contract->Manager_Id == $userId){ return true;};
                    }
                }
                return false;
                break;
            case "Sales":
                return $item->ClientAlias->User_Id == $userId  || $item->User_Id == $userId ? true:false;
                break;
            case "Accounting": // accounting should see invoices
                return true;
                break;
            default :
                break;
        }
        //default, we deny.
        return false;
    }
}
