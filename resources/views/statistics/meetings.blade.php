@extends('layout.main')
@section('page-title',Lang::get('labels.meetings-statistics'))

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
<script type="text/javascript">
    $(document).ready(function (event) {

        var leadIds = $.map(leadIds,function (a,b) {
            console.log(a);
            console.log(b);
        });

        var bookedLeadsTable= $('#bookedLeadsTable').DataTable({
        responsive:true,
        stateSave:true,
        "stateDuration": 300, // 5 minutes
        "language": {
        "url": base_url+"/datatables-"+locale+'.json'
        },
        "oLanguage": {
            "sSearch":       "",
            "sSearchPlaceholder": Lang.get('labels.search')
        },
        "lengthMenu": [[20,50,100], [20,50,100]],
        aaSorting:[[0,"desc"]], // shows the newest items first
        "sPaginationType": "full_numbers",
        "bProcessing": true,
        "bServerSide": true,
        "deferRender": true, // testing if speed is better with this
        "sAjaxSource": api_address+"CalendarEvents?$expand=User($select=FullName)",
        'filter' : 'Model eq \'Lead\' and Booker_Id eq \''+userId+'\' and User_Id ne \''+userId+'\' and ('+thisMonth+") and (EventType eq 'HealthCheck')",
        'select' : "Id,Model,ModelId",
        "fnRowCallback": function (nRow, aaData) {
            if(aaData.Model && aaData.ModelId) {

                $.when(getCompanyName(aaData.Model,aaData.ModelId))
                    .then(function (name) {
                        if(name.value != 'Undefined'){
                            $(nRow).find('td:nth-child(1)').append('-'+name.value);
                        }

                    });
            }
        },
        "aoColumns": [
            {mData:'Summary',mRender:function (Summary,unused,obj) {
                    return "<a target='_blank' href='"+base_url+"/appointments/show/"+obj.Id+"'>"+Summary+"</a>"
                }
            },
            {mData:"Created",sType:"date",mRender:function (Created) {
                    var date = new Date(Created);
                    return date.toDateTime();
                }
            },
            {mData:null,oData:'User/FullName',mRender:function (obj) {
                    return (obj.User ? obj.User.FullName:"");
                }
            },
            {mData:'EventType',searchable:false
            }
        ],
        "fnServerData": fnServerOData,
        "iODataVersion": 4,
        "bUseODataViaJSONP": false
       });
        
        var aliasIdQuery = $.map(aliasIds,function (id,index) {
                return 'Lead_Id eq '+id;
            });
        var query = aliasIdQuery.join(' or ');
        if(query==""){ query = "Id eq null"}
        var realizedSales = $('#realizedSales').DataTable({
            responsive:true,
            stateSave:true,
            "stateDuration": 300, // 5 minutes
            "language": {
                "url": base_url+"/datatables-"+locale+'.json'
            },
            "oLanguage": {
                "sSearch":       "",
                "sSearchPlaceholder": Lang.get('labels.search')
            },
            "lengthMenu": [[20,50,100], [20,50,100]],
            aaSorting:[[0,"desc"]], // shows the newest items first
            "sPaginationType": "full_numbers",
            "bProcessing": true,
            "bServerSide": true,
            "deferRender": true, // testing if speed is better with this
            "sAjaxSource": api_address+"ClientAlias",
            'filter' : query,
            'select':'Id',
            "aoColumns": [
                {mData:'Name',mRender:function (Name,unused,obj) {
                        return "<a href='"+base_url+"/clientAlias/show/"+obj.Id+"'>"+Name+"</a>"
                    }
                },
                {mData:"Created",sType:"date",mRender:function (Created) {
                        var date = new Date(Created);
                        return date.toDateTime();
                    }
                }
            ],
            "fnServerData": fnServerOData,
            "iODataVersion": 4,
            "bUseODataViaJSONP": false
        });

        $('.meetingStatsFilter').on('change',function(){
            $('#bookingFilters').submit();
        });

    })
</script>
@stop

@section('content')
    <div class="panel">
        <div class="panel-heading">
            <h4>{{$thisMonthName}} - {{$userName->FullName}}</h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <form id="bookingFilters">
                        <div class="form-group-sm col-md-3">
                            {!! Form::select('Created',$months,$thisMonth,['class'=>'form-control meetingStatsFilter']) !!}
                        </div>
                        <div class="form-group-sm col-md-3">
                            {!! Form::select('Booker_Id',withEmpty($bookers,'Select Booker'),$userName->Id,['class'=>'form-control meetingStatsFilter']) !!}
                        </div>
                    </form>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h4>Booked Leads</h4>
                        </div>
                        <div class="panel-body">
                            <div style="width: 100%">
                                <table class="table table-hover datatables table-condensed" style="font-size: 10px;"
                                       id="bookedLeadsTable">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Created At</th>
                                        <th>For User:</th>
                                        <th>Type</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h4>Realized Sales from bookings</h4>
                        </div>
                        <div class="panel-body">
                            <div style="width: 100%">
                                <table class="table table-hover datatables table-condensed" style="font-size: 10px;"
                                       id="realizedSales">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Created At</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop