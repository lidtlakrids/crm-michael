<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
/** DAshboards */
Route::group(['middleware'=>['whitelist']],function() {
});

Route::get('timeRegistrations/screen','TimeRegistrationsController@screen');

//    Route::controllers([
//        'auth' => 'Auth\AuthController',
//        'password' => 'Auth\PasswordController',
//    ]);
Route::get('auth/login', 'Auth\AuthController@showLoginForm');
Route::post('auth/login', 'Auth\AuthController@login');
Route::post('leads/bot','LeadsController@botsearch');
Route::get('leads/bot','LeadsController@bot');

Route::group(['middleware'=>['web','auth']],function(){
Route::get('auth/logout', 'Auth\AuthController@logout');

/** Partners */
Route::get('partners','PartnersController@index')->middleware('acl:partners,get');
Route::get('partners/create','PartnersController@create')->middleware('acl:partners,post');
Route::get('partners/show/{id}','PartnersController@show')->middleware('acl:partners,get');
Route::get('partners/edit/{id}','PartnersController@edit')->middleware('acl:partners,patch');
/** End Partners */

Route::get('loyalty-bonuses','LoyaltyBonusController@index')->middleware('acl:loyaltyBonus,get');
Route::get('loyalty-bonuses/edit/{id}','LoyaltyBonusController@edit')->middleware('acl:loyaltyBonus,patch');

Route::get('dashboard','DashboardsController@index');
Route::get('/','DashboardsController@index');
Route::get('home','DashboardsController@home');
Route::get('admin','DashboardsController@admin')->middleware(['role:Administrator']);
Route::get('accounting','DashboardsController@accounting')->middleware(['role:Accounting']);

Route::get('salaries','SalariesController@index')->middleware(['acl:salaries,overview']); // todo
Route::get('salaries/show/{id}','SalariesController@show')->middleware(['acl:salaries,get']);
Route::get('payments/{id?}','SalariesController@payments')->middleware(['acl:salaries,overview']);
Route::get('missing-payments/{id?}','SalariesController@missingPayments')->middleware(['acl:salaries,overview']);
Route::get('statistics/debt-collection/{id?}','StatisticsController@debtCollection')->middleware(['acl:salaries,overview']);
Route::get('logs','LogsController@index')->middleware(['acl:logs,get']);
Route::get('detailed-logs','LogsController@detailed')->middleware(['acl:DetailedLogs,get']);
Route::get('logs/show/{id}','LogsController@show')->middleware(['acl:logs,get']);

Route::get('stat','DashboardsController@stat');
Route::get('dashboard/appointments','AppointmentsController@index')->middleware(['acl:calendarEvents,get']);
Route::get('dashboard/unpaid','DashboardsController@unpaidInvoices')->middleware(['acl:invoices,get']);
Route::get('dashboard/unconfirmed','DashboardsController@unconfirmedOrders')->middleware(['acl:orders,get']);
Route::get('dashboard/contracts-renewal','DashboardsController@unconfirmedOrders')->middleware(['acl:orders,get']);
Route::get('dashboard/active-clients','DashboardsController@activeClients')->middleware(['acl:dashboards,clientAliasCount']);
Route::get('dashboard/contract-renewal','DashboardsController@contractRenewal')->middleware(['acl:contracts,get']);
Route::get('dashboard/overdue','DashboardsController@overdueInvoices')->middleware(['acl:contracts,get']);
Route::get('dashboard/winback','DashboardsController@winback')->middleware(['acl:contracts,get']);
Route::get('dashboard/optimizations','DashboardsController@optimizations')->middleware(['acl:contracts,get']);
Route::get('dashboard/ongoing-optimizations/{id?}','DashboardsController@ongoingOptimizations')->middleware(['acl:contracts,get']);

Route::get('taxonomy','TaxonomiesController@index')->middleware(['acl:taxonomies,get']);
Route::get('seller-goals','SellerGoalsController@index')->middleware(['acl:sellerGoals,get']);
Route::get('seller-goals/show/{id}','SellerGoalsController@show')->middleware(['acl:sellerGoals,get']);

/**
 * End dashboard
 */

/**
 * Client Logins
 */
Route::get('client-logins','ClientLoginsController@index')->middleware(['acl:clientLogins,get']);
Route::post('client-logins','ClientLoginsController@store')->middleware(['acl:clientLogins,post']);
Route::post('client-logins/{id}/decrypt-password','ClientLoginsController@decryptPassword')->middleware(['acl:clientLogins,post']);



/**
 * End Client Logins
 */
/**
 * Appointments
 */
Route::get('appointments/show/{id}','AppointmentsController@show')->middleware(['acl:calendarEvents,get']);
Route::get('appointments','AppointmentsController@index')->middleware(['acl:calendarEvents,get']);
/**
 * End appointments
 */

/**
 * Products + Types + Departments + Package routes
 */
Route::get('products/','ProductsController@index')->middleware("acl:products,get");
Route::get('products/create','ProductsController@create')->middleware("acl:products,post");
Route::get('products/show/{id}','ProductsController@show')->middleware("acl:products,get");
Route::get('products/edit/{id}','ProductsController@edit')->middleware(["auth","acl:products,patch"]);

Route::get('productDepartments','ProductDepartmentsController@index');
Route::get('productDepartments/create','ProductDepartmentsController@create');
Route::get('productDepartments/edit/{id}','ProductDepartmentsController@edit');
Route::get('productDepartments/show/{id}','ProductDepartmentsController@show');

Route::get('productTypes','ProductTypesController@index');
Route::get('productTypes/create','ProductTypesController@create');
Route::get('productTypes/edit/{id}','ProductTypesController@edit');
Route::get('productTypes/show/{id}','ProductTypesController@show');

Route::get('product-packages','ProductPackagesController@index')->middleware(['acl:productPackages,get']);
Route::get('product-packages/edit/{id}','ProductPackagesController@edit')->middleware(['acl:productPackages,patch']);
Route::get('product-packages/create','ProductPackagesController@create')->middleware(['acl:productPackages,post']);
Route::get('product-packages/show/{id}','ProductPackagesController@show')->middleware(['acl:productPackages,get']);

/** end product routes  */

/** Invoices  */

Route::get('invoices/','InvoicesController@index');
Route::post('invoices/updateDraftLine','InvoicesController@updateDraftLine');
Route::get('invoices/draftForContract/{contractId}','InvoicesController@draftForContract');
Route::get('invoices/invoiceForContract/{contractId}','InvoicesController@invoiceForContract');
Route::get('invoices/show/{id}','InvoicesController@show')->middleware(['acl:invoices,get']);
Route::get('invoices/invoicePdf/{hash}','InvoicesController@invoicePdf');

/** end invoices  */


/** Optimize Rules*/
Route::get('optimize-rules','OptimizeRulesController@index')->middleware(['acl:optimizeRules,get']);
Route::get('optimize-rules/show/{id}','OptimizeRulesController@show')->middleware(['acl:optimizeRules,get']);
Route::get('optimize-rules/create','OptimizeRulesController@create')->middleware(['acl:optimizeRules,post']);

/** end Optimize Rules*/

/** Clients  */
Route::get('clients', 'ClientsController@index')->middleware(['acl:clients,get']);
Route::get('clients/getClients', 'ClientsController@getClients');
Route::get('clients/show/{id}','ClientsController@show');
Route::get('clients/edit/{id}','ClientsController@edit');
Route::get('clients/create','ClientsController@create');
Route::get('clients/CINumbers','ClientsController@ciNumbers');
Route::get('clients/assign-managers','ClientsController@needClientManager')->middleware('acl:clients,patch');
Route::get('clientsAlias/search','ClientsController@search');
Route::post('clients/store','ClientsController@store');
/** end clients  */

/** client alias */
Route::get('clientAlias/search','ClientAliasController@search');
Route::get('clientAlias','ClientAliasController@index');
Route::post('clientAlias/store','ClientAliasController@store');
Route::get('clientAlias/show/{id}','ClientAliasController@show');
Route::get('clientAlias/edit/{id}','ClientAliasController@edit');
Route::post('clientAlias/update/{id}','ClientAliasController@update');
Route::get('clientAlias/create/{clientId}','ClientAliasController@create');
Route::get('clientAlias/aliasInvoices/{id}','ClientAliasController@aliasInvoices');
Route::get('clientAlias/aliasOrders/{id}','ClientAliasController@aliasOrders');
/** end client alias */

Route::get('client-contacts/edit/{id}','ContactsController@edit')->middleware('acl:contacts,patch');
Route::get('client-contacts','ContactsController@index')->middleware('acl:contacts,get');

/** Users  */
Route::get('users','UsersController@index');
Route::get('users/getList','UsersController@usersList');
Route::get('users/listByRoles','UsersController@listByRoles');
Route::get('users/userslist','UsersController@usersList');
Route::get('users/show/{id}','UsersController@show');
Route::get('users/create','UsersController@create');
Route::post('users/store','UsersController@store');
Route::get('users/edit/{id}','UsersController@edit')->middleware('acl:users,patch');
Route::patch('users/update/{id}','UsersController@update');
Route::post('users/update-current/{id?}','UsersController@updateCurrentUserInfo');
Route::get('my-profile','UsersController@profile');
Route::get('my-profile/edit','UsersController@editMyAccount');
Route::get('my-profile/change-password','UsersController@changePassword');
Route::get('users/changePassword/{id}','UsersController@setPassword')->middleware('acl:users,post');

/** end users  */

/** ORDERS  */
Route::get('orders','OrdersController@index')->middleware(['acl:orders,get']);
Route::get('orders/show/{id}','OrdersController@show');
Route::get('/orders/create/{id?}/{aliasId?}','OrdersController@create')->middleware(['acl:orders,post']);
Route::post('orders/store','OrdersController@store');
Route::get('orders/needApproval','OrdersController@needApproval');
Route::post('orders/approveOrder','OrdersController@approveOrder');
Route::get('orders/edit/{id}','OrdersController@edit');
Route::put('orders/update/{id}','OrdersController@update');
Route::get('orders/information/{contractId}','OrdersController@information')->middleware(['acl:informationSchemes,post']);
Route::get('orders/getOrderFields/{id}','OrdersController@getOrderFields');
Route::post('orders/groupOrderFields','OrdersController@groupOrderFields');

Route::get('orders/info-for-approval','InformationSchemesController@needApproval')->middleware(['acl:informationSchemes,approve']);

Route::get('information/show/{id}','InformationSchemesController@show')->middleware(['acl:informationSchemes,get']);
Route::get('information-schemes','InformationSchemesController@index')->middleware(['acl:informationSchemes,get']);
Route::get('contracts/need-information','ContractsController@needInformation')->middleware(['acl:informationSchemes,post']);
Route::get('contracts/need-information','ContractsController@needInformation')->middleware(['acl:informationSchemes,post']);
Route::get('contracts/missingDrafts','ContractsController@missingDrafts');
Route::get('contracts/addons/{id}','ContractsController@contractAddons')->middleware(['acl:informationSchemes,post']);

    /** End orders */

/** ORDERS TYPES  */
Route::get('ordertypes','OrderTypesController@index');
Route::get('ordertypes/getList','OrderTypesController@getList');
Route::get('ordertypes/create','OrderTypesController@create');
Route::post('ordertypes/store','OrderTypesController@store');
Route::put('ordertypes/update/{id}','OrderTypesController@update');
Route::get('ordertypes/edit/{id}','OrderTypesController@edit');
Route::get('ordertypes/show/{id}','OrderTypesController@show');
Route::get('ordertypes/addFields/{id}','OrderTypesController@addFields')->middleware(['acl:orderFields,patch']);


Route::get('ordertypes/fieldsList/{orderTypeId}','OrderTypesController@fieldsList');
Route::get('ordertypes/updateFieldOrder','OrderTypesController@updateFieldOrder');
Route::post('ordertypes/addFieldLink','OrderTypesController@addFieldLink');
Route::delete('ordertypes/removeFieldLink','OrderTypesController@removeFieldLink');

/** End Order Types */

/**
 * Contract fields
 */

Route::get('contract-fields','ContractFieldsController@index')->middleware(['acl:contractFields,get']);
Route::get('contract-fields/create','ContractFieldsController@create')->middleware(['acl:contractFields,post']);
Route::post('contract-fields/store','ContractFieldsController@store')->middleware(['acl:contractFields,post']);
Route::get('contract-fields/show/{id}','ContractFieldsController@show')->middleware(['acl:contractFields,get']);
Route::get('contract-fields/edit/{id}','ContractFieldsController@edit')->middleware(['acl:contractFields,patch']);
Route::get('contract-types/preview/');
Route::get('contract-types/show/{id}','ProductTypesController@show');
Route::get('contract-types/add-fields/{id}','ProductTypesController@addFields');

/**
 * End contract fields
 */


/** Order Fields */
Route::get('order-fields','OrderFieldsController@index')->middleware(['acl:orderFields,get']);
Route::get('order-fields/show/{id}','OrderFieldsController@show')->middleware(['acl:orderFields,get']);
Route::get('order-fields/edit/{id}','OrderFieldsController@edit')->middleware('acl:orderFields,patch');
Route::get('order-fields/create','OrderFieldsController@create')->middleware('acl:orderFields,post');
Route::post('order-fields/store','OrderFieldsController@store')->middleware('acl:orderFields,post');

Route::get('order-field-options/edit/{id}',"OrderFieldOptionsController@edit")->middleware(['acl:orderFieldOptions,patch']);

/**  End Order Fields */

/**  CONTRACTS */
Route::get('contracts','ContractsController@index')->middleware(['acl:contracts,get']);
Route::get('contracts/assign/{id?}','ContractsController@assignContracts')->middleware(['acl:contracts,assign']);
Route::get('contracts/show/{id}','ContractsController@show')->middleware(['acl:contracts,get']);
Route::get('contracts/edit/{id}','ContractsController@edit')->middleware('acl:contracts,patch');
Route::get('contracts/upgrade/{id}','ContractsController@upgrade')->middleware(['acl:orders,post']);
Route::get('contracts/renew/{id}','ContractsController@renew')->middleware(['acl:orders,post']);
Route::get('contracts/field-values/{id}','ContractsController@fieldValues')->middleware(['acl:Contract,post']);
Route::get('contracts/by-seller/{id?}','ContractsController@bySeller')->middleware(['role:Administrator']);

/** end Contracts */


/** Time logs */
Route::post('timeRegistrations/checkIn','TimeRegistrationsController@checkIn');
Route::post('timeRegistrations/checkOut','TimeRegistrationsController@checkOut');
Route::post('timeRegistrations/beginBreak','TimeRegistrationsController@beginBreak');
Route::post('timeRegistrations/beginWork','TimeRegistrationsController@beginWork');
Route::post('timeRegistrations/endBreak','TimeRegistrationsController@endBreak');
Route::get('timeRegistrations','TimeRegistrationsController@index');
Route::get('timeRegistrations/timeLogs','TimeRegistrationsController@timeLogs');
/** End time logs */

/** Phone statistics */

Route::get('phoneScreen','PhoneStatisticsController@index');
/** End Phone statistics */

/** random statistics  */

Route::get('statistics/','StatisticsController@index')->middleware(['role:Administrator']);
Route::get('statistics/meetings/{id?}','StatisticsController@meetingsStatistics');
Route::get('statistics/meetings-by-year/{year?}','StatisticsController@meetingsForTheYear')->middleware(['role:Administrator']);
Route::get('statistics/periodization','StatisticsController@periodization');
Route::get('statistics/contract-values/{id?}','StatisticsController@contractsValue')->middleware(['acl:contractDailyStats,get']);
Route::post('statistics/contract-values/{id?}','StatisticsController@contractsValue')->middleware(['acl:contractDailyStats,get']);
Route::get('statistics/expected-payments/{month?}','StatisticsController@expectedPayments')->middleware(['role:Administrator']);
Route::get('statistics/missing-payments/{id?}','SalariesController@missingPayments')->middleware(['acl:salaries,overview']);
Route::get('statistics/clients','StatisticsController@clients');
Route::get('statistics/clients/by-type','StatisticsController@clientsByType')->middleware(['acl:clientAlias,patch']);
Route::get('statistics/clients/active','StatisticsController@activeClients')->middleware(['acl:clientAlias,get']);
Route::get('statistics/optimizations/{id?}','StatisticsController@optimizations');
Route::post('statistics/optimizations/{id?}','StatisticsController@optimizations');
Route::get('statistics/sellers-overview/{id?}','StatisticsController@sellersOverview');
Route::get('statistics/sellers-screen','StatisticsController@sellerScreen');
Route::get('statistics/old-sellers','StatisticsController@deadSellers');

/** end random statistics  */


/** Settings  */
Route::get('settings','SettingsController@index')->middleware(['role:Administrator','acl:settings,patch']);
Route::get('settings/updateMetadata','SettingsController@updateMetadata')->middleware('role:Developer');
Route::get('settings/edit/{id}','SettingsController@edit')->middleware('acl:settings,patch');
    
/**
 * Rest logic
 */

Route::get('app/relatedEntities/{model}','RestController@getRelatedEntities');
Route::get('app/userRelations/{model}','RestController@getUserRelations');
Route::get('app/is-allowed/{controller}/{action}','RestController@getUserRelations');

Route::get('app/get-budget','AdwordsController@getBudget');

Route::post('app/check-adwords-link','AdwordsController@checkAdwordsLink');
Route::post('app/cancel-invitation','AdwordsController@cancelInvitation');
Route::post('app/send-invitation','AdwordsController@sendInvitation');


/** end of Rest logic*/

/**  End Settings  */

/** Task lists */
Route::get('tasks','TaskListsController@index')->middleware(['acl:taskLists,get']);
Route::get('tasks/create','TaskListsController@create');
Route::get('tasks/show/{id}','TaskListsController@show');
Route::post('tasks/forSideMenu','TaskListsController@forSideMenu');
Route::post('tasks/myTasks','TaskListsController@myTasks');
Route::post('tasks/itemTasks','TaskListsController@itemTasks');
Route::get('tasks/edit/{id}','TaskListsController@edit');
Route::get('tasks/quickEdit/{id}','TaskListsController@quickEdit');
Route::post('tasks','TaskListsController@store');
Route::post('tasks/save','TaskListsController@save');
Route::put('tasks/update/{id}','TaskListsController@update');
Route::post('tasks/check','TaskListsController@check');
Route::post('tasks/uncheck','TaskListsController@uncheck');
/** End task lists */

/** LEADS */
Route::get('leads','LeadsController@all')->middleware(['acl:leads,get']);
Route::get('leads/assign','LeadsController@assign')->middleware(['acl:leads,assign']);
Route::get('leads/create','LeadsController@create')->middleware(['acl:leads,post']);
Route::get('leads/show/{id}','LeadsController@show')->middleware(['acl:leads,get']);
Route::get('leads/edit/{id}','LeadsController@edit')->middleware('acl:leads,patch');
Route::get('leads/move','LeadsController@move')->middleware(['acl:leads,assign']);
Route::get('leads/bot','LeadsController@bot');

    /** END LEADS */


Route::group(['middleware'=>['acl:acls,get']],function() {

/** ACL */
Route::get('acl', 'AclController@index');
Route::get('acl/userRoles/{id?}', 'AclController@userRoles')->middleware('acl:acls,post');
Route::get('acl/userPermissions/{userId}', 'AclController@userPermissions')->middleware('acl:acls,post,');
Route::get('acl/rolePermissions/{id?}', 'AclController@rolePermissions')->middleware('acl:acls,post');
Route::get('acl/userPermissions/{id?}', 'AclController@userPermissions')->middleware('acl:acls,post');
Route::get('acl/roles', 'AclController@roles');
Route::get('acl/roles/create', 'AclController@createRole')->middleware(['acl:roles,post']);
Route::get('acl/roles/edit/{id}', 'AclController@editRole')->middleware(['acl:roles,patch']);
    /** END ACL */
});

/** COUNTRIES  */
Route::get('countries','CountriesController@index')->middleware('acl:countries,get');
Route::get('countries/show/{id}','CountriesController@show')->middleware('acl:countries,get');
Route::get('countries/create/','CountriesController@create')->middleware('acl:countries,post');
Route::get('countries/edit/{id}','CountriesController@edit')->middleware('acl:countries,patch');
/** END COUNTRIES */

/** DRAFTS */

Route::get('drafts','DraftsController@index')->middleware('acl:drafts,get');
Route::get('drafts/show/{id}','DraftsController@show')->middleware('acl:drafts,get');
Route::post('drafts/updateDraftLine','DraftsController@updateDraftLine')->middleware('acl:drafts,patch');
Route::get('drafts/preview/{id}','DraftsController@draftPreview')->middleware(['acl:drafts,get']);
/** END DRAFTS */

/** ADWORDS */
Route::get('adwords/edit/{id}','AdwordsController@edit')->middleware(['acl:contracts,patch']);
Route::get('adwords','AdwordsController@index')->middleware('role:Adwords','acl:contracts,get');
Route::get('adwords/show/{id}','AdwordsController@show')->middleware(['acl:contracts,get']);

/** END ADWORDS */

/** Seo */
Route::get('seo','SeoController@index')->middleware(['acl:contracts,get']);
Route::get('seo/show/{id}','SeoController@show')->middleware(['acl:contracts,get']);
Route::get('seo/edit/{id}','ContractsController@edit')->middleware(['acl:contracts,patch']);

/** END Seo */

/**  TEAMS */
Route::get('teams','TeamsController@index')->middleware('acl:managerTeams,get');
Route::get('teams/create','TeamsController@create')->middleware('acl:managerTeams,post');
Route::get('teams/show/{id}','TeamsController@show')->middleware('acl:managerTeams,get');
Route::get('teams/edit/{id}','TeamsController@edit')->middleware('acl:managerTeams,patch');
/**  END TEAMS */


/** Templates */
Route::get('emailTemplates/edit/{id}','TemplatesController@edit')->middleware('acl:templates,patch');
Route::get('emailTemplates/show/{id}','TemplatesController@show')->middleware('acl:templates,get');
Route::get('emailTemplates','TemplatesController@index')->middleware('acl:templates,get');
Route::get('emailTemplates/create','TemplatesController@create')->middleware('acl:templates,patch');
/** END Templates */

/** Task list Templates */
Route::get('taskTemplates/edit/{id}','TaskTemplatesController@edit')->middleware(['acl:taskListTemplates,patch']);
Route::get('taskTemplates/show/{id}','TaskTemplatesController@show')->middleware('acl:taskListTemplates,get');
Route::get('taskTemplates','TaskTemplatesController@index')->middleware('acl:taskListTemplates,get');
Route::get('taskTemplates/create','TaskTemplatesController@create')->middleware('acl:taskListTemplates,post');
/** END taskList templates */


/**    Titles (employee titles) */
Route::get('titles','TitlesController@index')->middleware(['acl:titles,get']);
Route::get('titles/create','TitlesController@create')->middleware(['acl:titles,post']);
Route::get('titles/edit/{id}','TitlesController@edit')->middleware(['acl:titles,patch']);
/**  end titles */


/** Notifications */
Route::get('notifications','NotificationsController@index')->middleware(['acl:notifications,get']);
Route::get('notifications/create','NotificationsController@create')->middleware(['acl:notifications,post']);
Route::get('notifications/show/{id}','NotificationsController@show')->middleware(['acl:notifications,get']);
/** end Notifications */

/** Files */
Route::post('files/upload','FileStorageController@store')->middleware(['acl:fileStorages,post']);
Route::get('files/download/{id}','FileStorageController@download')->middleware(['acl:fileStorages,get']);
Route::get('files/preview/{id}','FileStorageController@preview')->middleware(['acl:fileStorages,get']);
Route::get('files/bulk-download','FileStorageController@bulkDownload')->middleware(['acl:fileStorages,get']);

    /** End files */


/** Accounting  */

Route::get('accounting/','AccountingController@index')->middleware('role:Accounting');
Route::get('accounting/register','AccountingController@register')->middleware('role:Accounting');
Route::get('accounting/recurring','AccountingController@recurring')->middleware(['acl:drafts,get']);
Route::get('accounting/stat/{type?}','AccountingController@stat')->middleware('role:Accounting');

/**  */


/**
 * Salary groups
 */
Route::get('salary-groups','SalaryGroupsController@index')->middleware(['acl:salaryGroups,get']);
Route::get('salary-groups/create','SalaryGroupsController@create')->middleware(['acl:salaryGroups,post']);
Route::get('salary-groups/edit/{id}','SalaryGroupsController@edit')->middleware(['acl:salaryGroups,patch']);
Route::get('salary-groups/show/{id}','SalaryGroupsController@show')->middleware(['acl:salaryGroups,get']);

/**  */

/** Client Rates */
Route::get('client-rates','ClientRatesController@index')->middleware(['acl:clientRates,get']);
Route::get('client-rates/create','ClientRatesController@create')->middleware(['acl:clientRates,post']);
Route::get('client-rates/edit/{id}','ClientRatesController@edit')->middleware(['acl:clientRates,patch']);
Route::get('client-rates/show/{id}','ClientRatesController@show')->middleware(['acl:clientRates,get']);

/**  */

/** Sales */
Route::get('sales/','SalesController@index')->middleware(['auth','role:Sales']);
Route::get('sales/my-clients','ClientAliasController@index')->middleware(['role:Sales']);
Route::get('clients-to-call','SalesController@clientsToCall')->middleware(['acl:calendarEvents,get']);

/** end Sales*/

/** Sales */
Route::get('calendar/','CalendarController@index')->middleware('acl:calendarEvents,get');
//Route::post('calendar/create-event','CalendarController@createEvent')->;
//Route::get('calendar/get-events','CalendarController@getEvents');
Route::get('calendar/get-event-types','CalendarController@getEventTypes');
/**  */

Route::get('employee-manual','EmployeeManualController@index')->middleware('acl:employeeManuals,get');
Route::get('employee-manual/{id}','EmployeeManualController@show')->middleware('acl:employeeManuals,get');
Route::get('employee-manual/edit/{id}','EmployeeManualController@edit')->middleware('acl:employeeManuals,patch');


/**
* Whitelists
*/
Route::get('white-lists','WhiteListsController@index')->middleware('acl:whitelists,get');
Route::get('white-lists/show/{id}','WhiteListsController@show')->middleware('acl:whitelists,get');
Route::get('white-lists/edit/{id}','WhiteListsController@edit')->middleware('acl:whitelists,patch');
Route::get('white-lists/create','WhiteListsController@create')->middleware('acl:whitelists,post');

/**
* End whitelists
*/
});/** This wraps all routes in web middleware */

