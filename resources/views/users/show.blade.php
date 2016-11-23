@extends('layout.main')
@section('page-title',Lang::get('labels.user').": ".$user->FullName)
@section('styles')
    <style>
        .expanded {
            display: inline-block;
            margin-right: -500px;
            margin-left: 30px;
            margin-top: 20px;
        }

        .expanded th {
            min-width: 70px;
        }

        table#userSubTasksTbl, table#userComplSubTasksTbl {
            background-color: transparent;
        }

        table#userSubTasksTbl tbody tr, table#userComplSubTasksTbl tbody tr {
            background-color: transparent;
        }

        table#userSubTasksTbl thead tr, table#userComplSubTasksTbl thead tr {
            background-color: transparent;
        }

        table#userSubTasksTbl tr:nth-child(even), table#userComplSubTasksTbl tr:nth-child(even) {
            background-color: transparent;
        }
    </style>
@stop
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {
            var userId = $.trim($('#userId').text());
            var userQuery = "(AssignedTo_Id eq '" + userId + "' and ParentTaskListId eq null) or (ParentTaskListId ne null and AssignedTo_Id eq '" + userId + "' and Parent/AssignedTo_Id ne '" + userId + "' and Value eq false)";

            var table = $('#userTasksTable').DataTable(
                    {
                        "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                        aaSorting: [[1, "desc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        'filter': "Value eq false and " + userQuery,
                        "sAjaxSource": api_address + "TaskLists?$expand=CreatedBy($select=FullName),Children($expand=AssignedTo($select=FullName),CreatedBy($select=FullName);$orderby=Value)",
                        "select": "Id,Model,ModelId",
                        "bProcessing": true,
                        "bServerSide": true,
                        "fnRowCallback": function (nRow, aaData) {
                            if (aaData.Value) {
                                $(nRow).addClass('crossed-through');
                            } else {
                                $(nRow).removeClass('crossed-through');
                            }
                            if (aaData.Model && aaData.ModelId) {

                                $.when(getCompanyName(aaData.Model, aaData.ModelId))
                                        .then(function (name) {
                                            var clientName = "View";
                                            if (name.value != 'Undefined')clientName = name.value;

                                            $(nRow).find('.clientName').html("<a href='" + linkToItem(aaData.Model, aaData.ModelId, true) + "' target='_blank'>" + clientName + "</a>");
                                        });
                            }
                        },
                        "aoColumns": [
                            {
                                "mData": null,
                                sType: "string",
                                "orderable": false,
                                searchable: false,
                                sortable: false,
                                mRender: function (obj) {
                                    if (obj.Children.length > 0) {
                                        var subtable = '<span class="expand" style="cursor: pointer"><i class="fa fa-plus-circle"></i> <span>('
                                                + obj.Children.length + ')</span></span>' +
                                                '<div class="table-responsive expanded"><h4>Subtasks</h4><table id="userSubTasksTbl" class="table table-condensed table-hover"><thead><tr><th>Title</th>' +
                                                '<th>Description</th>' +
                                                '<th>Assigned to</th>' +
                                                '<th>Created by</th>' +
                                                '<th>Due date</th>' +
                                                '<th>Actions</th>' +
                                                '</tr></thead><tbody>';

                                        $.each(obj.Children, function (index, child) {

                                            var crossedThrough = "";
                                            if (child.Value) {
                                                crossedThrough = 'class="crossed-through"'
                                            }
                                            subtable += '<tr  ' + crossedThrough + '><td>' + child.Title + '</td><td class="show-more-container">';
                                            if (child.Description != null) {
                                                subtable += child.Description + '</td><td>';
                                            }
                                            else {
                                                subtable += '</td><td>'
                                            }

                                            if (child.AssignedTo != null) {
                                                subtable += child.AssignedTo.FullName + '</td><td>';
                                            }
                                            else {
                                                subtable += '</td><td>'
                                            }
                                            if (child.CreatedBy != null) {
                                                subtable += child.CreatedBy.FullName + '</td><td>';
                                            }
                                            else {
                                                subtable += '</td><td>'
                                            }
                                            if (child.DueTime != null) {

                                                var date = new Date(child.DueTime);
                                                subtable += date.toDateTime() + '</td><td>';
                                            }
                                            else {
                                                subtable += '</td><td>'
                                            }
                                            subtable += '<input class="taskCheck tableTask" ' + (child.Value ? 'checked="checked"' : '') + ' title="Complete the task" type="checkbox" id="task_' + child.Id + '" value="' + child.Id + '">';
                                        });

                                        subtable += '</td></tr></tbody></table></div>';
                                        return subtable;
                                    }
                                    else {
                                        return "";
                                    }
                                }
                            },
                            {
                                "mData": "Id",
                                "oData": "Id",
                                "sType": "numeric",
                                "width": "5%",
                                mRender: function (id) {

                                    return '<a href="' + base_url + '/tasks/show/' + id + '" title="' + Lang.get('labels.see-task') + '">' + id + '</a>';
                                }
                            },
                            {
                                mData: null,
                                'sortable': false,
                                searchable: false,
                                "width": "15%",
                                mRender: function (obj) {
                                    return "<span class='clientName'></span>";
                                }

                            },
                            {
                                "mData": "Title",
                                sType: "string",
                                "sClass": "show-more-container",
                                mRender: function (Title, display, obj) {
                                    return "<a href='" + base_url + "/tasks/show/" + obj.Id + "'>" + Title + "</a>";
                                }
                            },
                            {
                                "mData": "Description",
                                "sClass": "show-more-container",
                                sType: "string",
                                mRender: function (data, display, obj) {

                                    return escapeHtml(data || "");
                                }
                            },
                            {
                                "mData": null, "sType": "string", "oData": "CreatedBy/FullName",
                                mRender: function (data) {
                                    if (data.CreatedBy != null) {
                                        return data.CreatedBy.FullName;
                                    } else {
                                        return "";
                                    }
                                }
                            },
                            {
                                "mData": "StartTime", "sType": "date", mRender: function (data) {
                                if (data == null) {
                                    return "---";
                                }
                                var date = new Date(data);
                                return date.toDateTime();
                            }
                            },
                            {
                                "mData": "DueTime", "sType": "date", mRender: function (data) {
                                if (data == null) {
                                    return "---";
                                }
                                var date = new Date(data);
                                return date.toDateTime();
                            }
                            },
                            {
                                "mData": "Value",
                                sType: "string",
                                "orderable": false,
                                searchable: false,
                                "width": "15%",
                                mRender: function (data, row, task) {
                                    var percentage = taskProgress(task, true);
                                    return '<p class="col-sm-1 text-center progressP" data-task-id="' + a.Id + '" style="font-size: 12px; margin-top: -5px;">' + percentage + '%</p>' +
                                            '<div class="progress col-sm-offset-3">' +
                                            '<div class="progress-bar progress-bar-success progressBar" data-task-id="' + a.Id + '" style="width: ' + percentage + '%"></div>' +
                                            '</div>';
                                }
                            }
                        ],
                        "fnServerData": fnServerOData,
                        "iODataVersion": 4,
                        "bUseODataViaJSONP": false
                    }).on('draw.dt', function () {


                //initiate the more container after the table has loaded
                $('.show-more-container').more({
                    length: 40, ellipsisText: ' ...',
                    moreText: '<i class="fa fa-search-plus"></i>', lessText: '<i class="fa fa-search-minus"></i>'
                });

                // Event listener for opening and closing details
                $(".expanded").hide();
                $('.expand').on('click', function () {
                    $(this).next(".expanded").toggle();
                });
            });


            var table2 = $('#userComplTasksTable').DataTable(
                    {
                        deferLoading: 0,
                        "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                        aaSorting: [[8, "desc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        'filter': "Value eq true and CompletedBy_Id eq '" + userId + "'",
                        "sAjaxSource": api_address + "TaskLists?$expand=CreatedBy($select=FullName),Children($expand=AssignedTo($select=FullName),CreatedBy($select=FullName);$orderby=Value)",
                        "select": "Id,Model,ModelId",
                        "bProcessing": true,
                        "bServerSide": true,
                        "fnRowCallback": function (nRow, aaData) {
                            if (aaData.Value) {
                                $(nRow).addClass('crossed-through');
                            } else {
                                $(nRow).removeClass('crossed-through');
                            }
                            if (aaData.Model && aaData.ModelId) {

                                $.when(getCompanyName(aaData.Model, aaData.ModelId))
                                        .then(function (name) {
                                            var clientName = "View";
                                            if (name.value != 'Undefined')clientName = name.value;

                                            $(nRow).find('.clientName').html("<a href='" + linkToItem(aaData.Model, aaData.ModelId, true) + "' target='_blank'>" + clientName + "</a>");
                                        });
                            }
                        },
                        "aoColumns": [
                            {
                                "mData": null,
                                sType: "string",
                                "orderable": false,
                                searchable: false,
                                sortable: false,
                                mRender: function (obj) {
                                    if (obj.Children.length > 0) {
                                        var subtable = '<span class="expand" style="cursor: pointer"><i class="fa fa-plus-circle"></i> <span>('
                                                + obj.Children.length + ')</span></span>' +
                                                '<div class="table-responsive expanded"><h4>Subtasks</h4><table id="userComplSubTasksTbl" class="table table-condensed table-hover"><thead><tr><th>Title</th>' +
                                                '<th>Description</th>' +
                                                '<th>Assigned to</th>' +
                                                '<th>Created by</th>' +
                                                '<th>Due date</th>' +
                                                '<th>Actions</th>' +
                                                '</tr></thead><tbody>';

                                        $.each(obj.Children, function (index, child) {

                                            var crossedThrough = "";
                                            if (child.Value) {
                                                crossedThrough = 'class="crossed-through"'
                                            }
                                            subtable += '<tr  ' + crossedThrough + '><td>' + child.Title + '</td><td class="show-more-container">';
                                            if (child.Description != null) {
                                                subtable += child.Description + '</td><td>';
                                            }
                                            else {
                                                subtable += '</td><td>'
                                            }

                                            if (child.AssignedTo != null) {
                                                subtable += child.AssignedTo.FullName + '</td><td>';
                                            }
                                            else {
                                                subtable += '</td><td>'
                                            }
                                            if (child.CreatedBy != null) {
                                                subtable += child.CreatedBy.FullName + '</td><td>';
                                            }
                                            else {
                                                subtable += '</td><td>'
                                            }
                                            if (child.DueTime != null) {

                                                var date = new Date(child.DueTime);
                                                subtable += date.toDateTime() + '</td><td>';
                                            }
                                            else {
                                                subtable += '</td><td>'
                                            }
                                            subtable += '<input class="taskCheck tableTask" ' + (child.Value ? 'checked="checked"' : '') + ' title="Complete the task" type="checkbox" id="task_' + child.Id + '" value="' + child.Id + '">';
                                        });

                                        subtable += '</td></tr></tbody></table></div>';
                                        return subtable;
                                    }
                                    else {
                                        return "";
                                    }
                                }
                            },
                            {
                                "mData": "Id",
                                "oData": "Id",
                                "sType": "numeric",
                                "width": "5%",
                                mRender: function (id) {

                                    return '<a href="' + base_url + '/tasks/show/' + id + '" title="' + Lang.get('labels.see-task') + '">' + id + '</a>';
                                }
                            },
                            {
                                mData: null, 'sortable': false,
                                "width": "25%",
                                searchable: false, mRender: function (obj) {
                                return "<span class='clientName'></span>";
                            }
                            },
                            {
                                "mData": "Title", sType: "string", "sClass": "show-more-container",
                                mRender: function (Title, display, obj) {
                                    return "<a href='" + base_url + "/tasks/show/" + obj.Id + "'>" + Title + "</a>";
                                }
                            },
                            {
                                "mData": "Description",
                                "sClass": "show-more-container",
                                sType: "string",
                                mRender: function (data, display, obj) {

                                    return escapeHtml(data || "");
                                }
                            },
                            {
                                "mData": null, "sType": "string", "oData": "CreatedBy/FullName",
                                mRender: function (data) {
                                    if (data.CreatedBy != null) {
                                        return data.CreatedBy.FullName;
                                    } else {
                                        return "";
                                    }
                                }
                            },
                            {
                                "mData": "StartTime", "sType": "date", mRender: function (data) {
                                if (data == null) {
                                    return "---";
                                }
                                var date = new Date(data);
                                return date.toDateTime();
                            }
                            },
                            {
                                "mData": "DueTime", "sType": "date", mRender: function (data) {
                                if (data == null) {
                                    return "---";
                                }
                                var date = new Date(data);
                                return date.toDateTime();
                            }
                            },
                            {
                                "mData": "EndTime", "sType": "date", mRender: function (data) {
                                if (data == null) {
                                    return "---";
                                }
                                var date = new Date(data);
                                return date.toDateTime();
                            }
                            }
                        ],
                        "fnServerData": fnServerOData,
                        "iODataVersion": 4,
                        "bUseODataViaJSONP": false
                    }).on('draw.dt', function () {

                //initiate the more container after the table has loaded
                $('.show-more-container').more({
                    length: 40, ellipsisText: ' ...',
                    moreText: '<i class="fa fa-search-plus"></i>', lessText: '<i class="fa fa-search-minus"></i>'
                });

                // Event listener for opening and closing details
                $(".expanded").hide();
                $('.expand').on('click', function () {
                    $(this).next(".expanded").toggle();
                });
            });

            var dateFilter = '';
            var dateRangePicker = $('#dateFilter');

            //initialize date range filter
            dateRangePicker.daterangepicker({
                maxDate: moment(),
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });
            dateRangePicker.on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
            var settings = table2.settings();
            var originalFilter = settings[0].oInit.filter;
            dateRangePicker.on('apply.daterangepicker', function (event, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                event.preventDefault();
                dateFilter = '(date(EndTime) ge ' + picker.startDate.format('YYYY-MM-DD') + ' and date(EndTime) le ' + picker.endDate.format('YYYY-MM-DD') + ')';
                settings[0].oInit.filter = dateFilter + 'and ' + originalFilter;
                table2.draw();
            });


            $('#timelogsTab').on('click', function () {
                getTimelogsStats();
            });

            var date = moment().format("YYYY-MM-DD");
            var filters = "date(CheckIn) eq " + date;
            var timeLogsDatePicker = $('#timeLogsFilter');

            //initialize date range filter
            timeLogsDatePicker.daterangepicker({
                maxDate: moment(),
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });
            timeLogsDatePicker.on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });

            timeLogsDatePicker.on('apply.daterangepicker', function (event, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                event.preventDefault();
                filters = '(date(CheckIn) ge ' + picker.startDate.format('YYYY-MM-DD') + ' and date(CheckIn) le ' + picker.endDate.format('YYYY-MM-DD') + ')';
                getTimelogsStats();
            });
            //format time in milliseconds. Add low point to calculate difference in days and get initial date
            function formatTime(datetime, lowPoint){
                var year = 2000;
                var month = 0;
                var date = 1;
                if(+moment(lowPoint) < +moment(datetime)){
                    var diff = moment(datetime).dayOfYear() - moment(lowPoint).dayOfYear();
                    return +moment(datetime).year(year).month(month).date(date).add(1, 'hour').add(diff, 'day');
                }else {
                    return +moment(datetime).year(year).month(month).date(date).add(1, 'hour');
                }
            }

            //get the time logs
            function getTimelogsStats() {
                $.get(api_address + 'TimeRegistrations?$filter=User_Id eq \'' + userId + "' and " + filters + "&$select=CheckIn,CheckOut,Status&$expand=BreakRegistration($select=StartTime,EndTime)")
                        .success(function (data) {
                            var timeRegs = [];
                            var categories = [];
                            var x = 0;
                            //create a time registration to be shown in the statistics for each log
                            $.each(data.value, function (index, el) {
                                var low = 0;
                                var high = 0;
                                var status = "";
                                var color = "";
                                categories[0] = moment(data.value[0].CheckIn).format('DD-MM-YYYY');

                                //check if there is more than one day in the data logs
                                if (index > 1 && moment(el.CheckIn).format('YYYY-MM-DD') != moment(data.value[index - 1].CheckIn).format('YYYY-MM-DD')) {
                                    //add the log to a new chart category
                                    x = x + 1;
                                    categories.push(moment(el.CheckIn).format('DD-MM-YYYY'));
                                }

                                //add low point to the time log
                                low = formatTime(el.CheckIn);

                                //add high point to the time log
                                if (el.CheckOut) {
                                    high = formatTime(el.CheckOut, el.CheckIn);
                                    status = "CheckedIn";
                                } else {
                                    status = el.Status;
                                    if (data.value[index + 1]) {
                                        high = formatTime(data.value[index + 1].CheckIn, el.CheckIn);
                                    } else {
                                        high = formatTime(Date.now(), el.CheckIn);
                                    }
                                }
                                //add the time registration before the break so that the break is visible on top
                                if (low > 0 && high > 0 && status) {
                                    //apply colors
                                    if (status == "CheckedOut") {
                                        color = "#ff8080";
                                    } else if (status == "CheckedIn") {
                                        color = "#79d279";
                                    } else if (status == "Break") {
                                        color = "#ffc266";
                                    } else {
                                        color = "#7094db";
                                    }
                                    var isExist = false;
                                    for(var i = 0; i < timeRegs.length; i++){
                                        if (timeRegs[i].name == status){
                                            timeRegs[i].data.push({
                                                x: x,
                                                low: low,
                                                high: high});
                                            isExist = true;
                                            break;
                                        }
                                    }
                                    if(!isExist) {
                                        timeRegs.push({
                                            name: status, color: color, data: [{
                                                x: x,
                                                low: low,
                                                high: high
                                            }]
                                        });
                                    }
                                }
                                //add break logs if any
                                if (el.BreakRegistration.length > 0) {

                                    var breakLow = [];
                                    var breakHigh = [];

                                    $.each(el.BreakRegistration, function (indexBreak, elBreak) {
                                        breakLow[indexBreak] = formatTime(elBreak.StartTime);
                                        //add high point to the break log
                                        if (elBreak.EndTime) {
                                            breakHigh[indexBreak] = formatTime(elBreak.EndTime, elBreak.StartTime);
                                        } else {
                                            breakHigh[indexBreak] = formatTime(Date.now(), elBreak.StartTime);
                                        }
                                        //add the current break time to the time registrations
                                        if (breakLow) {
                                            //find a previous time reg with the same status and save the data into it
                                            for(var i = 0; i < timeRegs.length; i++){
                                                if (timeRegs[i].name == "Break"){
                                                    timeRegs[i].data.push({
                                                        x: x,
                                                        low: breakLow[indexBreak],
                                                        high: breakHigh[indexBreak]});
                                                    return;
                                                }else{

                                                }
                                            }
                                            timeRegs.push({
                                                name: "Break", color: "#ffc266", data: [{
                                                    x: x,
                                                    low: breakLow[indexBreak],
                                                    high: breakHigh[indexBreak]
                                                }]
                                            });
                                        }
                                    });
                                }
                            });

                            //console.log(data);
                            //console.log(timeRegs);

                            //initiate highchart
                            $('#timeChart').highcharts({
                                chart: {
                                    type: 'columnrange',
                                    inverted: true
                                },
                                legend: {
                                    labelFormatter: function () {
                                        //format statuses' labels
                                        if (this.name == "CheckedIn") {
                                            return "Checked in";
                                        } else if (this.name == "CheckedOut") {
                                            return "Checked out";
                                        } else {
                                            return this.name;
                                        }
                                    }
                                },
                                title: {
                                    text: 'Time registrations'
                                },
                                xAxis: {
                                    categories: categories
                                },
                                yAxis: {
                                    type: 'datetime',
                                    dateTimeLabelFormats: {
                                        day: '+1 day'
                                    },
                                    title: {
                                        text: null
                                    }
                                },
                                credits: {
                                    enabled: false
                                },
                                tooltip: {
                                    formatter: function () {
                                        return moment(this.point.low).subtract(1, 'hour').format('HH:mm:ss') +
                                                ' - ' + moment(this.point.high).subtract(1, 'hour').format('HH:mm:ss') + ': <b>' + this.series.name;
                                    }
                                },
                                plotOptions: {
                                    columnrange: {
                                        grouping: false
                                    }
                                },
                                //add timeRegs array to the chart
                                series: timeRegs
                            });
                        });
            }
        });
    </script>
@stop
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <i class="fa fa-info"></i> @lang('labels.user-info')
                    <div class="options">
                        <a href="{{url('users/edit',$user->Id)}}"><i class="fa fa-pencil"></i></a>
                        <a href="{{url('users/changePassword',$user->Id)}}"><i class="fa fa-lock"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-condensed">
                                    <h3><strong>{{ $user->FullName }}</strong></h3>
                                    <p class="hidden" id="userId">{{$user->Id}}</p>
                                    <tbody>

                                    <tr>
                                        <td>@lang('labels.name') </td>
                                        <td>
                                            {{$user->FullName or "--"}}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Username</td>
                                        <td> {{$user->UserName }}</td>
                                    </tr>

                                    <tr>
                                        <td>Email</td>
                                        <td>
                                            {{$user->Email or '--'}}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Employee local number</td>
                                        <td>
                                            {{$user->EmployeeLocalNumber or '--'}}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>My fone username</td>
                                        <td>
                                            {{$user->MyFoneUserName or '--'}}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Device</td>
                                        <td>
                                            {{$user->Device or '--'}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>@lang('labels.title') </td>
                                        <td>
                                            {{$user->Title->Name or '--'}}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>@lang('labels.salary-group') </td>
                                        <td>
                                            {{$user->SalaryGroup->Name or '--'}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td> Active</td>
                                        <td>
                                            @if($user->Active) Yes
                                            @else No
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Birth date</td>
                                        <td>
                                            @if($user->Birthdate != null)
                                                {{date('d-m-Y',strtotime($user->Birthdate))}}
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Address</td>
                                        <td>
                                            {{$user->Address or '--'}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Private phone</td>
                                        <td>
                                            {{$user->PrivatePhone or '--'}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Nearest relatives</td>
                                        <td>
                                            {{$user->NearestRelatives or '--'}}
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if(!empty($similarUsers))
                            <div class="col-md-6">
                                <h3 id="similarUsers">Similar users</h3>
                                @foreach($similarUsers as $roleName=>$users)
                                    @if(count($users) > 1)
                                        <div class="col-md-3">
                                            <h4>{{$roleName}}</h4>
                                            <ul style=" margin-left: -30px;">
                                                @foreach($users as $id=>$name)
                                                    @if($id != $user->Id)
                                                        <li><a href="{{url('users/show',$id)}}">{{$name}}</a></li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="tab-container tab-success">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#assignedTasks" data-toggle="tab">Assigned Tasks</a>
                                    </li>
                                    <li class=""><a href="#completedTasks" data-toggle="tab">Completed tasks</a></li>
                                    <li class=""><a href="#timelogs" id="timelogsTab" data-toggle="tab">Time logs</a>
                                    </li>
                                </ul>
                                <div class="tab-content">

                                    <div class="tab-pane clearfix active" id="assignedTasks">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-condensed" id="userTasksTable">
                                                <thead>
                                                <tr>
                                                    <th style="width: 70px;"></th>
                                                    <th>Id</th>
                                                    <th>Client</th>
                                                    <th>@lang('labels.title')</th>
                                                    <th>@lang('labels.description')</th>
                                                    <th>@lang('labels.created-by')</th>
                                                    <th>@lang('labels.start-date')</th>
                                                    <th>@lang('labels.due-date')</th>
                                                    <th>Progress</th>
                                                </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="tab-pane clearfix" id="completedTasks">
                                        </br>
                                        <div class="row">
                                            <div class="col-md-3 col-xs-12">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i
                                                                class="fa fa-calendar"></i></span>
                                                    <input name="time" placeholder="Pick a date range" type="text"
                                                           class="form-control " id="dateFilter"
                                                           style="background-color: white;cursor: pointer;"
                                                           readonly="readonly">
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="table-responsive ">
                                                    <table class="table table-striped table-condensed datatables"
                                                           id="userComplTasksTable" style="width: 100%;">
                                                        <thead>
                                                        <tr>
                                                            <th style="width: 70px;"></th>
                                                            <th>Id</th>
                                                            <th>Client</th>
                                                            <th>@lang('labels.title')</th>
                                                            <th>@lang('labels.description')</th>
                                                            <th>@lang('labels.created-by')</th>
                                                            <th>@lang('labels.start-date')</th>
                                                            <th>@lang('labels.due-date')</th>
                                                            <th>Completed date</th>
                                                        </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane clearfix" id="timelogs">
                                        <div class="row">
                                            <div class="col-md-3 col-xs-12">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i
                                                                class="fa fa-calendar"></i></span>
                                                    <input name="time" placeholder="Pick a date range" type="text"
                                                           class="form-control " id="timeLogsFilter"
                                                           style="background-color: white;cursor: pointer;"
                                                           readonly="readonly">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12" id="timeChart"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
