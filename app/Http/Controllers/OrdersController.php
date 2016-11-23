<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OrdersController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        $users = UsersController::queryListByRoles(['Sales'],'User_Id');

        $weekAgo = date('c', strtotime('-7 days'));
        $twoWeeksAgo = date('c', strtotime('-14 days'));
        $monthAgo = date('c', strtotime('-30 days'));
        $orderDates = [
            'Created ge ' . $weekAgo => '7 ' . Lang::get('labels.days'),
            'Created ge ' . $twoWeeksAgo => '14 ' . Lang::get('labels.days'),
            'Created ge ' . $monthAgo => '30 ' . Lang::get('labels.days'),
        ];

        $orderStatus = [
            'ArchivedDate eq null' => Lang::get('labels.select-status'),
            'ConfirmedDate ne null and ArchivedDate eq null' => Lang::get('labels.confirmed'),
            'ConfirmedDate eq null and ArchivedDate eq null' => Lang::get('labels.unconfirmed-orders'),
            'ApprovedDate ne null and ArchivedDate eq null' => Lang::get('labels.approved'),
            'ApprovedDate eq null and ArchivedDate eq null' => Lang::get('labels.waiting-approval'),
            'ArchivedDate ne null' => Lang::get('labels.archived'),
            'ConfirmedDate ne null and ArchivedDate eq null and Invoice eq null' => 'No invoice'
        ];

        return view('orders.index', compact('users', 'orderDates', 'orderStatus'));
    }

    /**
     * Show the form for creating a new resource.
     *
     *
     * @param $id
     * @param int|null $aliasId
     * @return View
     */
    public function create($id = null, $aliasId = 0)
    {
        $contr = new RestController();

        if (isset($_GET['company'])) {
            $aliasName = $_GET['company'];
            $url = $_GET['url'];
        }elseif(isset($_GET['lead'])){
        // get the lead information
            $lead = $contr->getRequest('Leads('.$_GET['lead'].')?$expand=Phone,Taxonomy');
            if($lead instanceof View){
                return $lead;
            }
            $aliasName = $lead->Company;
            $url = $lead->Website;
        }
        else{
            $aliasName = null;
        }


        $users = UsersController::listByRoles(['Sales']);
        //learn to reuse code
        $packages = ProductPackagesController::getPackagesByType();
        //payment terms

        $paymenTerms = $contr->getEnumProperties(['ContractTerms']);
        $paymenTerms = isset($paymenTerms['ContractTerms']) ? $paymenTerms['ContractTerms'] : [];

        $countries = CountriesController::countriesList();

        JavaScriptFacade::put(['paymentTerms' => $paymenTerms, 'countries' => $countries]);

        $partners = PartnersController::partnersList();

        if ($id != null && $id != 0) {
            $order = $contr->getRequest("OrderTypes($id)");
            if ($order instanceof View) {
                return $order;
            }
            $orderFields = $contr->getRequest('OrderTypes(' . $id . ')/OrderTypeOrderField?$expand=OrderField($expand=OrderFieldOption)');
            $orderFields = $this->renderFields($orderFields->value);
            //        $orderFields = $orderFields->value;

            return view('orders.create', compact('order', 'orderFields', 'aliasId', 'aliasName', 'users', 'packages', 'url', 'countries','partners'));
        } else {

            return view('orders.create', compact('aliasId', 'aliasName', 'users', 'packages', 'products', 'url', 'countries','lead','partners'));
        }
    }

    /**
     * @param array $fields
     * @return mixed
     */
    public function renderFields(array $fields)
    {
        $renderedFields = array();
        usort($fields,
            function ($a, $b) {
                $t1 = $a->SortOrder;
                $t2 = $b->SortOrder;
                return $t1 - $t2;
            });

        foreach ($fields as $field) {
            if ($field->OrderField->Required) {

                $required = "required";
                $fieldRequired = "<span class='' style='color:red;'>*</span>";
            } else {
                $required = null;
                $fieldRequired = null;
            }
            switch ($field->OrderField->OrderFieldType) {
                case "Text":
                    $renderedFields[$field->OrderField->Id]['label'] = "<label for='field[" . $field->OrderField->Id . "]' class='col-md-6 control-label-info'>" .
                                                                            $field->OrderField->DisplayName . $fieldRequired .
                                                                            ($field->OrderField->Description != null ?" <i class='fa fa-question-circle' title='".$field->OrderField->Description."'></i>" : "").
                                                                        "</label>";
                    $renderedFields[$field->OrderField->Id]['element'] = "<input id='field[".$field->OrderField->Id."]' type='text' name='". $field->OrderField->Id ."' class='form-control orderField'" . $required . ">";
                    break;
                case "AdwordsId":
                    $renderedFields[$field->OrderField->Id]['label'] = "<label for='field[" . $field->OrderField->Id . "]' class='col-md-6 control-label-info'>" .
                        $field->OrderField->DisplayName . $fieldRequired .
                        ($field->OrderField->Description != null ?" <i class='fa fa-question-circle' title='".$field->OrderField->Description."'></i>" : "").
                        "</label>";
                    $renderedFields[$field->OrderField->Id]['element'] = "<input id='field[".$field->OrderField->Id."]' type='text' name='". $field->OrderField->Id ."' class='form-control orderField adwordsIdInput'" . $required . " pattern='\b\d{3}[-]?\d{3}[-]?\d{4}\b'>";
                    break;
                case "Radio":
                    $renderedFields[$field->OrderField->Id]['label'] = "<label for='field[" . $field->OrderField->Id . "]' class='col-md-6 control-label-info'>" .
                        $field->OrderField->DisplayName . $fieldRequired .
                        ($field->OrderField->Description != null ?" <i class='fa fa-question-circle' title='".$field->OrderField->Description."'></i>" : "").
                        "</label>";
                    $renderedFields[$field->OrderField->Id]['element'] = '';
                    // sort the options and render them
                    if(!empty($field->OrderField->OrderFieldOption)){
                        //sort by the sort order
                        usort($field->OrderField->OrderFieldOption, function($a, $b)
                        {
                            return $a->SortOrder - $b->SortOrder;
                        });
                        foreach ($field->OrderField->OrderFieldOption as $option) {

                            $renderedFields[$field->OrderField->Id]['element'] .= "<div class='radio'>
                                                                                    <label for=" . $field->OrderField->Id . $option->Id . ">
                                                                                        <input type='radio' id='" . $field->OrderField->Id . $option->Id . "' name='" . $field->OrderField->Id . "' class='orderField ".$required."' value='" . $option->DisplayName . "'>" .
                                                                                        $option->DisplayName .
                                                                                    "</label>
                                                                                    </div>";
                        }
                        if($field->OrderField->Description != null){
                            $renderedFields[$field->OrderField->Id]['description'] = "<p class='help-block'>".$field->OrderField->Description."</p>";
                        }
                    }
                    break;

                case "CheckBox":

                    $renderedFields[$field->OrderField->Id]['label'] = "<label for='field[" . $field->OrderField->Id . "]' class='col-md-6 control-label-info'>" .
                        $field->OrderField->DisplayName . $fieldRequired .
                        ($field->OrderField->Description != null ?" <i class='fa fa-question-circle' title='".$field->OrderField->Description."'></i>" : "").
                        "</label>";
                    $renderedFields[$field->OrderField->Id]['element'] = '';
                    // sort the options and render them
                    if(!empty($field->OrderField->OrderFieldOption)){
                        //sort by the sort order
                        usort($field->OrderField->OrderFieldOption, function($a, $b)
                        {
                            return $a->SortOrder - $b->SortOrder;
                        });
                        foreach ($field->OrderField->OrderFieldOption as $option) {

                            $renderedFields[$field->OrderField->Id]['element'] .= "<div class='checkbox'>
                                                                                    <label for='" . $field->OrderField->Id . $option->Id . "'>
                                                                                        <input type='checkbox' id='" . $field->OrderField->Id . $option->Id . "' name='" . $field->OrderField->Id . "' class='orderField ".$required."' value='" . $option->DisplayName . "'>" .
                                                                                        $option->DisplayName . "</label></div>";
                        }
                        if($field->OrderField->Description != null){
                            $renderedFields[$field->OrderField->Id]['description'] = "<p class='help-block'>".$field->OrderField->Description."</p>";
                        }
                    }
                    break;
                case "Select":
                    $renderedFields[$field->OrderField->Id]['label'] = "<label for='field[" . $field->OrderField->Id . "]' class='col-md-6 control-label-info'>" .
                        $field->OrderField->DisplayName . $fieldRequired .
                        ($field->OrderField->Description != null ?" <i class='fa fa-question-circle' title='".$field->OrderField->Description."'></i>" : "").
                        "</label>";
                    $renderedFields[$field->OrderField->Id]['element'] = '<select ' . $required . ' class="form-control orderField" id="field[' . $field->OrderField->Id . ']" name="' . $field->OrderField->Id . '">';
                    foreach ($field->OrderField->OrderFieldOption as $option) {
                        $renderedFields[$field->OrderField->Id]['element'] .= '<option value ="' . $option->Value . '">' . $option->DisplayName . '</option>';
                    }
                    $renderedFields[$field->OrderField->Id]['element'] .= '</select>';
                    break;
                case "Textarea":
                    $renderedFields[$field->OrderField->Id]['label'] = "<label for='field[" . $field->OrderField->Id . "]' class='col-md-6 control-label-info'>" .
                        $field->OrderField->DisplayName . $fieldRequired .
                        ($field->OrderField->Description != null ?" <i class='fa fa-question-circle' title='".$field->OrderField->Description."'></i>" : "").
                        "</label>";
                    $renderedFields[$field->OrderField->Id]['element'] = "<textarea id='field[".$field->OrderField->Id."]' name='". $field->OrderField->Id ."' class='form-control orderField'" . $required . "></textarea>";
                    break;
                case "SpecialAdsLanding":
                    $renderedFields[$field->OrderField->Id]['label'] = "<label for='field[" . $field->OrderField->Id . "]' class='col-md-6 control-label-info'>" .
                        $field->OrderField->DisplayName . $fieldRequired .
                        ($field->OrderField->Description != null ?" <i class='fa fa-question-circle' title='".$field->OrderField->Description."'></i>" : "").
                        "</label> ";
                    $renderedFields[$field->OrderField->Id]['element'] = '<div class="col-md-8 adslanding">
                    <input type="hidden" name="AdsLandingId" value="'.$field->OrderField->Id.'">
                    <div class="form-group">
                        <div class="col-md-6">
                            <input type="text" name="AdgroupName" placeholder="AdGroup Name" class="form-control adgroupNames">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="AdgroupUrl" placeholder="url" class="form-control adgroupUrls">
                        </div>
                    </div>
                    
                    <a class="btn btn-green pull-right AddAdGroup" href="#" style="margin-top:5px" >Add another group</a>
                    </div>';
                    break;
                case "CampaignGoal":
                    $renderedFields[$field->OrderField->Id]['label'] = "<label for='field[" . $field->OrderField->Id . "]' class='col-md-6 control-label-info'>" .
                        $field->OrderField->DisplayName . $fieldRequired .
                        ($field->OrderField->Description != null ?" <i class='fa fa-question-circle' title='".$field->OrderField->Description."'></i>" : "").
                        "</label> ";
                    $renderedFields[$field->OrderField->Id]['element'] = '<div class="col-md-8 campaignGoal">
                    <input type="hidden" name="CampaignGoal" value="'.$field->OrderField->Id.'">';

                    foreach ($field->OrderField->OrderFieldOption as $option) {
                        $renderedFields[$field->OrderField->Id]['element'] .= '<div class="form-group">
                        <div class="col-md-4">'.$option->DisplayName.'</div>
                        <div class="col-md-4">
                            <input type="text" name="'.$option->Value.'[current]" placeholder="Current" class="form-control campGoals">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="'.$option->Value.'[expected]" placeholder="Expected" class="form-control campGoals">
                        </div>
                    </div>';
                    }

                    $renderedFields[$field->OrderField->Id]['element'] .= '</div>';

                    break;

                default:
                    break;
            }
        }
        return $renderedFields;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     */
    public function store(Request $request){
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
        $order = $cont->getRequest('Orders(' . $id . ')?$expand=OrderFieldValue($expand=OrderField),' .
            'OrderProduct($expand=Product),OrderProductPackage($expand=ProductPackage($expand=Product),Products($expand=Product),Country),' .
            'ClientAlias($expand=Contact,Country,Client($select=Id,CINumber,ClientManager_Id;$expand=ClientManager($select=FullName))),' .
            'User($select=FullName,UserName),' .
            'ApprovedBy($select=UserName),'.
            'Invoice,Contracts($expand=Product($select=Name),User($select=FullName,UserName),Manager($select=FullName,UserName),Country($select=CountryCode))');
        if ($order instanceof View) {
            return $order;
        }
        if(!empty($order->OrderProductPackage)){
            //getting the addons products because we can't do it in the query
            foreach ($order->OrderProductPackage as $k => $val) {
                $product = $cont->getRequest("Products(" . $val->ProductPackage->Product_Id . ")");
                if (!$product instanceof View) {
                    $order->OrderProductPackage[$k]->ProductPackage->Product = $product;
                }
            }
        }

        //get the contracts that came from this order
        $contractsResult  = $cont->getRequest("Orders($id)/action.Contracts".'?$expand=User($select=Id,FullName),Manager($select=Id,FullName),OriginalOrder($select=Id),Product($select=Name),Country($select=CountryCode)');
        if(!$contractsResult instanceof View){
            $order->Contract = $contractsResult->value;
        }else{
            $order->Contract = null;
        }
        if(!$this->isOwner($order)){
            return view('errors.denied');
        }
        //merge order products and order product packages , so we support both old and new orders
        $order->OrderProductPackage = array_merge($order->OrderProductPackage,$order->OrderProduct);
        $paymenTerms = $cont->getEnumProperties(['ContractTerms']);
        $paymenTerms = isset($paymenTerms['ContractTerms']) ? $paymenTerms['ContractTerms'] : [];

        //put the hashcode for a js
        JavaScriptFacade::put(['Hashcode' => $order->HashCode,'paymentTerms' => $paymenTerms]);
        //add the months corresponding to each OrderProduct payment term

        $contractTerms = $cont->getEnumProperties(['ContractTerms']);

        foreach ($order->OrderProduct as $k => $val) {
            $paymentTerms = array_search($val->PaymentTerms, $contractTerms['ContractTerms']);
            $order->OrderProduct[$k]->Months = $paymentTerms;
        }

        $clientManagers = UsersController::listByRoles(['Client Manager']);

        $order->OrderFieldValue = $this->groupOrderFieldValues($order->OrderFieldValue);
        return view('orders.show',compact('order', 'cont','clientManagers'));
    }

    public function needApproval()
    {
        return view('orders.approval');
    }

    public function approveOrder()
    {
        $orderId = Request::only('OrderId');
        $cont = new RestController();
        $approval = $cont->postRequest('orders/Approve/' . $orderId['OrderId']);
        if ($approval instanceof View) {
            return $approval;
        }
        Session::flash('message', Lang::get('messages.approve-success'));
        return redirect('orders/show/' . $orderId['OrderId']);
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return View
     */
    public function edit($id)
    {
        $cont = new RestController();
        $order = $cont->getRequest('Orders(' . $id . ')?$expand=OrderFieldValue($expand=OrderField),OrderType,ClientAlias($select=Id,Name),User($select=Id,UserName),OrderProductPackage($expand=ProductPackage($expand=Product))');
        if ($order instanceof View) {
            return $order;
        }

        if(!empty($order->OrderProductPackage)){
            //getting the  products because we can't do it in the query
            foreach ($order->OrderProductPackage as $k => $val) {
                $product = $cont->getRequest("Products(" . $val->ProductPackage->Product_Id . ")");
                if (!$product instanceof View) {
                    $order->OrderProductPackage[$k]->ProductPackage->Product = $product;
                }
            }
        }

        $orderFields = $this->editValues($order->OrderFieldValue);
        $users = UsersController::usersList();


        $paymentTerms = $cont->getEnumProperties(['ContractTerms']);
        $paymentTerms = isset($paymentTerms['ContractTerms']) ? $paymentTerms['ContractTerms'] : [];

        $countries = CountriesController::countriesList();

        return view('orders.edit', compact('order', 'orderFields', 'users','paymentTerms','countries'));
    }

    /**
     * @param array $fields
     * @return mixed
     */
    public function editValues(array $fields)
    {
        $renderedFields = array();

        foreach ($fields as $i=>$field) {
            if ($field->OrderField->Required) {

                $required = "required";
                $fieldRequired = "<span class='' style='color:red;'>*</span>";
            } else {
                $required = null;
                $fieldRequired = null;
            }
            switch ($field->OrderField->OrderFieldType) {
                case "Text":
                    $renderedFields[$field->Id]['label'] = "<label for='field[" . $field->Id . "]' class='col-md-6 control-label-info'>" .
                        $field->OrderField->DisplayName . $fieldRequired .
                        ($field->OrderField->Description != null ?" <i class='fa fa-question-circle' title='".$field->OrderField->Description."'></i>" : "").
                        "</label>";
                    $renderedFields[$field->Id]['element'] = "<input id='field[".$field->Id."]' type='text' value='".$field->value."' name='OrderFieldValue[".$i."][value]' class='form-control orderField'" . $required . ">";
                    $renderedFields[$field->Id]['element'] .=  "<input type='hidden' name='OrderFieldValue[".$i."][Id]' value='".$field->Id."'>";
                    break;

                case "AdwordsId":
                    $renderedFields[$field->Id]['label'] = "<label for='field[" . $field->Id . "]' class='col-md-6 control-label-info'>" .
                        $field->OrderField->DisplayName . $fieldRequired .
                        ($field->OrderField->Description != null ?" <i class='fa fa-question-circle' title='".$field->OrderField->Description."'></i>" : "").
                        "</label>";
                    $renderedFields[$field->Id]['element'] = "<input id='field[".$field->Id."]' type='text' value='".$field->value."' name='OrderFieldValue[".$i."][value]' class='form-control orderField adwordsIdInput'" . $required . " pattern='\b\d{3}[-]?\d{3}[-]?\d{4}\b'>";
                    $renderedFields[$field->Id]['element'] .=  "<input type='hidden' name='OrderFieldValue[".$i."][Id]' value='".$field->Id."'>";

                    break;
                case "Radio":
                    $renderedFields[$field->Id]['label'] = "<label class='col-md-6 control-label'>" . $field->OrderField->DisplayName . "-" . $field->OrderField->Description . "</label> <br />";
                    $renderedFields[$field->Id]['element'] = '';
                    foreach ($field->OrderField->OrderFieldOption as $option) {
                        if ($option->Value == $field->value) {
                            $checked = 'checked';
                        } else {
                            $checked = "";
                        }
                        $renderedFields[$field->Id]['element'] .= "<input type='radio' id='" . $field->Id . $option->Id . "' " . $checked . " name='field[" . $field->Id . "]' value='" . $option->Value . "'>
                                                                   <label for=field[" . $field->Id . $option->Id . "] class='control-label'>" . $option->DisplayName . "</label><br>";
                    }
                    break;

//                case "CHECKBOX":
//
//                    $renderedFields[$field->Id]['label']   =  "<label class='col-md-3 control-label'>".$field->DisplayName."-".$field->Description."</label> <br />";
//                    $renderedFields[$field->Id]['element'] = '';
//                    foreach($field->OrderFieldOption as $option){
//
//                        $renderedFields[$field->Id]['element'] .= "<input type='checkbox' id='".$field->Id.$option->Id."' name='".$field->Id."[]' value='".$option->Value."'>
//                                                                    <label for=".$field->Id.$option->Id." class='control-label'>".$option->DisplayName."</label><br>";
//                    }
//                    break;
//
                case "SELECT":

                    $renderedFields[$field->Id]['label'] = "<label for=field[" . $field->Id . "] class='col-md-6 control-label'>" . $field->OrderField->DisplayName . "-" . $field->OrderField->Description . "</label> <br />";
                    $renderedFields[$field->Id]['element'] = '<select class="form-control" id="' . $field->Id . '" name="field[' . $field->Id . ']">';
                    foreach ($field->OrderField->OrderFieldOption as $option) {
                        if ($option->Value == $field->value) {
                            $selected = "selected";
                        } else {
                            $selected = "";
                        }
                        $renderedFields[$field->Id]['element'] .= '<option value ="' . $option->Value . '" ' . $selected . '>' . $option->DisplayName . '</option>';
                    }
                    $renderedFields[$field->Id]['element'] .= '</select>';

                    break;
//                case "SPECIAL_OPENING_HOURS":
//
//                    $renderedFields[$f->id]['label']   = "<label class='col-md-3 control-label'>".$f->displayName."-".$f->description."</label> <br />";
//                    $renderedFields[$f->id]['element'] = '<div class="form-group" id="'.$f->id.'" name="'.$f->id.'">';
//                    foreach($f->orderFieldOptions as $opt)
//                    {
//                        $renderedFields[$f->id]['element'] .=  " <label for=".$f->id.$opt->id." class='control-label'>".$opt->displayName."</label>
//                                                                <input type='text' id='".$f->id.$opt->id."' name='".$f->id."[first][]"."'  value=''>";
//                        $renderedFields[$f->id]['element'] .=  "<input type='text' id='".$f->id.$opt->id."' name='".$f->id."[second][]"."'  value=''> <br />";
//                    }
//                    $renderedFields[$f->id]['element'] .= '</div>';
//
//                    break;
                case "TEXTAREA":
                    $renderedFields[$field->Id]['label'] = "<label for=field[" . $field->ValueName . "] class='col-md-6 control-label'>" . $field->OrderField->DisplayName . "-" . $field->OrderField->Description . "</label>";
                    $renderedFields[$field->Id]['element'] = "<textarea id='" . $field->Id . "'  name='field[" . $field->Id . "]' class='form-control parsley-validated' required > </textarea>";
                    break;

                default:

                    break;
            }
        }
        return $renderedFields;
    }


    public static function groupOrderFieldValues(array $fields)
    {
        $grouped = [];
        foreach ($fields as $field){
            switch ($field->OrderField->OrderFieldType) {
                case "CampaignGoal":
                    if ($field->value != null && json_decode($field->value)){
                        $grouped[$field->OrderField_Id]['DisplayName'] = $field->OrderField->DisplayName;
                    $grouped[$field->OrderField_Id]['Type'] = "CampaignGoal";
                    $grouped[$field->OrderField_Id]['value'] = json_decode($field->value);
                    }
                    break;
                default :
                    if(!isset($grouped[$field->OrderField_Id])){
                        $grouped[$field->OrderField_Id]['value'] = $field->value."\n";
                        $grouped[$field->OrderField_Id]['DisplayName'] = $field->OrderField->DisplayName;
                    }else{
                        $grouped[$field->OrderField_Id]['value'] .= $field->value."\n";
                    }
                    break;
            }

        }
        return $grouped;
    }


    /**
     * Update the specified resource in storage.
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {

    }


    public function orderStatusIcons($order)
    {
        $icons = '';
        if ($order->Approved) {
            $icons .= "<i title='" . Lang::get('labels.approved') . "' class='fa fa-thumbs-o-up'></i>";
        }
        if ($order->Confirmed) {
            $icons .= "<i title='" . Lang::get('labels.confirmed') . "' class='fa fa-check-square-o'></i>";
        }
        if ($order->Archived) {
            $icons .= "<i title='" . Lang::get('labels.dismissed') . "' class='fa fa-archive'></i>";
        }

        return $icons;

    }

    public function getOrderFields($orderTypeId)
    {

        $contr = new RestController();
        $orderFields = $contr->getRequest('OrderTypes(' . $orderTypeId . ')/OrderTypeOrderField?$expand=OrderField($expand=OrderFieldOption)');
        if ($orderFields instanceof RedirectResponse) {
            return json_encode(['status' => 'error']);
        }
        return json_encode(['status' => 'success', 'data' => $orderFields]);


    }

    public function groupOrderFields(Request $request)
    {
        $input = $request::all();
        if (isset($input['field'])) {
            $fields = $input['field'];

            $params = [];
            foreach ($fields as $k => $val) {
                if (is_array($val)) {
                    foreach ($val as $key => $value) {
                        array_push($params, array('value' => $value, 'OrderField' => array('Id' => $k)));
                    }
                } else {
                    array_push($params, array('value' => $val, 'OrderField' => array('Id' => $k)));
                }
            }
            return json_encode($params);
        } else {
            return null;
        }
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

    public function information($contractId)
    {
        $cont = new RestController();
        //get the contract info
        $contract = $cont->getRequest("Contracts($contractId)" .
            '?$expand=Product($expand=ProductType),ClientAlias($select=Name,Id,AdwordsId;$expand=Country($select=CountryCode)),'.
            'ProductPackage($expand=Products($expand=Product($select=Id,Name))),'.
            'Children($expand=Product($select=Name,Id)),OriginalOrder($select=Id),ClientAlias');
        if($contract instanceof View) {
            return $contract;
        }
        
//        // don't get information for add-ons
//        if($contract->Parent_Id != null){
//            return Redirect::to(url('contracts/show',$contract->Parent_Id))->withErrors(Lang::get('messages.no-information-on-addons'));
//        }
        
        // we do this only for new contracts, came from OrderPackage
        if($contract->ProductPackage_Id != null && $contract->Parent_Id == null){
            $order = $cont->getRequest("Orders(" . $contract->OriginalOrder->Id . ")" .
                '?$expand=OrderProductPackage($filter=' . urlencode('ProductPackage_Id eq ' . $contract->ProductPackage_Id . ' and Domain eq ' . "'" . $contract->Domain . "'") .
                ';$expand=ProductPackage,Products($expand=Product($select=Name)),Country),OrderFieldValue($expand=OrderField)');
            if ($order instanceof View) {
                return $order;
            }

            //getting the addons products because we can't do it in the query
            foreach ($order->OrderProductPackage as $k => $val) {
                foreach ($val->Products as $id=>$addon){
                    $add = $cont->getRequest("Products(" . $addon->Product_Id . ")");
                    if(!$add instanceof View){
                        $order->OrderProductPackage[$k]->Products[$id]->Product = $add;
                    }
                }
            }
        }else{
            $order = null;
        }

        //get the fields, depending on the product type
        $orderType = $cont->getRequest('OrderTypes?$expand=OrderTypeOrderField($expand=OrderField($expand=OrderFieldOption))&$filter=' . urlencode('Type_Id eq ' . $contract->Product->ProductType->Id));
        if(empty($orderType->value) || $orderType instanceof View){
         return view('orders.information')->withErrors(Lang::get('messages.no-information-scheme-found'));
        }
        $orderType = $orderType->value[0];
        $orderFields = $this->renderFields($orderType->OrderTypeOrderField);
        JavaScriptFacade::put(['adwordsId'=>$contract->ClientAlias->AdwordsId]);
        return view('orders.information', compact('order', 'contract', 'orderFields'));
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
        if ($result || in_array($userId,['34',"55",'155'])) { // allow mikker to see all forms
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
                // loop through all contracts and find if he has a contract assigned to him
                $owner = false;
                if(is_array($item->Contract)){
                    foreach ($item->Contract as $contract){
                        if($contract->Manager_Id == $userId){
                            $owner = true;
                            break;
                        }
                    }
                }
                return $owner;
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
