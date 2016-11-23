<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CountriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('countries.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('countries.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $cont = new RestController();
        $country = $cont->getRequest("Countries($id)");
        if($country instanceof RedirectResponse)
        {
            return $country;
        }
        return view('countries.show',compact('country'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $cont = new RestController();
        $country = $cont->getRequest("Countries($id)");
        if($country instanceof RedirectResponse)
        {
            return $country;
        }
        return view('countries.edit',compact('country'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
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
     * returns list of countries for selects and etc
     *
     * @return array|null
     */
    public static function countriesList(){

        $cont = new RestController();
        $countries = array();

        $countriesRaw = $cont->getRequest('Countries');

        if($countriesRaw instanceof View){
            return $countries;
        }
        foreach($countriesRaw->value as $c)
        {
            $countries[$c->Id]=$c->CountryCode;
        }
        return $countries;
    }


}
