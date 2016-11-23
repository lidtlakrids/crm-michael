<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;

class AclController extends Controller
{
    /**
     * base acl menu
     *
     */
    public function index()
    {
        return view('acl.index');
    }

    /**
     * Returns the users, mapped with their roles
     *
     * @param null $userId
     * @return \Illuminate\View\View
     */
    public function userRoles($userId = null)
    {
        $cont = new RestController();

        $users = $cont->getRequest('Users?$select=Id,UserName,FullName&$expand=Roles&$orderby=FullName&$filter=Active+eq+true');
        if($users instanceof View){
            return $users;
        }

        $roles = $cont->getRequest('Roles?$orderby=Id');
        return view('acl.userRoles',compact('users','roles'));
    }

    /**
     * @param null $userId
     * @return array|\Illuminate\View\View|null
     */
    public function userPermissions($userId=null)
    {
        $cont = new RestController();

        //no secified id- return list of users
        if($userId == null) {
            $users = $cont->getRequest('Aros?$orderby=Alias&$filter='.urlencode("Model eq 'User'"));

            if($users instanceof View)
            {
                return $users;
            }
            $users = $users->value;

            $userNames = UsersController::usersList();

            return view('acl.userPermissions',compact('users','userNames'));
        }

        $user = $cont->getRequest('Aros('.$userId.')?$expand=Aroaco');
        $acos = $cont->getRequest('Acos?$orderby=Controller');

        JavaScriptFacade::put(['userId'=>$user->ForeignKey]);

        if($user instanceof View)
        {
            return $user;
        }
        if($acos instanceof View)
        {
            return $acos;
        }

        // make array with all allowed permissions
        $allowedPermissions = [];
        $deniedPermissions  = [];
        foreach($user->Aroaco as $permission)
        {
            if($permission->Allowed)
            {
                // the key is the ID of the ACO, the value is the ID of the AROACO;
                $allowedPermissions[$permission->ACO_Id] = $permission->Id;
            }elseif(!$permission->Allowed){
                $deniedPermissions[$permission->ACO_Id] = $permission->Id;
            }
        }
        $acos = $acos->value;
        return view('acl.userPermissions',compact('user','acos','allowedPermissions','deniedPermissions'));
    }
    public static function listRoles(){

        $cont = new RestController();
        $roles = [];
        $res = $cont->getRequest('Roles');
        if($res instanceof View){
            return $roles;
        }else{
            foreach ($res->value as $role){
                $roles[$role->Id] = $role->Name;
            }
        }
        return $roles;
    }

    public function rolePermissions($id = null){

        $cont = new RestController();
        if($id == null){
            // get the roles
            $roles = $cont->getRequest('Aros?$filter='.urlencode("Model eq webapi.Models.Model'Role'"));
            if(!$roles instanceof View){
                $roles = $roles->value;
            }else{$roles = null;}

            return view('acl.rolePermissions',compact('roles'));
        }else{
            $role = $cont->getRequest('Aros('.$id.')?$expand=Aroaco');

            $acos = $cont->getRequest('Acos?$orderby=Controller');
            if($role instanceof View)
            {
                return $role;
            }
            if($acos instanceof View)
            {
                return $acos;
            }

            // make array with all allowed permissions
            $permissions = [];
            foreach($role->Aroaco as $permission)
            {
                if($permission->Allowed)
                {
                    // the key is the ID of the ACO, the value is the ID of the AROACO;
                    $permissions[$permission->ACO_Id] = $permission->Id;
                }
            }
            $acos = $acos->value;

            return view('acl.rolePermissions',compact('role','acos','permissions'));
        }
    }
    /**
     * Sets all permissions into session variable
     *
     * @return bool|null
     * @internal param $permissions
     */
    public static function setSessionPermissions($userId)
    {
        $cont = new RestController();
        //Get user permissions and set them as session
        $userRoles =  $cont->getRequest("Users/action.Permissions");
        if($userRoles instanceof View){

            return $userRoles;
        }
        if(empty($userRoles->value)){
            return view('errors.backend-fault')->withErrors('No permissions');
        }
        $roles = $cont->getRequest("Users('$userId')/Roles");
        if($roles instanceof View){
            return $roles;
        }
        $r=[];
        foreach ($roles->value as $item){
            array_push($r,$item->Name);
        }
        // set the roles into session
        Session::put('roles',$r);
        
        $permissions = $userRoles->value;
        // delete old session data
        Session::forget('acl');
        //make an assoc array in the format Model=>Action
        $perms =[];
        foreach($permissions as $perm){
            array_push($perms,$perm->Controller."/".$perm->Method);
        }

        Session::put('acl',$perms);
        return true;
    }


    /**
     * page with all roles
     */
    public function roles(){

        return view('acl.roles');
    }

    /**
     * create role page
     *
     */
    public function createRole(){

        return view('acl.createRole');
    }

    /**
     * edit role page
     * @param $id
     * @return View|null
     */
    public function editRole($id){
        $cont = new RestController();

        $role = $cont->getRequest("Roles('$id')");
        if($role instanceof View)
        {
            return $role;
        }

        return view('acl.editRole',compact('role'));
    }

    /**
     * checks if user can see an item
     *
     * @param $model
     * @param $modelId
     *
     */
    public static function isOwner($model,$modelId){
        
        
        
    }       

}
