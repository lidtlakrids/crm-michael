<?php namespace App\Services;
/**
 * Created by PhpStorm.
 * User: dib
 * Date: 18-Apr-16
 * Time: 10:09 AM
 */
use AdWordsUser;
use ExampleUtils;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

use LinkOperation;
use ManagedCustomerLink;
use OAuth2Exception;
use PhpSpec\Exception\Exception;
use ValidationException;

class AdWordsApi{

    protected $salesMcc = "509-130-5276";
    protected $mainMcc  = "810-561-3229";
    protected $bgMcc    = "362-763-2312";
    protected $itMcc    = "625-804-4471";
    protected $apiVersion  = "v201609";
    private $user;

    function __construct(){
        $this->user = new AdWordsUser(config_path('google-ads.ini'));
        $this->user->SetClientCustomerId(stripDashes($this->mainMcc));
        $this->user->LogAll();
    }
    /**
     * @param AdWordsUser $user
     * @return mixed
     */
    public function getClientCampaigns(AdWordsUser $user){

        $selector = new \Selector();
        $selector->fields = array('Id', 'Name','Status');

        $campaingService = $this->user->GetService('CampaignService',$this->apiVersion);
        try{
            $campaings = $campaingService->get($selector);
        }catch (Exception $e){
            dd($e);
        }
        return $campaings;
    }

    /**
     * @param $adwordsId
     * @return mixed
     */
    public function getManagedAccount($adwordsId){
        $accountService = $this->user->GetService('ManagedCustomerService');
        $selector = new \Selector();
        $selector->fields = ['CustomerId'];
        $selector->predicates[] = new \Predicate('CustomerId','EQUALS',stripDashes($adwordsId));
        $account = null;
        try{
            $account = $accountService->get($selector);
        }catch (Exception $e){

        }
        return $account;
    }

    /**
     * @param $params
     * @return string
     */
    public function sendInvitation($params){

        $managedCustomerService = $this->user->GetService('ManagedCustomerService',$this->apiVersion);
        $customerLink = new ManagedCustomerLink();
        $customerLink->clientCustomerId = stripDashes($params['adwordsId']);
        $customerLink->pendingDescriptiveName = str_replace(['http://','https://','www.'],'',trim($params['website']));
        $customerLink->linkStatus= "PENDING";
        $customerLink->managerCustomerId= stripDashes($this->salesMcc);

        $linkop = new LinkOperation();
        $linkop->operand = $customerLink;
        $linkop->operator = "ADD";
        $operations = $linkop;
        try {
            $result = $managedCustomerService->mutateLink($operations);
        } catch (OAuth2Exception $e) {
            ExampleUtils::CheckForOAuth2Errors($e);
        } catch (ValidationException $e) {
            ExampleUtils::CheckForOAuth2Errors($e);
        } catch (Exception $e) {
            return printf("An error has occurred: %s\n", $e->getMessage());
        }
        if($result){
            return "pending";
        }
    }

    /**
     * looks for accoutn with the give idd in the mcc
     *
     * @param $adwordsId
     * @return string
     */
    public function checkAccountLink($adwordsId){
        $managedService = $this->user->GetService('ManagedCustomerService');
        $selector = new \Selector();
        $selector->fields = ['CustomerId'];
        $selector->predicates[] = new \Predicate('CustomerId','EQUALS',stripDashes($adwordsId));
        $account = $managedService->get($selector);
        // if we don't find account, check for pending invitations
        if($account->entries == null){
            // check for pending invitations
            $pending = $this->pendingInvitations(['adwordsId'=>$adwordsId]);
            if($pending){
                return "pending";
            }else{
                return 'not-linked';
            }
        }else{
            return 'linked';
        }
    }

    /**
     * @param null $params
     * @return bool
     */
    public function pendingInvitations($params = null){
        $managedService = $this->user->GetService('ManagedCustomerService');
        $selector = new \PendingInvitationSelector();
        if($params == null) {
            $selector->managerCustomerIds=stripDashes($this->mainMcc);

        }else{
            if(isset($params['mcc'] )){
                $selector->managerCustomerIds=stripDashes($this->{$params['mcc']});

            }else{
                $selector->managerCustomerIds=stripDashes($this->salesMcc);
            }
        }

        $pending = $managedService->getPendingInvitations($selector);

        $res = false;
        if($pending != null){
            foreach ($pending as $item){
                if($item->client->customerId == stripDashes($params['adwordsId'])){
                    $res = true;
                    break;
                }
            }
        }
        return $res;
    }

    /**
     * @param $adwordsId
     * @return string
     * @internal param $adwordsId
     */
    public function cancelInvitation($adwordsId){
        $managedCustomerService = $this->user->GetService('ManagedCustomerService',$this->apiVersion);
        $customerLink = new ManagedCustomerLink();
        $customerLink->clientCustomerId = stripDashes($adwordsId);
        $customerLink->linkStatus= "CANCELLED";
        $customerLink->managerCustomerId= stripDashes($this->salesMcc);

        $linkop = new LinkOperation();
        $linkop->operand = $customerLink;
        $linkop->operator = "SET";
        $operations = $linkop;
        try {
            $result = $managedCustomerService->mutateLink($operations);
        } catch (OAuth2Exception $e) {
            ExampleUtils::CheckForOAuth2Errors($e);
        } catch (ValidationException $e) {
            ExampleUtils::CheckForOAuth2Errors($e);
        } catch (Exception $e) {
            printf("An error has occurred: %s\n", $e->getMessage());
        }
        if($result){
            return "not-linked";
        }
        return "not-linked";
    }

    public function getMccKeywords($mcc = null){
        if($mcc == null){
            $mcc = $this->itMcc;
        }
        $this->user->SetClientCustomerId($mcc);
        // get all accounts
        $managedCustomerService = $this->user->GetService('ManagedCustomerService',$this->apiVersion);
    }


}