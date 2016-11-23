@extends('layout.main')
@section('page-title',"Clients by type")

@section('styles')
@stop

@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {
            var dateOfInterest = getIsoDate(moment('2016-05-01 00:00').utc());
            var filters = "Contract/any(d:(d/StartDate le "+dateOfInterest+" and d/EndDate ge "+dateOfInterest+")" +
                    " and (d/Product/ProductType_Id eq 4 or d/ContractType_Id eq 4) and (d/Status eq 'Active' or d/Status eq 'Completed' or d/Status eq 'Standby'))";

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
                "sAjaxSource": api_address+"ClientAlias?$expand=Country($select=Name,CountryCode),Contact,Contract($select=AdwordsId)",
                'select':"Id",
                "aoColumns": [
                    {mData:'Name',mRender:function (name,dispaly,obj) {
                        return "<a target='_blank' href='"+linkToItem('ClientAlias',obj.Id,true)+"'>"+name+'</a>';
                    }
                    },
                    {mData:"Homepage",mRender:function (homepage,display,obj) {
                        if (homepage && homepage!=='NotFound') {
                            return "<a target='_blank' href='" + addhttp(homepage) + "'>" + homepage + '</a>';
                        } else {
                            return '<a href="#" class="quickSaveAliasHomepage" data-type="text" data-pk="'+obj.Id+'"><span class="alert-danger">Set Client Homepage</span></a>'
                        }
                    }
                    },
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
                    {mData:null,oData:'Country/Name',sType:"string",mRender:function (obj) {
                        if(obj.Country != null){
                            return obj.Country.Name+' ('+obj.Country.CountryCode+')';
                        }
                        return '';
                    }
                    },
                    {mData:null,oData:null,sortable:false,searchable:false,mRender:function (obj) {
                            if(obj.Contact.length > 0){
                                if(obj.Contact[0].Name){
                                    return obj.Contact[0].Name;
                                }else{
                                    return '<a href="#" class="quickSaveContactName" data-type="text" data-pk="'+obj.Contact[0].Id+'">Set Contact Name</a>'
                                }

                            }
                        }
                    },
                    {mData:'EMail',mRender:function (email,display,obj) {
                        if(!email){
                            return '<a href="#" class="quickSaveAliasEmail" data-type="text" data-pk="'+obj.Id+'">Set Client Email</a>'
                        }else{
                            return email;
                        }
                    }
                    }
                ],
                "fnServerData": fnServerOData,
                "iODataVersion": 4,
                "bUseODataViaJSONP": false
            });

            $('body').on('click','.quickSaveContactName',function(event){
                // find the row id
                var target = $(event.target);

                var id = $(this).data('pk');
                event.preventDefault();
                $(event.target).editable({
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
                        data['Name'] = params.value;
                        return JSON.stringify(data);
                    },
                    url:api_address+"Contacts("+id+")",
                    success: function() {
                        setTimeout(function(){
                            var name = target.text();
                            target.replaceWith(name);
                        },300)
                    }
                }).removeClass('quickSaveContactName');
                setTimeout(function(){
                    $(event.target).click();
                },200)
            });


            $('body').on('click','.quickSaveAliasHomepage',function(event){
                // find the row id
                var target = $(event.target);

                var id = $(this).data('pk');
                event.preventDefault();
                $(event.target).editable({
                    validate: function(value) {
                        if(!validateUrl(value)) {
                            return "Not a valid domain";
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
                        data['Homepage'] = params.value;
                        return JSON.stringify(data);
                    },
                    url:api_address+"ClientAlias("+id+")",
                    success: function() {
                        setTimeout(function(){
                            var homepage = target.text();
                            target.replaceWith("<a target='_blank' href='" + addhttp(homepage) + "'>" + homepage + '</a>');
                        },300)
                    }
                }).removeClass('quickSaveAliasHomepage');
                setTimeout(function(){
                    $(event.target).click();
                },200)
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


            $('body').on('click','.quickSaveAliasEmail',function(event){
                // find the row id
                var target = $(event.target);

                var id = $(this).data('pk');
                event.preventDefault();
                $(event.target).editable({
                    validate: function(value) {
                        if(!validateEmail(value)) {
                            return "Not a valid email";
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
                        data['EMail'] = params.value;
                        return JSON.stringify(data);
                    },
                    url:api_address+"ClientAlias("+id+")",
                    success: function() {
                        setTimeout(function(){
                            var email = target.text();
                            target.replaceWith(email);
                        },300)
                    }
                }).removeClass('quickSaveAliasEmail');
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
                    Adwords clients active in the last 3 months
            </div>
            <hr>

            <div class="row">
                <table class="table table-condensed" id="clientsByTypeTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Homepage</th>
                            <th>Adwords Id</th>
                            <th>Country</th>
                            <th>Contact Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                </table>

            </div>

        </div>
    </div>
</div>
@stop