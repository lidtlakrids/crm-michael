@extends('layout.main')

@section('page-title',Lang::get('labels.create-client'))


@section('content')
 <div class="row">
    <div class="col-md-5">
        <div class="panel panel-grape">
            <div class="panel-heading">
                <h4><i class="fa fa-group"></i> @lang('labels.create-client')</h4>
            </div>
            <div class="panel-body">

                {!! Form::open(['method'=>'POST','action'=>['ClientsController@store'],'class'=>'form-horizontal']) !!}

                <div class="form-group">
                    {!! Form::label('CINumber',Lang::get('labels.ci-number'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::text('CINumber',null,['class'=>'form-control']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('SellerId',Lang::get('labels.seller'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-3">
                        {!! Form::select('SellerId',$users,['class'=>'form-control']) !!}
                    </div>
                </div>

                <div class="btn-toolbar">
                    {!! Form::submit('Create client',['class'=> 'btn btn-primary form-control']) !!}
                </div>
                {!! Form::close() !!}

            </div>
        </div>
    </div>
 </div>
@stop