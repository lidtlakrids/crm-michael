<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Curl\Curl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use PhpSpec\Exception\Exception;

class RestController extends Controller {

    protected $localhost     = 'http://localhost:8484/api/';

    protected $liveServer    = 'http://gcmdev.dk/';
    protected $liveApiAddress = 'http://gcmdev.dk/api/';

    protected $devServer= 'http://svn.crmtest.dk:8483/';
    protected $devApiAddress = 'http://svn.crmtest.dk:8483/api/';

    protected $apiAddress  = 'http://svn.crmtest.dk:8483/api/';
    protected $serverAddress  = 'http://svn.crmtest.dk:8483/';

    const UserEntityType = "webapi.Models.ApplicationUser";

    public function initCurl(){
        return new Curl();
    }

    /**
     * @param $creditentials
     * @return Curl
     */
    public function getToken($creditentials)
    {
        $curl = new Curl();
        $curl->post($this->serverAddress.'token', array(
            'username'  => $creditentials['username'],
            'password'  => $creditentials['password'],
            'grant_type'=> 'password'
        ));
        if($curl->error == true) {
          return  $curl;
        }
        //put the auth token in the session
        Session::put('Bearer', $curl->response->access_token);

        //set cookie with the access token. This cookie is refreshed with every request at AppServiceProvider
       setcookie('auth',$curl->response->access_token, time()+(30*60),'/' ,'', false, false );
        return $curl;
    }

    /**
     * @return Curl
     */
    public function whoAmI()
    {
        $token = Session::get('Bearer');
        $curl = new Curl();
        $curl->setHeader('Authorization', 'Bearer '.$token);
        $curl->get($this->apiAddress.'Account/whoami');
        if(isset($token->error)) {
            Redirect::back()->withErrors($token->error);
        }
        return $curl;
    }

    /**
 * @param $url
 * @param null $params
 * @return null
 */
    public function getRequest($url,$params= null){

        $token = $this->sessionToken();
        $curl = new Curl();
        $curl->setHeader('Authorization', 'Bearer '.$token);
        $curl->setHeader('Accept','application/json');
        $curl->setHeader('Content-Type','application/json; charset=utf-8');
        $curl->get($this->apiAddress.$url,$params);

        if($curl->error == true || $curl->response == "null")
        {
            $error = $this->resolveError($curl);
            return view('errors.backend-fault')->withErrors($error);
        }
        return $curl->response;
    }

    /**
     * @param $url
     * @param null $params
     * @return null
     */
    public function getRequestAnon($url,$params= null){

        $curl = new Curl();
        $curl->setHeader('Accept','application/json');
        $curl->setHeader('Content-Type','application/json; charset=utf-8');
        $curl->get($this->apiAddress.$url,$params);

        if($curl->error == true || $curl->response == "null")
        {
            $error = $this->resolveError($curl);
            return view('errors.backend-fault')->withErrors($error);
        }
        return $curl->response;
    }
    /**
     * @param $url
     * @param null $params
     * @return null
     */
    public function postRequest($url,$params = null)
    {
        $token = $this->sessionToken();
        $curl = new Curl();
        $curl->setHeader('Content-Type','application/json');
        $curl->setHeader('Accept','application/json');
//        $curl->setHeader('ContentLength',strlen(json_encode($params)));
        $curl->setHeader('Authorization', 'Bearer '.$token);
        $curl->post($this->apiAddress.$url,$params);

        if($curl->error == true || $curl->response == "null")
        {
            $error = $this->resolveError($curl);
            return view('errors.backend-fault')->withErrors($error);
        }
        return $curl->response;
    }

    public function isWhitelist(){
        $curl = new Curl();
        $curl->setHeader('Content-Type','application/json');
        $curl->setHeader('Accept','application/json');
        $curl->post($this->apiAddress.'Publics/IsWhitelisted',['ip'=>getIp()]);

        if($curl->error == true || $curl->response == "null")
        {
            die();
        }
        return $curl->response;
    }
    
    
    /**
     * @param $url
     * @param null $params
     * @return null
     */
    public function putRequest($url,$params = null)
    {
        $token = $this->sessionToken();
        if($token instanceof RedirectResponse)
        {
            return $token;
        }
        $curl = new Curl();
        $curl->setHeader('Content-Type','application/json');
        $curl->setHeader('Accept','application/json');
        $curl->setHeader('Authorization', 'Bearer '.$token);
        $curl->put($this->apiAddress.$url,$params);

        if($curl->error == true || $curl->response == "null")
        {
            $error = $this->resolveError($curl);
            return view('errors.backend-fault')->withErrors($error);
        }
        return $curl->response;
    }

    /**
     * @param $url
     * @param null $params
     * @return null
     */
    public function patchRequest($url,$params = null)
    {
        $token = $this->sessionToken();
        if($token instanceof RedirectResponse)
        {
            return $token;
        }
        $curl = new Curl();
        $curl->setHeader('Content-Type','application/json');
        $curl->setHeader('Accept','application/json');
        $curl->setHeader('Authorization', 'Bearer '.$token);
        $curl->patch($this->apiAddress.$url,$params);

        if($curl->error == true || $curl->response == "null")
        {
            $error = $this->resolveError($curl);
            return view('errors.backend-fault')->withErrors($error);
        }
        return $curl->response;
    }


    /**TODO file download
     * @param $url
     * @return $this|null
     */
    public function downloadRequest($url,$fileName){
        $token = $this->sessionToken();
        $file_url = $this->apiAddress.$url;
        if (file_exists($file_url)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Authorization: Bearer ".$token);
        header("Content-disposition: attachment; filename=\"" . basename($fileName) . "\"");
        readfile($file_url);
        
    }
    /**
     * @param $url
     * @param null $params
     * @return null
     */
    public function deleteRequest($url,$params = null)
    {
        $token = $this->sessionToken();
        if($token instanceof RedirectResponse)
        {
            return $token;
        }
        $curl = new Curl();
        $curl->setHeader('Content-Type','application/json');
        $curl->setHeader('Accept','application/json');
        $curl->setHeader('Authorization', 'Bearer '.$token);
        $curl->delete($this->apiAddress.$url,$params);

        if($curl->error == true || $curl->response == "null")
        {
            $error = $this->resolveError($curl);

            return view('errors.backend-fault')->withErrors($error);
        }
        return $curl->response;
    }


    public function sessionToken(){

        if (Session::has('Bearer'))
        {
            return Session::get('Bearer');
        }
        else
        {
            return Redirect::to('auth/login');
        }
    }

    /**
     * transforms curl response with errors into a single error string
     *
     * @param $curl
     * @return string
     */
    public function resolveError($curl){

        if($curl->errorCode==404){
            return $error = Lang::get('messages.resource-not-found');
        }

        if($curl->httpError == true)
        {
            $error  = $curl->httpErrorMessage.' : '.$curl->url;

            if(isset($curl->response->error->message)) {
                $error .= "<br/>".$curl->response->error->message;
                if(isset($curl->response->error->innererror)){
                    $error .= "<br/>".$curl->response->error->innererror->message;
                }
            }
            if(isset($curl->response->MessageDetail)){
                $error .= $curl->response->MessageDetail;
            }
            if(isset($curl->response->ExceptionMessage)){
                $error .= $curl->response->ExceptionMessage;
            }
        }
        elseif($curl->curlError == true)
        {
            $error= $curl->curlErrorMessage;
            if(isset($curl->response->Message)) {
                $error .= $curl->response->Message;
            }
            if(isset($curl->response->MessageDetail)){
                $error .= $curl->response->MessageDetail;
            }
            if(isset($curl->response->ExceptionMessage)){
                $error .= $curl->response->ExceptionMessage;
            }
        }
        elseif($curl->response == "null" || empty($curl->response)) {
            $error = 'Nothing was found at '.$curl->url;
        }
        else {
            $error = 'Unknown error. fix your code m8';
        }
        if(isset($curl->response->error->innererror->stacktrace)){
            $error .= "<br>".$curl->response->error->innererror->stacktrace;
        }
        $this->saveError(['error'=>$error,'url'=>$curl->url]);
        return $error;
    }

    public function saveError($error){
        $uri = request()->path();
        $data = ['Error'=>$error['error']];
        $data['Error'] .= "\n \r"." Page : ".$uri;
        $data['User_Id'] = isset(Auth::user()->externalId) ? Auth::user()->externalId : null;
        $result = $this->postRequest('Logs',$data);
    }
    /**
     * Searches through the metadata of the api to find list of enum properties ( for selects and such)
     *
     * todo errors
     *
     * @param $enumName
     * @return array
     * @internal param $propertyName
     */
    public function getEnumProperties(array $enumName)
    {
        //init properties array
        $properties = [];
        // get the enum types
        $enumTypes = $this->getEnumTypes();
        if($enumTypes instanceof View){
            return $properties;
        }
        //find the enum with the specifiedName
        foreach($enumTypes as $type)
        {
            if(in_array($type['@attributes']['Name'],$enumName)){
                foreach($type['Member'] as $enumProp){
                    $properties[$type['@attributes']['Name']][$enumProp['@attributes']['Value']]=$enumProp['@attributes']['Name'];
                }
            }
        }
        return $properties;
    }

    public function refreshToken(){
        $username = "dib";

        $curl = new Curl();
        $curl->post($this->serverAddress.'token', array(
            'username'  => $username,
            'password'  => $pass = "Password1!",
            'grant_type'=> 'password'
        ));
        if($curl->error == true) {
            return  $curl;
        }
        //put the auth token in the session
        Session::put('Bearer', $curl->response->access_token);

        //set cookie with the access token. This cookie is refreshed with every request at AppServiceProvider
        setcookie('auth',$curl->response->access_token, time()+(30*60),'/' ,'', false, false );

    }


    /**
     * returns an array of the enum properties ,with value of the query needed to get them. Useful stuff
     *
     * @param $enumName
     * @param $propertyName  -- we need this because the enums are named differently in the models
     * @return array
     */
    public function getEnumQuerySelect($enumName,$propertyName){

        //init properties array
        $properties = [];
        // get the enum types
        $enumTypes = $this->getEnumTypes();
        if($enumTypes instanceof View){
            return $properties;
        }

        //find the enum with the specifiedName
        if(is_array($enumTypes)){
            foreach($enumTypes as $type)
            {
                if($type['@attributes']['Name'] ==$enumName){
                    foreach($type['Member'] as $enumProp){
                        $properties[$propertyName.' eq webapi.Models.'.$enumName."'".$enumProp['@attributes']['Name']."'"] = $enumProp['@attributes']['Name'];
                    }
                }
            }
        }

        return $properties;
    }


    /**
     * gets all enum types
     *
     * @return mixed
     */
    public function getEnumTypes(){

        $array = self::getMetadataArray();
        $types = $array['Schema'][0]['EnumType']; // MAGIC

        return $types;
    }

    /**
     * gets all complex types
     *
     * @return mixed
     */
    public function getComplexTypes(){

        $array = self::getMetadataArray();
        $types = $array['Schema'][3]['ComplexType']; // MAGIC

        return $types;
    }

    /**
     * gets all of the entities from the metadata
     * todo exceptions*
     *
     */
    public function getEntities(){
        $array = self::getMetadataArray();
        $entities = $array['Schema'][0]['EntityType']; // MAGIC

        return $entities;
    }

    /**
     * returns a single entity
     *
     * @param $model
     * @return
     * @internal param $entity
     */
    public function getEntity($model){

        $entities = self::getEntities();

        foreach($entities as $ent){
            if($ent['@attributes']['Name'] == $model){
                $entity = $ent;
                break;
            }
        }

        return $entity;
    }


    public function getRelatedEntities($model){

        //entities we care about
        $allowedEntities  = ['ClientAlias','Client','DraftLine','Invoice','Contract','InvoiceLines','InvoiceLine','Children','TaskList','Order'];
        $entity = $this->getEntity($model);

        //search in the Navigation property to find how is it connected with the other predefined entities
        $navigationProperties = $entity['NavigationProperty'];

        //the array with related entities;
        $relations = ['many'=>[],'one'=>[]];

        foreach($navigationProperties as $nav){
            if(in_array($nav['@attributes']['Name'],$allowedEntities)){
                //find the type of the relation
                //collection =  many
                if (strpos($nav['@attributes']['Type'], 'Collection') !== false) {
                    if($model=="Contract" && $nav['@attributes']['Name'] == "InvoiceLines"){
                        $name = "Invoice";
                    }elseif($model == 'Draft' && $nav['@attributes']['Name']=='DraftLine'){
                        $name = 'Contract';
                    } elseif($model == "Invoice" && $nav['@attributes']['Name'] == "InvoiceLine"){
                        $name = "Contracts";
                    }else{ $name = $nav['@attributes']['Name'];}
                    array_push($relations['many'],$name);
                }else{
                    if($model=="Contract" && $nav['@attributes']['Name'] == "ClientAlias"){
                        $name = "Client";
                    }elseif($model == "Invoice" && $nav['@attributes']['Name']=="Client"){
                        $name = "ClientAlias";
                    }elseif($model=="Invoice" && $nav['@attributes']['Name']=='Contract'){
                        continue; // this is not really used
                    }elseif($model == 'Draft' && $nav['@attributes']['Name']=='Contract'){
                        continue; // The contract on the draft will already be in the DraftLines
                    } else{
                        $name = $nav['@attributes']['Name'];
                    }
                    array_push($relations['one'],$name);
                }
            }
        }

        //add optimize comments to contracts
        if($model == "Contract"){
            array_push($relations['one'],'Optimize');
        }


        if(Request::ajax()){
            $relations = json_encode($relations);
        }
        return $relations;
    }


    public function updateMetadata(){

        try{
            $xmlResult = @file_get_contents($this->apiAddress.'$metadata');
        }catch(Exception $e){
            return view('errors.backend-fault');
        }
        if(!$xmlResult){
            return view('errors.backend-fault');
        }
        //replace the first 2 nodes with a simple one "asd"
        $xmlResult = str_replace('<edmx:Edmx Version="4.0" xmlns:edmx="http://docs.oasis-open.org/odata/ns/edmx">',"",$xmlResult);
        $xmlResult = str_replace('<edmx:DataServices>',"<asd>",$xmlResult);
        $xmlResult = str_replace('</edmx:Edmx>',"",$xmlResult);
        $xmlResult = str_replace('</edmx:DataServices>',"</asd>",$xmlResult);

        //metadata path
        $path = public_path().DIRECTORY_SEPARATOR.'metadata.xml';

        file_put_contents($path,$xmlResult);
    }

    /**
     * gets an array of metadata information
     *
     * @return View|mixed
     */
    public function getMetadataArray(){


        $handle = file_get_contents('metadata.xml');

        //initialize the array for all metadata information
        $array = [];

        if($handle){
        //$xml  = file_get_contents(asset('metadata.xml'));
        //load the xml string and make it into array
        $xml   = simplexml_load_string($handle);
        $json  = json_encode($xml);
        $array = json_decode($json,true);
        }
        return $array;
    }

    /**
     * finds all user relations to the gived model
     * @param $model
     * @return array
     */
    public function getUserRelations($model){
        // relations we want to update
        $allowed = ['Booker','User','Manager','ClientManager','Assigned'];
        $model = $this->getEntity($model);
        $rels = [];
        foreach ($model['NavigationProperty'] as $item){
            if(in_array($item['@attributes']['Name'] ,$allowed)){
                array_push($rels,[$item['@attributes']['Name'],$item['ReferentialConstraint']['@attributes']['Property']]);
            }
        }

        if(Request::ajax()){
            $rels = json_encode($rels,true);
        }

        return $rels;
    }
    


    /**
     * Generates a link for GET request to the backend
     *
     * @param array $oData
     * @return string
     */
    public function oDataGetLink(array $oData)
    {
        // add the api address
        //$link = $this->apiAddress;

            //add the resource address to the url
            $link = $oData['url'];

            //add parentheses for the selected id, if set
            isset($oData['urlId'])? $link.= "(".$oData['urlId'].")" : null ;
            $link .= '?';
            if(isset($oData['select'])){
                $link .= $this->generateSelect($oData['select'],false);
            }

            // add all parameters to the link
            if(isset($oData['parameters']))
            {
                $link .= $this->generateParameters($oData['parameters'],false);
            }

            // manage expand
            if(isset($oData['expand']))
            {
                $link .= '&$expand';
                $link .= $this->generateExpand($oData['expand']);

            }
        return $link;
    }

    /**
     * Generates the references string for the url
     *
     * @param array $expand
     * @return string
     */
    public function generateExpand(array $expand)
    {
        $link ='';
        foreach($expand as $expanding=>$options)
        {
            $link .= '';
        }
        return $link;
    }

    /**
     * generate parameters for the link, such as count
     *
     * @param array $params
     * @param bool $trail
     * @return string
     */
    public function generateParameters(array $params, $trail = false)
    {
        $link ='';
        foreach($params as $param=>$value){
        $link .= '$'.$param.'='.$value;
        $link .= "&";
            }
        if($trail)
        {
            return '&'.$link;
        }
        return $link;
    }

    /**
     * @param array $select
     * @param bool $trail Should the select start with " & " or not. Either true or false
     * @return string
     */
    public function generateSelect(array $select,$trail = false)
    {
        $link='';
        $link .= '$select=';
        $link .= implode($select,',');
        $link .= "&";

        if($trail){
            return "&".$link;
        }
        return $link;
    }

    public function getComplexTypeProperties($typeName){
        $types = self::getComplexTypes();
        $props = [];
        foreach ($types as $t){
            if($t['@attributes']['Name'] == $typeName){
                foreach ($t['Property'] as $prop){
                    array_push($props,$prop['@attributes']['Name']);
                }
                break;
            }
        }
        return $props;
    }
}
