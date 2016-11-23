function fnServerOData(sUrl, aoData, fnCallback, oSettings) {

    var oParams = {};
    $.each(aoData, function (i, value) {
        oParams[value.name] = value.value;
    });

    var data = {
        "$format": "json"
    };

    // If OData service is placed on the another domain use JSONP.
    var bJSONP = oSettings.oInit.bUseODataViaJSONP;

    if (bJSONP) {
        data.$callback = "odatatable_" + (oSettings.oFeatures.bServerSide ? oParams.sEcho : ("load_" + Math.floor((Math.random() * 1000) + 1)));
    }

    $.each(oSettings.aoColumns, function (i, value) {
        var sFieldName = (value.sName !== null && value.sName !== "") ? value.sName : ((typeof value.mData === 'string') ? value.mData : null);
        //if (sFieldName === null || !isNaN(Number(sFieldName))) {
        //    sFieldName = value.sTitle;
        //}
        if (sFieldName === null || !isNaN(Number(sFieldName))) {
            return;
        }
        if (data.$select == null) {
            data.$select = sFieldName;
        } else {
            data.$select += "," + sFieldName;
        }

    });
    //add custom selects  to the string
    if(typeof oSettings.oInit.select !== 'undefined'){
        data.$select += "," + oSettings.oInit.select;
    }


    if (oSettings.oFeatures.bServerSide) {

        data.$skip = oSettings._iDisplayStart;
        if (oSettings._iDisplayLength > -1) {
            data.$top = oSettings._iDisplayLength;
        }

        // OData versions prior to v4 used $inlinecount=allpages; but v4 is uses $count=true
        if (oSettings.oInit.iODataVersion !== null && oSettings.oInit.iODataVersion < 4) {
            data.$inlinecount = "allpages";
        } else {
            data.$count = true;
        }

        var asFilters = [];
        var asColumnFilters = []; //used for jquery.dataTables.columnFilter.js
        $.each(oSettings.aoColumns,
            function (i, value) {

                var sFieldName = value.sName || value.mData || value.oData;
                var columnFilter = oParams["sSearch_" + i]; //fortunately columnFilter's _number matches the index of aoColumns
                // we do this check in case the filtering is disabled in datatables
                if(typeof columnFilter !== 'undefined'){
                    if ((oParams.sSearch !== null && oParams.sSearch !== "" || columnFilter !== null && columnFilter !== "") && value.bSearchable) {
                        switch (value.sType) {
                        case 'string':
                        case 'html':

                            if (oParams.sSearch !== null && oParams.sSearch !== "")
                            {
                                // asFilters.push("substringof('" + oParams.sSearch + "', " + sFieldName + ")");
                                // substringof does not work in v4???
                                asFilters.push("indexof(tolower(" + sFieldName + "), '" + oParams.sSearch.toLowerCase().replace(/'/g, "''") + "') gt -1");
                            }

                            if (columnFilter !== null && columnFilter !== "") {
                                asColumnFilters.push("indexof(tolower(" + sFieldName + "), '" + columnFilter.toLowerCase().replace(/'/g, "''") + "') gt -1");
                            }
                            break;

                        case 'date':
                            var fnFormatValue =
                                (value.sType == 'numeric') ?
                                    function(val) { return val } :
                                    function(val) {
                                        // Here is a mess. OData V2, V3, and V4 se different formats of DateTime literals.
                                        switch(oSettings.oInit.iODataVersion){
                                            //v4 is iso string, apperantly
                                            case 4: return (new Date(val)).toISOString();
                                            // V3 works with the following format:
                                            // http://services.odata.org/V3/OData/OData.svc/Products?$filter=(ReleaseDate+lt+datetimeoffset'2008-01-01T07:00:00')
                                            case 3: return "datetimeoffset'" + (new Date(val)).toISOString() + "'";
                                            // V2 works with the following format:
                                            // http://services.odata.org/V2/OData/OData.svc/Products?$filter=(ReleaseDate+lt+DateTime'2014-04-29T09:00:00.000Z')
                                            case 2: return "DateTime'" + (new Date(val)).toISOString() + "'";
                                        }
                                    };
                            // Currently, we cannot use global search for date and numeric fields (exception on the OData service side)
                            // However, individual column filters are supported in form lower~upper

                            if (columnFilter !== null && columnFilter !== "" && columnFilter !== "~") {
                                asRanges = columnFilter.split("~");
                                if (asRanges[0] !== "") {
                                    asColumnFilters.push("(" + sFieldName + " gt " + fnFormatValue(asRanges[0]) + ")");
                                }
                                if (asRanges[1] !== "") {
                                    asColumnFilters.push("(" + sFieldName + " lt " + fnFormatValue(asRanges[1]) + ")");
                                }
                            }
                            break;
                            case 'numeric':
                                if (oParams.sSearch !== null && oParams.sSearch !== "")
                                {
                                    // asFilters.push("substringof('" + oParams.sSearch + "', " + sFieldName + ")");
                                    // substringof does not work in v4???
                                    asFilters.push("indexof(cast("+sFieldName+", 'Edm.String'),'"+oParams.sSearch.toLowerCase().replace(/'/g, "''") +"') gt -1");
                                }
                                break;
                        default:
                        }
                    }
                }
            });

        //check if we have custom filters set in the DataTables definitions and add them with "and"
        if(oSettings.oInit.filter != undefined){
            //add the search filter
            if(asFilters.length > 0){
                data.$filter = oSettings.oInit.filter;
                data.$filter += " and ("+asFilters.join(" or ")+")";
            }else{
                data.$filter = oSettings.oInit.filter;
            }
        }else{
            if (asFilters.length > 0) {
                data.$filter = asFilters.join(" or ");
            }
        }

        // todo commented because of new way of doing it
        // if (asFilters.length > 0) {
        //     data.$filter = asFilters.join(" or ");
        //     //check if we have custom filters set in the DataTables definitions and add them with "and"
        //     if(oSettings.oInit.filter != undefined){
        //         data.$filter += " and "+oSettings.oInit.filter;
        //     }
        //     //if we don't have other filters, just add the custom ones
        // } else if(oSettings.oInit.filter != undefined)
        // {
        //    data.$filter = oSettings.oInit.filter;
        // }

        if (asColumnFilters.length > 0) {
            if (data.$filter !== undefined) {
                data.$filter = " ( " + data.$filter + " ) and ( " + asColumnFilters.join(" and ") + " ) ";
            } else {
                data.$filter = asColumnFilters.join(" and ");
            }
        }

        var asOrderBy = [];
        for (var i = 0; i < oParams.iSortingCols; i++) {
            //workarround for odata 4 where we can't access Property of $expanded entities
            if(oParams["mDataProp_" + oParams["iSortCol_" + i]] == null) {
                asOrderBy.push(oSettings['aoColumns'][oParams["iSortCol_" + i]]['oData'] + " " + (oParams["sSortDir_" + i] || ""));
            }else{
            asOrderBy.push(oParams["mDataProp_" + oParams["iSortCol_" + i]] + " " + (oParams["sSortDir_" + i] || "desc"));
            }
        }
        if (asOrderBy.length > 0) {
            data.$orderby = asOrderBy.join();
        }
    }
    $.ajax(jQuery.extend({}, oSettings.oInit.ajax, {
        "url": sUrl,
        "data": data,
        "jsonp": bJSONP,
        "dataType": bJSONP ? "jsonp" : "json",
        "jsonpCallback": data["$callback"],
        "cache": false,
        //this sets a auth header with the current user's authentication token
        'beforeSend': function (request) {
            request.setRequestHeader("Authorization", "Bearer "+ $.cookie('auth'));
        },
        "success": function (data) {
            var oDataSource = {};
            // Probe data structures for V4, V3, and V2 versions of OData response
            oDataSource.aaData = data.value || (data.d && data.d.results) || data.d;
            var iCount = (data["@odata.count"]) ? data["@odata.count"] : ((data["odata.count"]) ? data["odata.count"] : ((data.__count) ? data.__count : (data.d && data.d.__count)));

            if (iCount == null) {
                if (oDataSource.aaData.length === oSettings._iDisplayLength) {
                    oDataSource.iTotalRecords = oSettings._iDisplayStart + oSettings._iDisplayLength + 1;
                } else {
                    oDataSource.iTotalRecords = oSettings._iDisplayStart + oDataSource.aaData.length;
                }
            } else {
                oDataSource.iTotalRecords = iCount;
            }
            oDataSource.iTotalDisplayRecords = oDataSource.iTotalRecords;
            fnCallback(oDataSource);
        }
    }));

} // end fnServerData