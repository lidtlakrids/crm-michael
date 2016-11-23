<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\View\View;

class ClientLoginsController extends Controller
{

    public function index(){

    }

    public function store(Request $request){

        $data = Input::all();
        $data['EncryptionKey'] = config('gcm.client-password-encryption-key');
        unset($data['_token']);
        $cont = new RestController();

        $result = $cont->postRequest('ClientLogins',$data);

        if($result instanceof View){
            return Response::make('Error saving the client login',500);
        }else{
            return response()->json($result);
        }
    }

    public function decryptPassword($clientLoginId){
        $decryptionKey = config('gcm.client-password-encryption-key');

        $cont = new RestController();

        $result = $cont->postRequest("ClientLogins($clientLoginId)/Decrypt",['EncryptionKey'=>$decryptionKey]);
        if($result instanceof View){
            return Response::make('Error decrypting the password',500);
        }else{
            return response()->json($result);
        }


    }
}
