<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;

class OptimizeRulesController extends Controller
{
    public function index(){

        $cont = new RestController();
        $sizes = $cont->getEnumProperties(['PackageSize']);
        $sizes = $sizes['PackageSize'];
        $templates = TaskTemplatesController::templateList();
        JavaScriptFacade::put(['sizes'=>$sizes,'taskTemplates'=>$templates]);
        return view('optimizeRules.index');
    }

    public function create(){

        $taskTemplates = TaskTemplatesController::templateList();

        return view('optimizeRules.create',compact('taskTemplates'));
    }
}
