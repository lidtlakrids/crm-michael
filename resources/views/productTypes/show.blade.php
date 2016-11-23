@extends('layout.main')
@section('page-title',Lang::get('labels.product-type')." : ".$productType->Name)
@section('scripts')
    @include('scripts.dataTablesScripts')

    <script>
        $(document).ready(function() {

            $('body').on('click','.removeContractFieldLink',function (event) {
                var target = $(event.target);
                var typeId = target.data('type-id');
                var fieldId = target.data('field-id');
                $.ajax({
                    url: api_address + "ContractFields("+fieldId+")/RemoveFrom",
                    type: "POST",
                    data: JSON.stringify({"ProductType_Id": typeId}),
                    success: function () {
                        target.closest('.contractFieldLink').remove();
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });

            });


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

    </script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','ProductType',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $productType->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-4">
                <div class="panel panel-grape">
                    <div class="panel-heading">
                        <h4><i class="fa fa-file-text-o" aria-hidden="true"></i> {{$productType->Name}}</h4>
                        <div class="options">
                            {{--<a  href="{{url('ordertypes/addFields',$orderType->Id)}}" title="@lang('labels.add-field')"><i class="fa fa-plus"></i></a>--}}
                            {{--<a href="{{url('ordertypes/edit',$orderType->Id)}}" title="@lang('labels.edit')"><i class="fa fa-pencil"></i></a>--}}
                        </div>
                    </div>
                    <div class="panel-body">
                        {!!Form::hidden('ProductTypeId',$productType->Id,['id'=>'ProductType_Id'])!!}
                        <div class="table-responsive">
                            <table class="table table-condensed table-striped table-list">
                                <thead>
                                    <tr>
                                        <th> @lang('labels.display-name')</th>
                                        <th> @lang('labels.value-name')</th>
                                        <th> @lang('labels.description')</th>
                                        <th> @lang('labels.type')</th>
                                        <th> @lang('labels.options')</th>
                                        <th> @lang('labels.actions')</th>
                                    </tr>
                                </thead>
                                <tbody id="sort">
                                @foreach($fields as $field )
                                        <tr id="{{$field->Id}}">
                                            <td>{{$field->DisplayName or "--"}}</td>
                                            <td>{{$field->ValueName or "--"}}</td>
                                            <td>{{$field->Description or "--"}}</td>
                                            <td>{{$field->FieldType or "--"}}</td>
                                            <td>{{count($field->FieldOption)}}</td>
                                            <td>
                                                <i class="fa fa-times removeContractFieldLink" title="Remove from this contract type" data-type-id="{{$productType->Id}}" data-field-id="{{$field->Id}}"></i> /
                                                <a href="{{url('contract-fields/edit',$field->Id)}}"><i class="fa fa-pencil" title="@lang('labels.edit')"></i></a>
                                            </td>
                                        </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <a href="{{url('contract-types/add-fields',$productType->Id)}}" title="@lang('labels.add-field')" class="btn btn-inverse pull-right">ADD FIELD</a>
                    </div>{{-- End panel body --}}
                </div> {{-- End Panel --}}




            </div>
        </div>
    </div>
@stop