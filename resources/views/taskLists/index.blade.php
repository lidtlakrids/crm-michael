@extends('layout.main')
@section('page-title',Lang::get('labels.tasks'))
@section('styles')
    <style>
        .expanded {
            margin-right: -500px;
            margin-left: 30px;
            margin-top: 20px;
        }

        .expanded th {
            min-width: 70px;
        }

        table#subTasksTbl {
            background-color: transparent;
        }

        table#subTasksTbl tbody tr {
            background-color: transparent;
        }

        table#subTasksTbl thead tr {
            background-color: transparent;
        }

        table#subTasksTbl tr:nth-child(even) {
            background-color: transparent;
        }
    </style>
@stop
@section('scripts')
    @include('scripts.dataTablesScripts')
    <script>
        $(document).ready(function () {
            var customFilters = "";
//        var userId = $('#user-Id').val();
//        var admin = isInArray('Administrator',roles) || isInArray('Developer',roles);
//
//        if(!admin){
//           customFilters += ' and (AssignedTo_Id eq \''+userId+'\' or CreatedBy_Id eq \''+userId+'\')';
//        }
//
            var filters = $('.taskFilters');

            //check if there are already selected filters / for example if we refresh the page
            var currentFilters = filters.filter(function () {
                return this.value;
            });
             tasksTable = $('.datatables').DataTable(
                    {
                        deferLoading: currentFilters.length > 0 ? 0 : null,
                        responsive: true,
                        "lengthMenu": [[20, 50, 100], [20, 50, 100]],
                        "oLanguage": {
                            "sSearch":       "",
                            "sSearchPlaceholder": Lang.get('labels.search')
                        },
                        aaSorting: [[10, "desc"]], // shows the newest items first
                        "sPaginationType": "full_numbers",
                        'filter': "Created ne null " + customFilters,
                        "sAjaxSource": api_address + "TaskLists?$expand=AssignedTo($select=FullName),CreatedBy($select=FullName),Children($expand=AssignedTo($select=FullName),CreatedBy($select=FullName);$orderby=Value)",
                        "select":"Id",
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
                                                '<div class="table-responsive expanded"><h4>Subtasks</h4><table id="subTasksTbl" class="table table-condensed table-hover"><thead><tr><th>Title</th>' +
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

                                    return '<a href="tasks/show/' + id + '" title="' + Lang.get('labels.see-task') + '">' + id + '</a>';
                                }
                            },
                            {
                                mData: null, 'sortable': false, searchable: false, mRender: function (obj) {
                                return "<span class='clientName'></span>";
                            }

                            },
                            {
                                "mData": "Title", sType: "string", mRender: function (Title, display, obj) {
                                return "<a href='"+base_url+"/tasks/show/"+obj.Id+"'>"+Title+"</a>";
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
                                "mData": "Model", sType: "string"

                            },
                            {
                                "mData": "ModelId", sType:"numeric"
                            },
                            {
                                "mData": null, "sType": "string", "oData": "AssignedTo/FullName",
                                mRender: function (data) {
                                    if (data.AssignedTo != null) {
                                        return data.AssignedTo.FullName;
                                    } else {
                                        return "";
                                    }
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
//                            {
//                                mData: "SortOrder",searchable:false
//                            },
                            {
                                "mData": "Value",
                                sType: "date",
                                "orderable": false,
                                searchable: false,
                                mRender: function (data, row, a) {
                                    var checked = (a.Value == true) ? "checked" : null;
                                    var checkbox = '<input ' + checked + ' id="task_' + a.Id + '" value="' + a.Id + '" class="pull-left taskCheck tableTask" type="checkbox" style="margin-right:5px;">';
                                    return ' / <span title="Edit the sub task" class="pseudolink"><i class="fa fa-pencil quickEditSubTask"></i></span>' + checkbox;
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

            //Calculate tasks due today and overdue
            function tasksDueToday() {
                var today = moment().format('YYYY-MM-DD');
                var assigned = $('#AssignedFilter').val();
                var userQuery = assigned == '' ? "" : ' and '+assigned;

                return $.get(api_address + "TaskLists/$count?$filter=(date(DueTime) eq " + today + " and DueTime ne null) and Value eq false"+userQuery);
            }

            function tasksOverdue() {
                var today = moment().format('YYYY-MM-DD');
                var assigned = $('#AssignedFilter').val();
                var userQuery = assigned == '' ? "" : ' and ' + assigned;

                return $.get(api_address + "TaskLists/$count?$filter=(date(DueTime) lt " + today + " and DueTime ne null) and Value eq false"+userQuery);
            }

            function updateDueToday(){
                $.when(tasksDueToday()).then(function (data) {
                    $("#tasksDueToday").text(data);
                    if (data > 0) {
                        $("#tasksDueToday").addClass("badge-warning");
                    }
                    else {
                        $("#tasksDueToday").removeClass("badge-warning");
                    }
                })
            }

            function updateOverDue(){
            $.when(tasksOverdue()).then(function (data) {
                $("#tasksOverdue").text(data);
                if (data > 0) {
                    $("#tasksOverdue").addClass("badge-danger");
                }
                else {
                    $("#tasksOverdue").removeClass("badge-danger");
                }
            })
            }

            updateDueToday();
            updateOverDue();

            // get original filters
            var settings = tasksTable.settings();
            var originalFilter = settings[0].oInit.filter;

            if (currentFilters.length > 0) {
                var oldFilters = $.map(currentFilters, function (obj) {
                    return $(obj).val();
                });

                customFilters = oldFilters.join(' and ');
                // add the extra filters to the existing ones
                settings[0].oInit.filter += " and " + customFilters;
                tasksTable.draw();
            }

            //if a contract filter is clicked, apply a corresponding string
            filters.on('change', function (event) {
               updateFilters();
            });

            $('.tasksDueToday').on('click',function (event) {
                $('#StatusFilter option:contains("Due today")').prop('selected',true);
                updateFilters();
            });

            $('.overdueTasks').on('click', function (event) {
                $('#StatusFilter option:contains("Overdue")').prop('selected', true);
                updateFilters();
            });
            
            function updateFilters() {

                settings[0].oInit.filter = originalFilter;
                customFilters = '';//clear old filters
                var newFilters = $.map(filters.toArray(), function (obj) {
                    if ($(obj).val() != "") {
                        return $(obj).val();
                    }
                });

                customFilters = newFilters.join(' and ');
                // add the extra filters to the existing ones
                if (customFilters.length > 0) {
                    settings[0].oInit.filter += " and " + customFilters;
                }
                //redraw the table
                tasksTable.draw();
                updateDueToday();
                updateOverDue();
                return false;
            }

        });

    </script>
@stop

@section('content')
    {!! Form::hidden('Model','TaskList',['id'=>'Model']) !!}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-tasks"></i> @lang('labels.tasks')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="{{ url('/tasks/create') }}" title="@lang('labels.create-task')"><i
                                    class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="panel-body collapse in">
                    <div class="row">
                        <div class="col-md-4 col-xs-12">
                            <a href="#" style="color: black" class="tasksDueToday">
                                <span class="list-group-item col-sm-6"><span class="badge" id="tasksDueToday"></span>Tasks due today</span>
                            </a>
                            <a href="#" style="color: black" class="overdueTasks">
                                <span class="list-group-item col-sm-6"><span class="badge" id="tasksOverdue"></span>Overdue tasks</span>
                            </a>
                            </br>
                        </div>
                        <div class="col-md-8 col-xs-12 text-right">
                        <div class="form-inline">
                            {!! Form::select('CreatedBy_Id',withEmpty($creators,Lang::get('labels.select-creator')),null,['class'=>'form-control taskFilters']) !!}
                            <?php $id = Auth::user()->externalId; ?>
                            {!! Form::select('AssignedTo_Id',withEmpty($users,Lang::get('labels.select-assigned')),"((ParentTaskListId eq null and AssignedTo_Id eq '$id') or (ParentTaskListId ne null and AssignedTo_Id eq '$id' and Parent/AssignedTo_Id ne '$id' and Parent/Value eq false))",['class'=>'form-control taskFilters','id'=>'AssignedFilter']) !!}
                            {!! Form::select('Created',withEmpty($taskDates,Lang::get('labels.select-period')),null,['class'=>'form-control taskFilters','id'=>'CreatedFilter']) !!}
                            {!! Form::select('Status',withEmpty($taskStatus,Lang::get('labels.select-status')),"Value eq false",['class'=>'form-control taskFilters', 'id'=>'StatusFilter']) !!}
                        </div>
                        </div>
                    </div>
                    <hr>
                    <div class="table-responsive">
                        <table cellpadding="0" cellspacing="0" border="0" width="100%"
                               class="table datatables" id="table-list">
                            <thead>
                            <tr>
                                <th style="width: 70px;"></th>
                                <th>@lang('labels.number')</th>
                                <th>Client</th>
                                <th>@lang('labels.title')</th>
                                <th>@lang('labels.description')</th>
                                <th>@lang('labels.item')</th>
                                <th>@lang('labels.item-nr')</th>
                                <th>@lang('labels.assigned-to')</th>
                                <th>@lang('labels.created-by')</th>
                                <th>@lang('labels.start-date')</th>
                                <th>@lang('labels.due-date')</th>
                                {{--<th>Priority</th>--}}
                                <th>@lang('labels.actions')</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop