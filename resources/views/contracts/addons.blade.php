@extends('layout.main')
@section('page-title','Select Addons : '.$contract->Id)

@section('styles')
@stop

@section('scripts')

    <script>
        $(document).ready(function () {

            $('.ContractAddons').on('change', function(evt) {
                var limit = contract.ProductPackage.AddonCount;
                if($('.ContractAddons:checked').length > limit) {
                    this.checked = false;
                    new PNotify({
                        title: 'Max number of Addons is ' + limit,
                        'type': 'error'
                    });
                }
            });

            $('#contractAddonsForm').on('submit',function (event) {
                event.preventDefault();
                var form = $(this);
                var data = form.serializeJSON();
                var btn =  form.find(':submit');
                if(data.Addons){
                    btn.prop('disabled',true);
                    $.ajax({
                        url: api_address + "Contracts("+getModelId()+')/SelectAddons',
                        type: "POST",
                        data:JSON.stringify(data),
                        success: function () {
                            new PNotify({
                                title: "Addons were saved. Redirecting"
                            });
                            window.location = base_url+'/contracts/show/'+getModelId();
                        },
                        error: function (err) {
                            btn.prop('disabled',false);
                        },
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        }
                    });
                }else{
                    new PNotify({
                        title:"Select addons"
                    })
                }

            })
        })
    </script>

@stop

@section('content')
    {!! Form::hidden('Model','Contract',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $contract->Id,['id'=>'ModelId']) !!}
<div class="panel panel-contract">
    <div class="panel-heading">
        <h4>Select addons</h4>
    </div>

    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <h4>Choose up to {{$contract->ProductPackage->AddonCount}} Addons</h4>
                <div class="form-horizontal">
                    <form id='contractAddonsForm'>
                        @foreach($contract->ProductPackage->Products as $addon)
                            <div class="form-group">
                                <div class="checkbox block">
                                    <label>
                                        <input name="Addons[]" value="{{$addon->Product_Id}}"
                                               class="ContractAddons" type="checkbox"
                                               @if(in_array($addon->Product_Id,$addons)) disabled="disabled" checked="checked" @endif>
                                        {{$addon->Product->Name}}  @if(in_array($addon->Product_Id,$addons)) <span style="color:red">Already existing</span> @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach
                        <div class="btn-toolbar">
                            <button  class="btn btn-green" type="submit">Save Addons</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                @if(isset($contract->InformationScheme))
                <h4>Contract information</h4>
                <div class="table-responsive">
                    <table class="table table-condensed table-bordered" style="width: 100%">
                        <tbody>
                        @foreach($contract->InformationScheme as $value)
                            <tr>
                                <td style="width: 33%">{{$value['DisplayName']}}: </td>
                                <td >

                                    @if(isset($value['Type']) && $value['Type'] == "CampaignGoal")
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-4"></div>
                                                <div class="col-md-4"><strong>Current</strong></div>
                                                <div class="col-md-4"><strong>Expected</strong></div>
                                            </div>
                                            @if($value['value'] != null)
                                                @foreach($value['value'] as $item=>$values)
                                                    <div class="row">
                                                        <div class="col-md-4"><b>{{$item}}</b></div>
                                                        <div class="col-md-4">{{$values->current}}</div>
                                                        <div class="col-md-4">{{$values->expected}}</div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @else
                                        <span class="multiline">{{ ($value['value']) }}</span>
                                    @endif

                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop