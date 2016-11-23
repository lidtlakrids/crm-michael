<?php namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class WhiteListsController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        return view('whiteLists.index');
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
        $whiteList = $cont->getRequest('Whitelists('.$id.')?$expand=User($select=Id,FullName)');
        if($whiteList instanceof View)
        {
            return $whiteList;
        }
        return view('whiteLists.show', compact('whiteList'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        $users = UsersController::usersList();
        return view('whiteLists.create',compact('users'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id){
        $cont = new RestController();
        $whiteList = $cont->getRequest('WhiteLists('.$id.')?$expand=User($select=Id,FullName)');
        if($whiteList instanceof RedirectResponse)
        {
            return $whiteList;
        }

        $users = UsersController::usersList();
        return view('whiteLists.edit',compact('whiteList', 'users'));
    }

}