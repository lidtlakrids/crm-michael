<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Response;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;
use NumberFormatter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Illuminate\View\View;
class DraftsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $cont = new RestController();
        $statuses = $cont->getEnumQuerySelect('DraftStatus','Status');
        $sellers = UsersController::listByRoles(['Sales']);
        $periods = '';
        return view('drafts.index',compact('statuses','sellers'));
    }

    /**
     * Create draft for AliasId
     *
     * @param $aliasId
     * @return Response
     */
    public function createForAlias($aliasId)
    {
        //
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
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $cont = new RestController();

        $draft = $cont->getRequest("Drafts($id)?".'$expand=DraftLine($expand=Contract($expand=User($select=UserName,FullName),ContractType),User($select=UserName)),ClientAlias($expand=Client($select=CINumber),Country($select=VatRate)),User($select=Id,UserName,FullName)');
        if($draft instanceof View)
        {
            return $draft;
        }
        //get all contracts for the clientAlias
        $clientAliasId  = $draft->ClientAlias->Id;

        $contracts = $cont->getRequest("ClientAlias($clientAliasId)".'/Contract?$expand=ContractType,Product($select=Name),OriginalOrder($select=Id)&$orderby=Id+desc');
        if($contracts instanceof View)
        {
            return $contracts;
        }
        $contracts = $contracts->value;

       // this might be used
        if(!empty($contracts)){//remove contracts that are already in the draft
           $draftContractIds = $this->removeContractsInDraftLines($contracts,$draft->DraftLine);
        }
//        if(!empty($contracts)){// group contracts into main and children
//            $contracts  = ClientAliasController::groupClientContracts($contracts);
//            rsort($contracts);
//        }

        //get a list of users for the select
        $users = UsersController::usersList();
        $userSelect=[];
        foreach($users as $id=>$name) {array_push($userSelect,array('label'=>$name,'value'=>$id));}  // one liners for life
        /// add empty option for the users select
        array_unshift($userSelect,['label'=>Lang::get('labels.select-user'),'value'=>'']);

        //json encoded because we will print it in javascript   UPDATE 01-10-2015 encoding happens in the view, because we use this array for other selects too
        //$userSelect = json_encode($userSelect,true);

        //get the invoice pay period from the settings
        $invoicePayPeriod= 8; // make it default, fuck it
        $result = $cont->getRequest('Settings?$select=Value&$filter='.urlencode('Model eq \'Invoice\' and Name eq \'Due\''));
        if(!$result instanceof View){
            if(!empty($result->value)){
                $invoicePayPeriod = $result->value[0]->Value;
            }
        }
        $types = $cont->getEnumProperties(['InvoiceType']);
        $types=$types['InvoiceType'];
        JavaScriptFacade::put(['draftType'=>$draft->Type,'invoiceTypes'=>$types]);
        return view('drafts.show',compact('draft','users','userSelect','contracts','draftContractIds','invoicePayPeriod'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @return Response
     * @internal param int $id
     */
    public function updateDraftLine(Request $request)
    {
        $input = $request->all();
        //initalize post data
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
                if($userId ==""){
                    $data['User_Id']= null;
                }else{
                $data['User_Id']= $userId;
                }
                //add the rest of the variables
                $data = array_merge($data,$input['data']);
                unset($data['NetAmount']); // Net amount no longer exists, but we simulate it;
                // format the discount
                $data['Discount'] = str_replace(",",".",$data['Discount']);


                $result = $cont->patchRequest('DraftLines('.$data['Id'].')',$data);
                if($result instanceof View)
                {
                    return false;
                }
                $responseArr = [];
                $responseArr['DT_RowId'] = "row_".$result->Id;
                $responseArr['Description']=$result->Description;
                $responseArr['Quantity']=$result->Quantity;
                $responseArr['UnitPrice']=$result->UnitPrice;
                $responseArr['Discount']=$result->Discount;
                $responseArr['NetAmount']=calculateLineDiscount($result);
                $responseArr['User']=($result->User_Id!=null)?UsersController::getUserNameById($result->User_Id):"";
                return json_encode($responseArr);
                break;

            case 'remove':

                $ids = $input['id'];
                $cont = new RestController();

                // the contracts that need to be unlocked after the lines are removed
                $contractIds = [];

                foreach($ids as $id) {

                //get the contract of the draft line
                    $contract = $cont->getRequest("DraftLines($id)".'/Contract?$select=Id');
                    if(!$contract instanceof RedirectResponse)
                    {
                        array_push($contractIds,$contract->Id);
                    }

                    $result = $cont->deleteRequest("DraftLines($id)");
                }
                return json_encode(['status'=>'success','ids'=>$contractIds]);
                break;

            default: break;
        }

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
     * this function compares which contracts are already in the draftlines and removes them if they are
     * this is so we don't put duplicate contracts in the draft line
     *
     * @param $contracts
     * @param $draftLines
     * @return array
     */
    public function removeContractsInDraftLines($contracts,$draftLines)
    {
        //initialize arrays with the ids for both contract places
        $contractIds = [];
        $draftContractIds = [];

        //get all contract ids
        foreach($contracts as $c){
           $contractIds[$c->Id] = $c;
        }

        //get all draft contract ids
        foreach($draftLines as $dLine)
        {
            if($dLine->Contract != null)
            $draftContractIds[$dLine->Contract->Id] = $dLine->Contract->Id;
        }

            return $draftContractIds; // TODO for now we need only the contract ids from the draft lines

        //remove duplicate contracts. very interesting since we use object. Using anon function
        $contracts = array_udiff($contractIds, $draftContractIds,
            function ($obj_a, $obj_b) {
                return $obj_a->Id - $obj_b->Id;
            }
        );
        return $contracts;
    }

    /**
     *
     * show a draft preview
     * @param $id
     * @return null
     */
    public function draftPreview($id){

        $cont = new RestController();

        $response = $cont->getRequest("Drafts($id)/action.Preview");

        if($response instanceof View){
            return $response;
        }

        $headers = array(
            'Content-type'          => 'application/pdf',
        );
        return Response::make($response, 200, $headers);

    }











}
