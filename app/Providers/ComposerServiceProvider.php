<?php namespace App\Providers;

use App\Http\Controllers\RestController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;

class ComposerServiceProvider extends ServiceProvider {

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {

        $this->app['view']->composer('layout.breadcrumbs',function($view)
        {
            JavaScriptFacade::put(['roles'=>Session::get('roles'),'locale'=>App::getLocale()]);

            $segments = Request::segments();
            $result=[];
            foreach($segments as $k => $v){
                $result[$v] = implode("/", array_slice($segments,0,($k+1)));
            }
            $count = count($result);
            $view->with('result',$result)->with('count',$count);

        });

//        $this->app['view']->composer('timeRegistrations.buttons',function($view)
//        {
//            // put the roles in javascript + the locale
////            if (Session::has('status') && session('status')!='error')
////            {
////                $status = session('status');
////            }
////            else
////            {
//                $cont = new RestController();
//                $status = $cont->getRequest('TimeRegistrations/action.CurrentStatus');
//                if($status instanceof View)
//                {
//                    $status = "error";
//                }
//                else
//                {
//                    $status = $status->Status;
//                    Session::put('status',$status);
//                }
////            }
////
////            $status = 'error';
//            $view->with('status',$status);
//        });
    }

    /**
     * Register
     *
     * @return void
     */
    public function register()
    {
        //
    }

}