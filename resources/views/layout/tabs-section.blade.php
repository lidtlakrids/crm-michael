<div class="tab-container tab-sky">
    <ul class="nav nav-tabs" id="myTab">
        <li class="active"><a href="#comments" data-toggle="tab"><i class="fa fa-comments"></i> @lang('labels.comments')
            </a></li>
        @if(isset($seo))
            <li class=""><a href="#seo" data-toggle="tab"><i class="fa fa-search"></i> SEO comments</a></li>@endif
        @if(isset($orders))
            <li class="loadOrders"><a href="#orders" data-toggle="tab"><i
                            class="fa fa-reorder"></i> @lang('labels.orders')</a></li>@endif
        @if(isset($contracts))
            <li class=""><a href="#contracts" data-toggle="tab"><i class="fa fa-file"></i> @lang('labels.contracts')</a>
            </li>@endif
        @if(isset($invoices))
            <li><a href="#invoices" data-toggle="tab"><i
                            class="fa fa-barcode"></i> @lang('labels.invoices')</a></li>@endif
        @if(isset($drafts) && isAllowed('drafts','get'))
            <li><a href="#draftsTab" class="loadDraftsTab" data-toggle="tab"><i class="fa fa-clock-o"></i> Drafts</a>
            </li>@endif
        @if(isset($information))
            <li class=""><a href="#information" data-toggle="tab"><i
                            class="fa fa-info-circle"></i> @lang('labels.information')</a></li>@endif
        @if(isset($contacts))
            <li><a href="#contacts" class="loadContactsTab" data-toggle="tab"><i class="fa fa-group"></i> @lang('labels.contacts')</a>
            </li>@endif
        @if(isset($appointments))
            <li class=""><a href="#appointments" data-toggle="tab"><i
                            class="fa fa-calendar"></i> @lang('labels.appointments')</a></li>@endif
        @if(isset($progress))
            <li class=""><a href="#progress" data-toggle="tab"><i class="fa fa-tachometer"></i> @lang('labels.progress')
                </a></li>@endif
        @if(isset($timeline))
            <li class="timelineLoad"><a href="#timeline" data-toggle="tab"><i class="fa fa-clock-o"></i> @lang('labels.timeline')
                </a></li>@endif
        @if(isset($files))
            <li class=""><a href="#files" data-toggle="tab"><i class="fa fa-files-o"></i> @lang('labels.files')</a>
            </li>@endif
        @if(inRole('Administrator') || isAllowed('clientAlias','move'))
            <li class=""><a href="#admin" data-toggle="tab"><i class="fa fa-lock"></i> @lang('labels.admin')</a>
            </li>
        @endif
        @if(isset($checklist))
            <li class=""><a href="#checklist" data-toggle="tab"><i class="fa fa-check"></i> @lang('labels.checklist')
                </a></li>@endif
        @if(isset($clientLogins) && isAllowed('clientLogins','get'))
            <li class=""><a href="#clientLogins" data-toggle="tab"><i class="fa fa-lock"></i> Client Logins</a>
            </li>@endif
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="comments">
            <div class="col-md-6">
                <div class="panel">
                    <form id="newCommentForm">
                        <div class="input-group">
                            <textarea cols="1" name="Message" required="required" class="form-control autosize" style="overflow: hidden; overflow-wrap: break-word; resize: horizontal;"></textarea>
                            <span class="input-group-btn">
                                   <button type="submit" class="btn btn-primary"><i class="fa fa-comments"></i></button>
		                       </span>
                        </div>
                    </form>
                    <div class="row">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="form-inline">
                                    <div class="checkbox" style="font-size: 11px;">
                                        <label for="commentTypeChange">@lang('labels.type')</label>
                                        <select id="commentTypeChange" data-relations="asdad">
                                            <option value="all">All</option>
                                            <option value="own">Own</option>
                                        </select>
                                        {{--<label>--}}
                                        {{--<input class="" id="showHiddenComments" type="checkbox">--}}
                                        {{--@lang('labels.show-hidden-comments')--}}
                                        {{--</label>--}}
                                    </div>
                                    <div class="checkbox" style="font-size: 11px;">
                                        <label for="commentSortingChange">@lang('labels.sort-order')</label>
                                        <select id="commentSortingChange">
                                            <option value="desc" selected="selected">@lang('labels.newest')</option>
                                            <option value="asc">@lang('labels.oldest')</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br/>
                    <ul class="panel-comments"></ul>
                </div>
            </div>
        </div>

        @if(isset($orders))
            <div class="tab-pane clearfix" id="orders">
                @if($orders)
                    <div class="table-responsive">

                    </div>
                @else
                    <div class="col-xs-3">
                        <a class="btn btn-orders"
                           href="{{url('orders/show',$orders->Id)}}">@lang('labels.see-original-order')</a>
                        <a class="btn btn-orders"
                           href="{{url('orders/information',$contractId)}}">@lang('labels.get-information')</a>
                    </div>
                @endif
            </div>
        @endif

        @if(isset($contracts))
            <div class="tab-pane clearfix" id="contracts">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="table-responsive">
                            <table class="table table-condensed table-hover datatables" id="table-list">
                                <thead>
                                <tr>
                                    <th>@lang('labels.number')</th>
                                    <th>@lang('labels.product')</th>
                                    <th>Homepage</th>
                                    <th>@lang('labels.country')</th>
                                    <th>@lang('labels.status')</th>
                                    <th>@lang('labels.start-date')</th>
                                    <th>@lang('labels.end-date')</th>
                                    <th>@lang('labels.assigned-to')</th>
                                    <th>@lang('labels.order')</th>
                                    <th>Information</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(is_array($contracts))
                                    <?php $contractsValue = 0;  ?>
                                    @foreach($contracts as $c)
                                        <?php
                                        if($c->ProductPackage_Id != null and $c->Parent_Id != null){ continue;}
                                        if ($c->Status == "Active") {
                                            $contractsValue += (isset($c->Product->SalePrice) ? $c->Product->SalePrice : 0);
                                        }
                                        ?>
                                        <tr>
                                            <td><a href="{{url('contracts/show',$c->Id)}}">{{$c->Id or "-"}}</a></td>
                                            <td>{{$c->Product->Name or "-"}}</td>
                                            <td>{{$c->Domain or $information->Homepage}}</td>
                                            <td>{{$c->Country->CountryCode or "-"}}</td>
                                            <td>{{$c->Status or "-"}}</td>
                                            <td>
                                                @if($c->StartDate != null)
                                                    {{date("d-m-Y",strtotime($c->StartDate))}}
                                                @endif
                                            </td>
                                            <td>
                                                @if($c->EndDate != null)
                                                    {{date("d-m-Y",strtotime($c->EndDate))}}
                                                @endif
                                            </td>

                                            <td>{{$c->Manager->FullName or "-"}}</td>
                                            <td>
                                                @if($c->OriginalOrder_Id != null)
                                                    <a href="{{url('orders/show',$c->OriginalOrder_Id)}}">{{$c->OriginalOrder_Id}}</a>
                                                @endif
                                            </td>
                                            <td>
                                                @if($c->NeedInformation)
                                                    <a target="_blank" href="{{url('orders/information',$c->Id)}}">Get
                                                        Info.</a>
                                                @else
                                                    Does not need information.
                                                @endif
                                            </td>

                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" id="contractsValue" value="{{$contractsValue}}">
                    </div>
                </div>
            </div>
        @endif

        @if(isset($invoices))
            <div class="tab-pane clearfix" id="invoices">
                <div class="table-responsive">
                    <table class="table table-condensed table-responsive">
                        <thead>
                        <tr>
                            <th>@lang('labels.invoice-number')</th>
                            <th>@lang('labels.name')</th>
                            <th>@lang('labels.created-date')</th>
                            <th>@lang('labels.due-date')</th>
                            <th>@lang('labels.paid')</th>
                            <th>@lang('labels.status')</th>
                            <th>@lang('labels.type')</th>
                            <th>@lang('labels.address')</th>
                            <th>@lang('labels.total-net-amount')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(is_array($invoices))
                            <?php $invoices = array_reverse($invoices);  ?>

                            @foreach($invoices as $inv)

                                <tr>
                                    <td><a href="{{url('invoices/show',$inv->Id)}}">{{$inv->InvoiceNumber or "-"}}</a>
                                    </td>
                                    <td>{{$inv->Name or "-"}}</td>
                                    <td>
                                        @if($inv->Created != null)
                                            {{date("d-m-Y",strtotime($inv->Created))}}
                                        @endif
                                    </td>
                                    <td>
                                        @if($inv->Due != null)
                                            {{date("d-m-Y",strtotime($inv->Due))}}
                                        @endif
                                    </td>
                                    <td>
                                        @if($inv->Payed != null)
                                            {{date("d-m-Y",strtotime($inv->Payed))}}
                                        @endif
                                    </td>
                                    <td>
                                        <strong style="color: @if($inv->Status == "Paid") green @else red @endif"
                                                class="@if($inv->Status == "Overdue")overdueInvoice @endif">
                                            {{$inv->Status or Lang::get('labels.unknown')}}
                                        </strong>
                                    </td>
                                    <td>{{$inv->Type}}</td>
                                    <td>{{$inv->Address}} , {{$inv->ZipCode}} {{$inv->City}}</td>
                                    <td>{{formatMoney($inv->NetAmount)}}</td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if(isset($drafts) && isAllowed('drafts','get'))
            <div class="tab-pane clearfix" id="draftsTab">
                <div class="table-responsive">

                </div>
            </div>
        @endif


        {{-- START Appointments --}}
        @if(isset($appointments))
            <div class="tab-pane clearfix" id="appointments">
                <form id='createAppointment' class="form-horizontal">
                    <div class="col-md-6">
                        <div class="form-group clearfix">
                            <label for="event-Type"
                                   class="col-md-2 control-label">@lang('labels.event-type')</label>
                            <div class="col-md-8">
                                <select name="EventType" id="event-Type" class="form-control" required="required"
                                        data-options="EventTypes">
                                    <option value="">@lang('labels.select-event-type')</option>
                                    <option value="Appointment" @if(isset($appointmentInfo['Type'])) selected="selected" @endif>Appointment</option>
                                    <option value="HealthCheck">Health check</option>
                                    <option value="ClosingCall">Closing Call</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <label for="appointment-Summary"
                                   class="col-md-2 control-label">Title</label>
                            <div class="col-md-8">
                                <input name="Summary" id="appointment-Summary" type="text" class="form-control"
                                       required="required" value="{{$appointmentInfo['Summary'] or null}}">
                            </div>
                        </div>

                        <div class="form-group clearfix">
                            <label for="appointment-Description"
                                   class="col-md-2 control-label">@lang('labels.description')</label>
                            <div class="col-md-8">
                                <textarea name="Description" id="appointment-Description" cols="50" rows="4"
                                          class="form-control" required="required">{{$appointmentInfo['Description'] or null}}</textarea>
                            </div>
                        </div>

                        <div class="form-group clearfix">
                            <label class="col-md-2 control-label">@lang('labels.for-who')</label>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <label for="userSearch_appointments" class="input-group-addon"><i class="fa fa-search"></i></label>
                                    <input id="userSearch_appointments" value="{{Auth::user()->fullName}} ({{Auth::user()->userName}})" required="required" class="form-control"
                                           placeholder="@lang('labels.search-user')">
                                    <input name="User_Id" type="hidden" value="{{Auth::user()->externalId}}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <label class="col-md-2 control-label">@lang('labels.appointment-time')</label>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <label for="appointment-Start" class="input-group-addon"><i
                                                class="fa fa-calendar"></i></label>
                                    <input type="text" required="required" name="Start" class="form-control"
                                           id="appointment-Start" autocomplete="off">
                                </div>
                            </div>
                        </div>

                        {{--<div class="form-group clearfix" id="appointment-Attendees">--}}
                            {{--<div class="form-group clearfix">--}}
                                {{--<label for="add-attendee-field"--}}
                                       {{--class="col-md-3 control-label"><strong>@lang('labels.add-attendee')</strong></label>--}}
                                {{--<div class="col-md-6">--}}
                                    {{--<input id="add-attendee-field" type="email" class="form-control" autocomplete="off"--}}
                                           {{--placeholder="Type e-mail">--}}
                                {{--</div>--}}
                                {{--<div class="col-md-2">--}}
                                    {{--<button class="btn btn-orange form-control"--}}
                                            {{--id="addAttendeeToEvent">@lang('labels.add')</button>--}}
                                {{--</div>--}}
                            {{--</div>--}}

                            {{--@if(isset($appointmentEmail) && $appointmentEmail != null && validateEmail($appointmentEmail))--}}
                                {{--<div class="form-group">--}}
                                    {{--<div class="col-md-8 col-md-offset-3">--}}
                                        {{--<div class="checkbox block">--}}
                                            {{--<label>--}}
                                                {{--<input checked="" value="{{$appointmentEmail or ''}}"--}}
                                                       {{--name="Attendees[][EMail]" type="checkbox">--}}
                                                {{--{{$appointmentEmail or "--"}}--}}
                                            {{--</label>--}}
                                        {{--</div>--}}
                                        {{--<hr/>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                            {{--@endif--}}
                        {{--</div>--}}

                        <div class="form-group clearfix">
                            <label class="col-sm-2 control-label">@lang('labels.add-attendee')</label>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <label for="tokenfield-email" class="input-group-addon"><i class="fa fa-envelope"></i></label>
                                    <input type="text" class="form-control" id="tokenfield-email" value="@if(isset($appointmentEmail) && $appointmentEmail != null && validateEmail($appointmentEmail)) {{$appointmentEmail or ''}} @endif" />
                                </div>
                            </div>
                        </div>

                        <div class="form-group clearfix">
                            <label for="checkbox" class="col-sm-2 control-label">Options</label>
                            <div class="col-md-8">
                                <div class="checkbox block">
                                    <label for="appointment-NotifyAttendees">
                                        <input type="checkbox" name="NotifyAttendees" id="appointment-NotifyAttendees"
                                               checked="checked" class=""/>
                                        @lang('labels.notify-attendees')
                                    </label>
                                </div>
                                <div class="checkbox block">
                                    <label for="appointment-CreateOnGoogleCalendar">
                                        <input type="checkbox" name="CreateOnGoogleCalendar"
                                               id="appointment-CreateOnGoogleCalendar" checked="checked" class=""
                                               style=""/>
                                        @lang('labels.create-on-google')
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group clearfix">
                            <div class="col-md-12 responsive-iframe-container" id="calendarIFrame"></div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-md-6 col-md-offset-1">
                                <button class="btn btn-orange" type="submit" title="@lang('labels.create-appointment')">@lang('labels.create-appointment')</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif
        {{-- END Appointments --}}
        @if(isset($contacts))
            <div class="tab-pane clearfix" id="contacts">
                <input type="hidden" id="ContactsClientAliasId" value="{{$contacts}}">
                @if(isAllowed('contacts','post'))
                    <div class="col-md-4" id="contactFormPlaceholder">

                    </div>
                @endif
                <div class="col-md-8">
                    <div class="panel">
                        <h4><i class="fa fa-group"></i> @lang('labels.contact-persons')</h4>
                        <div class="table-responsive">
                            <table class="table table-hover table-list" id="contacts-table-tab" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>@lang('labels.name')</th>
                                        <th>@lang('labels.phone')</th>
                                        <th>@lang('labels.email')</th>
                                        <th>@lang('labels.title')</th>
                                        <th>@lang('labels.department')</th>
                                        <th>@lang('labels.birthdate')</th>
                                        <th>Description</th>
                                        <th>@lang('labels.options')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                {{--@if(is_array($contacts))--}}
                                    {{--@foreach($contacts as $contact)--}}
                                        {{--<tr>--}}
                                            {{--<td>--}}
                                                {{--<span title="{{$contact->Description or ""}}"--}}
                                                      {{--style="cursor: pointer;"> {{$contact->Name or ""}}</span>--}}
                                            {{--</td>--}}
                                            {{--<td>--}}
                                                {{--@if(Auth::user()->localNumber != null)--}}
                                                    {{--<span class="pseudolink flexfoneCallOut">{{$contact->Phone  or ''}}</span>--}}
                                                {{--@else--}}
                                                    {{--<a href="tel:{{$contact->Phone or "---"}}">{{$contact->Phone or ""}}</a>--}}
                                                {{--@endif--}}
                                            {{--</td>--}}
                                            {{--<td><a href="#">{{$contact->Email or ""}}</a></td>--}}
                                            {{--<td>{{$contact->JobFunction or ""}}</td>--}}
                                            {{--<td>{{$contact->Department or ""}}</td>--}}
                                            {{--<td>--}}
                                                {{--@if($contact->Birthdate != null)--}}
                                                    {{--{{date('d-m-Y',strtotime($contact->Birthdate))}}--}}
                                                {{--@endif--}}
                                            {{--</td>--}}
                                            {{--<td>--}}
                                                {{--{{$contact->Description}}--}}
                                            {{--</td>--}}
                                            {{--<td>--}}

                                                {{--@if(isset($contact->Facebook))--}}
                                                    {{--<a href="{{$contact->Facebook}}" target="_blank">--}}
                                                        {{--<i class="fa fa-facebook"></i>--}}
                                                    {{--</a>--}}
                                                {{--@endif--}}

                                                {{--@if(isset($contact->LinkedIn))--}}
                                                    {{--<a href="{{$contact->LinkedIn}}" target="_blank">--}}
                                                        {{--<i class="fa fa-linkedin"></i>--}}
                                                    {{--</a>--}}
                                                {{--@endif--}}

                                                {{--@if(isAllowed('contacts','patch'))--}}
                                                    {{--<a href="{{url('client-contacts/edit',$contact->Id)}}"><i--}}
                                                                {{--class="fa fa-edit"></i></a>--}}
                                                {{--@endif--}}
                                                {{--@if(isAllowed('contacts','delete'))--}}
                                                    {{--<a href="#" class="deleteContactBtn"--}}
                                                       {{--data-contact-id="{{$contact->Id}}">--}}
                                                        {{--<i class="fa fa-times"></i>--}}
                                                    {{--</a>--}}
                                                {{--@endif--}}
                                            {{--</td>--}}
                                        {{--</tr>--}}
                                    {{--@endforeach--}}
                                {{--@endif--}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($information))
            <div class="tab-pane clearfix" id="information">
                <div class="col-md-5">
                    <h4><i class="fa fa-user"></i> @lang('labels.company-information')</h4>

                    <table class="table datatables dl-horizontal-row">
                        <tr>
                            <td>@lang('labels.client')</td>
                            <td>
                                @if(isset($information->Client))
                                    <a href="{{url('clientAlias/show',$information->Id)}}">{{$information->Name or $information->Company}}</a>
                                @else
                                    <a href="{{url('leads/show',$information->Id)}}">{{$information->Name or $information->Company}}</a>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>@lang('labels.ci-number')</td>
                            <td>{{$information->Client->CINumber or "--"}}</td>
                        </tr>
                        <tr>
                            <td>@lang('labels.homepage')</td>
                            <td><a href="{{$information->Homepage or "#"}}">{{$information->Homepage or "--"}}</a></td>
                        </tr>
                        <tr>
                            <td>@lang('labels.email')</td>
                            <td><a href="mailto:{{$information->EMail or ""}}">{{$information->EMail or "--"}}</a></td>
                        </tr>
                        <tr>
                            <td>@lang('labels.phone')</td>
                            <td>   @if(Auth::user()->localNumber != null)
                                    <span class="pseudolink flexfoneCallOut">{{$information->PhoneNumber  or ''}}</span>
                                @else
                                    <a href="tel:{{$information->PhoneNumber or ""}}">{{$information->PhoneNumber or ""}}</a>
                                @endif</td>
                        </tr>
                        <tr>
                            <td>@lang('labels.address')</td>
                            <td>{{$information->Address or ""}} , {{$information->zip or ""}} {{$information->City}}</td>
                        </tr>
                        <tr>
                            <td>@lang('labels.country')</td>
                            <td>{{$information->Country->CountryCode or '--'}}</td>
                        </tr>
                        <tr>
                            <td>@lang('labels.salesman')</td>
                            <td>{{$information->User->UserName or "--"}}</td>
                        </tr>
                    </table>

                    <!--<dl class="dl-horizontal-row">
                        <dt>@lang('labels.client')</dt>
                        <dd>
                            @if(isset($information->Client))
                                <a href="{{url('clientAlias/show',$information->Id)}}">{{$information->Name or $information->Company}}</a>
                            @else
                                <a href="{{url('leads/show',$information->Id)}}">{{$information->Name or $information->Company}}</a>
                            @endif
                        </dd>

                        <dt>@lang('labels.ci-number')</dt>
                        <dd>{{$information->Client->CINumber or "--"}}</dd>

                        <dt>@lang('labels.homepage')</dt>
                        <dd><a href="{{$information->Homepage or "#"}}">{{$information->Homepage or "--"}}</a></dd>

                        <dt>@lang('labels.email')</dt>
                        <dd><a href="mailto:{{$information->EMail or ""}}">{{$information->EMail or "--"}}</a></dd>

                        <dt>@lang('labels.phone')</dt>
                        <dd>   @if(Auth::user()->localNumber != null)
                                <span class="pseudolink flexfoneCallOut">{{$information->PhoneNumber  or ''}}</span>
                            @else
                                <a href="tel:{{$information->PhoneNumber or ""}}">{{$information->PhoneNumber or ""}}</a>
                            @endif</dd>

                        <dt>@lang('labels.address')</dt>
                        <dd>{{$information->Address or ""}} , {{$information->zip or ""}} {{$information->City}}</dd>

                        <dt>@lang('labels.country')</dt>
                        <dd>{{$information->Country->CountryCode or '--'}}</dd>

                        <dt>@lang('labels.salesman')</dt>
                        <dd>{{$information->User->UserName or "--"}}</dd>
                    </dl>-->

                </div>
                <div class="col-md-6">
                    <h4><i class="fa fa-info-circle"></i> Contract info</h4>
                    <div class="contractFieldValuesPlaceholder"></div>
                </div>
            </div>
        @endif

        @if(isset($progress))
            <div class="tab-pane clearfix" id="progress">
                <h4>@lang('labels.progress')</h4>
                Make view of progress on client contracts / products
            </div>
        @endif

        @if(isset($timeline))
            <div class="tab-pane clearfix" id="timeline">
                <div class="row">
                    <div class="form-group">
                        <div class="col-sm-12">
                            <div class="form-inline">
                                <form id="includeTimeline">
                                    <input type="checkbox" id="timeline-comments" name="timeline-comments">
                                    <label for="timeline-comments" style="margin-right:10px;">Comments</label>
                                    <input type="checkbox" id="timeline-payments" name="timeline-payments">
                                    <label for="timeline-payments" style="margin-right:10px;">Payments</label>
                                    <input type="checkbox" id="timeline-timelogs" name="timeline-timelogs">
                                    <label for="timeline-timelogs" style="margin-right:10px;">Timelogs</label>
                                    <input type="checkbox" id="timeline-tasks" name="timeline-tasks">
                                    <label for="timeline-tasks" style="margin-right:10px;">Tasks</label>
                                    <input type="submit" class="btn btn-primary" name="timeline-submit" style="margin-left: 10px;" value="Include">
                                </form>
                                </br>
                                <div class="checkbox" style="font-size: 11px;">
                                    <label for="timelineSortingChange">@lang('labels.sort-order')</label>
                                    <select id="timelineSortingChange">
                                        <option value="desc" selected="selected">@lang('labels.newest')</option>
                                        <option value="asc">@lang('labels.oldest')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br/>
                <div class="col-md-6  col-sm-6 panel-timeline" style="position: relative;">
               @if(isset($timeline) && is_array($timeline))
                        @foreach($timeline as $activity)
                            <?php
                            $message = '';
                            $icon = '';
                            $color = '';
                            if ($activity->ActivityType == "Produced") {
                                $message = 'produced the contract';
                                $icon = 'thumb-tack';
                                $color = '#24a854';
                            } elseif ($activity->ActivityType == "Start") {
                                $message = 'started the contract';
                                $icon = 'play';
                                $color = '#0f7e3e';
                            }elseif ($activity->ActivityType == "Completed") {
                                $message = 'completed the contract';
                                $icon = 'check';
                                $color = '#0f7e3e';
                            } elseif ($activity->ActivityType == "Optimize") {
                                $message = 'optimized the contract';
                                $icon = 'thumbs-o-up';
                                $color = '#113b7e';
                            }elseif ($activity->ActivityType == "StartOptimize") {
                                $message = 'began optimizing the contract';
                                $icon = 'gears';
                                $color = '#0e4194';
                            } elseif ($activity->ActivityType == 'Pause') {
                                $message = 'paused the contract';
                                $icon = 'pause';
                                $color = '#e9453a';
                            } elseif ($activity->ActivityType == 'Stop') {
                                $message = 'stopped the contract';
                                $icon = 'stop';
                                $color = '#d94136';
                            } elseif ($activity->ActivityType == 'Extend') {
                                $message = 'extended the contract';
                                $icon = 'refresh';
                                $color = '#f4842d';
                            } elseif ($activity->ActivityType == 'Upgrade') {
                                $message = 'upgraded the contract';
                                $icon = 'level-up';
                                $color = '#f4842d';
                            } elseif ($activity->ActivityType == 'Other') {
                                $message = '';
                                $icon = 'edit';
                                $color = 'grey';
                            }elseif ($activity->ActivityType == 'Assign') {
                                $message = '';
                                $icon = 'user';
                                $color = 'grey';
                            }
                            ?>
                            <div class="row timelineItem" data-date="{{$activity->Created}}">
                                <div class="col-xs-6 col-sm-2">
                                <span class=" fa-stack fa-2x">
                                <i class="fa fa-circle fa-stack-2x" style="color:lightgrey;"></i>
                                <i class="fa fa-{{$icon}} fa-stack-1x" style="color:{{$color}}"></i>
                                </span>
                                    <div class="borderDiv hidden-xs" style="border-left:3px solid lightgrey; margin-top: -5px; margin-bottom: -5px; margin-left: 27px;"></div>
                                </div>
                                <div class="col-xs-6 col-sm-3  col-sm-push-7 text-right" style="margin-top: 15px;">{{date('d-m-Y H:i',strtotime($activity->Created))}}</div>
                                <div class="col-xs-12 col-sm-7 col-sm-pull-3"
                                     style="padding-top: 15px;">
                                    <p>{{$activity->User->FullName or "--"}} <strong>{{$message}}</strong>@if($activity->Comment != null): </p>
                                    <p ><span class="multiline">{{$activity->Comment->Message or ''}}@endif</span>
                                    </p>
                                </div>
                                <div class="horizontalBorderDiv col-xs-12 col-sm-10" style="border-bottom:1px solid lightgrey; margin-bottom: 15px;"></div>
                            </div>
                        @endforeach
                @endif

                </div>

                <div class="col-sm-6" style="margin-top: 5px;">
                    <input type="button" class="btn btn-default" id="expandAllItems" value="Expand all items">
                </div>
            </div>
        @endif

        @if(isset($files))
            <div class="tab-pane clearfix" id="files">
                <div class="row">
                    <div class="col-md-8">
                        <h4><i class="fa fa-files-o"></i> @lang('labels.files')</h4>
                        <div class="table-responsive">
                            <table class="table table-condensed table-list" id="item-files">
                                <thead>
                                <tr>
                                    <th style="width:10%;"><input type="checkbox" id="selectAllFiles"> <label
                                                for="selectAllFiles"></label></th>
                                    <th>@lang('labels.created-date')</th>
                                    <th>@lang('labels.name')</th>
                                    <th>@lang('labels.user')</th>
                                    <th>@lang('labels.options')</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                        <button class="btn-primary pull-right" id="dlSelectedFiles">Download selected files</button>
                    </div>

                    <div class="col-md-4">
                        @if(isAllowed('fileStorages','post'))
                            <h4><i class="fa fa-cloud-upload"></i> @lang('labels.upload')</h4>
                            <form id="itemFileUploadForm" class="dropzone dz-clickable">
                                <div class="dz-default dz-message">
                                    <span>@lang('labels.drop-here-to-upload')</span>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if(inRole('Administration'))
            <div class="tab-pane clearfix" id="admin">
                <h4>Admin</h4>
                <!-- ADMIN to change or move leads, clients, contracts etc..-->
                <strong>Which person do you want to move the <span id="adminModelName"></span> to?</strong>
                <div class="adminUserAssign">
                </div>
            </div>
        @endif

        @if(isset($checklist))
            <div class="tab-pane clearfix" id="checklist">
                <div class="panel">
                    <div class="col-md-12" >
                        <h4><i class="fa fa-check"></i> @lang('labels.checklist')</h4>
                        <div class="form-horizontal">
                            <div class="seoChecklist">
                            </div>
                            <div class="col-md-12" style="padding-top: 10px; padding-bottom: 10px;">
                                <div class="col-md-2">
                                    <input id="showCompletedCheckList" class="btn btn-default form-control" value="Show completed" disabled="true">
                                </div>
                                <div class="col-md-2">
                                    <input id="addCheckList" class="btn btn-green form-control" value="Add">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($clientLogins) && isAllowed('clientLogins','get'))
            <div class="tab-pane clearfix" id="clientLogins">
                @if(isAllowed('clientLogins','post'))
                    <div class="col-md-4">
                        <h4><i class="fa fa-lock"></i> Add a client login</h4>
                        <div class="form-horizontal">
                            {!! Form::open(['id'=>'clientLoginsForm','style'=>'font-size:14px;','autocomplete'=>'off']) !!}
                            <input type="hidden" name="Contract_Id" value="{{$contractId or ''}}">
                            <div class="form-group">
                                <label for="clientLogins-Title" class="col-md-3 control-label">Title</label>
                                <div class="col-sm-6">
                                    <input class="form-control input-sm" id="clientLogins-Title" placeholder="Title"
                                           autocomplete="off" required="required" name="Title" type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="clientLogins-Protocol" class="col-sm-3 control-label">Protocol</label>
                                <div class="col-sm-6">
                                    <select name="Protocol" id="clientLogins-Protocol" required="required"
                                            class="form-control input-sm">
                                        <option value="">Select</option>
                                        <option value="https">HTTPS</option>
                                        <option value="http">HTTP</option>
                                        <option value="ftp">FTP</option>
                                        <option value="ftps">FTPS</option>
                                        <option value="sftp">SFTP</option>
                                        <option value="ssh">SSH</option>
                                        <option value="file">FILE</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="clientLogins-Host" class="col-md-3 control-label">Host/Login page</label>
                                <div class="col-sm-6">
                                    <input class="form-control input-sm" id="clientLogins-Host" autocomplete="off"
                                           placeholder="Enter Host" required="required" name="Host" type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="clientLogins-Username" class="col-md-3 control-label">Username</label>
                                <div class="col-sm-6">
                                    <input class="form-control input-sm" value="" id="clientLogins-Username"
                                           autocomplete="off" placeholder="Enter Username" name="Username" type="text">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="clientLogins-Password" class="col-md-3 control-label">Password</label>
                                <div class="col-sm-6">
                                    <input class="form-control input-sm" value="" id="clientLogins-Password"
                                           autocomplete="off" placeholder="Password" name="Password" type="password">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="clientLogins-Description" class="col-md-3 control-label">Description</label>
                                <div class="col-sm-6">
                                    <textarea class="form-control input-sm" value="" id="clientLogins-Description"
                                              autocomplete="off" placeholder="Description"
                                              name="Description"></textarea>
                                </div>
                            </div>

                            <div class="btn-toolbar col-sm-6">
                                {!! Form::submit(strtoupper(Lang::get('labels.save')),['class'=> 'btn btn-green form-control ']) !!}
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                @endif
                <div class="col-md-8">
                    <div class="panel">
                        <h4><i class="fa fa-lock"></i> Saved logins</h4>
                        <div class='table-responsive'>
                            <table class="table table-condensed" width="100%" id="clientLoginsTable">
                                <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Protocol</th>
                                    <th>Host</th>
                                    <th>Username</th>
                                    <th>Password</th>
                                    <th>Saved by</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($seo))
            <div class="tab-pane" id="seo">
                <div class="col-md-8">
                    <div class="panel">
                        <form id="newSeoCommentForm">
                            <div class="input-group">
                                <textarea name="Message" rows="1" placeholder="@lang('labels.enter-comment')"
                                          class="form-control autosize" required="required"></textarea>
                                <input type="hidden" name="Type" value="Seo">
                                <span class="input-group-btn">
                                       <button type="submit" class="btn btn-primary"><i
                                                   class="fa fa-comments"></i></button>
                                   </span>
                            </div>
                        </form>
                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="form-inline">
                                        <div class="checkbox" style="font-size: 11px;">
                                            <label for="seoCommentSortingChange">@lang('labels.sort-order')</label>
                                            <select id="seoCommentSortingChange">
                                                <option value="desc" selected="selected">@lang('labels.newest')</option>
                                                <option value="asc">@lang('labels.oldest')</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br/>
                        <ul class="panel-seo-comments"></ul>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>