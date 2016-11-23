@extends('layout.main')
@section('page-title',Lang::get('labels.loyalty-bonuses'))
@section('scripts')
    @include('scripts.dataTablesScripts')

    <script>
        $(document).ready(function () {
            var customFilters = '';

            var table = $('#table-list').DataTable(
                {
                    responsive: true,
                    stateSave: true,
                    bFilter:false,
                    bPaginate:false,
                    "language": {
                        "url": "datatables-"+locale+'.json'
                    },
                    "oLanguage": {
                        "sSearch":       "",
                        "sSearchPlaceholder": Lang.get('labels.search')
                    },
                    "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                    aaSorting: [[0, "desc"]], // shows the newest items first
                    "sPaginationType": "full_numbers",
                    "sAjaxSource": api_address + "LoyaltyBonus",
                    "bProcessing": true,
                    "bServerSide": true,
                    'select':"Id",
                    "aoColumns": [
                        {
                            "mData": "Discount", sType: "number"
                        },
                        {
                            mData:"Months",sType:"number"
                        },
                        {mData:null,oData:null,sType:"date",sortable:false,mRender:function (obj) {
                            return "<a href='"+base_url+"/loyalty-bonuses/edit/"+obj.Id+"'><i class='fa fa-pencil'></i></a>"
                        }

                        }
                    ],
                    "fnServerData": fnServerOData,
                    "iODataVersion": 4,
                    "bUseODataViaJSONP": false
                });
            
            $('.createLoyaltyBonus').on('click',function () {
                var modal = getDefaultModal();
                modal.find('.modal-title').append(Lang.get('labels.create-loyalty-bonus'));
                modal.find('.modal-body').loadTemplate(base_url+'/templates/loyaltyBonus/createForm.html',
                    {
                        DiscountLabel:Lang.get('labels.discount'),
                        MonthsLabel  :Lang.get('labels.months'),
                        CreateLabel  :Lang.get('labels.save')
                    },
                    {
                        overwriteCache:true
                    })
            });
            
            $('body').on('submit','#createLoyaltyBonusForm',function (event) {
                event.preventDefault();
                var data = $(this).serializeJSON();
                $.ajax({
                    url: api_address+"LoyaltyBonus",
                    type: "POST",
                    data:JSON.stringify(data),
                    success : function()
                    {
                        closeDefaultModal();
                        table.draw();
                        new PNotify({title:Lang.get('labels.success'),type:"success"})
                    },
                    error: handleError,
                    beforeSend: function (request)
                    {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            })
        });

    </script>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-tasks"></i>@lang('labels.loyalty-bonuses')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        @if(isAllowed('loyaltyBonus','post'))<i class="fa fa-plus createLoyaltyBonus" title="@lang('labels.create-loyalty-bonus')"></i>@endif
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <table id="table-list" cellpadding="0" cellspacing="0" border="0" class="table datatables table-hover" >
                        <thead>
                        <tr>
                            <th>@lang('labels.discount') %</th>
                            <th>@lang('labels.months')</th>
                            <th>@lang('labels.actions')</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop