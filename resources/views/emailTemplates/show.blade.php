@extends('layout.main')
@section('page-title',Lang::get('labels.template')." : ".$template->Model)


@section('scripts')

    <script>
        $(document).ready(function () {
            $('#openTemplatePreview').click(function () {

                var templateId = $('#ModelId').val();
                var modalBody = $('.modal-body');
                modalBody.empty();
                $.ajax({
                    type: "GET",
                    url: api_address + 'Templates('+templateId+')/action.Example',
                    success: function (data) {
                        modalBody.append(data);
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
        });
    </script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Template',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $template->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-gray">
                <div class="panel-heading">
                    <h4>@lang('labels.template')</h4>
                    &nbsp;
                    <!-- Modal button -->
                    <a data-toggle="modal" id="openTemplatePreview" href="#previewTemplate" class="btn btn-sm btn-orange">Preview</a>

                    <div class="options">
                        <a href="{{url('emailTemplates/edit',$template->Id)}}"><i class="fa fa-pencil"></i></a>
                        <!-- MODAL START -->
                        <div class="modal fade" id="previewTemplate" tabindex="-1" role="dialog" aria-labelledby="ModalLabel"
                             aria-hidden="true">
                            <div class="modal-dialog" style="width: 768px;">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal"
                                                aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">Preview</h4>
                                    </div>
                                    <div class="modal-body">

                                    </div>
                                    <div class="modal-footer">
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->

                    </div>
                </div>
                <div class="panel-body">
                    <dl class="dl-horizontal">
                        <dt>@lang('labels.name')</dt>
                        <dd>{{$template->Model or "---"}}</dd>

        <dt>@lang('labels.description')</dt>
        <dd><pre>{{$template->TemplateData or "---"}} </pre></dd>
        </dl>
    </div>
    </div>
    </div>
    </div>
@stop