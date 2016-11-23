<?php

namespace App\Http\Controllers;
define('APACHE_MIME_TYPES_URL','http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types');
use ForceUTF8\Encoding;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class FileStorageController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ini_set('memory_limit','128M');
        $data = Input::all();

        $file = $data['file'];

        $cont = new RestController();

        $result = $cont->postRequest('FileStorages',$file);

        return Response::make("penis", 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }


    public function bulkDownload(){
        File::cleanDirectory(storage_path().'/app/temp');
        $data = Input::only('file');

        if(isset($data['file'])){
            $data = $data['file'];
        }else{
            return null;
        }
        $cont= new RestController();
        ini_set('memory_limit','-1');
        $zipname = 'files'.time().'.zip';
        $zip = new ZipArchive;
        $zip->open(storage_path().'/app/'.$zipname, ZipArchive::CREATE);
        $cont = new RestController();
        foreach ($data as $id){
            $file = $cont->getRequest("FileStorages($id)");
            if(!$file instanceof View){
                $content = $cont->getRequest("FileStorages($id)/GetFile");
                $result = Storage::put('temp/'.$file->Name, $content);;
                $f = Storage::get('temp/'.$file->Name);
                $res = $zip->addFile(storage_path().'/app/temp/'.$file->Name,$file->Name);
            }
        }
        $zip->close();
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$zipname);
        header('Content-Length: ' . filesize(storage_path().'/app/'.$zipname));
        readfile(storage_path().'/app/'.$zipname);
        ignore_user_abort(true);
        unlink(storage_path().'/app/'.$zipname);
//        Storage::put($zipname,$zip);
//        $resp =   response()->download(storage_path().'/app/temp/'.$zipname)->deleteFileAfterSend(true);
//        File::cleanDirectory(storage_path().'/app/temp');
//        return  $resp;
    }

    /**
     * @param $fileId
     * @return $this|null
     */
    public function download($fileId){

        ini_set('memory_limit','-1');
        $cont = new RestController();
        $file = $cont->getRequest("FileStorages($fileId)");
        if($file instanceof View){
            return Response::make(Lang::get('labels.error'), 400);
        }
        $content = $cont->getRequest("FileStorages($fileId)/GetFile");
        $result = Storage::put($file->Name, $content);;
        $f = Storage::get($file->Name);
        $resp =   response()->download(storage_path().'/app/'.$file->Name)->deleteFileAfterSend(true);
        return  $resp;

//        $curl = $cont->initCurl();
//        $curl->setHeader('Authorization', 'Bearer '.$cont->sessionToken());
//        return response()->download($curl->download("FileStorages($fileId)/action.GetFile",$file->Name));
//        return $cont->downloadRequest("FileStorages($fileId)/GetFile",$file->Name);
    }


    /**
     * @param $fileId
     * @return $this|null
     */
    public function preview($fileId){
        ini_set('memory_limit','-1');
        $cont = new RestController();

        $file = $cont->getRequest("FileStorages($fileId)");
        if($file instanceof View){
            return Response::make(Lang::get('labels.error'), 400);
        }
        $ext = pathinfo($file->Name, PATHINFO_EXTENSION);
        $f= $cont->getRequest("FileStorages($fileId)/action.GetFile");

        if($ext == "pdf"){
            $headers = array(
                'Content-type' => 'application/pdf',
            );
        }elseif(in_array(strtolower($ext),['txt','csv'])){
            $headers = ['Content-type'=>'text/plain; charset=utf-8'];
            return self::download($fileId);
        }elseif(in_array(strtolower($ext),['doc','docx','xls','odt'])){
            {
                return self::download($fileId);
            }
        }elseif(in_array(strtolower($ext),['png','jpg','gif','tif','jpeg']))
        {
            $headers = ['Content-type'=>'image/'.$ext];
        }else{
            $headers = ['Content-type'=>'application/octet-stream'];
        }
        return Response::make($f, 200,$headers);

    }


    /**
     * @param $url
     * @return bool|string
     */
    function getMimeTypes($url){
        $s=array();
        foreach(@explode("\n",@file_get_contents($url))as $x):
            if(isset($x[0])&&$x[0]!=='#'&&preg_match_all('#([^\s]+)#',$x,$out)&&isset($out[1])&&($c=count($out[1]))>1):
                $s[$out[0][1]]=$out[0][0];
            endif;
        endforeach;
        return $s;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
