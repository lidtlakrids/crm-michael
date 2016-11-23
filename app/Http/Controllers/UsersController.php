<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Illuminate\View\View;


class UsersController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return View
	 */
	public function index()
	{
        $activeFilter = [
            'Active eq true'=>Lang::get('labels.active'),
            'Active eq false'=>Lang::get('labels.inactive'),
        ];

        return view('users.index',compact('activeFilter'));

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return View
	 */
	public function create()
	{
        $cont = new RestController();
        $titles = TitlesController::titleList();
        $salaryGroups = SalaryGroupsController::getList();
        return view('users.create',compact('titles','salaryGroups'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        //get the form input
        $input  = Request::all();
        unset($input['_token']);
        $cont = new RestController();
       $result = $cont->postRequest('Users',$input);
        if($result instanceof RedirectResponse)
        {
            return Redirect::back();
        }
        return redirect('users')->with('message','User added successfully');
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
        $user =  $cont->getRequest("Users('".$id."')".'?$expand=Title,SalaryGroup,Roles($expand=Role)');
        if($user instanceof View)
        {
            return $user;
        }
        $similarUsers = [];
        if(!empty($user->Roles)){
            foreach ($user->Roles as $role){
                if($role->Role->Name != "User") {
                    $similarUsers[$role->Role->Name] = UsersController::listByRoles([$role->Role->Name]);
                }
            }
        }
        return view('users.show',compact('user','similarUsers'));
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
        $user =  $cont->getRequest("Users('".$id."')");
        $titles = TitlesController::titleList();
        $salaryGroups = SalaryGroupsController::getList();

		return view('users.edit',compact('user','titles','salaryGroups'));
	}

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     * @internal param Request $request
     */
	public function update($id)
	{
        //get the user
		$user = User::find($id);
        //get the input from the form
		$input = Request::all();

        //check if the user is beign deactivated
		if(!isset($input['is_active']))
		{
			$input['is_active']='off';
		}
        try
        {
            // update the user's information;
            $user->update($input);

            //get all user roles
            $userRoles = $user->roles()->get()->toArray();
            // make easy-to-work with array with roles
            $rolesArr = [];
            foreach($userRoles as $ur):
            {
                $rolesArr[$ur['id']]= $ur['id'];
            }
            endforeach;

            //if roles are beign updated check for attached and detached.
            if(isset($input['roles'])){
            $detachedRoles = array_diff($rolesArr,$input['roles']);
            $attachedRoles = array_diff($input['roles'],$rolesArr);
            }

            //if we have detached roles, detached them from the user
            if(isset($detachedRoles) && !empty($detachedRoles))
            {
                foreach($detachedRoles as $r):
                    $user->detachRole($r);
                endforeach;
            }

            //if we have attached roles, detached them from the user
            if(isset($attachedRoles) && !empty($attachedRoles))
            {
                foreach ($attachedRoles as $a):
                    $user->attachRole($a);
                endforeach;
            }
        }
        catch (\Exception $e)
        {
            return Redirect::back()->withErrors($e->getMessage());
        }

        return Redirect::back()->with('message','Profile edited successfully');
	}

    /**
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        $rest = new RestController();

        $userId = Auth::user()->externalId;

        $user = $rest->getRequest("Users('$userId')");
        if($user instanceof View){
            return $user;
        }

        return view('users.myAccount',compact('user'));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function editMyAccount()
    {
        $userId = Auth::user()->externalId;
        $rest = new RestController();
        $user = $rest->getRequest("Users('$userId')");
        if($user instanceof View){
            return $user;
        }
        return view('users.editProfile',compact('user'));
    }



    /**
     * @return mixed
     */
    public function updateMyAccount()
    {
        $userId = Auth::user()->id;

        $user  = User::findOrFail($userId);

        $input = Request::all();

        $user->update($input);

        return Redirect::back()->with('message','Profile edited successfully');
    }


    /**
     * Admin function for setting password.
     * Users change their passwords from /my-profile/change-password
     *
     * @return View
     * @internal param $id
     */
    public function changePassword()
    {
        $userId = Auth::user()->externalId;
        return view('users.changePassword',compact('userId'));
    }
    /**
     * Admin function for setting password.
     * Users change their passwords from /my-profile/change-password
     *
     * @param $userId
     * @return View
     * @internal param $id
     */
    public function setPassword($userId)
    {
        
        return view('users.setPassword',compact('userId'));
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
     *  Gives a list of users with id=>username
     *
     * @param bool|string $active
     * @return array|null
     */
    public static function usersList($active = 'true')
    {
        $cont = new RestController();
        $users = array();

        if($active == 'true'){
            $active  = '&$filter='.urlencode('Active eq true');
        }else{
            $active= '';
        }

        $usersRaw = $cont->getRequest('Users?$orderby=FullName&$select=Id,UserName,FullName,Active'.$active);
        if($usersRaw instanceof View)
        {
            return $users;
        }
        foreach($usersRaw->value as $user)
        {
            $users[$user->Id] = $user->FullName." - ".$user->UserName;
        }


        if(Request::ajax()){
            return json_encode($users);
        }
        return $users;
    }


    /**
     * @param $userId
     * @return null
     */
    public static function getUserNameById($userId){
        $cont = new RestController();
        $user = $cont->getRequest("Users('$userId')".'?$select=UserName,Id,FullName&$expand=SalaryGroup');
        if($user instanceof View)
        {
            return null;
        }

        return $user;
    }

    /**
     * @return array|null
     */
    public static function teamsList()
    {
        $cont = new RestController();
        $teams = array();

        $teamsRaw = $cont->getRequest('ManagerTeams?$select=Id,Name');
        if($teamsRaw instanceof View)
        {
            return [];
        }
        foreach($teamsRaw->value as $team)
        {
            $teams[$team->Id] = $team->Name;
        }

        return $teams;
    }


    /**
     * returns a select, used to query data
     *
     * @param $property if we set this, it will overwrite the default : User_Id
     * @param null $active
     * @return array
     */
    public static function queryUsersList($property = null,$active = null){
        if($active != null){
            $activeQ = "Active ne null";
        }else{
            $activeQ = "Active eq true";
        }
        $cont = new RestController();
        $users = $cont->getRequest('Users?$select=FullName,Id,UserName&$filter='.urlencode($activeQ).'&$orderby=FullName');
        if($users instanceof View){
            return [];
        }
        $userList= [];
        if($property == null){
            $property = "User_Id";
        }

        foreach($users->value as $user){
            $userList[$property.' eq \''.$user->Id."'"]=$user->FullName."-".$user->UserName;
        }
        return $userList;
    }

    public static function listByRoles(array $roles = null,$active = null){

        if($roles == null){
            $roles = Input::all('roles');
            if(isset($roles['roles']) && !empty($roles['roles'])){
                $roles = $roles['roles'];
            }else{
                return [];
            }
        }

        $aciveQuery = "Active eq true";
        if($active == 'all'){
            $aciveQuery = "Active ne null";
        }

        $cont = new RestController();
        //make array with or conditions for the user roles
        if(count($roles) > 1){
            $query ='';
           for($i=0;$i<count($roles);$i++){
               if($i==0){
                   $query .= 'Roles/any(d:d/Role/Name eq \''.$roles[$i].'\')';
               }else{
                   $query .= 'or Roles/any(d:d/Role/Name eq \''.$roles[$i].'\')';
               }
           }
        }else{
            $query = 'Roles/any(d:d/Role/Name eq \''.$roles[0].'\')';
        }

        $users = $cont->getRequest('Users?$filter='.urlencode($aciveQuery.' and ('.$query.')').'&$select=Id,FullName&$expand=Roles($select=Id;$expand=Role($select=Id))&$orderby=FullName');
        if($users instanceof View){
            return [];
        }
        $list = [];
        foreach ($users->value as $u){
            $list[$u->Id] = $u->FullName;
        }
        if(Request::ajax()){
            return json_encode($list);
        }
        return $list;
    }

    public static function queryListByRoles(array $roles,$property = null,$extraCond = null){

        $cont = new RestController();

        //make array with or conditions for the user roles
        if(count($roles) > 1){
            $query ='';
            for($i=0;$i<count($roles);$i++){
                if($i==0){
                    $query .= 'Roles/any(d:d/Role/Name eq \''.$roles[$i].'\')';
                }else{
                    $query .= ' or Roles/any(d:d/Role/Name eq \''.$roles[$i].'\')';
                }
            }
        }else{
            $query = 'Roles/any(d:d/Role/Name eq \''.$roles[0].'\')';
        }

        $users = $cont->getRequest('Users?$expand=Roles($select=Id;$expand=Role($select=Id))&$select=Id,FullName&$filter='.urlencode('Active eq true and ('.$query.')').'&$orderby=FullName');
        if($users instanceof View){
            return [];
        }
        $userList= [];
        if($property == null){
            $property = "User_Id";
        }

        foreach($users->value as $user){
            if($extraCond == null) {
                $userList[$property . ' eq \'' . $user->Id . "'"] = $user->FullName;
            }else{
                $userList['('.$property . ' eq \'' . $user->Id . "' or $extraCond eq '".$user->Id."')"] = $user->FullName;
            }
        }

        return $userList;
    }
    // updates info for the frontend
    public function updateCurrentUserInfo($id = null)
    {
        $cont = new RestController();

        if ($id == null){
        $user = Auth::user();
        $info = $cont->getRequest('Users/UserInfo');
        }else{
            $user = User::where('externalId','=',$id)->firstOrFail();
            $info = $cont->getRequest('Users(\''.$id.'\')?$select=FullName,Email,EmployeeLocalNumber');
            $info->LocalNumber = $info->EmployeeLocalNumber;
        }
        $user->fullName = $info->FullName;
        $user->email = $info->Email;
        $user->localNumber = $info->LocalNumber;
        $user->update();
        return Response::make(200);
    }

    public static function activeUserId(){
        return Auth::user()->externalId;
    }
}
