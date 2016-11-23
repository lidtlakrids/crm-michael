@extends('layout.main')
@section('page-title',Lang::get('labels.create-optimize-rule'))
@section('styles')
@stop

@section('scripts')

    <script>
        $(document).ready(function(){

            $('#createProduct').on('submit', function (event) {
                event.preventDefault();

                var formData = convertSerializedArrayToHash($(this).serializeArray());

                delete(formData['_token']);

                // sets null for all empty input
                for (var prop in formData) {
                    if (formData[prop] === "") {
                        delete(formData[prop]);
                    }
                }
                $.ajax({
                    type: "POST",
                    url: api_address + 'OptimizeRules',
                    data: JSON.stringify(formData),
                    success: function (data) {
                        new PNotify({
                            title: Lang.get('labels.success'),
                            text: Lang.get('messages.update-was-successful'),
                            type: 'success'
                        });
                    },
                    error: function (err) {
                        new PNotify({
                            title: Lang.get('labels.error'),
                            text: Lang.get(err.statusText),
                            type: 'error'
                        });
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            });

            $("#productSearch").autocomplete({
                source: function (request, response) {
                    var str = request.term;
                    $.get(api_address + "Products?$expand=OptimizeRule&$select=Name,Id&$filter=contains(tolower(Name),'" + str.toLowerCase() + "') and Active eq true", {},
                            function (data) {
                                response($.map(data.value, function (el) {
                                            return {id: el.Id, label: el.Name};
                                        })
                                );
                            });
                },
                minLength: 2,
                select: function (event, ui) {
                    setProductId(ui)
                }
            });

            function setProductId(data) {
                $('input[name=Product_Id]').val(data.item.id);
            }
        });
    </script>

@stop

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h4 class="panel-title">@lang('labels.create-optimize-rule')</h4>
            </div>
            <div class="panel-body">
                <div class="form-horizontal">
                    {!! Form::open(['id'=>'createProduct'])!!}

                    <div class="form-group">
                        {!! Form::label('optimizeRule-OptimizeInterval',Lang::get('labels.optimize-interval'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::number('OptimizeInterval',null,['class'=>'form-control','id'=>'optimizeRule-OptimizeInterval','required'=>'required','min'=>0]) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class="fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="productSearch" class="col-md-3 control-label">@lang('labels.product')</label>
                        <div class="col-sm-6">
                            <input id="productSearch" class="form-control" placeholder="Search product...">
                            <input type="hidden" name="Product_Id">
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class=" fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        {!! Form::label('product-TaskList_Id',Lang::get('labels.task-template'),['class'=>'col-md-3 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::select('TaskList_Id',withEmpty($taskTemplates),null,['class'=>'form-control','id'=>'optimizeRule-TaskList_Id']) !!}
                        </div>
                        <div class="col-sm-3">
                            <p class="help-block"><i class="fa fa-info-circle" title="Help Text!"></i></p>
                        </div>
                    </div>

                    <div class="btn-toolbar">
                        {!! Form::submit(strtoupper(Lang::get('labels.save')),['class'=> 'btn btn-orange btn-label form-control']) !!}
                    </div>
                    {!! Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>

@stop