@extends('layout.main')
@section('page-title',"Active Clients")

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {
            var past = getIsoDate(moment().subtract(30, 'days').utc());
            var future = getIsoDate(moment().add(30, 'days').utc());
            var filters = "Contract/any(d:((d/Status eq 'Active' and d/EndDate ge "+future+") or (d/Status eq 'Standby' and d/Created ge "+past+")) and d/Product/ProductType_Id eq 4) and Invoice/any(i:i/Status eq 'Paid' and i/Type eq 'Invoice')";

            var table = $('#clientsByTypeTable').DataTable({
                'bFilter':false,
                responsive:true,
                stateSave:true,
                aaSorting:[[0,"asc"]], // shows the newest items first
                "lengthMenu": [[20,50,100,-1], [20,50,100,'all']],
                "bProcessing": true,
                "bServerSide": true,
                "deferRender": true, // testing if speed is better with this
                'filter' : filters,
                "sAjaxSource": api_address+"ClientAlias?$expand=User($select=FullName),Contract($select=AdwordsId)",
                'select':"Id",
                "aoColumns": [
                    {mData:'Name',mRender:function (name,dispaly,obj) {
                        return "<a target='_blank' href='"+linkToItem('ClientAlias',obj.Id,true)+"'>"+name+'</a>';
                    }},
                    {mData:'AdwordsId',mRender:function (adwordsId,display,obj) {
                        if(!adwordsId){
                            var cAdwordsId = false;
                            $.each(obj.Contract,function (index,val) {
                                if(val.AdwordsId){
                                    cAdwordsId = val.AdwordsId;
                                }
                            });
                            if(!cAdwordsId){
                                return '<a href="#" class="quickSaveAdwordsId" data-type="text" data-pk="'+obj.Id+'">Set Adwords Id</a>'
                            }else{
                                return String(cAdwordsId).replaceAll('-','');
                            }
                        }else{
                            return String(adwordsId).replaceAll('-','');
                        }
                    }
                    },
                    {mData:"EMail"
                    },
                    {mData:null,oData:"User/FullName",mRender:function (obj) {
                        if(obj.User){
                            return obj.User.FullName;
                        }
                        return ''
                    }
                    }
                ],
                "fnServerData": fnServerOData,
                "iODataVersion": 4,
                "bUseODataViaJSONP": false
            });

            $('body').on('click','.quickSaveAdwordsId',function(event){
                // find the row id
                var target = $(event.target);

                var id = $(this).data('pk');
                event.preventDefault();
                $(event.target).editable({
                    validate: function(value) {
                        var regex = new RegExp(/\b\d{3}[-]?\d{3}[-]?\d{4}\b/g);
                        console.log(value);
                        if(! regex.test(value)) {
                            return '########## or ###-###-####';
                        }
                    },
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
                        data['AdwordsId'] = params.value;
                        return JSON.stringify(data);
                    },
                    url:api_address+"ClientAlias("+id+")",
                    success: function() {
                        setTimeout(function(){
                            var adwordsid = target.text();
                            target.replaceWith(String(adwordsid).replaceAll('-',''));
                        },300)
                    }
                }).removeClass('quickSaveAdwordsId');
                setTimeout(function(){
                    $(event.target).click();
                },200)
            });

        })
    </script>


@stop

@section('content')
    <div class="row">
        <div class="panel panel-activities">
            <div class="panel-heading"><i class="fa fa-bar-chart-o"></i> Clients by Product type </div>
            <div class="panel-body">
                <div class="row">
                    Active clients

                    <div class="col-md-3"></div>
                </div>
                <hr>

                <div class="row">
                    <table class="table datatables" style="width: 100%" id="clientsByTypeTable">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Adwords Id</th>
                            <th>Email</th>
                            <th>Seller</th>
                        </tr>
                        </thead>
                    </table>

                </div>

            </div>
        </div>
    </div>
@stop