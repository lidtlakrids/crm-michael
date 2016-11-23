@extends('layout.main')
@section('page-title',Lang::get('labels.assign_leads'))

@section('styles')
@stop

@section('scripts')

    <script>
        $(document).ready(function () {

            $('#bulkAssignLeads').on('submit',function(event){

                event.preventDefault();

                var formData = convertSerializedArrayToHash($(this).serializeArray());
                var btn =     $(event.target).find('input:submit');
                //disable the link until the function is over
                btn.prop('disabled','disabled');
                $.ajax({
                    type: "POST",
                    url: api_address + 'Leads/action.Assign',
                    data:JSON.stringify(formData),
                    success: function (data) {
                        btn.prop('disabled',false);

                        new PNotify({
                            title: Lang.get('labels.success'), text: formData.Amount+" "+Lang.get('labels.leads-were-moved'), type: 'success'
                        });
                        loadLeadStatistics();
                    },
                    error: handleError,
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
                return false;
            });

            function getLeadTypeCount(nameKey, myArray){
                for (var i=0; i < myArray.length; i++) {
                    if (myArray[i].Key === nameKey) {
                        return myArray[i].Count;
                    }
                }
            }

            // Load the statistics after the page is ready
            function loadLeadStatistics(){

                var container = $('div.leads-count');
                container.parent('div').addClass('spinner');

                //get the statistics
                $.ajax({
                    type: "GET",
                    url: api_address + 'Leads/action.Stats',
                    success: function (data) {

                        // load the table
                        container.loadTemplate(base_url+'/templates/leads_assign/table.html',{},{overwriteCache:true,success:function(){
                            var tbody = container.find('#lead-user-count > tbody');

                            var stats =[];
                            var newLeadsCount=0;
                            var rotateLeadsCount=0;

                            data.value.forEach(function (stat) {

                                var newLeads  = getLeadTypeCount("New",stat.Stats);
                                var rotateLeads  = getLeadTypeCount("ReUse",stat.Stats);
                                stats.push({
                                    Initials:stat.UserId != null ? users[stat.UserId] : "Nobody (Select)",
                                    New:newLeads,
                                    Local:getLeadTypeCount("Local",stat.Stats),
                                    LocalLink:base_url+'/leads/move?lead_type=Local&user_id='+stat.UserId,
                                    NewLink:base_url+'/leads/move?lead_type=New&user_id='+stat.UserId,
                                    Appointment:getLeadTypeCount("Appointment",stat.Stats),
                                    AppointmentLink:base_url+'/leads/move?lead_type=Appointment&user_id='+stat.UserId,
                                    NoAnswer:getLeadTypeCount("NoAnswer",stat.Stats),
                                    NoAnswerLink:base_url+'/leads/move?lead_type=NoAnswer&user_id='+stat.UserId,
                                    Dead:getLeadTypeCount("Dead",stat.Stats),
                                    DeadLink:base_url+'/leads/move?lead_type=Dead&user_id='+stat.UserId,
                                    NoAds:getLeadTypeCount("NoAds",stat.Stats),
                                    NoAdsLink:base_url+'/leads/move?lead_type=NoAds&user_id='+stat.UserId,
                                    NotInterested:getLeadTypeCount("NotInterested",stat.Stats),
                                    NotInterestedLink:base_url+'/leads/move?lead_type=NotInterested&user_id='+stat.UserId,
                                    ReUse:rotateLeads,
                                    ReUseLink:base_url+'/leads/move?lead_type=ReUse&user_id='+stat.UserId,
                                    Customer:getLeadTypeCount("Customer",stat.Stats),
                                    CustomerLink:base_url+'/leads/move?lead_type=Customer&user_id='+stat.UserId
                                });

                                newLeadsCount += (typeof newLeads === 'undefined')? 0: stat.UserId == null ? newLeads :0;
                                rotateLeadsCount += (typeof rotateLeads === 'undefined')? 0:stat.UserId == null ? rotateLeads :0;
                            });

                            $('.newLeadsCount').empty().text(newLeadsCount);
                            $('.rotateLeadsCount').empty().text(rotateLeadsCount);
                            tbody.loadTemplate(base_url+'/templates/leads_assign/tableRow.html',stats, {append:true,overwriteCache:true});
                        }
                        });


                        container.parent('div').removeClass('spinner');
                    },
                    error: handleError,
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            }

            loadLeadStatistics();
        });


    </script>

@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-lead">
                <div class="panel-heading">
                    <h4><i class="fa fa-bullhorn"> </i> Leads @lang('labels.dashboard')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Assign Lead</h4>
                            Who do you want to give leads today?
                            <br />
                            {!! Form::open(['id'=>'bulkAssignLeads']) !!}
                            <div class="form-inline">

                                {!!  Form::select('User_Id', $users, null, ['class' => 'form-control']) !!}

                                <select name="Amount" id="lead-count" class="form-control" title="Antal">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                </select>
                                {!!  Form::select('Status', [0=>"New",6=>'Rotate'], null, ['class' => 'form-control']) !!}

                                <input class="btn btn-green form-control" type="submit" value="ASSIGN">
                                {!! Form::close() !!}

                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4>Status Leads - DK</h4>
                            <dl class="dl-horizontal">
                                    <dt>New</dt>
                                    <dd class="newLeadsCount"></dd>
                                    <dt>Rotate</dt>
                                    <dd class="rotateLeadsCount"></dd>
                            </dl>
                        </div>
                    </div>
                    <hr />
                    <div style="position:relative;">
                        <div class="leads-count">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop