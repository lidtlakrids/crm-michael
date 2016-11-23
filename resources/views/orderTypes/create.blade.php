@extends('layout.main')
@section('page-title',Lang::get('labels.create-order-type'))
@section('content')
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                   @lang('labels.create-order-type')
                </div>

                <div class="panel-body">
                    {!! Form::open(['method'=>'POST','action'=>['OrderTypesController@store'],'class'=>'form-horizontal']) !!}

                    <div class="form-group">
                        {!! Form::label('FormName','FormName',['class'=>'col-md-3 control-label','required'=>'required']) !!}
                        <div class="col-md-4">
                            {!! Form::text('FormName',null,['class'=>'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('Type_Id',Lang::get('labels.order-type'),['class'=>'col-md-3 control-label','required'=>'required']) !!}
                        <div class="col-md-4">
                            {!! Form::select('Type_Id',$types,null,['class'=>'form-control']) !!}
                        </div>
                    </div>


                    <div class="btn-toolbar">
                        {!! Form::submit(Lang::get('labels.create-order-type'),['class'=> 'btn btn-success form-control']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop