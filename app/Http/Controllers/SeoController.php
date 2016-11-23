<?php

namespace App\Http\Controllers;

use Curl\Curl;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;

class SeoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {

        $sellers = UsersController::queryListByRoles(['Sales'],null,'ClientAlias/User_Id');
        $managers = UsersController::queryListByRoles(['SEO'],'Manager_Id');
        $teamStatus = [
            '(not Activity/any(d:d/ActivityType eq \'Produced\') and not Activity/any(d:d/ActivityType eq \'Start\') and Manager_Id ne null)'=>Lang::get('labels.produce'),
            '(not Activity/any(d:d/ActivityType eq \'Start\') and Manager_Id ne null and Activity/any(d:d/ActivityType eq \'Produced\'))'   =>Lang::get('labels.start'),
            'Activity/any(d:d/ActivityType eq \'Start\')'       =>Lang::get('labels.optimize'),
            'Activity/any(d:d/ActivityType eq \'Pause\')'       =>Lang::get('labels.pause')
        ];

        $contractStatus = [
            "Status ne 'Suspended'"=>"Select",
            'Status eq \'Active\''=>Lang::get('labels.active'),
            'Status eq \'Standby\''=>Lang::get('labels.standby'),
            'Status eq \'Suspended\''=>Lang::get('labels.suspended'),
            'Status eq \'Completed\''=>Lang::get('labels.completed'),
            'Status eq \'Cancelled\''=>'Cancelled',
            'not ClientAlias/Invoice/any()'=>'No payment info.'
        ];

//        $contractIds = array_map('str_getcsv', file('seoContracts.csv'));
//        $ids = [];
//        foreach ($contractIds as $id){
//            $ids[$id[0]] = $id[1];
//        }
//
//        $uIds = array_map('str_getcsv',file('teamworkUsers.csv'));
//        $userIds = [];
//        foreach ($uIds as $id){
//            $userIds[$id[0]] = $id[2];
//        }
//
//        $test = include(public_path('timelogs.php'));
//        $cont = new RestController();
//        foreach ($test as $task){
//            if(!isset($ids[$task['Item']])){ continue;}
//
//            if(!in_array($task['User_Id'],["112101","158434","155203","166802"])) {continue;}
//
//            $data = [
//                'Comment'=>$task['Comment'],
//                'Model'=>'Contract',
//                'Item'=>$ids[$task['Item']],
//                'UserId' => isset($userIds[$task['User_Id']]) ? $userIds[$task['User_Id']] : null,
//                'Created'=>date('c',strtotime($task['Created'])),
//                'Minutes'=>$task['Minutes']
//            ];
////            sleep(2);
//            $res = $cont->postRequest('TimeVaults/Withdraw',$data);
//            if($res instanceof View){
//                var_dump($data);
//            }
//        }
        return view('seo.index',compact('sellers','managers','teamStatus','contractStatus'));
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

        //get the contract
        $contract = $cont->getRequest("Contracts($id)".'?$expand=InformationSchemes($expand=FieldValue,Products($select=Id;$expand=Product($select=Name))),Product($select=Id,Name),ProductPackage($select=Id),' .
            'User($select=Id,UserName,FullName),'.
            'Country($select=CountryCode),'.
            'Activity($select=ActivityType,Created,User_Id,Id;$expand=Comment($select=Message),User($select=FullName)),'.
            'ClientAlias($expand=User($select=FullName,UserName),'.
            'Country($select=CountryCode),Contact($select=Id,Name,Description,Phone,Email,JobFunction,Department,Birthdate),'.
            'Contract($expand=Manager($select=Id,FullName),OriginalOrder($select=Id),Product($select=Name),Country($select=CountryCode,Id)),'.
            'Client($select=CINumber;$expand=ClientManager($select=FullName));$select=Name,PhoneNumber,Id,EMail,City,Address,zip,Homepage),'.
            'Manager($select=Id,FullName,UserName),'.
            'InvoiceLines($expand=Invoice;$select=Id),'.
            'OriginalOrder($expand=Children;$select=Id)');
        if($contract instanceof View){
            return $contract;
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
            $users = UsersController::usersList();
            $templates = TaskTemplatesController::templateList();
            $userContractsCount = StatisticsController::userContractsCount('SEO');

            return view('seo.assign_to',compact('contract','users','templates','userContractsCount'));
        }

        return view('seo.show',compact('contract'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
     * checks for entity ownership depending on a role
     * @param $item
     * @return bool
     */
    public function isOwner($item)
    {
        return true;
        $roles = Session::get('roles');
        $userId = Auth::user()->externalId;

        $result = !empty(array_intersect($roles, ['Administrator','Accounting','Developer']));
        if ($result) {
            return true;
        }
        //remove the user role. we don't care about it
        $roles = array_diff($roles, array('User'));
        $roles = array_values($roles);
        switch ($roles[0]){
            case "Client Manager":
                return $item->ClientAlias->Client->ClientManager_Id == null? true:$userId ? true:false;
                break;
            case "Adwords":
            case "SEO":
                return $item->Manager_Id == $userId? true:false;
                break;
            case "Sales":
                return $item->ClientAlias->User_Id == $userId ? true:false;
                break;
            default :
                break;
        }
        //default, we deny.
        return false;
    }

}
