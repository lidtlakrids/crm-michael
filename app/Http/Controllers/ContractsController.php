<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;
class ContractsController extends Controller {

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
		    "Status ne 'Suspended'"=>"Select",
			'Status eq \'Active\''=>Lang::get('labels.active'),
			'Status eq \'Standby\''=>Lang::get('labels.standby'),
			'Status eq \'Suspended\''=>Lang::get('labels.suspended'),
			'Status eq \'Completed\''=>Lang::get('labels.completed'),
			'Status eq \'Cancelled\''=>'Cancelled',
            'not ClientAlias/Invoice/any()'=>'No payment info.'
		];
        return view('contracts.index',compact('sellers','managers','teamStatus','contractStatus'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
     *
	 * Create contract for order
	 *
	 * @return Response
	 */
	public function store()
	{
        $params = Request::only('OrderId');
        $cont = new RestController();
        $result = $cont->postRequest('Contracts/Create/'.$params['OrderId']);
        if($result instanceof RedirectResponse)
        {
            return $result;
        }
        Session::flash('message','Contract created successfully');
        return Redirect::to('contracts/show/'.$result[0]->Id);
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
        $contract = $cont->getRequest('Contracts('.$id.')?$expand=Parent($expand=Product($select=Name),ContractType($select=Name),'.
                                'InformationSchemes($select=Id);$select=Id,NeedInformation,Status),InformationSchemes,'.
                                'ClientAlias($select=Id,Name,Homepage,Address,zip,City,PhoneNumber,EMail,AdwordsId,User_Id;$expand=Client($select=Id,CINumber,ClientManager_Id;$expand=ClientManager($select=FullName))),'.
								'User($select=FullName),Product($select=Id,Name;$expand=ProductType($select=Name)),ContractType,'.
								'OriginalOrder($expand=OrderFieldValue($expand=OrderField($select=DisplayName,OrderFieldType)),Children),'.
								'Manager($select=FullName),Children($expand=Product($select=Name),User($select=FullName)),Country,Activity($expand=Comment($select=Message),User($select=FullName)),InvoiceLines($expand=Invoice;$select=Id)');
        if($contract instanceof View) {
            return $contract;
        }
		if(!$this->isOwner($contract)){
			return view('errors.denied');
		}
		$clientManagers = UsersController::listByRoles(['Client Manager']);
		// if the contract is addon : get the team and payment statuses for the parent
        $contract->TeamStatus  = self::teamStatus($contract);
		if($contract->Parent_Id != null && $contract->ProductPackage_Id != null){
			$contract->Invoice    = self::getPaymentStatus($contract->Parent_Id);
//			$contract->TeamStatus = self::getTeamStatus($contract);
		}else{
			$contract->Invoice    = self::getPaymentStatus($contract->InvoiceLines);
//			$contract->TeamStatus = self::getTeamStatus($contract);
		}
		// put the teamstatus in javascript, for the header
		JavaScriptFacade::put(['team_status' => $contract->TeamStatus]);
		return view('contracts.show',compact('contract','clientManagers'));
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

		$contract = $cont->getRequest("Contracts($id)".'?$expand=User($select=Id,UserName),Product,Manager($select=Id),Country,ClientAlias($select=Id,User_Id;$expand=Client($select=ClientManager_Id))');
		if($contract instanceof View)
		{
			return $contract;
		}
		if(!$this->isOwner($contract))
		{
			return view('errors.denied');
		}
        $users = UsersController::usersList();

		$countries = CountriesController::countriesList();

		$statuses = $cont->getEnumProperties(['ContractStatus','ContractPriority','ContractTerms']);

		return view('contracts.edit',compact('contract','countries','statuses','users'));
	}

	/**
	 * Update the specified resource in storage.
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        return Redirect::to('contracts/show/'.$id);
	}

    /**
     * Determine the payment status of a contract. Also returns a list of invoices
     * @param array $invoiceLines
     * @return string
     */
	public static function getPaymentStatus($invoiceLines)
	{
		// if we send an id, find the contract with this id, and determine the status for it
		if( is_numeric($invoiceLines)){
			$cont = new RestController();
			$contract = $cont->getRequest("Contracts($invoiceLines)?".'$expand=Activity,Manager($select=FullName),InvoiceLines($select=Id;$expand=Invoice)');
			if($contract instanceof View){
				$status = "Unknown";
			}
			$invoiceLines = $contract->InvoiceLines;
		}

		//make array with all invoices
		$invoices = [];
		$invoices['Invoices']= [];
        $ids = [];
        foreach($invoiceLines as $line){
            if($line->Invoice->Type == "CreditNote") continue;
//            if($line->Invoice->Type == "Invoice" && $line->Invoice->Status =='Canceled') continue;
            if(!in_array($line->Invoice->Id,$ids)) {
                array_push($invoices['Invoices'], $line->Invoice);
                array_push($ids,$line->Invoice->Id);
            }
        }
        $status = !empty($invoices['Invoices'])? end($invoices['Invoices'])->Status : "Unknown";
		$invoices['PaymentStatus']= $status;

        return $invoices;
	}

    /**
     * Determine the team status of a contract
     * @param $contract  Either the whole contract object or the id of it
     * @return string
     */
	public static function getTeamStatus($contract){
	    return self::teamStatus($contract);
        //  if it's id , get the contract object from the backend
        if($contract->Parent_Id != null && $contract->ProductPackage_Id){
            $cont = new RestController();
            $parent = $cont->getRequest("Contracts($contract->Parent_Id)?".'$expand=Activity,Manager($select=FullName)');
            if($parent instanceof View){
                $teamStatus['status'] = "Problem";
                return $teamStatus;
            }
			//first, see if the status is different than the parent status;
			if($contract->Status != $parent->Status){
				$parent->Invoice = self::getPaymentStatus($parent->Id);
				$parent->TeamStatus = self::getTeamStatus($parent);

				return $parent->TeamStatus;
			}
        }

		//is it suspended or standby. Standby waits for something. Suspended is when we hate them
		//first, see if the status is active;
			if($contract->Status != "Active" && $contract->Manager_Id != null){
				//is it suspended or standby. Standby waits for something. Suspended is when we hate them
				$teamStatus = ['status'=>$contract->Status];
				$teamStatus['reasons'] =[];
				//find out why is it like this
				// is it the invoice?
				if($contract->Invoice['PaymentStatus'] != "Paid" ){
					if(empty($contract->Invoice['Invoices'])){
						$teamStatus['reasons']['Invoice']= 'No payment information';
					}else{
						$duedate = Carbon::parse(end($contract->Invoice['Invoices'])->Due);
						$today = Carbon::today();
						$diff = $today->diffInDays($duedate,false);
						if($diff>0){

						}else{
							$teamStatus['reasons']['Invoice']= end($contract->Invoice['Invoices']);
						}
					}
//					array_push($teamStatus['reasons'],['Invoice'=>end($contract->Invoice['Invoices'])]); // todo make the array key the reason instead of pushing
				}
				// check if it was paused
				if(!empty($contract->Activity)){
					foreach ($contract->Activity as $item){
						if($item->ActivityType == "Pause"){
							$teamStatus['reasons']['Pause']=$item;
							break;
						}
					}
				}
				return $teamStatus;
			}
		//second, if there is nobody assigned to the contract, send it to assign
		if($contract->Manager == null){
            $teamStatus['status'] = "Assign";
		}else {
			// check if the payment status is ok
			if($contract->Invoice['PaymentStatus'] !== "Paid"){
			    //if it's first invoice, wait till it's paid, if it's second invoice, check if it's overdue
			    if(count($contract->Invoice['Invoices']) == 1){

                }elseif(empty($contract->Invoice['Invoices'])){
                    // TODO This is because some contracts didn't have invoice links, we will let them work for free for now
//                    $teamStatus['status'] = 'Standby';
//                    $teamStatus['reasons']['Payment'] = "No payment information for this contract";
//                    return $teamStatus;
                }
                else{
                    $duedate = Carbon::parse(end($contract->Invoice['Invoices'])->Due);
                    $today = Carbon::today();
                    $diff = $today->diffInDays($duedate,false);
                    if($diff>0){

                    }else{
                        $teamStatus['status'] = 'Standby';
                        $teamStatus['reasons']['Payment'] = Lang::get('labels.payment-status')." : ".$contract->Invoice['PaymentStatus']
                            ."\n"."Invoice ".end($contract->Invoice['Invoices'])->InvoiceNumber;
                        return $teamStatus;
                    }
                }
            }

			//Contract hasn't been started.
			if ($contract->StartDate == null && $contract->EndDate == null) {
				// no activity on the contract-> Must be in production
				if (count($contract->Activity) == 0) {
					$teamStatus['status'] = "Production";
				} else { // if we have contract activity, check what it is
					foreach ($contract->Activity as $activity) {
						if ($activity->ActivityType == "Produced") {
							$teamStatus['status'] = "Starting";
							break;
						}
						$teamStatus['status'] = "Production";
					}
				}
			} else {  // we have start and end date, so it is started, but is it produced?
				if (count($contract->Activity) == 0) {
					$teamStatus['status'] = "Production";
				} else {
					foreach ($contract->Activity as $activity) {
						if ($activity->ActivityType == "Produced") {
							$teamStatus['status'] = "Optimize";
							break;
						}
						$teamStatus['status'] = "Production";
					}
				}
			}
		}
        // if we don't determine the team status by now, indicate a problem
        return $teamStatus;
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Responses
	 */
	public function destroy($id)
	{
		//
	}

	/**
	 * @param null $id
	 * @return View|null
	 */
	public function assignContracts($id = null){
		if ($id == null) {
            $types = ProductTypesController::queryTypesList('Product/ProductType_Id');

            return view('contracts.assign',compact('types'));
		}else{
			$cont = new RestController();

			$contract = $cont->getRequest("Contracts($id)?".'$expand=User($select=UserName,FullName),Children,Product($select=Name),'.
				'ClientAlias($select=Homepage,Name;$expand=Contracts($filter=StartDate ne null)),'.
				'InvoiceLines($expand=Invoice)');
			if($contract instanceof View){
				return $contract;
			}
			$users = UsersController::usersList();

			$templates = TaskTemplatesController::templateList();

			if (!empty($contract->InvoiceLines)){
				$contract->PaymentStatus = self::getPaymentStatus($contract->InvoiceLines);
			}


			return view('contracts.assign_to',compact('contract','users','templates'));
		}
	}

	//list with all contracts needing information
	public function needInformation(){
		$sellers = UsersController::queryListByRoles(['Sales','Administrator']);
		$managers = UsersController::queryUsersList('Manager_Id');
		$teamStatus = [
			'Manager_Id eq null'=>Lang::get('labels.assign'),
			'not Activity/any(d:d/ActivityType eq webapi.Models.ContractActivityType\'Produced\')'=>Lang::get('labels.produce'),
			'not Activity/any(d:d/ActivityType eq webapi.Models.ContractActivityType\'Start\')'   =>Lang::get('labels.start'),
			'Activity/any(d:d/ActivityType eq webapi.Models.ContractActivityType\'Start\')'       =>Lang::get('labels.optimize'),
			'Activity/any(d:d/ActivityType eq webapi.Models.ContractActivityType\'Pause\')'       =>Lang::get('labels.pause'),
		];

		$contractStatus = [
			'Status eq webapi.Models.ContractStatus\'Active\''=>Lang::get('labels.active'),
			'Status eq webapi.Models.ContractStatus\'Standby\''=>Lang::get('labels.standby'),
			'Status eq webapi.Models.ContractStatus\'Suspended\''=>Lang::get('labels.suspended'),
		];
		return view('contracts.needInformation',compact('sellers','managers','teamStatus','contractStatus'));
	}
	
	
    /**
     * upgrades or downgrades a contract package
     * Sends an order that needs to be approved, if there are special fields and confirmed by the client again
     * @param $contractId
     * @return View
     */
    public function upgrade($contractId){

        $cont = new RestController();

        $contract = $cont->getRequest('Contracts('.$contractId.')?$expand=ProductPackage($expand=Product),Product($expand=ProductType),ClientAlias($select=Id,Homepage)');
        if($contract instanceof View){
            return $contract;
        }
        // the type of the product we have now
        $productType = $contract->Product->ProductType->Name;

        // get all packages
        $packages = ProductPackagesController::getPackagesByType();

//        //return only the ones with the same type as the old one. We can't upgrade AdWords contract to SEO contract
//        $packages = isset($packages[$productType]) ? $packages[$productType]:[];

        // get the payment terms
        $paymentTerms = $cont->getEnumProperties(['ContractTerms']);
        $paymentTerms = isset($paymentTerms['ContractTerms']) ? $paymentTerms['ContractTerms'] : [];

        $countries = CountriesController::countriesList();
        if($contract->Status == 'Active' && strtotime($contract->EndDate) > time()){
            $splitting = true;
        }else{
            $splitting = false;
        }
        JavaScriptFacade::put(
			[   'paymentTerms' => $paymentTerms,
			    'countries' => $countries,
				'contractId'=>$contract->Id,
				'domain' => $contract->Domain != null ? $contract->Domain : $contract->ClientAlias->Homepage,
				'country_Id' => ($contract->Country_Id == null ? 19:$contract->Country_Id),
				'contractTypeId' => $contract->Product->ProductType_Id,
                'splittable' => $splitting
            ]);
		$users = UsersController::listByRoles(['Sales']);
        return view('contracts.upgrade',compact('contract','packages','productType','users'));
    }

	/**
	 * upgrades or downgrades a contract package
	 * Sends an order that needs to be approved, if there are special fields and confirmed by the client again
	 * @param $contractId
	 * @return View
	 */
	public function renew($contractId){

		$cont = new RestController();

		$contract = $cont->getRequest('Contracts('.$contractId.')?$expand=ProductPackage($expand=Product),Product($expand=ProductType),ClientAlias($select=Id)');
		if($contract instanceof View){
			return $contract;
		}

		// get the payment terms
		$paymentTerms = $cont->getEnumProperties(['ContractTerms']);
		$paymentTerms = isset($paymentTerms['ContractTerms']) ? $paymentTerms['ContractTerms'] : [];

		$countries = CountriesController::countriesList();

		$packages = ProductPackagesController::getPackagesByType();
		// unset the same product package types as the ones we have already
		unset($packages[$contract->Product->ProductType->Name]);

        if($contract->Status == 'Active' && strtotime($contract->EndDate) > time()){
            $splitting = true;
        }else{
            $splitting = false;
        }

		JavaScriptFacade::put(
			['paymentTerms' => $paymentTerms,
				'countries'=>$countries,
				'contractId'=>$contract->Id,
				'packageId'=>$contract->ProductPackage->Id,
				'domain1' => $contract->Domain,
				'country_Id' => ($contract->Country_Id == null ? 19:$contract->Country_Id),
				'contractTypeId' => $contract->Product->ProductType_Id,
                'splittable' => $splitting

			]);
		$users = UsersController::listByRoles(['Sales']);

		return view('contracts.renew',compact('contract','packages','users'));
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
		if ($result || in_array($userId,['34',"55",'155','117'])) { // allow Mikker/ Andreas Wewer , michael posberg to see everything , Jacob Madsen
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
			case "SEO":
				return in_array($item->ContractType_Id,[3,8,18,20]) ?true:false;
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


    public function contractStatus($contract){

        // todo new team status

    }

	/**
	 * return contract team status based on payment, contract activities and other information, such as assigned to
	 *
	 * @param $contract
	 * @return array
	 */
	public static function teamStatus($contract){

	    //if the contract has a parent and a package, means it's an addon, and we need to look in the parent contract
	    if($contract->Parent_Id != null && $contract->ProductPackage_Id != null){
	        $status = $contract->Parent->Status;
        }else{
            $status = $contract->Status;
        }
        $start    = false;
        $produced = false;
        // active and wihout manager - > Assign
        if ($contract->Manager_Id == null) {
            return 'Assign';
        }
        $assigned = true;

        // no start and end date  -> check for activities
        if ($contract->StartDate == null) {
            if(empty($contract->Activity)) {
                return "Production";
            } else {
                foreach ($contract->Activity as $activity) {
                    if($activity->ActivityType == 'Start') { $start = true;}
                    if($activity->ActivityType == 'Produced') {$produced = true;}
                    if($activity->ActivityType == 'Assign') {$assigned = true;}
                }
                if($assigned && !$produced){
                    return "Production";
                }elseif($assigned && $produced){
                    return "Starting";
                }else{
                    return "Production";
                }
            }
        }else{
            // if we don't have any activity, we will be in produced
            if(empty($contract->Activity)){
                return "Production";
            }else {

                foreach ($contract->Activity as $activity) {
                   if($activity->ActivityType == 'Start') { $start = true;}
                   if($activity->ActivityType == 'Produced') {$produced = true;}
                }

                if($produced && !$start){
                    return "Starting";
                }elseif($produced && $start){
                    return "Optimize";
                }elseif (!$produced && $start){
                    return "Production";
                }

            }

        }

		// if we don't determine the team status by now, indicate a problem
	}

    /**
     * menu that allows us to set addon products on a contract, that has a package
     *
     * @param $id
     * @return View
     */
	public function contractAddons($id){


	    $cont = new RestController();

        $contract = $cont->getRequest("Contracts($id)?".
            '$expand=ProductPackage($expand=Products($expand=Product)),Product,InformationSchemes($expand=FieldValue($expand=OrderField)),Children($expand=Product)'
        );
        if($contract instanceof View){
            return $contract;
        }

        if(!empty($contract->InformationSchemes)) {
            $contract->InformationScheme = OrdersController::groupOrderFieldValues(end($contract->InformationSchemes)->FieldValue);
        }

        $addons = [];
        //get the existing addon ids
        if(!empty($contract->Children)){
            foreach ($contract->Children as $addon){
                array_push($addons,$addon->Product_Id);
            }
        }

        JavaScriptFacade::put(['contract'=>$contract]);

	    return view('contracts.addons',compact('contract','addons'));
    }

    public function missingdrafts()
    {
        $cont = new RestController();

        $terms = $cont->getEnumProperties(['ContractTerms']);

        JavaScriptFacade::put(['terms' =>array_flip($terms['ContractTerms'])]);

        return view('contracts.missingDrafts');
    }

    public function fieldValues($contractId){

        $cont = new RestController();

        $res = $cont->getRequest("Contracts($contractId)".'?$expand=FieldValues');
    }

    public function bySeller($sellerId = null){
        $sellers = UsersController::queryUsersList(null,true);
        $managers = UsersController::queryUsersList("Manager_Id",true);
        $teamStatus = [
            '(not Activity/any(d:d/ActivityType eq \'Produced\') and not Activity/any(d:d/ActivityType eq \'Start\'))'=>Lang::get('labels.produce'),
            '(not Activity/any(d:d/ActivityType eq \'Start\') and Manager_Id ne null and Activity/any(d:d/ActivityType eq \'Produced\'))'   =>Lang::get('labels.start'),
            'Activity/any(d:d/ActivityType eq webapi.Models.ContractActivityType\'Start\')'  =>Lang::get('labels.optimize'),
        ];

        $contractStatus = [
            ""=>"Select",
            'Status eq \'Active\''=>Lang::get('labels.active'),
            'Status eq \'Standby\''=>Lang::get('labels.standby'),
            'Status eq \'Suspended\''=>Lang::get('labels.suspended'),
            'Status eq \'Completed\''=>Lang::get('labels.completed'),
            'Status eq \'Cancelled\''=>'Cancelled',
            'not ClientAlias/Invoice/any()'=>'No payment info.'
        ];

        if($sellerId == null){
            $sellerId = UsersController::activeUserId();
        }
        $users = UsersController::usersList('true');
        JavaScriptFacade::put(['users'=>$users]);
        return view('contracts.bySeller',compact('sellers','managers','teamStatus','contractStatus','sellerId'));
    }

}
