@extends('layout.main')
@section('page-title',Lang::get('labels.partner')." : ".$partner->Name)

@section('styles')
    {!! Html::style(asset('css/jquery.datetimepicker.css')) !!}
    {!! Html::style(asset('css/dropzone.min.css')) !!}
@stop



@section('scripts')
    {!! Html::script(asset('js/lib/jquery.datetimepicker.js')) !!}
    {!! Html::script(asset('js/lib/dropzone.min.js/')) !!}
@include('scripts.dataTablesScripts')
<script>
    $(document).ready(function () {
        $("#ClientAliasSearch").autocomplete({
            source: function (request, response) {
                var str = request.term;
                $.get(api_address + "ClientAlias?$filter=contains(tolower(Name),'" + str + "') and Partner_Id eq null", {},
                    function (data) {
                        response($.map(data.value, function (el) {
                                return {id: el.Id, label: el.Name+' - '+el.Homepage};
                            })
                        );
                    });
            },
            minLength: 3,
            select: function (event, ui) {
                addClientToPartner(ui)
            }
        });
        function addClientToPartner(data) {
            console.log(data);
        }
});
</script>
@stop
@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Partner',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $partner->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-client">
            <div class="panel-heading">
                <h4><i class="fa fa-briefcase"> </i> @lang('labels.partner')</h4>
                <div class="options">
                    @if(isAllowed('partners','patch'))
                        <a href="{{url('partners/edit',$partner->Id)}}" title="@lang('labels.edit-partner')">
                            <i class="fa fa-edit"></i>
                        </a>
                    @endif
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                        <dl class="dl-horizontal-row-2">
                            <dt>@lang('labels.name')</dt>
                            <dd>{{$partner->Name or ""}}</dd>
                            <dt>@lang('labels.web')</dt>
                            <dd><a href="{{$partner->Homepage or ""}}" target="_blank">{{$partner->Homepage or ""}}</a></dd>
                            <dt>@lang('labels.phone')</dt>
                            <dd>
                                @if(Auth::user()->localNumber != null)
                                    <span class="pseudolink flexfoneCallOut">{{$partner->PhoneNumber  or ''}}</span>
                                @else
                                    <a href="tel:{{$partner->PhoneNumber or ""}}">{{$partner->PhoneNumber or ""}}</a>
                                @endif
                            </dd>
                            <dt>@lang('labels.email')</dt>
                            <dd><a href="mailto:{{$partner->EMail}}">{{$partner->EMail or ""}}</a></dd>

                            <dt>@lang('labels.address')</dt>
                            <dd>{{$partner->Address or ''}} {{ $partner->zip or '' }} {{$partner->City or ''}} {{$partner->Country->CountryCode or ""}}</dd>

                        </dl>
                    </div>
                </div><!-- end row 1 -->
            </div>
        </div>
    </div>
</div>
</div> {{--end row--}}

@stop