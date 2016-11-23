@extends('layout.main')
@section('page-title',Lang::get('labels.order-type').": ".$orderType->FormName)
@section('scripts')
    @include('scripts.dataTablesScripts')

    <script>
    $(document).ready(function() {
    $("#sort").sortable({ helper: fixHelper, opacity: 0.8, cursor: 'move', update: function() {
        var order = $(this).sortable("toArray");
        //update each field sort order
        var sortOrder = 1;
        order.forEach(function(linkId){
            $.ajax({
                type     : "PATCH",
                url      : api_address+'OrderTypeOrderFields('+linkId+')',
                data     : JSON.stringify({SortOrder:sortOrder}),
                success  : function() {
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
            sortOrder++;
        });
    }});

    $('body').on('click','.deleteField',function(event){
        var tr = $(event.target).closest('tr');
        var fieldId = tr.attr('id');
        bootbox.confirm("Are you sure?", function(result)
        {
            if(result) {
                $.ajax({
                    type: "DELETE",
                    url: api_address + 'OrderTypeOrderFields(' + fieldId + ')',
                    success: function () {
                        tr.remove();
                    },
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
    {!! Form::hidden('Model','OrderType',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $orderType->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-file-text-o" aria-hidden="true"></i> {{$orderType->FormName}}</h4>
                    <div class="options">
                        <a  href="{{url('ordertypes/addFields',$orderType->Id)}}" title="@lang('labels.add-field')"><i class="fa fa-plus"></i></a>
                        <a href="{{url('ordertypes/edit',$orderType->Id)}}" title="@lang('labels.edit')"><i class="fa fa-pencil"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    {!!Form::hidden('OrderTypeId',$orderType->Id,['id'=>'OrderTypeId'])!!}
                    <div class="table-responsive">
                        <table class="table table-condensed table-striped table-list">
                            <thead>
                                <tr>
                                    <th> @lang('labels.display-name')</th>
                                    <th> @lang('labels.value-name')</th>
                                    <th> @lang('labels.description')</th>
                                    <th> @lang('labels.type')</th>
                                    <th> @lang('labels.options')</th>
                                    <th> @lang('labels.required')</th>
                                    <th> @lang('labels.actions')</th>
                                </tr>
                            </thead>
                            <tbody id="sort">
                            @foreach($orderType->OrderTypeOrderField as $field )
                                @if($field->OrderField != null)
                                    <tr id="{{$field->Id}}" @unless($field->OrderField->Active) style="background-color: lightcoral;" title="@lang('messages.field-is-not-active')" @endunless>
                                        <td>{{$field->OrderField->DisplayName or "--"}}</td>
                                        <td>{{$field->OrderField->ValueName or "--"}}</td>
                                        <td>{{$field->OrderField->Description or "--"}}</td>
                                        <td>{{$field->OrderField->OrderFieldType or "--"}}</td>
                                        <td>{{count($field->OrderField->OrderFieldOption)}}</td>
                                        <td>@if($field->OrderField->Required) <i class="fa fa-check"></i> @else <i class="fa fa-times"></i> @endif</td>
                                        <td>
                                            <span class="pseudolink deleteField"><i class="fa fa-times" title="@lang('labels.delete')"></i></span> /
                                            <a href="{{url('order-fields/edit',$field->OrderField->Id)}}"><i class="fa fa-pencil" title="@lang('labels.edit')"></i></a>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <a href="{{url('ordertypes/addFields',$orderType->Id)}}" title="@lang('labels.add-field')" class="btn btn-inverse pull-right">ADD FIELD</a>
                </div>{{-- End panel body --}}
            </div> {{-- End Panel --}}
        </div>
    </div>
@stop