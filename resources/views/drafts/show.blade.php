@extends('layout.main')
@section('page-title',Lang::get('labels.draft')." : ".$draft->Id)

@section('styles')
    {!! Html::style('css/dataTables.css') !!}
    {!! Html::style('css/dataTables.editor.css') !!}
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/tabletools/2.2.4/css/dataTables.tableTools.css">
@stop

@section('scripts')
<script type="text/javascript" charset="utf-8" src="//cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/tabletools/2.2.4/js/dataTables.tableTools.min.js"></script>
<script type="text/javascript" charset="utf-8" src="{{asset('/js/lib/dataTables.editor.min.js')}}"></script>

<script>
    $(document).ready(function (){

    $("#productSearch").autocomplete({
        source: function (request, response) {
            var str = request.term;
            $.get(api_address + "Products?$filter=indexof(tolower(Name), '" + str + "') ge 0 and Active eq true", {},
                    function (data) {
                        response($.map(data.value, function (el) {
                            return {id: el.Id, label: el.Name + "  " + el.SalePrice + " kr.",data:el};
                        }));
                    });
        },
        minLength: 2,
        select: function (event, ui) {
            addProductToDraft(ui.item.data)
        }
    });
        var model = $('#Model').val();
        var modelId = $('#ModelId').val();

        var editor = new $.fn.dataTable.Editor({
            'ajax': {
                type: "POST",
                url: '/drafts/updateDraftLine'
            },
            "table": "#ddd",
            i18n: {},
            "fields": [
                {
                    "label": Lang.get('labels.description'),
                    "name": "Description",
                    "type": "textarea"
                },
                {
                    "label": Lang.get('labels.quantity'),
                    "name": "Quantity"
                },
                {
                    "label": Lang.get('labels.unit-price'),
                    "name": "UnitPrice"
                },
                {"label": Lang.get('labels.discount'),
                    "name":"Discount"
                },
                {
                    "label": Lang.get('labels.net-amount'),
                    "name": "NetAmount"
                },
                {
                    "label": Lang.get('labels.seller'),
                    "name": "User",
                    type: "select",
                    ipOpts: <?php echo json_encode($userSelect) ?>
                }
            ]
        });
        editor.on('preSubmit', function (e, o, action) {
            if (action !== 'remove') {
                if (o.data.Description == '') {
                    this.error('Description', 'required');
                    return false;
                }
                else if (o.data.Quantity == '') {
                    this.error('Quantity', 'type number');
                    return false;
                }
                else if (o.data.UnitPrice == '') {
                    this.error('Quantity', 'type number');
                    return false;
                }
                else if (o.data.NetAmount == '') {
                    this.error('Quantity', 'type number');
                    return false;
                }
                if(o.data.Discount ==""){
                    o.data.Disocunt = 0;
                }else if(o.data.Discount < 0 || o.data.Discount > 100){

                    this.error('Discount', '0 -> 100 ');
                    return false;
                }
            }
        });
        editor.on( 'initEdit', function () {editor.disable('NetAmount');} );
        editor.on('remove', function (e, o, action) {
            var contractIds = o.ids;
            contractIds.forEach(function (contractId) {
                $('#' + contractId).removeAttr("disabled");
                updateDraftTotal();
            });
        });
        editor.on('postEdit',function(editor,id,values){
            console.log(id);
            var i = id.DT_RowId.replace('row_','');
            var row =  $('tr[id='+i+']');
            console.log(row);
           row.find('.lineNetAmount').text(id.NetAmount.format());
            updateDraftTotal();
        });

        var dataTable = $('#ddd').DataTable({
            "searching":false,
            'bSort':false,
            "dom": "Tfrtip",
            "bPaginate": false,
            "columns": [
                {
                    "data": "Description"
                },
                {
                    "data": "Quantity"
                },
                {
                    "data": "UnitPrice"
                },
                {
                    "data":"Discount"
                },
                {
                    "data": "NetAmount"
                },
                {
                    "data": "User"
                }
            ],
            "tableTools": {
                "sRowSelect": "os",
                "aButtons": [
                    {"sExtends": "editor_edit", "editor": editor},
                    {"sExtends": "editor_remove", "editor": editor}
                ]
            }
        });
    function addProductToDraft(productInfo) {
        $.ajax({
            type: "POST",
            url: api_address + 'Drafts(' + getModelId()  + ')/action.AddProduct',
            data: JSON.stringify({Description:productInfo.Description,Quantity:'1',ProductId:productInfo.Id}),
            success: function (data) {

                var rowNode = dataTable
                        .row.add({
                            Description: productInfo.Description,
                            Quantity: 1,
                            UnitPrice: productInfo.SalePrice,
                            NetAmount: productInfo.SalePrice,
                            Discount: 0,
                            User: ""
                        })
                        .draw().node();

                $(rowNode)
                        .css('color', 'red')
                        .animate({color: 'black'})
                        .attr('id', data.Id);

                // add a class to the third TD for the sum calculations. shit code.
                $(rowNode).find('td').eq(4).addClass('lineNetAmount');

                new PNotify({
                    title: Lang.get('labels.success'),
                    text: Lang.get('messages.update-was-successful'),
                    type: 'success'
                });

                updateDraftTotal();
                $('#productSearch').val('')

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
    }
        $(".addContractToDraft").click(function (event) {
            var contractId = event.target.id;
            var draftId = modelId;
            var formData = {
                Contract_Id: contractId    // TODO there is optional Quantity, which represents the billing period or something like that
            };
            $.ajax({
                type: "POST",
                url: api_address + 'Drafts(' + draftId + ')/action.AddContract',
                data: JSON.stringify(formData),
                success: function (data) {
                    var rowNode = dataTable
                            .row.add({
                                Description: data.Description,
                                Quantity: data.Quantity,
                                UnitPrice: data.UnitPrice,
                                NetAmount: data.UnitPrice*data.Quantity,
                                Discount: data.Discount,
                                User: ""
                            })
                            .draw().node();
                    $(rowNode)
                            .css('color', 'red')
                            .animate({color: 'black'})
                            .attr('id', data.Id);

                    // add a class to the third TD for the sum calculations. shit code.
                    $(rowNode).find('td').eq(4).addClass('lineNetAmount');

                    $(event.target).attr("disabled", true);
                    new PNotify({
                        title: Lang.get('labels.success'),
                        text: Lang.get('messages.update-was-successful'),
                        type: 'success'
                    });
                    updateDraftTotal();
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

        $('#createInvoice').click(function (event) {
            var draftId = modelId;
            var btn = $(event.target);
            btn.prop('disabled',true);

            var sendMail = $('#sendMail').prop('checked');
            $.ajax({
                type: "POST",
                data:JSON.stringify({SendMail:sendMail}),
                url: api_address + 'Drafts(' + draftId + ')/action.Finalize',
                success: function (data) {
                    new PNotify({
                        title: Lang.get('labels.success'),
                        text: Lang.get('messages.update-was-successful'),
                        type: 'success'
                    });
                    window.location = base_url + "/invoices/show/" + data.Id;
                },
                error: function (err) {
                    btn.prop('disabled',false);
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });

        //unlick the select. We need this link for the acl
        $('#User_Id_Select').change( function (event) {

            var userId = $(event.target).val();
            var userName = $(event.target).find('option:selected').text();
            $.ajax({
                url: api_address + "Drafts("+getModelId()+')',
                type: "PATCH",
                data:JSON.stringify({User_Id : userId}),
                success: function (data) {
                    $('.draftUserPlaceholder').html(userName);
                    $('#User_Id_Select').addClass('hidden');
                },
                error: handleError,
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });

        $('#editDraftUser').click(function(){
            $('#User_Id_Select').toggleClass('hidden');
        });

        // update the total sum when adding or removing contracts
        function updateDraftTotal() {
            var total = 0;

            $('.lineNetAmount').each(function (i) {
                total += parseFloat($(this).text().replace(/\./g,'').replace(',','.'));
            });
            $('.subTotal').text(total.format());
            var vatRate = parseInt($('.vatRate').text());
            if(draftType != 'Reminder'){
                var vatAmount =(total*(vatRate/100));
                $('.vatAmount').text((vatAmount).format());
                total = total+vatAmount;
            }
            $('.draftSum').text((total).format());
        }
        //update the price when page loads
        updateDraftTotal();

        // set a handling date
        $( "#handlingDate" ).editable({
            url:api_address+"Drafts("+getModelId()+")",
            params: function(params) {
                var data = {};
                data['NoticeAccountant'] = params.value;
                return JSON.stringify(data);
            },
            ajaxOptions:{
                type:"patch",
                dataType: 'application/json',
                beforeSend: function (request)
                {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            }
        });

        $('#deleteDraft').on('click',function(event){
            $(event.target).prop('disabled',true);

            $.ajax({
                type: "PATCH",
                url: api_address + 'Drafts(' + modelId + ')',
                data:JSON.stringify({'Status':"Deleted"}),
                success: function (data) {
                    location.reload();
                    $(event.target).prop('disabled',false);
                },
                error: function (err) {
                    $(event.target).prop('disabled',false);
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        });

        $('#restoreDraft').on('click',function(event){
            $(event.target).prop('disabled',true);

            $.ajax({
                type: "PATCH",
                url: api_address + 'Drafts(' + modelId + ')',
                data:JSON.stringify({'Status':"None"}),
                success: function (data) {
                    location.reload(true);
                    $(event.target).prop('disabled',false);
                },
                error: function (err) {
                    $(event.target).prop('disabled',false);
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        })


        $('body').on('click','.quickEditDraftType',function(event){
            // find the row id
            var target = $(event.target);

            var id = $(this).data('pk');
            event.preventDefault();
            $(event.target).editable({
                source: invoiceTypes, // comes from the controller
                ajaxOptions:{
                    type:"patch",
                    dataType: 'application/json',
                    beforeSend: function (request)
                    {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                },
                params: function(params) {
                    var data = {};
                    data['Type'] = params.value;
                    return JSON.stringify(data);
                },
                url:api_address+"Drafts("+id+")"
            }).removeClass('quickEditDraftType');
            setTimeout(function(){
                $(event.target).click();
            },200)
        });



    });

</script>
@stop

@section('content')
    {{--hidden fields for tasks--}}
    {!! Form::hidden('Model','Draft',['id'=>'Model']) !!}
    {!! Form::hidden('ModelId', $draft->Id,['id'=>'ModelId']) !!}
    {{--hidden fields for tasks--}}
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-invoice">
                <div class="panel-heading">
                    <h4><i class="fa fa-pencil-square-o"></i> Invoice Draft</h4>
                    <div class="options">
                        <a href="{{url("drafts/preview",$draft->Id)}}" target="_blank" title="Show Draft"><i class="fa fa-file-pdf-o"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    @if($draft->Status == 'Invoice' && $draft->Status != 'Deleted')
                        <div class="alert-success text-center">Already an invoice</div>
                    @elseif($draft->Status == 'Deleted')
                        <div class="alert-danger text-center">Deleted</div>
                    @endif
                    <div class="clearfix">
                        <div class="pull-left">
                            <address>
                                <a href="{{url('clientAlias/show',$draft->ClientAlias->Id)}}">{{$draft->ClientAlias->Name or ""}}</a><br>
                                {{$draft->ClientAlias->Address or ""}} <br/>
                                {{$draft->ClientAlias->City or ""}} <br/>
                                {{$draft->ClientAlias->zip or ""}} <br/>
                                {{$draft->ClientAlias->Client->CINumber or ""}}
                            </address>
                        </div>
                        <div class="pull-right">
                            <dl>
                                <dt>Handling Date:</dt>
                                <dd>
                                    <a id="handlingDate" title="Click to set or change handling date"
                                       @if(Carbon::parse($draft->NoticeAccountant)->isPast()) style="color:red" @endif
                                       data-type="date" data-viewformat="dd.mm.yyyy" data-pk="{{$draft->Id}}" data-placement="right"
                                    >
                                        @if(isset($draft->NoticeAccountant))
                                            {{toDate($draft->NoticeAccountant)}}
                                        @else
                                            Set a handling date
                                        @endif
                                    </a>
                                </dd>
                            </dl>
                            @if(isAdmin() && $draft->Status != 'Deleted')
                                <button class="btn btn-danger" id='deleteDraft'>Delete draft</button>
                            @elseif(isAdmin() && $draft->Status == 'Deleted')
                                <button class="btn btn-success" id='restoreDraft'>Restore draft</button>
                            @endif
                        </div>

                        <div class="pull-right">
                            <dl class="dl-horizontal-row-2">
                                <dt>@lang('labels.status')</dt>
                                <dd>{{$draft->Status}}</dd>

                                <dt>@lang('labels.type')</dt>
                                <dd><a href="#" title="Click to change draft type" class="quickEditDraftType" data-type="select" data-pk="{{$draft->Id}}">{{$draft->Type}}</a></dd>

                                @if($draft->Type=='Reminder')
                                    <dt>Reminder type</dt>
                                    <dd>{{$draft->ReminderType or "--"}}</dd>
                                @endif

                                <dt>@lang('labels.invoice-date')</dt>
                                <dd>{{date('d-m-Y',strtotime('today'))}}</dd>

                                <dt>@lang('labels.due-date')</dt>
                                <dd>{{date('d-m-Y',strtotime('+'.$invoicePayPeriod.' days'))}}</dd>

                                <dt>@lang('labels.client-number')</dt>
                                <dd>{{$draft->ClientAlias->Id or ""}}</dd>

                                <dt>@lang('labels.our-reference')</dt>
                                <dd>
                                    <span class="draftUserPlaceholder">
                                    {!! $draft->User->FullName or Form::select('User_Id',withEmpty($users),null,['class'=>'form-control','id'=>'User_Id_Select'])!!}
                                    </span>
                                    <span class="pseudolink" id="editDraftUser">@lang('labels.edit')</span>
                                    {!! Form::select('User_Id',withEmpty($users),null,['class'=>'form-control hidden','id'=>'User_Id_Select']) !!}
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <hr/>
                    <div class="table-responsive">
                        <table class="table table-condensed table-list" id="ddd">
                            <thead>
                            <tr>
                                <th>@lang('labels.description')</th>
                                <th>@lang('labels.quantity')</th>
                                <th>@lang('labels.unit-price')</th>
                                <th>@lang('labels.discount')%</th>
                                <th>@lang('labels.total')</th>
                                <th>@lang('labels.seller')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($draft->DraftLine as $line)
                                <tr id="{{$line->Id}}">
                                    <td class="multiline">{{$line->Description}}</td>
                                    <td>{{$line->Quantity}}</td>
                                    <td>{{$line->UnitPrice}}</td>
                                    <td>{{$line->Discount}}</td>
                                    <td class="lineNetAmount">{{formatMoney(calculateLineDiscount($line))}}</td>
                                    <td>{{$line->User->UserName or ""}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="clearfix">
                        <div class="col-md-3 col-md-offset-9">
                            <p class="text-right">@lang('labels.sub-total'): <span class="subTotal"></span></p>
                            @if($draft->Type != 'Reminder')
                                <p class="text-right">@lang('labels.vat'):
                                    <span class="vatRate">
                                        @if(isset($draft->ClientAlias->Country->VatRate))
                                            {{$draft->ClientAlias->Country->VatRate*100}}
                                        @else
                                        25 <!-- default vat amount -->
                                        @endif
                                    </span>%
                                </p>
                                <p class="text-right">Vat amount : <span class="vatAmount"></span> </p>
                            @endif
                            <hr>
                            <h3 class="text-right">{{config('gcm.money-code')}} <span class="draftSum"></span></h3>

                        </div>
                        <div class="pull-left">
                            <a target="_blank" href="{{url("drafts/preview",$draft->Id)}}" class="btn btn-default btn-label"><i class="fa fa-search"></i>@lang('labels.preview')</a>
                        </div>
                        <div class="pull-right">
                            <p>
                                <button class="btn btn-success btn-label" id="createInvoice"><i class="fa fa-check"></i> @lang('labels.create-invoice')</button>
                                <div class="form-group">
                                    <label for="sendMail">Send invoice as mail?</label>
                                    <input type="checkbox" checked="checked" name="SendMail" id="sendMail">
                                </div>

                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div> {{-- End of col-md-6 for draft lines --}}

        <div class="col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h4><i class="fa fa-file"></i> @lang('labels.contracts')</h4>
                </div>
                <div class="panel-body">
                    <div id="accordioninpanel" class="accordion-group">
                        @foreach($contracts as $c)
                            <div class="accordion-item">
                                <a class="accordion-title" data-toggle="collapse" data-parent="#accordioninpanel"
                                   href="#collapsein{{$c->Id}}"><h4>@lang('labels.contract') : {{$c->Id}} {{ $c->Product->Name or '' }} <span id="childrenCount">{{--({{count($c->Children)}}) --}}</span></h4></a>

                                <div id="collapsein{{$c->Id}}" class="collapse">
                                    <div class="accordion-body">
                                        <div class="table-responsive">
                                            <table class="table table-condensed table-list">
                                                <tbody>
                                                <tr>
                                                    <td>
                                                        <button class="btn btn-sm addContractToDraft"
                                                                id="{{$c->Id}}" @if(in_array($c->Id,$draftContractIds)) {{"disabled"}}  @endif >@lang('labels.add')
                                                        </button>
                                                    </td>
                                                    <td>#{{$c->Id}}</td>
                                                    <td><a href="{{url('contracts/show',$c->Id)}}">{{$c->Product->Name or "No product is set"}}</a> {{$c->Status or ''}}</td>
                                                    <td>@if($c->StartDate != null){{Carbon::parse($c->StartDate)->format('d-m-Y')}}@endif</td>
                                                    <td>@if($c->EndDate != null){{Carbon::parse($c->EndDate)->format('d-m-Y')}}@endif</td>
                                                    <td>{{$c->ContractType->Name or "No product is set"}}</td>
                                                </tr>
                                                @if(isset($c->Children))
                                                    @foreach($c->Children as $child)
                                                        <tr>
                                                            <td>
                                                                <button class="btn btn-sm addContractToDraft"
                                                                        id="{{$child->Id}}"@if(in_array($child->Id,$draftContractIds)) {{"disabled"}} @endif>@lang('labels.add')</button>
                                                            </td>
                                                            <td>#{{$child->Id}}</td>
                                                            <td><a href="{{url('contracts/show',$child->Id)}}">{{$child->Product->Name or "No product is set"}}</a> {{ $child->Status or '' }}</td>
                                                            <td>@if($child->StartDate != null){{Carbon::parse($child->StartDate)->format('d-m-Y')}}@endif</td>
                                                            <td>@if($child->EndDate != null){{Carbon::parse($child->StartDate)->format('d-m-Y')}}@endif</td>
                                                            <td>{{$child->ContractType->Name or "No product is set"}}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-xs-10">
                            <div class="panel">
                                <div class="search-classic1">
                                    <input autocomplete="off" id="productSearch" type="text" class="form-control"
                                           placeholder="@lang('labels.search-products')">
                                </div>
                            </div>
                        </div>
                    </div> {{-- end row
                    {{--Comments--}}
                    @include('layout.tabs-section',
                    ['information'=>$draft->ClientAlias])
                    {{--end comments--}}
                </div>
            </div>


        </div>
    </div>
@stop
