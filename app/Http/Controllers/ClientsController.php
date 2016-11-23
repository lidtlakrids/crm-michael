<?php namespace App\Http\Controllers;

use App\Http\Requests;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\RestController;
use Illuminate\Support\Facades\Session;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ClientsController extends Controller
{

    protected $backendUrl = 'Clients';
    /**
     * Display a listing of clients.
     *
     * @return Response
     */
    public function index()
    {
        $cont = new RestController();
//        $clients = $cont->getRequest('Clients?$select=Id,CINumber,Created&$expand=User($select=UserName)');
//        if($clients instanceof RedirectResponse)
//        {
//            return $clients;
//        }
//        $clients = $clients->value;
        //,compact('clients')
        return view('clients.index');
    }

    public function ciNumbers()
    {
//        $contr = new RestController();
//
//        $clients = $contr->getRequest('Clients?$count=true');
//        if ($clients instanceof RedirectResponse) {
//            return $clients;
//        }
//
//        $clients = $clients->value;
        return view('clients.ciNumbers');
    }


    /**
     * used for the ajax call to populate table
     */
    public function getClients()
    {
        //initialzie the rest controller
        $contr = new RestController();
        $odata = [
                'url'=>$this->backendUrl,
                'urlId'=>(isset($id))?$id:null,
                'select' => ['CINumber','Created'], // what fields do we want
                //parameters passed for the main resource
                'parameters'=>[
                    'count'=>'true',
                ],
                'expand' => ['User'=>[
                    'select'=>['UserName']
                     ],
                ]
        ];
        $link = $contr->oDataGetLink($odata);
        $clients = $contr->getRequest($link);
        if ($clients instanceof RedirectResponse) {
            return json_encode(['status'=>'error']);
        }
        $responseArray=[];
        $responseArray['recordsTotal']= $clients->{'@odata.count'};
        $rows = array();
        foreach ($clients->value as $row) {
            $rows[] = array_values((array)$row);
        }

        $responseArray['data'] = $rows;

        return json_encode($responseArray);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $users = UsersController::usersList();
        return view('clients.create',compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input  = Request::all();
        $client = [];
        $client['User_Id']  = $input['SellerId'];
        $client['CINumber'] = $input['CINumber'];
        $client['Created']  = date("Y-m-d");

        $cont = new RestController();
        $result = $cont->postRequest('Clients',json_encode($client));
        if($result instanceof RedirectResponse)
        {
            return $result;
        }
        return Redirect::to('clients/show/'.$result->Id)->with('message', Lang::get('messages.client-created-successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $cont = new RestController();
        $client = $cont->getRequest('Clients('.$id.')?$expand=ClientAlias($expand=Contact,Country,Contract($select=Manager_Id))');
        if ($client instanceof RedirectResponse) {
            return $client;
        }
        if(!$this->isOwner($client)){
            return view('errors.denied');
        }

        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $cont = new RestController();

        $client = $cont->getRequest('Clients('.$id.')?$expand=ClientAlias($select=Id,Name,City),ClientManager');

        $managers = UsersController::listByRoles(['Client Manager']);
        $sellers = UsersController::listByRoles(['Sales']);

        return view('clients.edit',compact('client','managers','sellers'));
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


    public function needClientManager(){

        $managers = UsersController::listByRoles(['Client Manager']);

        JavaScriptFacade::put(['managers'=>$managers]);
        return view('clients.needManager');
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
        if ($result) {
            return true;
        }
        //remove the user role. we don't care about it
        $roles = array_diff($roles, array('User'));
        $roles = array_values($roles);
        switch ($roles[0]){
            case "Client Manager":
                return $item->ClientManager_Id == null? true: Auth::user()->externalId ? true:false;
            break;

            case "Adwords":
            case "SEO" :
                if(isset($item->ClientAlias)){
                    foreach ($item->ClientAlias as $alias){
                        if(isset($alias->Contract)){
                            foreach ($alias->Contract as $contract){
                                if($contract->Manager_Id == $userId){ return true;}
                            }
                        }
                    }
                }
                break;
            case "Sales":
                if(isset($item->ClientAlias)){
                    foreach ($item->ClientAlias as $alias){
                        if($alias->User_Id == $userId){return true;};
                    }
                }
                break;
            default :
                break;
        }
        //default, we deny.
        return false;
    }



}
