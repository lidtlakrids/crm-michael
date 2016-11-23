<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Illuminate\Support\Facades\Request;


class SettingsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if(UsersController::activeUserId() == '39f3b2fd-10c6-4d48-9e2e-bb0aa4f86d39') exit();
        $cont = new RestController();
        $result = $cont->getRequest('Settings');
        if ($result instanceof RedirectResponse) {
            return $result;
        }
        $result = $result->value;
        return view('settings.index', compact('result'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {

        return view('settings.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input = Request::all();
        unset($input['_token']);
        $cont = new RestController();
        $result = $cont->postRequest('Settings', $input);
        if ($result instanceof RedirectResponse) {
            return Redirect::back()->withErrors();
        }
        Session::flash('message', 'Setting saved');
        return redirect('settings');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * @param $id
     * @return Response
     * @internal param int $id
     */
    public function edit($id)
    {
        $cont = new RestController();

        $setting = $cont->getRequest("Settings($id)");
        if($setting instanceof View){
            return $setting;
        }
        return view('settings.edit', compact('setting'));
    }

    /**
     * Update the specified resource in storage.
     * @return Response
     * @internal param int $id
     */
    public function update()
    {
        $input = Request::all();
        unset($input['_token']);
        $cont = new RestController();
        $result = $cont->putRequest('Settings/' . $input['Id'], $input);
        if ($result instanceof RedirectResponse) {
            return Redirect::back()->withErrors();
        }
        Session::flash('message', 'Setting updated');
        return redirect('settings');

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

    /**
     * updates the metadata from the server
     */
    public function updateMetadata(){

        $cont = new RestController();

        $cont->updateMetadata();
        return json_encode(['success'=>true]);
    }


}
