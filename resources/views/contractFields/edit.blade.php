@extends('layout.main')
@section('page-title',Lang::get('labels.edit-field')." : ".$field->DisplayName)

@section('scripts')
    <script>
        $(document).ready(function() {

            var form = $('#editContractField');

            // turn the form into hash map
            var startItems = convertSerializedArrayToHash(form.serializeArray());
            $(form).on('submit', function (event) {
                event.preventDefault();

                var productId = $('#ModelId').val();

                // find eventual changes
                var currentItems = convertSerializedArrayToHash(form.serializeArray());

                var itemsToSubmit = hashDiff( startItems, currentItems);

                // sets null for all empty input
                for (var prop in itemsToSubmit) {
                    if (itemsToSubmit[prop] === "") {
                        itemsToSubmit[prop] = null;
                    }
                }
                //best one-liner ever to exist.
                itemsToSubmit.Active = $('#contractField-Active').prop('checked');
                itemsToSubmit.Required = $('#contractField-Required').prop('checked');
                itemsToSubmit.Special = $('#contractField-Special').prop('checked');

                //send request only if something changed
                if(!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + 'ContractFields('+productId+')',
                        data: JSON.stringify(itemsToSubmit),
                        success: function (data) {
                            new PNotify({
                                title: Lang.get('labels.success'),
                                text: Lang.get('messages.update-was-successful'),
                                type: 'success'
                            });

                            window.location = '{{URL::previous()}}';
                        },
                        error: function (err) {
                            new PNotify({
                                title: Lang.get('labels.error'),
                                text: Lang.get(err.responseJSON.error.innererror.message),
                                type: 'error'
                            });
                        },
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        }
                    });
                }
            });


            $('#addOptionToField').on('submit', function (event) {
                event.preventDefault();
                var form = $(this);
                var formData =form.serializeJSON();
                delete(formData._token);
                //send request only if something changed
                $.ajax({
                    type: "POST",
                    url: api_address + 'ContractFieldOptions',
                    data: JSON.stringify(formData),
                    success: function (data) {
                        form[0].reset();// reset the form
                        // add it to the table
                        // find the tbody
                        var tbody = $('.sort');

                        // todo move to template.. maybe
                        tbody.append("<tr id='"+data.Id+"'>" +
                                "<td>"+data.DisplayName+"</td>" +
                                "<td>"+data.Value+"</td>" +
                                "<td class='sortOrder' style='position:relative;'>"+data.SortOrder+"</td>" +
                                "<td><a href='"+base_url+"/order-field-options/edit/"+data.Id+"'><i class='fa fa-pencil'></i></a> / <a><i class='fa fa-times'></i></a></td>" +
                                "</tr>")
                    },
                    error: function (err) {
                        new PNotify({
                            title: Lang.get('labels.error'),
                            text: Lang.get(err.responseJSON.error.innererror.message),
                            type: 'error'
                        });
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });


            $('body').on('click','.deleteOrderFieldOption',function(event){
                var tr = $(event.target).closest('tr');
                var optionId = tr.attr('id');
                bootbox.confirm("Are you sure?", function(result)
                {
                    if(result) {
                        $.ajax({
                            type: "DELETE",
                            url: api_address + 'OrderFieldOptions(' + optionId + ')',
                            success: function () {
                                tr.remove();
                            },
                            error: handleError,
                            beforeSend: function (request) {
                                request.setRequestHeader("Content-Type", "application/json");
                            }
                        });
                    }
                });
            });

        });

    </script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','OrderField',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $field->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-grape">
            <div class="panel-heading">
                <h4><i class="fa fa-gears"></i> @lang('labels.edit-field')</h4>
            </div>
            <div class="panel-body">
                <div class="form-horizontal">
                    {!! Form::open(['id'=>'editContractField']) !!}
                    <div class="form-group">
                        {!! Form::label('contractField-DisplayName',Lang::get('labels.display-name'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('DisplayName',$field->DisplayName,['class'=>'form-control','required'=>'required','id'=>'contractField-DisplayName']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('contractField-ValueName',Lang::get('labels.value'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('ValueName',$field->ValueName,['class'=>'form-control','title'=>Lang::get('messages.order-field-value'),'required'=>'required','id'=>'contractField-ValueName']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('contractField-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::textarea('Description',$field->Description,['class'=>'form-control','required'=>'required','id'=>'contractField-Description']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('contractField-Active',Lang::get('labels.active'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::checkbox('Active',null,$field->Active,['class'=>'form-control','contractField-Active','id'=>'contractField-Active']) !!}
                        </div>
                    </div>


                    <div class="form-group">
                        {!! Form::label('contractField-FieldType',Lang::get('labels.type'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::select('FieldType',$fieldTypes,$field->FieldType,['class'=>'form-control','id'=>'contractField-FieldType']) !!}
                        </div>
                    </div>

                    <div class="btn-toolbar">
                        {!! Form::submit(Lang::get('labels.update'),['class'=> 'btn btn-primary form-control']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    @if(in_array($field->FieldType,['Select','Radio','CheckBox','CampaignGoal']))
        <div class="col-md-6">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <i class="fa fa-gears"></i> @lang('labels.options')
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'addOptionToField']) !!}

                        {!! Form::hidden("ContractField_Id",$field->Id) !!}

                        <div class="form-group">
                            {!! Form::label('DisplayName',Lang::get('labels.display-name'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('DisplayName',null,['class'=>'form-control','required'=>'required','id'=>'DisplayName']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            {!! Form::label('Value',Lang::get('labels.value'),['class'=>'col-md-3 control-label']) !!}
                            <div class="col-md-6">
                                {!! Form::text('Value',null,['class'=>'form-control','required'=>'required','id'=>'Value']) !!}
                            </div>
                        </div>

                        <div class="btn-toolbar">
                            {!! Form::submit(Lang::get('labels.save'),['class'=> 'btn btn-primary form-control']) !!}
                        </div>
                        {!! Form::close() !!}
                    </div>
                    <hr>
                    <table id="field-options-table" class="table table-condensed">
                        <thead>
                        <tr>
                            <th>@lang('labels.display-name')</th>
                            <th>@lang('labels.value')</th>
                            <th>@lang('labels.sort-order')</th>
                            <th>@lang('labels.options')</th>
                        </tr>
                        </thead>
                        <tbody class="sort">
                            @foreach($field->FieldOption as $option)
                                <tr id="{{$option->Id}}">
                                    <td>
                                        {{$option->DisplayName}}
                                    </td>
                                    <td>
                                        {{$option->Value}}
                                    </td>
                                    <td class="sortOrder" style="position: relative;">
                                        {{$option->SortOrder}}
                                    </td>
                                    <td>
                                        <a href="{{url('order-field-options/edit',$option->Id)}}"><i class="fa fa-pencil"></i></a> /
                                        <span title="@lang('labels.delete')" class="pseudolink"><i class="fa fa-times deleteOrderFieldOption"></i></span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                </div>
            </div>
        </div>
    @endif
</div>
@stop