<?php

namespace App\Http\Controllers;

use App\Services\AdWordsApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;

class AdwordsController extends Controller
{

    //The model used for backend calls
    protected $model = "Contract";

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $sellers = UsersController::queryListByRoles(['Sales'],null,'ClientAlias/User_Id');
        $managers = UsersController::queryListByRoles(['Adwords'],'Manager_Id');
        $teamStatus = [
            '(not Activity/any(d:d/ActivityType eq \'Produced\') and not Activity/any(d:d/ActivityType eq \'Start\') and Manager_Id ne null)'=>Lang::get('labels.produce'),
            '(not Activity/any(d:d/ActivityType eq \'Start\') and Manager_Id ne null and Activity/any(d:d/ActivityType eq \'Produced\'))  and NeedInformation eq false'   =>Lang::get('labels.start'),
            'Activity/any(d:d/ActivityType eq webapi.Models.ContractActivityType\'Start\') and NeedInformation eq false'  =>Lang::get('labels.optimize'),
        ];

        $contractStatus = [
            'Status eq \'Active\''=>Lang::get('labels.active'),
            'Status eq \'Standby\''=>Lang::get('labels.standby'),
            'Status eq \'Suspended\''=>Lang::get('labels.suspended'),
        ];

        return view('adwords.index',compact('sellers','managers','teamStatus','contractStatus'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     * @param  int  $id
     * @return View
     */
    public function show($id)
    {
        $cont = new RestController();
        //get the contract
        $contract = $cont->getRequest("Contracts($id)".'?$expand=InformationSchemes($expand=FieldValue,Products($select=Id;$expand=Product($select=Name))),Product($select=Id,Name),ProductPackage($select=Id),' .
            'User($select=Id,UserName,FullName),'.
            'Country($select=CountryCode),'.
            'Activity($select=ActivityType,Created;$expand=Comment($select=Message),User($select=FullName);$orderby=Created),'.
            'ClientAlias($expand=User($select=FullName,UserName),'.
                'Country($select=CountryCode),Contact($select=Id,Name,Description,Phone,Email,JobFunction,Department,Birthdate),'.
                'Contract($expand=Manager($select=Id,FullName),OriginalOrder($select=Id),Product($select=Name),Country($select=CountryCode,Id)),'.
                'Client($select=CINumber,ClientManager_Id;$expand=ClientManager($select=FullName));$select=Name,PhoneNumber,Id,EMail,City,Address,zip,Homepage,AdwordsId,User_Id),'.
            'Manager($select=Id,FullName,UserName),'.
            'InvoiceLines($expand=Invoice;$select=Id),'.
            'OriginalOrder($expand=Children;$select=Id),'.
            'Children($select=Id,StartDate,EndDate;$expand=Activity($select=Created;$filter=ActivityType+eq+\'Optimize\'),Product($select=Name;$expand=ProductType($select=Name,Id)),Manager($select=FullName)),'.
            'Parent($expand=Product($select=Name),ContractType($select=Name),InformationSchemes($select=Id);$select=Id,NeedInformation,Status)');
        if($contract instanceof View){
            return $contract;
        }
//        if(!$this->isOwner($contract))
//        {
//            return view('errors.denied');
//        }

        // if it's a main package contract, the next optimization date should be the last optimization done for the children :D
        if($contract->ProductPackage_Id != null && $contract->Parent_Id == null){
            $activities = [];
            //add all activities from the children
            if(!empty($contract->Children)){
                foreach ($contract->Children as $addon){
                    if(!empty($addon->Activity)){
                       $activities = array_merge($addon->Activity,$activities);
                    }
                }
            }
            if(!empty($activities)){
                usort($activities,'date_compare');
                $contract->NextOptimize = $activities[0]->Created;
            }
        }


        $contract->ClientAlias->Contract = ClientAliasController::groupClientContracts($contract->ClientAlias->Contract);
        // if the contract is addon : get the team and payment statuses for the parent
        if($contract->Parent_Id != null && $contract->ProductPackage_Id != null){
            $contract->Invoice    = ContractsController::getPaymentStatus($contract->Parent_Id);
            $contract->TeamStatus = ContractsController::getTeamStatus($contract);
        }else{
            $contract->Invoice    = ContractsController::getPaymentStatus($contract->InvoiceLines);
            $contract->TeamStatus = ContractsController::getTeamStatus($contract);
        }

        JavaScriptFacade::put(['team_status' => $contract->TeamStatus]);

        if($contract->TeamStatus == "Assign"){
            $users = UsersController::listByRoles(['AdWords']);
            $templates = TaskTemplatesController::templateList();
            $userContractsCount = StatisticsController::userContractsCount('Adwords');
            return view('adwords.assign_to',compact('contract','users','templates','userContractsCount'));
        }

        return view('adwords.show',compact('contract'));
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
        $contract = $cont->getRequest("Contracts($id)".'?$expand=User($select=Id,UserName),Product,Manager($select=Id),Country,ClientAlias($expand=Client($select=ClientManager_Id))');
        if($contract instanceof View)
        {
            return $contract;
        }
//        if(!$this->isOwner($contract))
//        {
//            return view('errors.denied');
//        }
        $teams = UsersController::teamsList();

        $users = UsersController::usersList();

        $countries = CountriesController::countriesList();

        $statuses = $cont->getEnumProperties(['ContractStatus','ContractPriority','ContractTerms']);

        return view('contracts.edit',compact('contract','teams','countries','statuses','users'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request|Request $request
     * @param  int $id
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
     * @param null $id
     * @return \Illuminate\View\View
     */
    public function assignContracts($id = null)
    {
        if ($id == null) {
            return view('adwords.assign');
        }else{
            $cont = new RestController();

            $contract = $cont->getRequest("Contracts($id)?".'$expand=User($select=UserName,FullName),Children,Product($select=Name),'.
                'ClientAlias($select=Homepage;$expand=Contracts($filter=StartDate ne null)),'.
                'InvoiceLines($expand=Invoice)');
            if($contract instanceof View){
                return $contract;
            }


            $users = UsersController::usersList();

            $taskTemplates = $cont->getRequest('TaskListTemplates');
            if($taskTemplates instanceof View){
                $templates=[];
            }else{
                $templates=[];
                foreach($taskTemplates->value as $temp){
                    $templates[$temp->Id] = $temp->Title;
                }
            }

            // todo function for the team status
            if (!empty($contract->InvoiceLines)){
                $contract->Invoice = [];
                foreach($contract->InvoiceLines as $inv){
                    array_push($contract->Invoice,$inv->Invoice);
                }
                $contract->PaymentStatus = end($contract->InvoiceLines)->Invoice->Status;
            }
            return view('adwords.assign_to',compact('contract','users','templates'));
        }
    }

    // checks if account is linked to our mcc
    public function checkAdwordsLink(AdWordsApi $api){
        $input = Request::all();
        if(isset($input['adwordsId']) && is_numeric(stripDashes($input['adwordsId']))){
        $result = $api->checkAccountLink($input['adwordsId']);
        return $result;
        }
    }

    public function cancelInvitation(AdWordsApi $api){
        $input = Request::all();
        $result = $api->cancelInvitation($input['adwordsId']);
        return $result;
    }

    public function sendInvitation(AdWordsApi $api){
        $input = Request::all();
        $result = $api->sendInvitation($input);
        return $result;
    }

    public function getBudget($adwordsId){
        $api = new AdWordsApi();
        $result = $api->getBudget($adwordsId);
        return $result;
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
        if ($result || $userId == '34') { // allow Mikker to see everything
            return true;
        }
        //remove the user role. we don't care about it
        $roles = array_diff($roles, array('User'));
        $roles = array_values($roles);

        switch ($roles[0]){
            case "Client Manager":
                return $item->ClientAlias->Client->ClientManager_Id == null? true: Auth::user()->externalId ? true:false;
                break;
            case "Adwords":
                return $item->Manager_Id == Auth::user()->externalId ? true:false;
                break;
            case "Sales":
                return $item->ClientAlias->User_Id == Auth::user()->externalId ? true:false;
                break;
            default :
                break;
        }
        //default, we deny.
        return false;
    }

}
