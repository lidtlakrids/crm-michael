@extends('layout.main')
@section('page-title',Lang::get('labels.create-type'))
@section('styles')
@stop

@section('scripts')
    <script>
        $('#createProductType').on('submit', function (event) {
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
                url: api_address + 'ProductTypes',
                data: JSON.stringify(formData),
                success: function (data) {
                    new PNotify({
                        title: Lang.get('labels.success'),
                        text: Lang.get('messages.update-was-successful'),
                        type: 'success'
                    });
                    window.location=base_url+'/productTypes/show/'+data.Id;
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
    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-primary">
              <div class="panel-heading">@lang('labels.create-type')</div>
              <div class="panel-body">
                  <div class="form-horizontal">
                      {!! Form::open(['id'=>'createProductType']) !!}

                      <div class="form-group">
                          {!! Form::label('Name',Lang::get('labels.name'),['class'=>'col-md-3 control-label']) !!}
                          <div class="col-md-3">
                              {!! Form::text('Name',null,['class'=>'form-control','required'=>'required']) !!}
                          </div>
                      </div>

                      <div class="btn-toolbar">
                          {!! Form::submit(Lang::get('labels.save'),['class'=> 'btn btn-primary form-control']) !!}
                      </div>
                      {!! Form::close() !!}

                  </div>
              </div>
            </div>
        </div>
    </div>

@stop