@extends('layout.main')
@section('page-title','Create Field')
@section('content')

    <div class="col-md-6">
        <div class="panel panel-grape">
            <div class="panel-heading">
                <h4><i class="fa fa-gears"></i> @lang('labels.create-order-field')</h4>
            </div>
            <div class="panel-body">
                {!! Form::open(['method'=>'POST','action'=>['ContractFieldsController@store'],'class'=>'form-horizontal']) !!}
                <div class="form-group">
                    {!! Form::label('DisplayName',Lang::get('labels.display-name'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-4">
                        {!! Form::text('DisplayName',null,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('ValueName',Lang::get('labels.value'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-4">
                        {!! Form::text('ValueName',null,['class'=>'form-control','title'=>Lang::get('messages.order-field-value'),'required'=>'required']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-4">
                        {!! Form::textarea('Description',null,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                </div>


                <div class="form-group">
                    {!! Form::label('Active',Lang::get('labels.active'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-4">
                        {!! Form::checkbox('Active',true,true,['class'=>'form-control']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldType',Lang::get('labels.type'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-4">
                        {!! Form::select('FieldType',$fieldTypes,null,['class'=>'form-control']) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('OrderIds[]',Lang::get('labels.order-type'),['class'=>'col-md-3 control-label']) !!}
                    <div class="col-md-4">
                        {!! Form::select('OrderIds[]',$types,null,['class'=>'form-control','multiple'=>'multiple','style'=>'height:150px']) !!}
                    </div>
                </div>
                <div class="btn-toolbar">
                    {!! Form::submit(Lang::get('labels.save'),['class'=> 'btn btn-primary form-control']) !!}
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop