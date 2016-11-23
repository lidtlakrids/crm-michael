<nav id="page-leftbar" role="navigation">
    <!-- BEGIN SIDEBAR MENU -->
    <ul class="acc-menu" id="sidebar">
        @if(isAllowed('clientAlias','get'))
            <li id="search">
                <a href="javascript:;"><i class="fa fa-search opacity-control"></i></a>
                <form>
                    <input type="text" id="main-search" class="search-query"
                           placeholder="@lang('labels.search-clients')...">
                    <button type="submit"><i class="fa fa-search"></i></button>
                </form>
            </li>
            <li class="divider"></li>
        @endif
        <?php /** BEGIN DASHBOARD MENU AND PERMISSIONS TODO */?>
        <li><a href="{{url('/')}}"><i class="fa fa-home"></i> <span>@lang('labels.dashboard')</span></a>
            {{--<ul class="acc-menu">--}}
                {{--<li><a href="{{url('/')}}"><i class="fa fa-home"></i><span>@lang('labels.home')</span></a></li>--}}
                {{--<li><a href="{{url('/admin')}}"><i class="fa fa-ambulance"></i><span>@lang('labels.admin')</span></a></li>--}}
                {{--<li><a href="{{url('/accounting/dashboard')}}"><i class="fa fa-apple"></i><span>@lang('labels.accounting')</span></a></li>--}}
                {{--<li><a href="{{url('/phoneStatistics')}}"><i class="fa fa-phone"></i><span>Phone statistics</span></a></li>--}}
            {{--</ul>--}}
        </li>

        <?php /** BEGIN ADMIN MENU AND PERMISSIONS TODO */?>
        @if(inRole('Administrator'))
            <li><a href="javascript:;"><i class="fa fa-group"></i><span>@lang('labels.admin')</span></a>
                <ul class="acc-menu">
                    @if(isAllowed('settings','get'))
                        <li><a href="{{ url('settings') }}"><i
                                        class="fa fa-gear"></i><span>@lang('labels.settings')</span></a></li>@endif
                    @if(isAllowed('users','get'))
                        <li><a href="{{ url('users') }}"><i
                                        class="fa fa-user"></i><span>@lang('labels.users')</span></a></li>@endif
                    @if(isAllowed('sellerGoals','get'))
                        <li><a href="{{ url('seller-goals') }}"><i
                                        class="fa fa-bar-chart-o"></i><span>@lang('labels.seller-goals')</span></a>
                        </li>@endif
                    {{--@if(isAllowed('managerTeams','get'))--}}
                        {{--<li><a href="{{ url('teams') }}"><i--}}
                                        {{--class="fa fa-group"></i><span>@lang('labels.teams')</span></a></li>@endif--}}
                    @if(isAllowed('orderTypes','get'))
                        <li><a href="{{ url('ordertypes')}}"><i
                                        class="fa fa-reorder"></i><span>@lang('labels.order-types')</span></a>
                        </li>@endif

                    @if(isAllowed('contractFields','get'))
                        <li><a href="{{ url('contract-fields')}}"><i
                                        class="fa fa-reorder"></i><span>Contract fields</span></a>
                        </li>@endif
                    @if(isAllowed('templates','get'))
                        <li><a href="{{ url('emailTemplates')}}"><i
                                        class="fa fa-file"></i><span>@lang('labels.templates')</span></a></li>@endif
                    @if(isAllowed('taskListTemplates','get'))
                        <li><a href="{{ url('taskTemplates')}}"><i
                                        class="fa fa-file-o"></i><span>@lang('labels.task-templates')</span></a>
                        </li> @endif
                    @if(isAllowed('products','get'))
                        <li><a href="javascript:;"><i
                                        class="fa fa-asterisk"></i><span>@lang('labels.products')</span></a>
                            <ul class="acc-menu">
                                @if(isAllowed('products','get'))
                                    <li><a href="{{url('products')}}">@lang('labels.products')</a></li>@endif
                                @if(isAllowed('products','get'))
                                    <li><a href="{{url('productTypes')}}">@lang('labels.product-type')</a></li>@endif
                                @if(isAllowed('products','get'))
                                    <li><a href="{{url('productDepartments')}}">@lang('labels.product-department')</a>
                                    </li>@endif
                                @if(isAllowed('products','get'))
                                    <li><a href="{{url('product-packages')}}">@lang('labels.product-package')</a>
                                    </li>@endif
                                @if(isAllowed('optimizeRules','get'))
                                    <li><a href="{{url('optimize-rules')}}">@lang('labels.optimize-rules')</a>
                                    </li>@endif
                            </ul>
                        </li>
                    @endif
                    @if(isAllowed('titles','get'))
                        <li>{!! Html::linkAction('TitlesController@index',Lang::get('labels.titles')) !!}</li>@endif
                    @if(isAllowed('taxonomies','get'))
                        <li>{!! Html::linkAction('TaxonomiesController@index',Lang::get('labels.taxonomy')) !!}</li>@endif
                    @if(isAllowed('notifications','get'))
                        <li>{!! Html::linkAction('NotificationsController@index',Lang::get('labels.notifications')) !!}</li>@endif
                    @if(isAllowed('taskLists','get'))
                        <li>{!! Html::linkAction('TaskListsController@index',Lang::get('labels.tasks')) !!}</li>@endif
                    @if(isAllowed('countries','get'))
                        <li>{!! Html::linkAction('CountriesController@index',Lang::get('labels.countries')) !!}</li>@endif
                    @if(isAllowed('acls','get'))
                        <li>{!! Html::linkAction('AclController@index',"ACL") !!}</li>@endif
                    @if(isAllowed('salaryGroups','get'))
                        <li>{!! Html::linkAction('SalaryGroupsController@index',Lang::get('labels.salary-groups')) !!}</li>@endif
                    @if(isAllowed('clientRates','get'))
                        <li>{!! Html::linkAction('ClientRatesController@index',Lang::get('labels.client-rates')) !!}</li>@endif
                    @if(isAllowed('salaries','bonus'))
                        <li>{!! Html::linkAction('SalariesController@index',Lang::get('labels.salaries')) !!}</li>@endif
                    @if(isAllowed('logs','get'))
                        <li>{!! Html::linkAction('LogsController@index',"Logs") !!}</li>@endif
                    @if(isAllowed('sellergoals','get'))
                        <li>{!! Html::linkAction('SellerGoalsController@index',Lang::get('labels.seller-goals')) !!}</li>@endif
                    @if(isAllowed('loyaltyBonus','get'))
                        <li>{!! Html::linkAction('LoyaltyBonusController@index',Lang::get('labels.loyalty-bonuses')) !!}</li>@endif
                    @if(isAllowed('whitelists','get'))
                        <li>{!! Html::linkAction('WhiteListsController@index',"White lists") !!}</li>@endif
                </ul>
            </li>
        @endif
        <?php /** END ADMIN MENU AND PERMISSIONS TODO */ ?>

        <?php /** BEGIN ACCOUNTING MENU AND PERMISSIONS TODO */?>
        @if(inRole('Accounting'))
            <li><a href="javascript:;"><i class="fa fa-apple"></i> <span>Accounting</span> </a>
                <ul class="acc-menu">
                    <li><a href="{{url('/accounting/')}}"><span>Missing Payments</span></a></li>
                    <li><a href="{{url('/accounting/register')}}"><span>Register Payments</span></a></li>
                    @if(isAllowed('drafts','get'))
                        <li>
                            <a href="{{url('/accounting/recurring')}}"><span>@lang('labels.recurring-payments')</span></a>
                        </li>@endif
                    <li><a href="{{url('/accounting/stat')}}"><span>Stat Payments</span></a></li>
                </ul>
            </li>
        @endif
        <?php /** END ACCOUNTING MENU AND PERMISSIONS TODO */?>

        <?php /** BEGIN STATISTICS MENU AND PERMISSIONS TODO */?>
        @if(inRole('Administrator'))
            <li><a href="javascript:;"><i class="fa fa-bar-chart-o"></i> <span>Statistics</span> </a>
                <ul class="acc-menu">
                    <li><a href="{{url('/statistics/periodization/')}}"><span>Periodization</span></a></li>
                    <li><a href="{{url('/statistics/meetings/')}}"><span>Meetings statistics</span></a></li>
                    <li><a href="{{url('/statistics/contract-values/')}}"><span>Contract values</span></a></li>
                    <li><a href="{{url('/statistics/expected-payments/')}}"><span>Expected payments</span></a></li>
                    <li><a href="{{url('/statistics/missing-payments/')}}"><span>Missing Payments</span></a></li>
                    <li><a href="{{url('/statistics/debt-collection/')}}"><span>Debt collection</span></a></li>
                    <li><a href="{{url('/statistics/clients/')}}"><span>Client stats</span></a></li>
                    <li><a href="{{url('/statistics/optimizations/')}}"><span>Optimization statistics</span></a></li>
                    <li><a href="{{url('/dashboard/ongoing-optimizations/')}}"><span>Ongoing optimizations</span></a></li>
                    <li><a href="{{url('/statistics/sellers-overview/')}}"><span>Sellers Overview</span></a></li>
                    <li><a href="{{url('timeRegistrations/timeLogs')}}"><span>Time Logs</span></a></li>
                </ul>
            </li>
        @endif
        <?php /** END STATISTICS MENU AND PERMISSIONS TODO */?>

        <?php /** BEGIN Clients MENU AND PERMISSIONS TODO */?>
        @if(isAllowed('clientAlias','get'))
            <li><a href="javascript:;"><i class="fa fa-suitcase"></i> <span>@lang('labels.clients')</span> </a>
                <ul class="acc-menu">
                    @if(isAllowed('clients','get'))
                        <li><a href="{{url('/clients')}}"><span>@lang('labels.ci-numbers')</span></a></li>@endif
                    @if(isAllowed('clientAlias','get'))
                        <li><a href="{{ url('/clientAlias') }}"><span>@lang('labels.clients')</span></a></li>@endif
                    @if(isAllowed('contacts','get'))
                        <li><a href="{{url('/client-contacts')}}"><span>Client Contacts</span></a></li> @endif
                    @if(isAllowed('partners','get'))
                        <li><a href="{{ url('/partners') }}"><span>@lang('labels.partners')</span></a></li>@endif
                    {{--@if(isAllowed('clientAlias','get'))<li><a href="{{url('/clientAlias/stat')}}"><span>Client Stat</span></a></li>@endif--}}
                    {{--@if(isAllowed('clientAlias','get'))<li><a href="{{url('/clientAlias/graph')}}"><span>Client Graph</span></a></li>@endif--}}
                </ul>
            </li>
        @endif
        <?php /** END Clients MENU AND PERMISSIONS TODO */?>

        <?php /** BEGIN INVOICES MENU AND PERMISSIONS TODO */?>
        @if(isAllowed('invoices','get'))
            <li><a href="javascript:;"><i class="fa fa-barcode"></i> <span>@lang('labels.invoices')</span> </a>
                <ul class="acc-menu">
                    @if(isAllowed('invoices','get'))
                        <li><a href="{{ url('/invoices/') }}"><span>Show all invoices</span></a></li>@endif
                    @if(isAllowed('drafts','get'))
                        <li><a href="{{ url('/drafts/') }}"><span>All drafts</span></a></li>@endif
                </ul>
            </li>
        @endif
        <?php /** END INVOICES MENU AND PERMISSIONS TODO */?>

        <?php /** BEGIN CONTRACTS MENU AND PERMISSIONS TODO */?>
        @if(isAllowed('contracts','get'))
            <li><a href="javascript:;"><i class="fa fa-book"></i> <span>Contracts</span> </a>
                <ul class="acc-menu">
                    @if(isAllowed('contracts','get'))
                        <li><a href="{{ url('contracts') }}"><span>Show all contracts</span></a></li>@endif
                    @if(isAllowed('contracts','assign'))
                        <li>{!! Html::linkAction('ContractsController@assignContracts',Lang::get('labels.assign-contracts')) !!}</li>@endif
                    @if(isAllowed('informationSchemes','post'))
                        <li>{!! Html::linkAction('ContractsController@needInformation',Lang::get('labels.need-information')) !!}</li>@endif
                    @if(isAllowed('informationSchemes','get'))
                        <li>{!! Html::linkAction('InformationSchemesController@index',Lang::get('labels.information-schemes')) !!}</li>@endif
                        @if(isAdmin())
                            <li>{!! Html::linkAction('ContractsController@bySeller',"By Seller") !!}</li>@endif
                </ul>
            </li>
        @endif
        <?php /** BEGIN CONTRACTS MENU AND PERMISSIONS TODO */?>

        <?php /** BEGIN ADWORDS MENU AND PERMISSIONS TODO */?>
        @if(inRole('Adwords'))
            <li><a href="javascript:;"><i class="fa fa-external-link-square"></i> <span>Adwords</span> </a>
                <ul class="acc-menu">
                    @if(isAllowed('contracts','get'))
                        <li>{!! Html::linkAction('AdwordsController@index',Lang::get('labels.all')) !!}</li>@endif
                </ul>
            </li>
        @endif
        <?php /** END ADWORDS MENU AND PERMISSIONS TODO */?>

        <?php /** BEGIN SEO MENU AND PERMISSIONS TODO */?>
        @if(inRole('SEO'))
            <li><a href="javascript:;"><i class="fa fa-search"></i> <span>SEO</span> </a>
                <ul class="acc-menu">
                    @if(isAllowed('contracts','get'))
                        <li>{!! Html::linkAction('SeoController@index',Lang::get('labels.all')) !!}</li>@endif
                </ul>
            </li>
        @endif
        <?php /** END SEO MENU AND PERMISSIONS TODO */?>

        <?php /** BEGIN ORDERS MENU AND PERMISSIONS TODO */?>
        @if(isAllowed('orders','get'))
            <li><a href="javascript:;"><i class="fa fa-shopping-cart"></i><span>Orders</span></a>
                <ul class="acc-menu">
                    @if(isAllowed('orders','post'))
                        <li><a href="{{url('orders/create')}}"> <span>@lang('labels.create-order')</span></a></li>@endif
                    @if(isAllowed('orders','get'))
                        <li><a href="{{url('orders')}}"><span>@lang('labels.orders')</span></a></li>@endif
                    @if(isAllowed('orders','approve'))
                        <li>
                            <a href="{{url('orders/needApproval')}}"><span>@lang('labels.orders-for-approval')</span></a>
                        </li>@endif
                    {{--@if(isAllowed('informationSchemes','approve'))<li><a href="{{url('orders/info-for-approval')}}"><span>@lang('labels.information-for-approval')</span></a></li>@endif--}}
                </ul>
            </li>
        @endif
        <?php /** END ORDERS MENU AND PERMISSIONS TODO */?>

        <?php /** BEGIN LEADS MENU AND PERMISSIONS TODO */?>
        @if(isAllowed('leads','get'))
            <li><a href="javascript:;"><i class="fa fa-bullseye"></i><span>Leads</span></a>
                <ul class="acc-menu">
                    @if(isAllowed('leads','get'))
                        <li><a href="{{url('leads')}}"><span>@lang('labels.all-leads')</span></a></li>@endif
                    @if(isAllowed('leads','post'))
                        <li><a href="{{url('leads/create')}}"><span>@lang('labels.create-lead')</span></a></li>@endif
                    @if(isAllowed('leads','assign'))
                        <li><a href="{{url('leads/assign')}}"><span>@lang('labels.assign')</span></a></li>@endif
                </ul>
            </li>
        @endif
        <?php /** END LEADS MENU AND PERMISSIONS TODO */?>

        <?php /** BEGIN Time logs  TODO */?>
        <li><a href="javascript:;"><i class="fa fa-clock-o"></i><span>@lang('labels.activities')</span></a>
            <ul class="acc-menu">
                @if(isAllowed('calendarEvents','get'))
                    <li><a href="{{url('/appointments')}}"><span>@lang('labels.appointments')</span></a></li>
                @endif
                @if(isAllowed('taskLists','get'))
                    <li><a href="{{url('/tasks/')}}"><span>@lang('labels.tasks')</span></a></li>
                @endif

                <li><a href="{{url('/calendar/')}}"><span>@lang('labels.calendar')</span></a></li>

                @if(inRole('Adwords') || inRole('SEO'))
                    <li><a href="{{url('/dashboard/optimizations')}}"><span>@lang('labels.my-optimizations')</span></a>
                    </li>
                @endif
                @if(isAllowed('timeRegistrations','get'))
                    <li><a href="{{url('timeRegistrations/index')}}"><span>Users checked in</span></a></li>
                @endif
            </ul>

        </li>
        <?php /** END time logsTODO */?>
        <?php /** BEGIN SALES MENU AND PERMISSIONS TODO */?>
        @if(inRole('Sales'))
            <li><a href="javascript:;"><i class="fa fa-bank"></i> <span>@lang('labels.sales')</span> </a>
                <ul class="acc-menu">
                    <li><a href="{{url('/sales')}}"><span>@lang('labels.menu')</span></a></li>
                    <li>
                        <a href="{{url('/payments',Auth::user()->externalId)}}"><span>@lang('labels.payments')</span></a>
                    </li>
                    <li>
                        <a href="{{url('/missing-payments',Auth::user()->externalId)}}"><span>@lang('labels.missing-payments')</span></a>
                    </li>
                    <li><a href="{{url('/dashboard/contract-renewal')}}"><span>@lang('labels.up-for-renewal')</span></a>
                    </li>
                    <li><a href="{{url('/orders')}}"><span>@lang('labels.my-orders')</span></a></li>
                    <li><a href="{{url('/dashboard/winback')}}"><span>@lang('labels.win-back')</span></a></li>
                    <li><a href="{{url('/seller-goals')}}"><span>@lang('labels.seller-goals')</span></a></li>
                    <li><a href="{{url('/clients-to-call')}}"><span>Clients to Call</span></a></li>
                    <li><a href="{{url('/sales/my-clients')}}"><span>My Clients</span></a></li>
                </ul>
            </li>
        @endif
        <?php /** END sales TODO */?>
    </ul>
</nav>
<!-- END SIDEBAR MENU -->