@extends('layout.main')
@section('page-title',Lang::get('labels.edit-field')." : ".$field->DisplayName)

@section('scripts')
    <script>
        $(document).ready(function() {

            var form = $('#editOrderField');

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
                itemsToSubmit.Active = $('#orderField-Active').prop('checked');
                itemsToSubmit.Required = $('#orderField-Required').prop('checked');
                itemsToSubmit.Special = $('#orderField-Special').prop('checked');

                //send request only if something changed
                if(!$.isEmptyObject(itemsToSubmit)) {
                    $.ajax({
                        type: "PATCH",
                        url: api_address + 'OrderFields('+productId+')',
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
                    url: api_address + 'OrderFieldOptions',
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

            function slideout() {
                setTimeout(function() {
                    $("div#response").slideUp("slow", function () {
                        $("div#response").hide();
                        $("div#response").html('');
                        $("div#response").removeAttr('style');
                    });
                }, 2000);
            }

            $.ajaxSetup({
                error: function(jqXHR, exception) {
                    $("div#response").css('background-color',  'red');
                    if (jqXHR.status === 0) {
                        $("div#response").html('Not connect.\n Verify Network.');
                    } else if (jqXHR.status == 404) {
                        $("div#response").html('Requested page not found. [404]');
                    } else if (jqXHR.status == 500) {
                        $("div#response").html('Internal Server Error [500].');
                    } else if (exception === 'parsererror') {
                        $("div#response").html('Requested JSON parse failed.');
                    } else if (exception === 'timeout') {
                        $("div#response").html('Time out error.');
                    } else if (exception === 'abort') {
                        $("div#response").html('Ajax request aborted.');
                    } else {
                        $("div#response").html('Uncaught Error.\n' + jqXHR.responseText);
                    }
                }
            });

            $(".sort").sortable({ helper: fixHelper, opacity: 0.8, cursor: 'move', update: function() {
                $('.sortOrder').addClass('spinner');
                var order = $(this).sortable("toArray");
                //update each field sort order
                var sortOrder = 1;
                order.forEach(function(id){
                    $.when(
                        $.ajax({
                            type     : "PATCH",
                            url      : api_address+'OrderFieldOptions('+id+')',
                            data     : JSON.stringify({SortOrder:sortOrder}),
                            success  : function() {
                            },
                            error:handleError,
                            beforeSend: function (request) {
                                request.setRequestHeader("Content-Type", "application/json");
                            }
                        })
                    ).then(function(){
                        // sorting cell
                        var cell = $("tr[id="+id+']').find('.sortOrder');
                        $.get(api_address+'OrderFieldOptions('+id+')?$select=SortOrder').success(function(data){
                            cell.text(data.SortOrder);
                            cell.removeClass('spinner');
                        })
                    });
                    sortOrder++;
                });
            }});

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
        // Return a helper with preserved width of cells
        var fixHelper = function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        };
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
                    {!! Form::open(['id'=>'editOrderField']) !!}
                    <div class="form-group">
                        {!! Form::label('orderField-DisplayName',Lang::get('labels.display-name'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('DisplayName',$field->DisplayName,['class'=>'form-control','required'=>'required','id'=>'orderField-DisplayName']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('orderField-ValueName',Lang::get('labels.value'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('ValueName',$field->ValueName,['class'=>'form-control','title'=>Lang::get('messages.order-field-value'),'required'=>'required','id'=>'orderField-ValueName']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('orderField-Description',Lang::get('labels.description'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::textarea('Description',$field->Description,['class'=>'form-control','required'=>'required','id'=>'orderField-Description']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('orderField-Active',Lang::get('labels.active'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::checkbox('Active',null,$field->Active,['class'=>'form-control','orderField-Active','id'=>'orderField-Active']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('orderField-Required',Lang::get('labels.required'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::checkbox('Required',null,$field->Required,['class'=>'form-control','orderField-Required','id'=>'orderField-Required']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('orderField-Special',Lang::get('labels.special'),['class'=>'col-md-3 control-label','title'=>Lang::get('messages.field-is-special')]) !!}
                        <div class="col-md-6">
                            {!! Form::checkbox('Special',null,$field->Special,['class'=>'form-control','title'=>Lang::get('messages.field-is-special'),'id'=>'orderField-Special']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('orderField-OrderFieldType',Lang::get('labels.type'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::select('OrderFieldType',$fieldTypes,findEnumNumber($fieldTypes,$field->OrderFieldType),['class'=>'form-control','id'=>'orderField-OrderFieldType']) !!}
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
    @if(in_array($field->OrderFieldType,['Select','Radio','CheckBox','CampaignGoal']))
        <div class="col-md-6">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <i class="fa fa-gears"></i> @lang('labels.options')
                </div>
                <div class="panel-body">
                    <div class="form-horizontal">
                        {!! Form::open(['id'=>'addOptionToField']) !!}

                        {!! Form::hidden("OrderField_Id",$field->Id) !!}

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
                            @foreach($field->OrderFieldOption as $option)
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