@extends('layout.main')
@section('page-title',Lang::get('labels.salaries'))
@section('scripts')
@include('scripts.dataTablesScripts')
<script>
    $(document).ready(function () {
        var customFilters = '';
        var table = $('.datatables').DataTable(
            {
                responsive: true,
                'stateSave': true,
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
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": api_address + "Salaries?$expand=BonusForUser($select=FullName)",
                'filter':'Generated eq true',
                "aoColumns": [
                    {
                        mData: "Id", "oData": "Id", "sType": "numeric", "width": "5%", mRender: function (id) {
//                            return '<a href="' + base_url + '/salaries/show/' + id + '" title="See Salary">' + id + '</a>';
                            return  id ;
                        }
                    },
                    {mData:null,oData:"BonusForUser/FullName",mRender:function (obj) {
                            return obj.BonusForUser ? obj.BonusForUser.FullName:"";
                        }

                    },
                    {mData:"StartDate",sType:"date",mRender:function (data) {
                            var date = moment(data);
                            return date.format('LL');
                        }
                    },
                    {mData:"EndDate",sType:"date",mRender:function (data) {
                            var date = moment(data);
                            return date.format('LL');
                        }
                    },
                    {mData:"Bonus",type:"numberic",mRender:function (bonus) {
                            return bonus.format();
                        }
                    },
                    {mData:"PaidAmount",type:"numberic",mRender:function (bonus) {
                        return bonus.format();
                    }

                    }
                ],
                "fnServerData": fnServerOData,
                "iODataVersion": 4,
                "bUseODataViaJSONP": false
            });


        // get original filters
        var settings = table.settings();

        var originalFilter = settings[0].oInit.filter;

        var filters = $('.orderFilters');

        //check if there are already selected filters / for example if we refresh the page
        var currentFilters = filters.filter(function(){
           return this.value;
        });

        if(currentFilters.length>0){
            var oldFilters =$.map(currentFilters, function (obj) {
                    return $(obj).val();
            });

            customFilters = oldFilters.join(' and ');
            // add the extra filters to the existing ones
            settings[0].oInit.filter += " and " + customFilters;
            table.draw();
        }

        //if a contract filter is clicked, apply a corresponding string
        filters.on('change', function (event) {
            settings[0].oInit.filter = originalFilter;
            customFilters = '';//clear old filters
            var newFilters = $.map(filters.toArray(), function (obj) {
                if ($(obj).val() != "") {
                    return $(obj).val();
                }
            });

            customFilters = newFilters.join(' and ');
            // add the extra filters to the existing ones
            if(customFilters.length > 0){
            settings[0].oInit.filter += " and " + customFilters;
            }
            //redraw the table
            table.draw();
            return false;
        });

        $('#salaryBonusRequest').on('submit',function (event) {
            event.preventDefault();
            var data = $(this).serializeJSON();
            var times = data.Time.split(',');
            delete(data['Time']);
                data.StartDate = times[0];
                data.EndDate = times[1];
            if(data.User_Id == "") delete(data.User_Id);
            $.ajax({
                type: "POST",
                url: api_address + 'Salaries/Bonus',
                data: JSON.stringify(data),
                success: function (data) {
                    new PNotify({
                        title: 'Bonus requested.',
                        text: 'You will receive notification when it is generated',
                        type: 'success'
                    });
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });        })
    });
</script>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-green">
            <div class="panel-heading">
                <h4><i class="fa fa-money"></i>&nbsp;@lang('labels.salary')</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="row">
                        <div class="col-md-6">
                            <form id="salaryBonusRequest">
                                <div class="form-group-sm col-md-3">
                                    {!! Form::select('Time',$months,null,['class'=>'form-control']) !!}
                                </div>
                                <div class="form-group-sm col-md-3">
                                    {!! Form::select('User_Id',withEmpty($sellers,'All Sellers'),null,['class'=>'form-control']) !!}
                                </div>
                                <div class="btn-toolbar">
                                    <button type="submit" class="btn btn-sm btn-green">Request bonus calculation</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="table-responsive">
                        <table id="table-list" class="table table-striped datatables" width="100%">
                            <thead>
                                <tr>
                                    <th>Number</th>
                                    <th>For User</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Bonus</th>
                                    <th>Paid amount</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop



 