<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\AclController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\RestController;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Registration & Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users, as well as the
	| authentication of existing users. By default, this controller uses
	| a simple trait to add these behaviors. Why don't you explore it?
	|
	*/

    protected $loginPath='auth/login';
	use AuthenticatesUsers;

    /**
     * Create a new authentication controller instance.
     *
     * @internal param Guard $auth
     * @internal param Registrar $registrar
     */
	public function __construct()
	{
//		$this->middleware('guest', ['except' => 'getLogout']);
	}

	public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required', 'password' => 'required',
        ]);
        $credentials = $request->only('username', 'password');
        $cont = new RestController();
        $token = $cont->getToken($credentials);

        if($token->error == true)
        {
            if($token->httpError==true)
            {
                if(isset($token->response->error_description)){
                    $error = $token->response->error_description;
                }
                else{
                    $error  = $token->httpErrorMessage.' : '.$token->url;
                }
            }
            elseif($token->curlError==true)
            {
                $error= $token->curlErrorMessage;
            }

            return redirect('auth/login')
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'username' => $error,
                ]);
        }
        
        //if user does not exist, save it in the local database and log it in
        $user = User::where('userName', 'like', $credentials['username'])->first();

        if (!isset($user['id'])){

            $cont = new RestController();
            $user = new User();
            $userInfo = $cont->getRequest('Users/action.UserInfo');
            if($userInfo instanceof View){
                return redirect('auth/login')
                    ->withInput($request->only('username', 'remember'))
                    ->withErrors([
                        'username' => 'Permissions denied',
                    ]);
            }
            $user->userName = $userInfo->UserName;
            $user->email = $userInfo->Email;
            $user->externalId= $userInfo->Id;
            $user->fullName= $userInfo->FullName;
            $user->localNumber = $userInfo->LocalNumber;
            $user->save();
            $userId = $userInfo->Id;

            // set user permissions
            $acl =  AclController::setSessionPermissions($userId);
            if($acl instanceof View){
                return view('auth.login')->withErrors('Can\'t  get permissions');
            }
            Auth::login($user);
            return redirect('/');
        }else{
            $userId = $user['externalId'];
            // set user permissions

            $acl =  AclController::setSessionPermissions($userId);
            if($acl instanceof View){
                return view('auth.login')->withErrors('Can\'t  get permissions');
            }
        }
        //login the user
        Auth::login($user);
        if(Input::get('returnUrl') != ''){
            $url = parse_url(Input::get('returnUrl'));
            //redirect only to the crm address
            $baseUrl = parse_url(url('/'));
            if(isset($url['host'])){
            $returnUrl = $url['host'] == $baseUrl['host'] ? $url['path'] : "/";
            }else{
                $returnUrl='/';
            }
            return redirect($returnUrl);
        }

        return redirect('/');
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        Session::forget('Bearer');
        Session::forget('status');
        Session::forget('acl');

        $removeAuthCookie= Cookie::forget('auth');
        $removeStatusCookie= Cookie::forget('timeReg');
        Auth::logout();
        $returnUrl = URL::previous();
        return redirect('auth/login?return_url='.$returnUrl)->withCookie($removeAuthCookie)->withCookie($removeStatusCookie);
    }

    public function showLoginForm(){
        $returnUrl = Input::get('return_url');
        return view('auth.login',compact('returnUrl'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data)
    {
        return Validator::make($data, [
            'username'=>'required|min:3|unique:users',
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'is_active' => '1',
            'password' => bcrypt($data['password']),
        ]);
    }
}
