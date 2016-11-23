
// prototype and helper functions
/**
 * custom function that expands $.when
 * @see http://stackoverflow.com/a/16208232
 */
if (jQuery.when.all === undefined) {
    jQuery.when.all = function (deferreds) {
        var deferred = new jQuery.Deferred();
        $.when.apply(jQuery, deferreds).then(
            function () {
                deferred.resolve(Array.prototype.slice.call(arguments));
            },
            function () {
                deferred.fail(Array.prototype.slice.call(arguments));
            });
        return deferred;
    }
}

/**
 *
 * @param value
 * @param array
 * @returns {boolean|*}
 */
function isInArray(value, array) {
    result = array.indexOf(value) > -1;
    return result;
}
function minutesToStr(minutes) {
    var sign = '';
    if (minutes < 0) {
        sign = '-';
    }
    var hours = (Math.floor(Math.abs(minutes) / 60));
    var minutes = leftPad(Math.abs(minutes) % 60);

    return sign + hours + ' h ' + minutes + ' min';
}

/*
 * add zero to numbers less than 10,Eg: 2 -> 02
 */
function leftPad(number) {
    return ((number < 10 && number >= 0) ? '0' : '') + number;
}
/**
 * combines VAT + Net amount on invoice
 */
function invoiceValue(invoice, suffix) {
    if (typeof suffix == "undefined") {
        suffix = "DKK";
    }
    if (invoice.NetAmount != null && invoice.VatAmount != null) {
        var amount = Number(invoice.NetAmount) + Number(invoice.VatAmount);
        return amount.toLocaleString('da') + " " + suffix;
    } else {
        return ""
    }
}

/**
 * checks two hast maps for differentces
 *
 * @param h1
 * @param h2
 * @returns {{}}
 */
function hashDiff(h1, h2) {
    var d = {};
    var k;
    for (k in h2) {
        if (h1[k] !== h2[k]) d[k] = h2[k];
    }
    return d;
}

/**
 * turns serialized form array into hash map
 *
 * @param a
 * @returns {{}}
 */
function convertSerializedArrayToHash(a) {
    var r = {};
    for (var i = 0; i < a.length; i++) {
        r[a[i].name] = a[i].value;
    }
    delete (r['_token']);
    return r;
}
/*!
 * jQuery UI Touch Punch 0.2.3
 *
 * Copyright 2011–2014, Dave Furfero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Depends:
 *  jquery.ui.widget.js
 *  jquery.ui.mouse.js
 */

/**
 * check if the url is valid and add http
 */
function addhttp(url) {
    if (!/^(f|ht)tps?:\/\//i.test(url)) {
        url = "http://" + url;
    }
    return url;
}

(function ($) {
    /**
     * Auto-growing textareas; technique ripped from Facebook
     *
     *
     * http://github.com/jaz303/jquery-grab-bag/tree/master/javascripts/jquery.autogrow-textarea.js
     */
    $.fn.autogrow = function (options) {
        return this.filter('textarea').each(function () {
            var self = this;
            var $self = $(self);
            var minHeight = $self.height();
            var noFlickerPad = $self.hasClass('autogrow-short') ? 0 : parseInt($self.css('lineHeight')) || 0;
            var settings = $.extend({
                preGrowCallback: null,
                postGrowCallback: null
            }, options);

            var shadow = $('<div></div>').css({
                position: 'absolute',
                top: -10000,
                left: -10000,
                width: $self.width(),
                fontSize: $self.css('fontSize'),
                fontFamily: $self.css('fontFamily'),
                fontWeight: $self.css('fontWeight'),
                lineHeight: $self.css('lineHeight'),
                resize: 'none',
                'word-wrap': 'break-word'
            }).appendTo(document.body);

            var update = function (event) {
                var times = function (string, number) {
                    for (var i = 0, r = ''; i < number; i++) r += string;
                    return r;
                };

                var val = self.value.replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\n$/, '<br/>&#xa0;')
                    .replace(/\n/g, '<br/>')
                    .replace(/ {2,}/g, function (space) { return times('&#xa0;', space.length - 1) + ' ' });

                // Did enter get pressed?  Resize in this keydown event so that the flicker doesn't occur.
                if (event && event.data && event.data.event === 'keydown' && event.keyCode === 13) {
                    val += '<br />';
                }

                shadow.css('width', $self.width());
                shadow.html(val + (noFlickerPad === 0 ? '...' : '')); // Append '...' to resize pre-emptively.

                var newHeight = Math.max(shadow.height() + noFlickerPad, minHeight);
                if (settings.preGrowCallback != null) {
                    newHeight = settings.preGrowCallback($self, shadow, newHeight, minHeight);
                }

                $self.height(newHeight);

                if (settings.postGrowCallback != null) {
                    settings.postGrowCallback($self);
                }
            }

            $self.change(update).keyup(update).keydown({ event: 'keydown' }, update);
            $(window).resize(update);

            update();
        });
    };
})(jQuery);

var re_weburl = new RegExp(
    "^" +
    // protocol identifier
    "(?:(?:https?|ftp)://)" +
    // user:pass authentication
    "(?:\\S+(?::\\S*)?@)?" +
    "(?:" +
    // IP address exclusion
    // private & local networks
    "(?!(?:10|127)(?:\\.\\d{1,3}){3})" +
    "(?!(?:169\\.254|192\\.168)(?:\\.\\d{1,3}){2})" +
    "(?!172\\.(?:1[6-9]|2\\d|3[0-1])(?:\\.\\d{1,3}){2})" +
    // IP address dotted notation octets
    // excludes loopback network 0.0.0.0
    // excludes reserved space >= 224.0.0.0
    // excludes network & broacast addresses
    // (first & last IP address of each class)
    "(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])" +
    "(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}" +
    "(?:\\.(?:[1-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))" +
    "|" +
    // host name
    "(?:(?:[a-z\\u00a1-\\uffff0-9]-*)*[a-z\\u00a1-\\uffff0-9]+)" +
    // domain name
    "(?:\\.(?:[a-z\\u00a1-\\uffff0-9]-*)*[a-z\\u00a1-\\uffff0-9]+)*" +
    // TLD identifier
    "(?:\\.(?:[a-z\\u00a1-\\uffff]{2,}))" +
    // TLD may end with dot
    "\\.?" +
    ")" +
    // port number
    "(?::\\d{2,5})?" +
    // resource path
    "(?:[/?#]\\S*)?" +
    "$", "i"
);


function validateUrl(url) {
    return url.match(re_weburl);
}
/**
 *
 *
 * @param email
 * @returns {boolean}
 */
function validateEmail(email) {
    var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    return re.test(email);
}

/**
 * @deprecated
 * error handler for ajax calls to the backend
 * shows a pnotify message
 */
function handleError(xhr, status, error) {
    return;
    // if(xhr.status == 401){
    //     new PNotify({
    //         title: Lang.get('labels.error'),
    //         text: Lang.get('messages.no-permissions'),
    //         type: 'error'
    //     });
    //     return;
    // }
    //
    // //sometimes we refresh the page while requests are loading. if the status is 0, don't give an error
    // if(xhr.status !== 0){
    //    // var errorMessage = xhr.responseJSON.error.message || error.message || error.responseJSON.error.innererror.message || error.message || Lang.get('labels.server-error');
    //     // if(typeof error.message !== 'undefined'){
    //     //     errorMessage = error.message;
    //     // }else if(typeof error.responseJSON !== 'undefined'){
    //     //      errorMessage = error.responseJSON.error.innererror.message;
    //     // }else{
    //     //     var errorMessage = xhr.responseText;
    //     //     if(typeof errorMessage == "object"){
    //     //         errorMessage= (errorMessage.error.message)
    //     //     }
    //     // }
    //     // //last if it's still undefined, do a standard message
    //     // if(typeof errorMessage == 'undefined'){
    //     //     errorMessage = Lang.get('labels.server-error')
    //     // }
    //
    //
    // }
    // // save the error in the logs
    // saveError(xhr);

}
$(document).ajaxError((function (event, error, settings) {
    // if (settings.suppressErrors) {
    //     return;
    // }
    // sometimes we refresh and ajax requests get cancelled, before initialization. Only throw an error for states, other than 0
    if (error.readyState != 0) {

        if (!settings.suppressErrors) {
            new PNotify({
                title: Lang.get('labels.error'),
                text: Lang.get(error.statusText) + '<br>' + 'Developers are notified',
                type: 'error'
            });
        }
        var message = settings.type + " " + settings.url + '\r\n';
        if (error.responseJSON) {
            message += error.responseJSON.error.message;
            if (typeof error.responseJSON.error.innererror !== 'undefined') {
                message += error.responseJSON.error.innererror.message;
            }
        } else if (error.statusText) {
            message += 'Response status : ' + error.statusText;
        } else if (error.message) {
            message = error.message;
        }
        // message += error.responseJSON.error.message || error.responseJSON.error.innererror.message || error.statusText+'\r\n';
        message += '\r\n' + "Page :" + event.target.URL + '\r\n';
        if (settings.type == "POST" || settings.type == "PATCH" || settings.type == "PUT") {
            message += '\r\n' + settings.type + ' body : ' + settings.data;
        }
        if (typeof settings.saveError == 'undefined' || settings.saveError) {
            saveError(message);
        }
    }
}));

function saveError(message) {

    var data = {
        Module: getModel(),
        ItemId: getModelId(),
        Error: message,
        User_Id: getUserId()
    };

    $.ajax({
        method: 'POST',
        url: api_address + 'Logs',
        data: JSON.stringify(data),
        suppressErrors: true,// if this request fails, we will have infinite loop of errors
        saveError: false,
        success: function (data) {
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}
//replace all occurences
String.prototype.replaceAll = function (search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};
/**
 * format to danish krona
 * @returns {string}
 * @param rounded
 */
Number.prototype.format = function (rounded) {

    if (typeof rounded == 'undefined') {
        rounded = 2
    } else {
        rounded = 0
    }
    return this.toLocaleString('da-DK', { maximumFractionDigits: rounded, minimumFractionDigits: rounded });

    // var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
    // num = this.toFixed(Math.max(0, ~~n));
    // return (s && p ? s : '') + (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (d || ',')) + (s && !p ? s : '');
};

/**
 * basic padding
 * @param length
 * @param character
 * @returns {string}
 */
String.prototype.padLeft = function (length, character) {
    return new Array(length - this.length + 1).join(character || ' ') + this;
};

/**
 * Formats date object to datetime string in d-m-Y H:i format
 *
 * @returns {string}
 */
Date.prototype.toDateTime = function () {
    return [String(this.getDate()).padLeft(2, '0'),
            String(this.getMonth() + 1).padLeft(2, '0'),
            String(this.getFullYear())].join("-") + " " +
        [String(this.getHours()).padLeft(2, '0'),
            String(this.getMinutes()).padLeft(2, '0')].join(":");
};

/**
 * Formats date object to datetime string in d-m-Y H:i format
 *
 * @returns {string}
 */
Date.prototype.toDate = function () {
    return [String(this.getDate()).padLeft(2, '0'),
            String(this.getMonth() + 1).padLeft(2, '0'),
            String(this.getFullYear())].join("-")
};

function toDateTime(date) {
    var d = new Date(date);
    return d.toDateTime();
}

/**
 * Formats date object to date string in d-m-Y format
 *
 * @returns {string}
 */
Date.prototype.toDate = function () {
    return [String(this.getDate()).padLeft(2, '0'),
        String(this.getMonth() + 1).padLeft(2, '0'),
        String(this.getFullYear())].join("-");
};
function toDate(date) {
    var d = new Date(date);
    return d.toDate();
}

function monthsListBetweenDates(start, end) {
    var dateStart = moment(start);
    var dateEnd = moment(end);
    var timeValues = [];

    while (dateEnd > dateStart) {
        timeValues.push(dateStart.format('YYYY-M'));
        dateStart.add(1, 'month');
    }
    return timeValues;
}


/**
 * differemt formatters for the templating engine
 */
$.addTemplateFormatter({
    DateFormatter: function (value, template) {
        if (value == null) return "";
        var date = new Date(value);
        return date.toDate();
    },
    DateTimeFormatter: function (value, template) {
        if (value == null) return "";
        var date = new Date(value);
        return date.toDateTime();
    },
    UpperCaseFormatter: function (value, template) {
        return value.toUpperCase();
    },
    LowerCaseFormatter: function (value, template) {
        return value.toLowerCase();
    },
    SameCaseFormatter: function (value, template) {
        if (template == "upper") {
            return value.toUpperCase();
        } else {
            return value.toLowerCase();
        }
    }
});

function getYearsSelect(startYear) {
    var currentYear = new Date().getFullYear(), years = [];
    startYear = startYear || currentYear - 2;

    while (startYear <= currentYear + 2) {
        years.push(startYear++);
    }
    return years;
}

/**
 * team urls ( adwords/seo)
 */
var contract_team_urls = { 'Adwords': 'adwords', 'SEO': 'seo' };
var contract_default_url = 'contracts';
var paymentTermsDK = {
    'Monthly': "Månedsvis",
    "Quarterly": "Kvartalsvis",
    "Semiannually": "Halvårlig",
    "Annually": "Årlig",
    "OneTime": "Engangs betaling"
};

/**
 * Addresses for different servers.
 * @type {string}
 */
var localhost = 'http://localhost:8080/api/';
var liveServer = 'http://gcmdev.dk/api/';
var server = 'http://svn.crmtest.dk:8483/api/';
var api_address = server;

// ------------------------------
// Sidebar Accordion Menu
// ------------------------------

$((function () {
    //if($.cookie('admin_leftbar_collapse') === 'collapse-leftbar') {
    //    $('body').addClass('collapse-leftbar');
    //} else {
    //    $('body').removeClass('collapse-leftbar');
    //}
    $('body').on('click', 'ul.acc-menu a', (function () {
        var LIs = $(this).closest('ul.acc-menu').children('li');
        $(this).closest('li').addClass('clicked');
        $.each(LIs, (function (i) {
            if ($(LIs[i]).hasClass('clicked')) {
                $(LIs[i]).removeClass('clicked');
                return true;
            }
            if ($.cookie('admin_leftbar_collapse') !== 'collapse-leftbar' || $(this).parents('.acc-menu').length > 1) $(LIs[i]).find('ul.acc-menu:visible').slideToggle();
            $(LIs[i]).removeClass('open');
        }));
        if ($(this).siblings('ul.acc-menu:visible').length > 0)
            $(this).closest('li').removeClass('open');
        else
            $(this).closest('li').addClass('open');
        if ($.cookie('admin_leftbar_collapse') !== 'collapse-leftbar' || $(this).parents('.acc-menu').length > 1) $(this).siblings('ul.acc-menu').slideToggle({
            duration: 200,
            progress: function () {
                checkpageheight();
                if ($(this).closest('li').is(":last-child")) { //only scroll down if last-child
                    $("#sidebar").animate({ scrollTop: $("#sidebar").height() }, 0);
                }
            },
            complete: function () {
                $("#sidebar").getNiceScroll().resize();
            }
        });
    }));

    var targetAnchor;
    $.each($('ul.acc-menu a'), (function () {
        if (this.href == window.location) {
            targetAnchor = this;
            return false;
        }
    }));

    var parent = $(targetAnchor).closest('li');
    while (true) {
        parent.addClass('active');
        parent.closest('ul.acc-menu').show().closest('li').addClass('open');
        parent = $(parent).parents('li').eq(0);
        if ($(parent).parents('ul.acc-menu').length <= 0) break;
    }

    var liHasUlChild = $('li').filter((function () {
        return $(this).find('ul.acc-menu').length;
    }));
    $(liHasUlChild).addClass('hasChild');

    if ($.cookie('admin_leftbar_collapse') === 'collapse-leftbar') {
        $('ul.acc-menu:first ul.acc-menu').css('visibility', 'hidden');
    }
    $('ul.acc-menu:first > li').hover((function () {
        if ($.cookie('admin_leftbar_collapse') === 'collapse-leftbar')
            $(this).find('ul.acc-menu').css('visibility', '');
    }), (function () {
        if ($.cookie('admin_leftbar_collapse') === 'collapse-leftbar')
            $(this).find('ul.acc-menu').css('visibility', 'hidden');
    }));

    // Reads Cookie for Collapsible Leftbar
    if ($.cookie('admin_leftbar_collapse') === 'collapse-leftbar')
        $("body").addClass("collapse-leftbar");

    //Make only visible area scrollable
    $("#widgetarea").css({ "max-height": $("body").height() });
    //Bind widgetarea to nicescroll
    $("#widgetarea").niceScroll({ horizrailenabled: false });


    //Will open menu if it has link
    //$('.hasChild.active ul.acc-menu').slideToggle({duration: 200});

    // Toggle Buttons
    // ------------------------------

    //On click of left menu
    $("a#leftmenu-trigger").click((function () {
        if ((window.innerWidth) < 768) {
            $("body").toggleClass("show-leftbar");
        } else {
            $("body").toggleClass("collapse-leftbar");

            //Sets Cookie for Toggle
            if ($.cookie('admin_leftbar_collapse') === 'collapse-leftbar') {
                $.cookie('admin_leftbar_collapse', '');
                $('ul.acc-menu').css('visibility', '');

            } else {
                $.each($('.acc-menu'), (function () {
                    if ($(this).css('display') == 'none')
                        $(this).css('display', '');
                }));

                $('ul.acc-menu:first ul.acc-menu').css('visibility', 'hidden');
                $.cookie('admin_leftbar_collapse', 'collapse-leftbar');
            }
        }
        checkpageheight();
        leftbarScrollShow();
    }));

    // On click of right menu
    $("a#rightmenu-trigger").click((function () {
        $("body").toggleClass("show-rightbar");
        widgetheight();

        if ($.cookie('admin_rightbar_show') === 'show-rightbar')
            $.cookie('admin_rightbar_show', '');
        else
            $.cookie('admin_rightbar_show', 'show-rightbar');
    }));

    //set minimum height of page
    dh = ($(document).height() - 40);
    $("#page-content").css("min-height", dh + "px");
    checkpageheight();

}));

// Recalculate widget area on a widget being shown
$(".widget-body").on('shown.bs.collapse', (function () {
    widgetheight();
}));

// -------------------------------
// Sidebars Positionings
// -------------------------------

$(window).scroll((function () {
    $("#widgetarea").getNiceScroll().resize();
    $(".chathistory").getNiceScroll().resize();
    rightbarTopPos();
    leftbarTopPos();
}));

$(window).resize((function () {
    widgetheight();

    rightbarRightPos();
    $("#sidebar").getNiceScroll().resize();
}));
rightbarRightPos();

// -------------------------------
// Mobile Only - set sidebar as fixed position, slide
// -------------------------------
//
//enquire.register("screen and (max-width: 767px)", {
//    match : function() {
//        // For less than 768px
//        $(function() {
//
//            //Bind sidebar to nicescroll
//            $("#sidebar").niceScroll({horizrailenabled:false});
//            leftbarScrollShow();
//
//            //Click on body and hide leftbar
//            $("#wrap").click(function(){
//                if ($("body").hasClass("show-leftbar")) {
//                    $("body").removeClass("show-leftbar");
//                    leftbarScrollShow();
//                }
//            });
//
//            //Fix a bug
//            $('#sidebar ul.acc-menu').css('visibility', '');
//
//            //open up leftbar
//            $("body").removeClass("show-leftbar");
//            $.removeCookie("admin_leftbar_collapse");
//
//            $("body").removeClass("collapse-leftbar");
//
//        });
//
//        console.log("match");
//    },
//    unmatch : function() {
//
//        //Remove nicescroll to clear up some memory
//            $("#sidebar").niceScroll().remove();
//            $("#sidebar").css("overflow","visible");
//
//        console.log("unmatch");
//
//        //hide leftbar
//        $("body").removeClass("show-leftbar");
//
//    }
//});

//Helper functions
//---------------

//Fixing the show of scroll rails even when sidebar is hidden
function leftbarScrollShow() {
    if ($("body").hasClass("show-leftbar")) {
        $("#sidebar").getNiceScroll().show();
    } else {
        $("#sidebar").getNiceScroll().hide();
    }
    $("#sidebar").getNiceScroll().resize();
}
//set Top positions for changing between static and fixed header
function leftbarTopPos() {
    var scr = $('body.static-header').scrollTop();
    if (scr < 41) {
        $('ul#sidebar').css('top', 40 - scr + 'px');
    } else {
        $('ul#sidebar').css('top', 0);
    }
}
function rightbarTopPos() {
    var scr = $('body.static-header').scrollTop();
    if (scr < 41) {
        $('#page-rightbar').css('top', 40 - scr + 'px');
    } else {
        $('#page-rightbar').css('top', 0);
    }
}
//Set Right position for fixed layouts
function rightbarRightPos() {
    if ($('body').hasClass('fixed-layout')) {
        var $pc = $('#page-content');
        var ending_right = ($(window).width() - ($pc.offset().left + $pc.outerWidth()));
        if (ending_right < 0) ending_right = 0;
        $('#page-rightbar').css('right', ending_right);
    }
}
// Match page height with Sidebar Height
function checkpageheight() {
    sh = $("#page-leftbar").height();
    ch = $("#page-content").height();

    if (sh > ch) $("#page-content").css("min-height", sh + "px");
}
// Recalculate widget area to area visible
function widgetheight() {
    $("#widgetarea").css({ "max-height": $("body").height() });
    $("#widgetarea").getNiceScroll().resize();
}
// -------------------------------
// Back to Top button
// -------------------------------

$('#back-to-top').click((function () {
    $('body,html').animate({
        scrollTop: 0
    }, 500);
    return false;
}));

// -------------------------------
// Panel Collapses
// -------------------------------
$('a.panel-collapse').click((function () {
    $(this).children().toggleClass("fa-chevron-down fa-chevron-up");
    $(this).closest(".panel-heading").next().slideToggle({ duration: 200 });
    $(this).closest(".panel-heading").toggleClass('rounded-bottom');
    ////eventual comments
    //var commentsSection=$('.modelComments');
    //if(commentsSection.length > 0){
    //    //reinitalize nicescroll
    //    commentsSection.getNiceScroll().remove();
    //    commentsSection.niceScroll({horizrailenabled:false});
    //}
    return false;
}));
// -------------------------------
// Quick Start
// -------------------------------
$('#headerbardropdown').click((function () {
    $('#headerbar').css('top', 0);
    return false;
}));
$('#headerbardropdown').click((function (event) {
    $('html').one('click', (function () {
        $('#headerbar').css('top', '-1000px');
    }));
    event.stopPropagation();
}));
// -------------------------------
// Keep search open on click
// -------------------------------
$('#search>a').click((function () {
    $('#search').toggleClass('keep-open');
    $('#search>a i').toggleClass("opacity-control");
}));

$('#search').click((function (event) {
    $('html').one('click', (function () {
        $('#search').removeClass('keep-open');
        $('#search>a i').addClass("opacity-control");
    }));

    event.stopPropagation();
}));

//Presentational: set all panel-body with br0 if it has panel-footer
$(".panel-footer").prev().css("border-radius", "0");

//base url loaction used in some functions
var base_url = location.origin;


/**
 *
 * CUSTOM SCRIPTS FOR THE CRM
 */
$(document).ready((function () {
    var authCookie = $.cookie('auth');
    if (!authCookie) {
        $.get(base_url + "/auth/logout", (function (data) {
            window.location.replace(base_url + '/auth/login')
        }));
    }

    //// this sends a token with each ajax request. Cross-site protection
    $.ajaxSetup({
        headers: {
            'Authorization': 'Bearer ' + authCookie,
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('textarea.autosize').autogrow();

    if ($('#rightmenu-trigger').length) {
        var model = getModel();
        var modelId = getModelId();

        //get amount of tasks and put a label on the right menu icon
        taskCountBadge();
        if (model && modelId) {
            itemTaskCountBadge(model, modelId);
        }
        $('#rightmenu-trigger').click((function () {
            // we request tasks,only if we are opening the sidebar
            if ($('body').hasClass("show-rightbar")) {
                //load tasks for the side menu
                // sideMenuTasks();
                // load personal tasks
                myTasks();
                //item specific tasks
                if (model && modelId) {
                    itemTasks(model, modelId);
                }
            }
        }));
    }


    var body = $('body');
    //mark notifications as seen
    body.on('click', '.read-all-notifications', (function (event) {
        markNotificationsAsSeen(event);
        return false;
    }));

    //show subtasks in modal when clicked from the right sidebar

    body.on('click', '.expandTasks', (function (event) {
        event.preventDefault();
        var id = $(event.target).closest('.rightMenuTaskContainer').find('.taskCheck').val();
        expandSubtasks(id);
    }))


    /**
     * Shows notification information in modal
     *
     */
    body.on('click', '.notification', (function (event) {
        showNotification(event);
        return false;
    }));


    body.on('click', '.mark-as-not-seen', (function (event) {
        var noti_id = $(event.target).data('notification-id');
        setNotificationAsNotSeen(noti_id);
        return false;
    }));

    /**
     * delete contacts
     */
    body.on('click', '.deleteContactBtn', (function (event) {
        deleteContact(event);
        return false;
    }));

    body.on('submit', '#addContactToAlias', (function (event) {
        saveContact(event);
        return false;
    }));

    body.on('click', '.loadContactsTab', (function (event) {
        $(event.target).removeClass('loadContactsTab');
        initContacts()
    }));

    if ($('#item-files').length > 0) {
        getItemFiles();
    }

    body.on('click', '.downloadFile', (function (event) {
        downloadFile(event);
        return false;
    }));

    body.on('click', '.deleteFile', (function (event) {
        deleteFile(event);
        return false;
    }));

    //if we have elements with dropzone class, then make them dropzones
    if ($('form.dropzone').length > 0) {
        initializeDropzone();
    }

    //if the notifications menu is rendered, request the newest
    if ($('.headerNotifications').length > 0) {
        getNotifications();

        // body.on('click','#openNotifications',function () {
        // });
    }

    $('.createOrder').on('click', (function () {
        openCreateOrderModal();
        return false;
    }));


    // show more or less text on log descriptions
    /**
     * function that makes a text appear multiline when we click "Show more"
     */
    body.on('click', '.more-link', (function () {
        if ($(this).closest('.is-more').hasClass('multiline')) {
            $(this).closest('.is-more').removeClass('multiline');
        } else {
            $(this).closest('.is-more').addClass('multiline');
        }
    }));


    // all logic and event listeners if appointment section is loaded
    if ($('input#userSearch_appointments').length > 0) {
        initializeAppointments();
    }

    // all logic and event listeners if appointment section is loaded
    if ($('#admin').length > 0) {
        initializeAdminMenu();
    }

    //display update modal for each appointment
    body.on('click', '.updateAppointment', (function (event) {
        event.preventDefault();
        var id = $(event.target).data('calendarevent-id');
        updateAppointment(id);

    }));

    // End of appointments logic
    body.on('click', '#addAttendeeToEvent', (function (event) {
        event.preventDefault();
        var field = $('#add-attendee-field');
        field.closest('.form-group').removeClass('has-error');
        //attendee input
        //disable the button untill the function is done
        var email = field.val();

        if (!validateEmail(email)) {
            field.closest('.form-group').addClass('has-error')
        } else {
            addAttendee(email);
        }
    }));

    body.on('click', '.previewFile', (function (event) {
        previewFile(event);
        return false;
    }));

    body.on('click', '.quickEditTask', (function (event) {
        event.preventDefault();
        //get the task id
        var id = $(event.target).closest('.rightMenuTaskContainer').find('.taskCheck').val();
        quickEditTask(id);
    }));

    //initialize seo checklist
    if ($('.seoChecklist').length > 0) {
        initializeSeoChecklist();
    }

    body.on('submit', '#quickTaskEditForm', (function (event) {
        event.preventDefault();
        var formData = $(this).serializeJSON();
        updateTask(event, formData);
    }));


    body.on('change', '.taskCheck', (function (event) {
        checkTask(event);
        return false;
    }));

    $('.seeAllComments').click((function () {
        $('#myTab a[href="#comments"]').tab('show');
        window.location.hash = 'comments';
        return false;
    }));

    $('.addCommentButton').click((function () {
        $('#myTab a[href="#comments"]').tab('show');
        window.location.hash = 'comments';
        $('#newCommentForm').find('textarea').focus();
        return false;
    }));


    // Save the tabs state
    $('#myTab a').click((function (e) {
        e.preventDefault();
        $(this).tab('show');
    }));

    // store the currently selected tab in the hash value
    $("ul.nav-tabs > li > a").on("shown.bs.tab", (function (e) {
        var id = $(e.target).attr("href").substr(1);
        window.location.hash = id;
    }));

    // on load of the page: switch to the currently selected tab
    var hash = window.location.hash;
    $('#myTab a[href="' + hash + '"]').tab('show');
    if (hash == '#draftsTab') {
        $('.loadDraftsTab').removeClass('loadDraftsTab');
        loadDraftsTab();
    } else if (hash == '#orders') {
        $('.loadOrders').removeClass('loadOrders');
        loadOrders();
    } else if (hash == '#contacts') {
        $('.loadContactsTab').removeClass('loadContactsTab');
        initContacts();
    }
    /**
     *firefox bug with hashes not loading the favico
     * @see http://stackoverflow.com/questions/2409759/firefox-3-6-9-drops-favicon-when-changing-window-location
     * @see https://kilianvalkhof.com/2010/javascript/the-case-of-the-disappearing-favicon/
     */
    if (navigator.userAgent.toLowerCase().indexOf('firefox') > -1 && hash !== '') {
        setFavicon();
    }

    //make all inputs for number with min value 1
    if ($('input[type=number]:not([min])').length > 0) {
        $('input[type=number]').prop('min', 0);
    }

    if ($("#tokenfield-email").length > 0) {
        initAttendeesInput();
    }

    if ($("#userSearch_appointments").length > 0) {
        initUserSearchInput();
    }

    body.on('click', '#addItemToCheckList', (function (event) {
        event.preventDefault();

        var titleInput = $('#checklistItem-Title');
        var descInput = $('#checklistItem-Description');
        var assignInput = $('#checklistItem-AssignedTo_Id');
        var container = $('.checklistItemsList');

        if (titleInput.val() == "") {
            titleInput.closest('.form-group').addClass('has-error');
            titleInput.focus();
            return;
        }

        container.loadTemplate(
            base_url + '/templates/tasks/checklistFormItem.html',
            {
                Title: titleInput.val(),
                Description: descInput.val(),
                AssignedTo: assignInput.val() == "" ? 'null' : assignInput.val()
            },
            {
                prepend: true,
                success: function () {
                    titleInput.val('');
                    descInput.val('');
                    assignInput.val('');
                }
            }
        )
    }));

    body.on('submit', '#checkListForm', (function (event) {
        event.preventDefault();
        var formData = $(this).serializeJSON();
        if (formData.AssignedTo_Id == "") {
            formData.AssignedTo_Id = null;
        }
        if (formData.Children) {
            $.each(formData.Children, function (index, obj) {
                if (formData.Children[index].AssignedTo_Id == 'null') {
                    formData.Children[index].AssignedTo_Id = null;
                }
            })
        }


        $.ajax({
            url: api_address + "TaskLists",
            type: "POST",
            data: JSON.stringify(formData),
            success: function (data) {
                // get the id of the newly created task and children
                $.get(api_address + 'TaskLists(' + data.Id + ')?$expand=Children($select=Id,Title,Description,Value)&$select=Id,Title,Description,Value')
                    .success((function (data) {
                        renderChecklistItems([data]);
                    }));
                closeDefaultModal();
            },
            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        });
    }));

    body.on('click', '#addCheckList', (function () {
        openCheckListMenu();
        return false;
    }));

    //show or hide completed tasks in SEO checklist
    body.on('click', '#showCompletedCheckList', function (event) {
        event.preventDefault();
        $(".seoChecklist > .completedSeo").toggle();

        var $this = $(this);
        $this.toggleClass('showSeo');
        if ($this.hasClass('showSeo')) {
            $this.val('Hide completed');
        } else {
            $this.val('Show completed');
        }
    });

    body.on('submit', '.adminUserAssignForm', (function (event) {
        event.preventDefault();
        var form = $(this);
        adminUserAssigned(form);
    }));

    body.on('click', '#addEventAttendee', (function (event) {
        addEventAttendee(event);
    }));

    $('.createEvent').click((function () {
        var modal = getDefaultModal();
        modal.find('.modal-title').append(Lang.get('labels.create-event'));
        modal.find('.modal-body').loadTemplate(
            base_url + "/templates/calendar/addEventForm.html",
            {
                TypeLabel: Lang.get('labels.type'),
                //                        EventTypes : [Lang.get('labels.introduction-call'),Lang.get('labels.follow-up-call'),Lang.get('labels.closing-call'),Lang.get('labels.health-check')],
                SummaryLabel: Lang.get('labels.summary'),
                DescriptionLabel: Lang.get('labels.description'),
                TimeLabel: Lang.get('labels.time'),
                AttendeesLabel: Lang.get('labels.attendees'),
                CreateLabel: Lang.get('labels.create'),
                OptionsLabel: Lang.get('labels.options'),
                NotifyAttendeesLabel: Lang.get('labels.notify-attendees'),
                CreateOnGoogleLabel: Lang.get('labels.create-on-google')
            },
            {
                success: function () {
                    initAttendeesInput();
                    $('#event-time').daterangepicker(
                        {
                            "parentEl": "#defaultModal",
                            minDate: moment(),
                            timePicker: true,
                            "timePicker24Hour": true,
                            locale: {
                                format: 'YYYY-MM-DD H:mm'
                            }
                        }
                    );
                    //populate the type select
                    var select = $('#event-Type');
                    $.get(base_url + '/calendar/get-event-types')
                        .success((function (data) {
                            $.each(data, (function (a, b) {
                                select.append("<option value='" + a + "'>" + b + "</option>");
                            }))
                        }));
                }
            }
        );
    }));

    body.on('submit', '#createEventForm', (function (event) {
        event.preventDefault();
        var form = $(event.target);
        var btn = form.find(':submit');
        btn.prop('disabled', true);
        var formData = $(this).serializeJSON();
        formData.NotifyAttendees = formData.NotifyAttendees ? true : false;
        var createOnCalendar = formData.CreateOnGoogleCalendar ? true : false;
        delete (formData.CreateOnGoogleCalendar);
        formData.User_Id = getUserId();
        var times = formData['time'].split(' - ');
        delete (formData.time);
        formData.Start = new Date(times[0]);
        formData.End = new Date(times[1]);
        // get the Model and ModelId
        formData.Model = formData.Model || getModel() || null;
        formData.ModelId = formData.ModelId || getModelId() || null;
        if (formData.Model && formData.ModelId) {
            formData.Source = { Url: linkToItem(formData.Model, formData.ModelId, true) };
        }
        $.ajax({
            url: api_address + "CalendarEvents",
            type: "POST",
            data: JSON.stringify(formData),
            success: function (data) {
                if (createOnCalendar) {
                    $.ajax({
                        url: api_address + "CalendarEvents(" + data.Id + ")/CreateOnGoogle",
                        type: "GET",
                        success: function (data) {
                            btn.prop('disabled', false);
                            new PNotify({
                                title: Lang.get('labels.success'),
                                text: Lang.get('labels.appointment-created'),
                                type: 'success'
                            });
                            new PNotify({ text: Lang.get('messages.calendar-sync-delay') });
                            //reset the form;
                            refreshCalendar();
                        },
                        error: function (err) {
                            btn.prop('disabled', false);
                        },
                        beforeSend: function (request) {
                            request.setRequestHeader("Content-Type", "application/json");
                        }
                    });
                }
                closeDefaultModal();
            },
            error: function (err) {
                btn.prop('disabled', false);
            },
            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        });
    }));

    $('#pauseContractForm').on('submit', (function (event) {
        event.preventDefault();
        var data = $(this).serializeJSON();
        $.post(api_address + "Contracts(" + getModelId() + ")/action.Pause", JSON.stringify(data))
            .success((function () {
                location.reload(true);
            }))
    }));
    $('#resumeContractForm').on('submit', (function (event) {
        event.preventDefault();
        var data = $(this).serializeJSON();
        $.post(api_address + "Contracts(" + getModelId() + ")/action.Resume", JSON.stringify(data))
            .success((function () {
                location.reload(true);
            }))
    }));

    $(body).on('click', '.cancelInvitation', (function (event) {
        var button = $(event.target);
        //disable double clicking
        button.css('pointer-events', 'none');

        //get the adwords id
        var adwordsId = $('.adwordsIdCheck').text().trim();
        if (adwordsId != '') {
            $.when(cancelInvitation(adwordsId)).then((function (result) {
                changeAdwordsLinkStatus(result);
            })).fail((function (error) {
                button.css('pointer-events', '');

            }));
        }
    }));

    body.on('click', '.sendInvitation', (function (event) {
        var button = $(event.target);
        //disable double clicking
        button.css('pointer-events', 'none');
        var data = {}; // array for the post
        //get the adwords id
        var adwordsId = $('.adwordsIdCheck').text().trim();
        // get information about the account name todo will differ depending if we view lead, alias or contract
        if ($('.adwordsPendingLinkName').text() != "") {
            data.website = $('.adwordsPendingLinkName').text();
        } else {
            new PNotify({
                title: "Invalid Homepage",
                text: "Please update the information with a valid homepage",
                type: "error"
            });
            button.css('pointer-events', '');
            return false;
        }
        data.adwordsId = adwordsId;
        if (adwordsId != '') {
            $.when(sendInvitation(data)).then((function (result) {
                changeAdwordsLinkStatus(result);
            })).fail((function (error) {
                button.css('pointer-events', '');
            }));
        }
    }));
    /// check if we have adwords id set and find out if its linked to our mcc
    var adwordsPlaceholder = $('.adwordsIdCheck');
    var adwordsId = adwordsPlaceholder.text().trim().replaceAll(/\D/g, "");
    if (adwordsId != "") {
        //check if it's linked to our mcc
        $.when(checkAdWordsLink(adwordsId))
            .then((function (result) {
                changeAdwordsLinkStatus(result);
            }))
    }

    body.on('click', '.makeStartupMeeting', (function (event) {
        var modal = getDefaultModal();
        modal.find('.modal-title').append(Lang.get('labels.create-event'));
        modal.find('.modal-body').loadTemplate(
            base_url + "/templates/calendar/addEventForm.html",
            {
                TypeLabel: Lang.get('labels.type'),
                EventTypes: ["StartUpMeeting"],
                SummaryLabel: Lang.get('labels.summary'),
                DescriptionLabel: Lang.get('labels.description'),
                TimeLabel: Lang.get('labels.time'),
                AttendeesLabel: Lang.get('labels.attendees'),
                CreateLabel: Lang.get('labels.create'),
                OptionsLabel: Lang.get('labels.options'),
                NotifyAttendeesLabel: Lang.get('labels.notify-attendees'),
                CreateOnGoogleLabel: Lang.get('labels.create-on-google')
            },
            {
                success: function () {
                    $('#event-time').daterangepicker(
                        {
                            minDate: moment(),
                            timePicker: true,
                            "timePicker24Hour": true,
                            locale: {
                                format: 'YYYY-MM-DD H:mm'
                            }
                        }
                    );
                }
            }
        );
    }));

    /**
     * function that makes a text appear multiline when we click "Show more"
     *
     */
    body.on('click', '.more-link', (function () {
        $(this).closest('.is-more').toggleClass('multiline');
    }));
    /**
     * open seller goal creation form
     */
    body.on('click', '#createGoal', (function () {
        var modal = getDefaultModal();
        modal.find('.modal-title').empty().append(Lang.get('labels.create-goal'));
        modal.find('.modal-body').empty().loadTemplate(base_url + "/templates/sellerGoals/createGoalForm.html",
            {
                SelectUserLabel: Lang.get('labels.select-seller'),
                GoalPeriodLabel: Lang.get('labels.goal-period'),
                Years: getYearsSelect(),
                NewSalesCountGoalLabel: Lang.get('labels.new-sales-count'),
                NewSalesGoalLabel: Lang.get('labels.new-sales-goal'),
                UpSalesCountGoalLabel: Lang.get('labels.upsale-count'),
                UpSalesGoalLabel: Lang.get('labels.upsale-goal'),
                ReSalesCountGoalLabel: Lang.get('labels.resale-count'),
                ReSalesGoalLabel: Lang.get('labels.resale-goal'),
                CallsGoalLabel: Lang.get('labels.calls-goal'),
                HealthChecksGoalLabel: Lang.get('labels.healthchecks-goal'),
                CreateLabel: Lang.get('labels.create')
            },
            {
                success: function () {
                    $.get(base_url + '/users/listByRoles', { roles: ['Sales'] })
                        .success((function (data) {
                            //create a users select
                            var userSelect = $('#goal-User_Id');
                            var users = JSON.parse(data);
                            for (var key in users) {
                                if (users.hasOwnProperty(key)) {
                                    userSelect.append($("<option></option>")
                                        .attr("value", key)
                                        .text(users[key]));
                                }
                            }
                            var monthSelect = $('#goal-Month');
                            var months = moment.months();
                            var i = 1;
                            months.forEach((function (month) {
                                monthSelect.append($("<option></option>")
                                    .attr("value", i)
                                    .text(month));
                                i++;
                            }))
                        }))
                }
            });
    }));

    body.on('click', '.CopySellerGoal', (function (event) {
        var id = $(event.target).data('goal-id');
        $.get(api_address + 'SellerGoals(' + id + ")?$expand=User($select=Id)")
            .success((function (data) {
                var modal = getDefaultModal();
                modal.find('.modal-title').empty().append(Lang.get('labels.copy-goal'));
                modal.find('.modal-body').empty().loadTemplate(base_url + "/templates/sellerGoals/copyGoalForm.html",
                    {
                        GoalId: id,
                        SelectUserLabel: Lang.get('labels.select-seller'),
                        GoalPeriodLabel: Lang.get('labels.goal-period'),
                        Years: getYearsSelect(),
                        NewSalesCountGoalLabel: Lang.get('labels.new-sales-count'),
                        NewSalesCountGoal: data.NewSalesCountGoal,
                        NewSalesGoalLabel: Lang.get('labels.new-sales-goal'),
                        NewSalesGoal: data.NewSalesGoal,
                        UpSalesCountGoalLabel: Lang.get('labels.upsale-count'),
                        UpSalesCountGoal: data.UpSalesCountGoal,
                        UpSalesGoalLabel: Lang.get('labels.upsale-goal'),
                        UpSalesGoal: data.UpSalesGoal,
                        ReSalesCountGoalLabel: Lang.get('labels.resale-count'),
                        ReSalesCountGoal: data.ReSalesCountGoal,
                        ReSalesGoalLabel: Lang.get('labels.resale-goal'),
                        ReSalesGoal: data.ReSalesGoal,
                        CallsGoalLabel: Lang.get('labels.calls-goal'),
                        CallsGoal: data.CallsGoal,
                        HealthChecksGoalLabel: Lang.get('labels.healthchecks-goal'),
                        HealthChecksGoal: data.HealthChecksGoal,
                        CreateLabel: Lang.get('labels.copy')
                    },
                    {
                        success: function () {
                            $.get(base_url + '/users/listByRoles', { roles: ['Sales'] })
                                .success((function (u) {
                                    //create a users select
                                    var userSelect = $('#goal-User_Id');
                                    var users = JSON.parse(u);
                                    for (var key in users) {
                                        if (users.hasOwnProperty(key)) {
                                            userSelect.append($("<option></option>")
                                                .attr("value", key)
                                                .text(users[key]));
                                        }
                                    }
                                    userSelect.val(data.User_Id);
                                }));
                            var monthSelect = $('#goal-Month');
                            var months = moment.months();
                            var i = 1;
                            months.forEach((function (month) {
                                monthSelect.append($("<option></option>")
                                    .attr("value", i)
                                    .text(month));
                                i++;
                            }));
                            monthSelect.val(data.Month);

                            $('#goal-Year').val(data.Year)

                        }
                    });
            }));
    }));

    // Left menu search field
    $("#main-search").autocomplete({
        source: function (request, response) {
            var str = request.term;
            $.get(api_address + "ClientAlias?$filter=indexof(tolower(Name), '" + encodeURIComponent(str.replaceAll("'", "''")) + "') ge 0 or indexof(tolower(Homepage), '" + encodeURIComponent(str.replaceAll("'", "''")) + "') ge 0", {},
                (function (data) {
                    response($.map(data.value, (function (el) {
                        return { id: el.Id, label: el.Name + "  " + (el.Homepage != null ? el.Homepage : "*missing homepage*"), value: el.Name, homepage: el.Homepage };
                    }))
                    );
                }));
        },
        minLength: 2,
        select: function (event, ui) {
            window.location = base_url + '/clientAlias/show/' + ui.item.id;
            //setAliasId(ui.item.id, ui.item.value,ui.item.homepage)
        }
    });

    body.on('click', '.addOptimizeRule', (function (event) {
        var productId = $(this).data('product-id');
        var modal = getDefaultModal();
        modal.find('.modal-title').empty().append(Lang.get('labels.create-optimize-rule'));
        modal.find('.modal-body').loadTemplate(
            base_url + '/templates/optimizeRules/createOptimizeRuleForm.html',
            {
                ProductId: productId,
                CreateLabel: Lang.get('labels.save'),
                OptimizeIntervalLabel: Lang.get('labels.optimize-interval'),
                Sizes: sizes,
                SizeLabel: Lang.get('labels.size'),
                TaskTemplateLabel: Lang.get('labels.task-template')
            },
            {
                success: function () {
                    var taskTemplateSelect = $('#optimizeRule-TaskList_Id');
                    for (var prop in taskTemplates) {
                        taskTemplateSelect.append($("<option></option>")
                            .attr("value", prop)
                            .text(taskTemplates[prop]));
                    }
                }
            }
        )
    }));

    body.on('click', '.editOptimizeRule', (function (event) {
        var optimizeRuleId = $(this).data('optimize-rule-id');

        $.get(api_address + 'OptimizeRules(' + optimizeRuleId + ')')
            .success((function (data) {
                var modal = getDefaultModal();
                modal.find('.modal-title').empty().append(Lang.get('labels.edit-optimize-rule'));
                modal.find('.modal-body').loadTemplate(
                    base_url + '/templates/optimizeRules/editOptimizeRuleForm.html',
                    {
                        OptimizeRuleId: optimizeRuleId,
                        SaveLabel: Lang.get('labels.save'),
                        OptimizeIntervalLabel: Lang.get('labels.optimize-interval'),
                        TaskTemplateLabel: Lang.get('labels.task-template'),
                        OptimizeInterval: data.OptimizeInterval
                    },
                    {
                        success: function () {
                            var taskTemplateSelect = $('#optimizeRule-TaskList_Id');
                            for (var prop in taskTemplates) {
                                taskTemplateSelect.append(
                                    $("<option></option>")
                                        .attr("value", prop)
                                        .text(taskTemplates[prop])
                                );
                            }
                            taskTemplateSelect.val(data.TaskList_Id);
                        }
                    }
                )
            }));
    }));

    // flexfone call if user has local number
    if (canCall() != "") {
        body.on('click', '.flexfoneCallOut', (function (event) {
            event.preventDefault();
            var button = $(event.target);
            button.css('pointer-events', 'none');

            var phonenumber = $(event.target).text();

            var phone = formatPhoneNumber(phonenumber);
            if (phone != '') {
                $.ajax({
                    url: api_address + "CallLogs/InitiateCall",
                    type: "POST",
                    data: JSON.stringify({ "TargetNumber": phone }),
                    success: function () {
                        new PNotify({ "title": Lang.get('messages.calling-through-phone') });

                        setTimeout((function () {
                            button.css('pointer-events', '');
                        }), 3000);
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            }
        }))
    }

    body.on('click', '.quickClientComment', (function (event) {
        event.preventDefault();
        var modelId = $(event.target).data('client-id');
        var model = getModel() || 'ClientAlias';
        var modal = getDefaultModal();
        modal.find('.modal-title').append(Lang.get('labels.quick-comment'));
        modal.find('.modal-body').loadTemplate(base_url + '/templates/comments/quickCommentForm.html',
            {
                Model: model,
                ModelId: modelId,
                SaveLabel: Lang.get('labels.save-comment'),
                EnterMessageLabel: Lang.get('labels.enter-comment')
            })
    }));

    body.on('submit', '#quickCommentForm', (function (event) {
        event.preventDefault();
        var data = $(this).serializeJSON();
        $.ajax({
            url: api_address + "Comments",
            type: "POST",
            data: JSON.stringify(data),
            success: function () {
                closeDefaultModal();
                new PNotify({ title: Lang.get('messages.comment-was-saved'), type: "success" })
            },
            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        });
    }));

    $('.refreshCalendar').click((function (event) {
        $(event.target).css('pointer-events', 'none');
        refreshCalendar();
        setTimeout((function () {
            $(event.target).css('pointer-events', '');
        }), 2000)
    }));

    $('.printPage').click((function (event) {
        event.preventDefault();
        window.print();
    }));

    $('.sendBugReport').on('click', (function (event) {
        var modal = getDefaultModal();
        modal.find('.modal-body').append('<iframe src="https://docs.google.com/a/greenclickmedia.dk/forms/d/1AAFy10pfnQcEv_-1b_aii191DHWRIinmqZ17GG8OwU8/viewform?embedded=true" width="500" height="500" frameborder="0" marginheight="0" marginwidth="0">Loading...</iframe>')
    }));

    /**
     * find next appointment time
     */
    if ($('.next-appointment').length > 0) {
        findNextAppointment(getModel(), getModelId());
    }

    body.on('mouseenter', '.progress-title', (function (event) {
        var task = $(event.target).closest('.progress-title');
        if (task.data('task-model-id') && task.data('task-model')) {
            $.when(getCompanyName(task.data('task-model'), task.data('task-model-id')))
                .then((function (result) {
                    if (result) {
                        task.prop('title', 'Company name: ' + result.value + ' \n ' + task.prop('title'))
                    }
                    task.data("task-model", null);
                }))
        }
    }));

    body.on('click', '.loadDraftsTab', (function (event) {
        $(event.target).removeClass('loadDraftsTab');
        loadDraftsTab(event)
    }));

    body.on('click', '.loadOrders', (function (event) {
        $(event.target).removeClass('loadOrders');
        loadOrders(event)
    }));

    $('#saveAdwordsId').on('submit', (function (event) {
        event.preventDefault();
        var form = $(this);
        var btn = form.find(':submit');
        btn.prop('disabled', true);
        var data = form.serializeJSON();
        var aliasId = data.clientAliasId;
        delete (data.clientAliasId);
        $.ajax({
            url: api_address + "ClientAlias(" + aliasId + ')',
            type: "PATCH",
            data: JSON.stringify(data),
            success: function (data) {
                new PNotify({
                    title: 'AdwordsId saved. Refreshing...'
                });
                location.reload(true);
            },
            error: function (error) {
                btn.prop('disabled', false);
            },
            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        });
    }))


    $('body').on('click', '.quickSaveContractId', (function (event) {
        // find the row id
        var target = $(event.target);
        var id = $(this).data('pk');
        event.preventDefault();
        $(event.target).editable({
            validate: function (value) {
                var regex = new RegExp(/\b\d{3}[-]?\d{3}[-]?\d{4}\b/g);
                if (!regex.test(value)) {
                    return '########## or ###-###-####';
                }
            },
            ajaxOptions: {
                type: "patch",
                dataType: 'application/json',
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            },
            params: function (params) {
                var data = {};
                data['AdwordsId'] = params.value;
                return JSON.stringify(data);
            },
            url: api_address + "Contracts(" + id + ")",
            success: function () {
                location.reload(true);
            }
        }).removeClass('quickSaveContractId');
        setTimeout((function () {
            $(event.target).click();
        }), 200)
    }));


    body.on('click', '.quickEditSubTask', function (event) {
        var id = $(event.target).closest('td').find('.taskCheck').val();
        quickEditTask(id, true);
    });

    body.on('change', '#setForCurrentItem', (function (event) {
        var model = getModel();
        var id = getModelId();

        var TaskModel = document.getElementById("task-Model");
        var TaskModelId = document.getElementById("task-ModelId");

        var checked = $(event.target).prop('checked');

        if (checked) {
            if (!id) {
                new PNotify({
                    "title": "Error",
                    "text": "You are not at any item",
                    'type': 'error'
                });


                document.getElementById("setForCurrentItem").checked = false;
                return false;

            }
            TaskModel.value = model;

            TaskModelId.value = id;

        } else {
            TaskModel.value = "";

            TaskModelId.value = "";
        }

    }));

    body.on('click', '.quickUpdateAppointment', (function (event) {
        var target = $(event.target);
        target.css('pointer-events', 'none');
        var appointmentId = target.data('appointment-id');
        $.get(api_address + 'CalendarEvents(' + appointmentId + ')?$expand=Activity($expand=User($select=FullName))')
            .success((function () {

            }))
    }));

    body.on('submit', '#appointmentUpdateForm', (function (event) {
        event.preventDefault();
        var form = $(event.target);
        var btn = form.find(':submit');
        var formData = $(this).serializeJSON();
        var textarea = $('#appointment-comment');
        //if the comment is longer than 15 characters


        var calendarEventId = formData.CalendarEvent_Id;
        delete (formData.CalendarEvent_Id);

        var activity = false, type = null;
        var move = formData.moveAppointment;

        if (formData.Cancel) {
            activity = true;
            type = 'Cancel';
        } else if (formData.NoAnswer) {
            activity = true;
            type = 'NoAnswer';
        } else if (formData.Completed) {
            activity = true;
            type = 'Completed';
        }

        if (activity) {
            btn.prop('disabled', true);

            var a = {};
            a.Type = type;
            a.Comment = formData.Comment;

            if (textarea.is(':visible') && textarea.val().length < 15) {
                $.get(api_address + "CalendarEvents(" + calendarEventId + ")?$select=EventType")
                    .success(function (data) {
                        if (data.EventType == "HealthCheck") {
                            new PNotify({
                                "title": "Error",
                                "text": "Please write a comment of at least 15 characters",
                                'type': 'error'
                            });
                            textarea.focus();
                            btn.prop('disabled', false);

                            return false;
                        } else {
                            $.ajax({
                                url: api_address + "CalendarEvents(" + calendarEventId + ")/AddActivity",
                                type: "POST",
                                data: JSON.stringify(a),
                                success: function (data) {
                                    if (!move && getModel() == 'CalendarEvent' && getModelId()) {
                                        location.reload(true);
                                    } else if (getModel() == "CalendarEvent" && !getModelId()) {
                                        closeDefaultModal();
                                        appointmentsListTable.draw();
                                    }
                                },
                                beforeSend: function (request) {
                                    request.setRequestHeader("Content-Type", "application/json");
                                }
                            });
                        }
                    })
            } else {
                $.ajax({
                    url: api_address + "CalendarEvents(" + calendarEventId + ")/AddActivity",
                    type: "POST",
                    data: JSON.stringify(a),
                    success: function (data) {
                        if (!move && getModel() == 'CalendarEvent' && getModelId()) {
                            location.reload(true);
                        } else if (getModel() == "CalendarEvent" && !getModelId()) {
                            closeDefaultModal();
                            appointmentsListTable.draw();
                        }
                    },
                    beforeSend: function (request) {
                        request.setRequestHeader("Content-Type", "application/json");
                    }
                });
            }
        }

        if (move) {
            btn.prop('disabled', true);

            // if time is not set
            if (!$('#appointment-time').val()) {
                new PNotify({
                    "title": "Error",
                    "text": "Please pick a new date",
                    'type': 'error'
                });
                $('#appointment-time').focus();
                return false;
            }
            var data = {};
            var times = formData['time'].split(' - ');
            delete (formData.time);
            data.Start = new Date(times[0]);
            data.End = new Date(times[1]);
            $.ajax({
                url: api_address + "CalendarEvents(" + calendarEventId + ')',
                type: "Patch",
                data: JSON.stringify(data),
                success: function (data) {
                    closeDefaultModal();
                    if (getModel() == 'CalendarEvent') {
                        if (getModelId()) {
                            location.reload(true);
                        } else {
                            appointmentsListTable.draw();
                        }
                    }

                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        }


        // $.ajax({
        //     url: api_address + "CalendarEvents("+calendarEventId+")/AddActivity",
        //     type: "POST",
        //     data: JSON.stringify(formData),
        //     success: function (data) {
        //         closeDefaultModal();
        //     },
        //     beforeSend: function (request) {
        //         request.setRequestHeader("Content-Type", "application/json");
        //     }
        // });

    }));

    //error message when Item ID is present but no item type at quick edit task
    body.on('click', '#saveTask', (function () {

        if ($("#task-ModelId").val() != '' && $("#task-Model").val() == '') {
            new PNotify({
                "title": "Please select item type",
                'type': 'error'
            });
            return false;
        }
    }));

    //display client name when client id is added
    body.on('mouseout', '#task-ModelId', (function () {
        if ($('#task-ModelId').val() &&
            ["ClientAlias", "Contract", "Invoice", "Order", "Order", "Lead"].indexOf($('#task-Model').val()) !== -1) {

            $.when(getCompanyName($('#task-Model').val(), $('#task-ModelId').val()))
                .then((function (name) {
                    var clientName = "View";
                    if (name.value != 'Undefined') clientName = name.value;

                    $("#clientName").html('');
                    $("#clientName").append(clientName);

                    //$(nRow).find('td:nth-child(3)').html("<a href='" + linkToItem(aaData.Model, aaData.ModelId, true) + "' target='_blank'>" + clientName + "</a>");
                }));
        }
        else {
            $("#clientName").html('');
        }
    }));

    body.on('click', '.LogTime', (function (event) {
        var VaultId = $(event.target).data('vault-id');
        openTimeVaultMenu(VaultId);
    }));
    body.on('submit', '#logTimeForm', (function (event) {
        event.preventDefault();
        var form = $(event.target);
        var data = form.serializeJSON();
        data.Model = getModel();
        data.Item = getModelId();
        data.Minutes = data.Minutes * (data.Model == 'ClientAlias' ? -1 : 1);
        $.ajax({
            url: api_address + "TimeVaults/Withdraw",
            type: "POST",
            data: JSON.stringify(data),
            success: function () {
                initializeTimeVault(data.Model, data.Item);
                closeDefaultModal();
            },

            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        });

    }));

    // if($('#timelineTable').length > 0){
    //     $('#timelineTable tr').sort(function(a,b) {
    //         return b.dataset.created > a.dataset.created;
    //     }).appendTo('#timelineTable > tbody');
    // }
    $('.timelineLoad').on('click', function () {
        setTimeLineBorders();
    })

    // intialize timeline
    if ($('#timeline').length > 0) {
        sortTimeline();
        setTimeLineBorders();

        //initiate timeline sorting
        $('#timelineSortingChange').on('change', (function () {
            sortTimeline();
        }));
    }
    //include items in the contract timeline
    $('#includeTimeline').on('submit', function (event) {
        event.preventDefault();
        var form = $(event.target);
        var btn = form.find(':submit');
        // btn.prop('disabled',true);
        var formData = form.serializeJSON();
        var requests = [];
        if (formData['timeline-payments']) {
            requests.push(timelinePayments());
        }
        if (formData['timeline-comments']) {
            requests.push(timelineComments());
        }
        if (formData['timeline-timelogs']) {
            requests.push(timelineTimeLogs());
        }
        if (formData['timeline-tasks']) {
            requests.push(timelineTasks());
        }
        // $.when.apply($,requests).then(function (data1) {
        //     console.log(data1);
        //     // console.log(data2);
        // })
        if (requests.length > 0) {
            $.when.all(requests).then(function (objects) {
                var timelinePanel = $('.panel-timeline');
                timelinePanel.addClass('spinner');

                if (!Array.isArray(objects[1])) {
                    objects = [objects[0]];
                }
                var elements = [];
                $.each(objects, function (index, val) {
                    if (Array.isArray(val)) {
                        var type = val[0]['@odata.context'].split('#')[1].split('(')[0];
                        var items = val[0].value;
                    } else {
                        var type = val['@odata.context'].split('#')[1].split('(')[0];
                        items = val.value;
                    }
                    if (items.length > 0) {
                        switch (type) {
                            case "Invoices":
                                $('#timeline-payments').prop('disabled', true).prop('checked', false);

                                $.each(items, function (index, val) {
                                    var created = {
                                        Created: val.Created,
                                        ColorStyle: "color:yellow",
                                        FullName: val.User.FullName,
                                        Message: " sent out Invoice .: " + val.InvoiceNumber,
                                        Class: 'fa fa-barcode fa-stack-1x'
                                    };
                                    elements.push(created);

                                    if (val.Payed != null) {
                                        var paid = created = {
                                            Created: val.Payed,
                                            ColorStyle: "color:green",
                                            Message: "Invoice " + val.InvoiceNumber + " was paid",
                                            Class: 'fa fa-money fa-stack-1x'
                                        };
                                        elements.push(paid);
                                    }
                                });
                                break;

                            case "Comments":
                                $('#timeline-comments').prop('disabled', true).prop('checked', false);
                                $.each(items, function (index, val) {
                                    var comment = {
                                        Created: val.Created,
                                        ColorStyle: "color:yellow",
                                        FullName: val.User.FullName,
                                        Message: " commented ",
                                        Comment: val.Message,
                                        Class: 'fa fa-comment fa-stack-1x'
                                    };
                                    elements.push(comment);
                                });
                                break;
                            case "TimeTransactions":
                                $('#timeline-timelogs').prop('disabled', true).prop('checked', false);
                                $.each(items, function (index, val) {
                                    var transaction = {
                                        Created: val.Created,
                                        ColorStyle: "color:orange",
                                        FullName: val.User.FullName,
                                        Message: " logged time  : " + minutesToStr(val.Amount),
                                        Comment: val.Comment ? val.Comment.Message : '',
                                        Class: 'fa fa-calendar-o fa-stack-1x'
                                    };
                                    elements.push(transaction);
                                });
                                break;

                            case "TaskLists":
                                $('#timeline-tasks').prop('disabled', true).prop('checked', false);
                                $.each(items, function (index, val) {
                                    var completedTask = {
                                        Created: val.EndTime != null ? val.EndTime : val.Created,
                                        ColorStyle: "color:orange",
                                        FullName: val.CompletedBy != null ? val.CompletedBy.FullName : "Unknown user",
                                        Message: " completed task : " + val.Title,
                                        Comment: val.Description,
                                        Class: 'fa fa-list-ul fa-stack-1x'
                                    };
                                    elements.push(completedTask);
                                });
                                break;
                            default:
                                break;
                        }
                    }
                });
                if (elements.length > 0) {
                    $('.panel-timeline').loadTemplate(base_url + '/templates/timeline/timelineItem.html', elements,
                        {
                            prepend: true,
                            overwriteCache: true,
                            success: function () {
                                sortTimeline();
                            }
                        });
                } else {
                    timelinePanel.removeClass('spinner');
                }
            })
        }
    });

    // if we have time registration status cookie, use it
    if ($.cookie('timeReg')) {
        timeRegStatus = $.cookie('timeReg');
        loadTimeRegButtons(timeRegStatus);
    } else {
        // if not , get the current status
        $.when(getTimeregStatus())
            .then(function (status) {
                //set status cookie
                var timeReg = status.Status;
                setTimeRegCookie(timeReg);
                timeRegStatus = $.cookie('timeReg');
                loadTimeRegButtons(timeRegStatus);
            })
    }

    //apply timeline border to expanded elements
    body.on('click', '.accordion-title', function (event) {
        event.preventDefault();
        var target = $(event.target);

        if ($(target).closest('.accordion-title').hasClass('collapsed')) {
            setTimeout(function () { setTimeLineBorders() }, 1);
        }
    });

    //toggle expand all timeline elements
    body.on('click', '#expandAllItems', function (event) {
        event.preventDefault();
        var items = $('.accordion-item');
        var $this = $(this);

        $this.toggleClass('expandAll');
        if ($this.hasClass('expandAll')) {
            $this.val('Hide all items');
            $.each(items, function (index, element) {
                $(element).find('.accordion-title').removeClass('collapsed').attr("aria-expanded", "true");
                $(element).find('.collapse').addClass('in').attr("aria-expanded", "true").css('height', 'auto');
            });
        } else {
            $this.val('Expand all items');
            $.each(items, function (index, element) {
                $(element).find('.accordion-title').addClass('collapsed').attr("aria-expanded", "false");
                $(element).find('.collapse').removeClass('in').attr("aria-expanded", "false").css('height', '0px');
            });
        }
        setTimeLineBorders();
    });

    body.on('change', '#event-Type', function (event) {
        var value = $(this).val();
        var $description = $("#appointment-Description");
        var $title = $("#appointment-Summary");

        $title.val("");
        $description.text("");

        if (value == "HealthCheck") {
            var
                contactperson = lead.ContactPerson || $("#quickUpdateLead table tr td.leadContactPerson").text().trim(),
                email = lead.ContactEmail || $("#quickUpdateLead table tr td.leadContactEmail").text().trim(),
                adwordsid = lead.AdwordsId || $("#quickUpdateLead table tr td.adwordsIdCheck").text().trim(),
                phone = lead.Phone || $("#quickUpdateLead table tr td.leadPhone").text().trim();

            var html = "";
            if (contactperson != "") html += "Kontaktperson: " + contactperson;
            if (phone != "") html += " - " + phone;
            if (email != "") html += "\n" + "Email: " + email;
            if (adwordsid != "") html += "\n" + "AdWords ID: " + adwordsid;

            html += "\n" + "---" + "\n\n";

            $title.val("Sundhedstjek - " + lead.Website);
            $description.text(html);
            $description.focus();
        }
    });

    if ($('.contractFieldValuesPlaceholder').length > 0) {
        initContractFieldValues();
    }


}));
/** END OF THE EVENT LISTENERS */

////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////// /**  FUNCTIONS *////////////////////////////////////////////////////////////

function initContractFieldValues() {
    var placeholder = $('.contractFieldValuesPlaceholder');
    var cId = getModelId();
    $.get('Contracts(' + cId + ')/FieldValues')
        .success(function (data) {
            // console.log(data);
        })



}


function splitTimelineIntoMonths() {
    var items = $('.timelineItem');
    items.wrapAll("<div id='accordioninpanel' class='accordion-group'></div>")
    var months = [];
    $.each(items, function (index, element) {
        var month = moment($(element).data('date')).month();
        var year = moment($(element).data('date')).year();
        //add month class to group elements
        var monthClass = "month_" + year + "_" + month;

        if (!isInArray(monthClass, months)) {
            months.push(monthClass);
        }
        $(element).addClass(monthClass);
    });
    $.each(months, function (index, value) {
        var monthElements = $('.' + value);
        //get month name and year from class name
        var monthNumber = Number(value.substring(11)) + 1;
        var monthName = moment(monthNumber, "MM").format('MMMM');
        var yearNumber = Number(value.substring(6, 10));
        var year = moment(yearNumber, 'YYYY').format('YYYY');
        if ($('.grouped_' + value).length == 0) {
            $('<div class="accordion-item grouped_' + value + '"> ' +
                '<a class="accordion-title text-center" data-toggle="collapse" data-parent="#accordioninpanel" href="#collapsein' + value + '"><h4>' + monthName + " " + year + '</h4></a> ' +
                '<div id="collapsein' + value + '" class="collapse">' +
                '<div class="accordion-body">').insertBefore(monthElements[0]);
        }
        monthElements.prependTo($('.grouped_' + value).find('.accordion-body'));
        $('.panel-timeline').removeClass('spinner');
    })

    //close all accordions but the first one
    var accordionItems = $('.accordion-item');
    $.each(accordionItems, function (index, element) {
        $(element).find('.accordion-title').addClass('collapsed');
        $(element).find('.collapse').removeClass('in');
    });
    $(accordionItems[0]).find('.accordion-title').removeClass('collapsed');
    $(accordionItems[0]).find('.collapse').addClass('in');
    setTimeLineBorders();
}

function timelineTasks() {
    var model = getModel();
    var modelId = getModelId();

    return $.get(api_address + "TaskLists?$filter=Model eq '" + model + "' and ModelId eq " + modelId + " and Value eq true&$expand=Parent($select=Id,Title),CompletedBy($select=FullName)")
}

function timelineComments() {
    var model = getModel();
    var modelId = getModelId();
    return $.get(api_address + "Comments?$filter=(Type eq 'Public') and Model eq '" + model + "' and ModelId eq " + modelId + " and User_Id ne null&$expand=Children($select=Id,Message,Created,ParentCommentId;$expand=User($select=UserName,FullName)),User($select=FullName,UserName)" +
        "&$select=Id,Message,Model,ModelId,Created&$orderby=Created desc")
}

function timelinePayments() {
    return $.get(api_address + "Invoices?$filter=InvoiceLine/any(d:d/Contract_Id eq " + getModelId() + ")&$expand=User($select=FullName)&$select=InvoiceNumber,Id,Payed,Created,Status,Type")
}

function timelineTimeLogs() {
    var data = { Model: getModel(), Item: getModelId() };

    return $.ajax({
        url: api_address + 'TimeVaults/RetrieveTransactions?$expand=Comment($select=Message),User($select=FullName)',
        type: "POST",
        data: JSON.stringify(data),
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}

function getTimeregStatus() {
    return $.get(api_address + 'TimeRegistrations/CurrentStatus')
}

function openTimeVaultMenu(vaultId) {
    var modal = getDefaultModal();
    var body = modal.find('.modal-body');
    var Title = modal.find('.modal-title').text('Time logs');
    body.loadTemplate(base_url + '/templates/timeVaults/menu.html', { VaultId: vaultId }, {
        success: function () {
            var table = $('#TimeVaultTransactionsTable').DataTable({
                "bPaginate": false,
                'bInfo': false,
                'bFilter': false,
                "iDisplayLength": "All",
                aaSorting: [[0, "desc"]], // shows the newest items first
                "bProcessing": true,
                "bServerSide": true,
                "deferRender": true, // testing if speed is better with this
                "sAjaxSource": api_address + "TimeVaults(" + vaultId + ")/Transactions?$expand=User($select=FullName),Comment($select=Message)",
                "aoColumns": [
                    {
                        mData: "Created", mRender: function (created) {
                            return toDateTime(created);
                        }
                    },
                    {
                        mData: "Amount", mRender: function (amount) {
                            return minutesToStr(amount);
                        }

                    },
                    {
                        mData: null, oData: "User/FullName", mRender: function (obj) {
                            return obj.User.FullName;
                        }
                    },
                    {
                        mData: null, oData: "Comment/Message", "sClass": "multiline", mRender: function (obj) {
                            return obj.Comment ? obj.Comment.Message : '';
                        }
                    }

                ],
                "fnServerData": fnServerOData,
                "iODataVersion": 4,
                "bUseODataViaJSONP": false
            })
        }
    })
}
function loadOrders() {

    var modelId = getModelId();
    var placeholder = $('#orders').find('.table-responsive');
    placeholder.loadTemplate(base_url + '/templates/orders/ordersTabTable.html', {}, {
        success: function () {
            var table2 = $('#ordersTabTable').DataTable({
                "bPaginate": false,
                'bInfo': false,
                'bFilter': false,
                "iDisplayLength": "All",
                responsive: true,
                stateSave: true,
                aaSorting: [[2, "desc"]], // shows the newest items first
                "bProcessing": true,
                "bServerSide": true,
                "deferRender": true, // testing if speed is better with this
                "sAjaxSource": api_address + "Orders?$expand=User($select=Id,FullName),OrderProduct($select=Id;$expand=Product($select=Name)),OrderProductPackage($select=Id;$expand=ProductPackage($select=Name))",
                'filter': "ClientAlias_Id eq " + modelId,
                "select": "ArchivedDate",
                "fnRowCallback": function (row, data) {
                    if (data.ArchivedDate) {
                        $(row).addClass('danger').prop('title', 'Order is archived');
                    }
                },
                "aoColumns": [
                    {
                        mData: 'Id', mRender: function (obj) {
                            return "<a target='_blank' href='" + linkToItem('Order', obj, true) + "'>" + obj + '</a>';
                        }
                    },
                    {
                        mData: null, oData: "User/FullName", mRender: function (obj) {
                            return "<a target='_blank' href='" + linkToItem('User', obj.User.Id, true) + "'>" + obj.User.FullName + '</a>';
                        }
                    },
                    {
                        mData: "Created", sType: 'date', mRender: function (obj) {
                            return new Date(obj).toDateTime();
                        }
                    },
                    {
                        mData: "ConfirmedDate", sType: 'date', mRender: function (obj) {
                            if (obj != null) {
                                return new Date(obj).toDateTime();
                            } else {
                                return '';
                            }
                        }
                    },
                    {
                        mData: null, oData: null, mRender: function (obj) {
                            var products = '';
                            var prPackages = '';

                            if (obj.OrderProduct.length > 0) {
                                $.each(obj.OrderProduct, function (index, element) {

                                    products += element.Product.Name + "</br>";
                                });
                            }
                            if (obj.OrderProductPackage.length > 0) {
                                $.each(obj.OrderProductPackage, function (index, element) {
                                    prPackages += element.ProductPackage.Name + "</br>";
                                });
                            }
                            return products + " " + prPackages;
                        }
                    }
                ],
                "fnServerData": fnServerOData,
                "iODataVersion": 4,
                "bUseODataViaJSONP": false
            })
        }
    })
}

function initAttendeesInput() {
    $('#tokenfield-email')
     .on('beforeCreateToken', function (e) {
         var token = e.token.value.split('|')
         e.token.value = token[1] || token[0]
         e.token.label = token[1] ? token[0] + ' (' + token[1] + ')' : token[0]
     })
     .on('afterCreateToken', function (e) {
         if (!validateEmail(e.token.value)) {
             $(e.relatedTarget).addClass('invalid')
         }
     })
     .on('beforeEditToken', function (e) {
         if (e.token.label !== e.token.value) {
             var label = e.token.label.split(' (');
             e.token.value = label[0] + '|' + e.token.value
         }
     })
     .on('preventDuplicateToken', function (e) {
         new PNotify({ title: 'This email is already an attendee! ' + e.token.value, type: "error" });
     })
     .tokenfield();
}

function initUserSearchInput() {
    $('#userSearch_appointments')
        .on('beforeCreateToken', function (e) {
            var token = e.token.value.split('|')
            e.token.value = token[1] || token[0]
            e.token.label = token[1] ? token[0] + ' (' + token[1] + ')' : token[0]
        })
/*        .on('afterCreateToken', function (e) {
            if (!validateEmail(e.token.value)) {
                $(e.relatedTarget).addClass('invalid')
            }
        })*/
        .on('beforeEditToken', function (e) {
            if (e.token.label !== e.token.value) {
                var label = e.token.label.split(' (');
                e.token.value = label[0] + '|' + e.token.value
            }
        })
        .on('preventDuplicateToken', function (e) {
            new PNotify({ title: 'This email is already an attendee! ' + e.token.value, type: "error" });
        })
        .tokenfield({
            autocomplete:{
                source: function (request, response) {
                    var str = request.term;
                    $.get(api_address + "Users?$filter=indexof(tolower(FullName), '" + str + "') ge 0 or indexof(tolower(UserName), '" + str + "') ge 0 and Active eq true&$select=FullName,UserName,Id", {},
                        function (data) {
                            response($.map(data.value, function (el) {
                                return { id: el.Id, label: el.FullName + " (" + el.UserName + ")", value: el.UserName };
                            }));
                        });
                },
                minLength: 2,
                focus: function( event, ui ) {
                    event.preventDefault();
                    $("#userSearch_appointments").val( ui.item.label );
                },
                select: function (event, ui) {
                    event.preventDefault();
                    $('input[name=User_Id]').val(ui.item.id);
                    addAttendee(ui.item.value + "@greenclickmedia.dk");
                    openCalendarIFrame(ui.item.value);
                    $("#userSearch_appointments").val( ui.item.label );
                }
            }
        });
}

function loadDraftsTab() {
    var model = getModel();
    var modelId = getModelId();
    //apply different filters, depending on the model
    var filters = '';

    if (model == 'Contract') {
        filters += 'DraftLine/any(d:d/Contract_Id eq ' + modelId + ')';
    } else if (model == 'ClientAlias') {
        filters += 'ClientAlias_Id eq ' + modelId
    }

    var placeholder = $('#draftsTab').find('.table-responsive');
    placeholder.loadTemplate(base_url + '/templates/drafts/draftsTabTable.html', {}, {
        success: function () {
            var table = $('#draftsTabTable').DataTable({
                "bPaginate": false,
                'bInfo': false,
                'bFilter': false,
                "iDisplayLength": "All",
                responsive: true,
                stateSave: true,
                aaSorting: [[2, "asc"]], // shows the newest items first
                "bProcessing": true,
                "bServerSide": true,
                "deferRender": true, // testing if speed is better with this
                'filter': '(Status ne \'Deleted\' and Status ne \'Invoice\') and ' + filters,
                "sAjaxSource": api_address + "Drafts?$expand=DraftLine($expand=Product)",
                "aoColumns": [
                    {
                        mData: "Id", mRender: function (id) {
                            return "<a target='_blank' href='" + linkToItem('Draft', id, true) + "'>" + id + '</a>';
                        }
                    },
                    {
                        mData: 'Created', sType: 'date', mRender: function (created) {
                            return new Date(created).toDateTime();
                        }
                    },
                    {
                        mData: 'NoticeAccountant', sType: 'date', mRender: function (created) {
                            if (created != null) {
                                return new Date(created).toDateTime();
                            } else {
                                return "-"
                            }
                        }
                    },
                    {
                        mData: null, oData: null, sortable: false, mRender: function (obj) {
                            var lines = '';

                            if (obj.DraftLine.length > 0) {
                                $.each(obj.DraftLine, (function (index, line) {
                                    if (line.Product) {
                                        lines += line.Product.Name + ' x ' + line.Quantity + ' months x ' + Number(line.UnitPrice).format(true) + " DKK <br>"
                                    } else {
                                        lines += 'No product for draft line ' + line.Id + '<br>'
                                    }
                                }))
                            }
                            return lines;

                        }
                    }
                ],
                "fnServerData": fnServerOData,
                "iODataVersion": 4,
                "bUseODataViaJSONP": false
            })
        }
    })
}

/**
 * returns the eligable discount for a client alias
 */
function getEligableDiscount(aliasId) {
    // if we don't rquest specific alias Id , get the current model one
    var id = aliasId || getModelId();
    return $.get(api_address + 'ClientAlias(' + id + ')/EligibleDiscountProcentage')
}


function formatPhoneNumber(phonenumber) {
    // replace + with 00 //  //remove any non-digits chars/ including whitespaces
    return phonenumber.replace('+', '').replace(/\D/g, '');
}

function createCallingLink(canCall, phonenumber) {

    if (phonenumber == null) return "";
    if (canCall) {
        return "<span class='pseudolink flexfoneCallOut'>" + formatPhoneNumber(phonenumber) + "</span>"
    } else {
        return "<a href='tel:" + formatPhoneNumber(phonenumber) + "'>" + phonenumber + "</a>"
    }

}
/**
 * checks if the user has a LocalNumber, from which we can use FlexFones calling system
 * If he doesn't have local number, we generate a phone link
 */
function canCall() {
    return $('#user-LocalNumber').val();
}
function getIsoDate(date) {
    if (!date) {
        var da = new Date();
        return da.toISOString();
    } else {
        var da = new Date(date);
        return da.toISOString();
    }
}
/**
 * @deprecated
 * @param data
 */
function createEvent(data) {
    $.post(base_url + '/calendar/create-event', data).success((function (data) {
        refreshCalendar();
        closeDefaultModal();
    }));
}

function fingUserByLocalNumber(localnumber) {
    return $.get(api_address + 'Users?$select=FullName,Id&$filter=EmployeeLocalNumber eq ' + localnumber + '&$top=1')
}

function refreshCalendar() {
    var calPlaceholder = $('.responsive-iframe-container');
    openCalendarIFrame(getUserName(), calPlaceholder);
}


function sortMyTasks() {
    var $wrapper = $('#myTaskBody');
    var tasks = $wrapper.find(".rightMenuTaskContainer").filter((function () {
        return $(this).data('dueDate') !== undefined;
    }));
    tasks.sort((function (a, b) {
        var date1 = new Date(a.dataset.dueDate);
        var date2 = new Date(b.dataset.dueDate);
        return date2 < date1 ? 1 : -1;
    })).prependTo($wrapper);
}

function sortDashboardTasks() {
    var $wrapper = $('.panel-tasks');
    var tasks = $wrapper.find(".DashboardTask").filter((function () {
        return $(this).data('dueDate') !== undefined;
    }));
    tasks.sort((function (a, b) {
        var date1 = new Date(a.dataset.dueDate);
        var date2 = new Date(b.dataset.dueDate);
        return date2 < date1 ? 1 : -1;
    })).prependTo($wrapper);
}

function dashboardTasks() {
    var tasksContainer = $('.panel-tasks');
    tasksContainer.empty();
    var userId = getUserId();
    var userQuery = "(AssignedTo_Id eq '" + userId + "' and ParentTaskListId eq null) or (ParentTaskListId ne null and AssignedTo_Id eq '" + userId + "' and Parent/AssignedTo_Id ne '" + userId + "' and Value eq false)";
    $.get(api_address + "TaskLists?$filter=Value eq false and " + userQuery + "&$expand=Children($filter=Value eq false)&$top=10&$orderby=DueTime desc,Created").success(function (data) {
        var tasks = [];
        $.each(data.value, (function (a, b) {
            var task = {};
            if (b.DueTime) {
                task.Due = "Due: ";
            }
            if (b.Model && b.ModelId) {
                task.TaskModel = b.Model;
                task.TaskModelId = b.ModelId
            }
            task.Title = b.Title + (b.Children.length > 0 ? " (" + b.Children.length + ")" : "");
            task.Id = b.Id;
            task.taskLink = base_url + '/tasks/show/' + b.Id;
            task.DueTime = b.DueTime;
            task.Description = b.Description;
            tasks.push(task);
        }));
        tasksContainer.loadTemplate(base_url + '/templates/tasks/dashboardTask.html', tasks, {
            append: true,
            success: function () {
                sortDashboardTasks()
            }
        });
    });
}

//refresh dashboard tasks on button click
$('.refreshTasks').click((function (event) {
    $(event.target).css('pointer-events', 'none');
    dashboardTasks();
    setTimeout(function () {
        $(event.target).css('pointer-events', '');
    }, 2000)
}));

/**
 * move certain item to different sellers, managers...
 */
function initializeAdminMenu() {
    $.get(base_url + '/app/userRelations/' + getModel())
        .success((function (relations) {
            // get users list
            $.when($.get(base_url + '/users/getList'))
                .then((function (users) {
                    // render a select form for each user relation
                    var parsed = $.parseJSON(users);
                    var placeholder = $('.adminUserAssign');
                    var relation = JSON.parse(relations);
                    relation.forEach((function (rel) {

                        placeholder.loadTemplate(base_url + '/templates/admin/userSelect.html', {
                            Name: rel[1],
                            Label: rel[0],
                            FormName: rel[0] + rel[1]
                        },
                            {
                                append: true,
                                success: function () {
                                    placeholder = $('#' + rel[0] + rel[1]).find('select');
                                    for (var prop in parsed) {
                                        placeholder.append($("<option></option>")
                                            .attr("value", prop)
                                            .text(parsed[prop]));
                                    }
                                }
                            })

                    }));
                }))
        }));

    $('#adminModelName').text(getModel());
}

function adminUserAssigned(form) {

    var data = form.serializeJSON();
    var model = getModel();
    if (model == 'ClientAlias') {
        $.ajax({
            url: api_address + "ClientAlias(" + getModelId() + ")/Move",
            type: "POST",
            data: JSON.stringify(data),
            success: function () {
                new PNotify({ "title": "Updated. Refreshing..." });
                location.reload(true);
            },
            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        });
    } else if (model == 'Contract') {
        $.ajax({
            url: api_address + "Contracts(" + getModelId() + ")/Move",
            type: "POST",
            data: JSON.stringify(data),
            success: function () {
                new PNotify({ "title": "Updated. Refreshing..." });
                location.reload(true);
            },
            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        });
    }
    else {


        $.ajax({
            url: api_address + (getModel() == "ClientAlias" ? "ClientAlias" : getModel() + "s") + "(" + getModelId() + ")",
            type: "Patch",
            data: JSON.stringify(data),
            success: function () {
                new PNotify({ "title": "updated" });
            },
            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        });
    }
}



/**
 * opens a checklsit creation menu
 */
function openCheckListMenu() {
    // get the modal
    var modal = getDefaultModal();

    // set the title
    modal.find('.modal-title').append(Lang.get('labels.add-checklist'));
    modal.find('.modal-body').loadTemplate(
        base_url + '/templates/tasks/checklistForm.html',
        {
            ChecklistGroupLabel: Lang.get('labels.checklist-group-name'),
            SaveLabel: Lang.get('labels.save'),
            TitleLabel: Lang.get('labels.title'),
            DescriptionLabel: Lang.get('labels.description'),
            Model: getModel(),
            ModelId: getModelId()
        },
        {
            append: true,
            success: function () {
                $.get(base_url + '/users/getList').success((function (data) {
                    var users = JSON.parse(data);
                    var usersSelect = $('#checklistItem-AssignedTo_Id');
                    var usersSelect1 = $('#checklist-AssignedTo_Id');
                    for (var prop in users) {
                        usersSelect.append($("<option></option>")
                            .attr("value", prop)
                            .text(users[prop]));
                    }

                    for (var prop in users) {
                        usersSelect1.append($("<option></option>")
                            .attr("value", prop)
                            .text(users[prop]));
                    }
                }));
            }
        });
}

/**
 * reinitializes the favicon in the tab
 */
function setFavicon() {
    var link = $('link[type="icon"]').remove().attr("href");
    $('<link href="' + link + '" rel="icon"  type="icon" />').appendTo('head');
}

/**
 * updates a task from the quick form
 */
function updateTask(event, formData) {

    var button = $(event.target).find('input:submit');
    button.prop('disabled', true);

    // sets null for all empty input
    for (var prop in formData) {
        if (formData[prop] === "") {
            formData[prop] = null;
        }
    }
    var id = formData.TaskId;
    delete (formData.TaskId);

    var updateRow = formData.UpdateRow ? true : false;
    delete (formData.UpdateRow);

    // make dates suitable for odata
    if (formData.StartTime) {
        formData.StartTime = moment(formData.StartTime);
    }
    if (formData.DueTime) {
        formData.DueTime = moment(formData.DueTime);
    }
    var forItem = formData.ForItem ? true : false;
    delete (formData.ForItem);
    // check whether creator should be notified
    formData.NotifyCreator = formData.NotifyCreator ? true : false;
    return $.ajax({
        url: api_address + "TaskLists(" + id + ")",
        type: "Patch",
        data: JSON.stringify(formData),
        success: function (task) {
            closeDefaultModal();
            if ($('body').hasClass('show-rightbar')) {
                myTasks();
            }
            if (forItem) {
                itemTasks(formData.Model, formData.ModelId);
            }

            if (updateRow && getModelId()) {
                subtaskstable.draw();
            } else if (getModel() == 'TaskList') {
                tasksTable.draw();
            }

            //if dashboard Tasks list is rendered refresh the tasks
            if ($('.panel-tasks').length > 0) {
                dashboardTasks();
            }
            button.prop('disabled', false)
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}

/**
 * adds attendee for an appointment
 *
 * @param email
 */
function addAttendee(email) {
    //check if attendee exists
    var attendeesRaw = $("input[name='Attendees[EMail]']").map((function () {
        return $(this).val();
    })).get();

    if (isInArray(email, attendeesRaw)) {
        new PNotify({
            title: Lang.get('labels.error'),
            text: Lang.get('labels.already-attendee'),
            type: 'error'
        });
        return false;
    }
    $('#appointment-Attendees').loadTemplate(base_url + '/templates/appointmentAttendee.html', { AttendeeEmail: email },
        {
            append: true,
            success: function () {
                $("#add-attendee-field").val('');
            }
        });
}

/**
 * initializes seo checklist menu
 */
function initializeSeoChecklist() {
    //get the model and modelId
    var model = getModel();
    var modelId = getModelId();
    $.get(api_address + 'TaskLists?$filter=ParentTaskListId eq null and Model eq \'$model\' and ModelId eq $modelId'.replace('$model', model).replace('$modelId', modelId) + '&$expand=Children($orderby=Value,SortOrder asc)&$orderby=Value')
        .success(function (data) {
            // render checklist items
            if (data.value.length > 0) {
                renderChecklistItems(data.value);
            }
        });
}

//Renders checklist group and it's items
function renderChecklistItems(tasks) {

    var container = $('.seoChecklist');

    //load a group container and it's tasks
    // the main task is the name of the group
    tasks.forEach((function (a, b) {
        //add a class to all completed tasks
        var hidden = "";
        if (a.Value) {
            hidden = "completedSeo";
        }
        var data = {
            Link: linkToItem('TaskList', a.Id, true),
            Id: a.Id,
            Title: a.Title,
            Description: a.Description,
            Checked: a.Value,
            Hidden: hidden
        };
        container.loadTemplate(
            base_url + '/templates/tasks/checklistGroup.html', data, {
                append: true,
                success: function () {
                    var completedSeo = $(".seoChecklist > .completedSeo");
                    // load the children, find the container of the current task
                    var childrenContainer = $('.checklistParent[value="' + a.Id + '"]').closest('.form-group').find('.checklistChildren');
                    //if we found the conainer - map the children into array
                    if (childrenContainer.length > 0) {
                        if (a.Children.length > 0) {
                            var children = $.map(a.Children, (function (child) {
                                var c = {
                                    Id: child.Id,
                                    Title: child.Title,
                                    Description: child.Description
                                };
                                if (a.Value) c.Disabled = a.Value;
                                c.Checked = child.Value;
                                return c;
                            }));
                            // load all children
                            childrenContainer.loadTemplate(
                                base_url + "/templates/tasks/checklistItem.html",
                                children,
                                {
                                    prepend: true
                                }
                            )
                        }
                    }
                    completedSeo.hide();
                    if (completedSeo.length > 0) {
                        $('#showCompletedCheckList').prop('disabled', false);
                    }
                }
            }
        )
    }));
}

/**
 * opens task edit modal
 *
 * @param id
 * @param row , if we send this, it should update the row of the subtask
 */
function quickEditTask(id, row) {

    //open the modal and put the form in it
    var modal = getDefaultModal();
    var body = modal.find('.modal-body');
    var header = modal.find('.modal-title');
    header.append(Lang.get('labels.edit-task') + ' ' + linkToItem('TaskList', id));
    //get the task info
    $.when($.get(api_address + 'TaskLists(' + id + ')?$expand=Children')
        .success((function (result) {
            return result;
        }))
    ).then((function (task) {

        if (task.StartTime != null) {
            var start = moment(task.StartTime);
            task.StartTime = start.format('YYYY/MM/DD HH:mm');
        }

        if (task.DueTime != null) {
            var due = moment(task.DueTime);
            task.DueTime = due.format('YYYY/MM/DD HH:mm');
        }

        var taskObj = {
            TaskId: task.Id,
            ItemLabel: Lang.get('labels.item'),
            Model: task.Model,
            ModelId: task.ModelId,
            TitleLabel: Lang.get('labels.title'),
            Title: task.Title,
            DescriptionLabel: Lang.get('labels.description'),
            Description: task.Description,
            StartTimeLabel: Lang.get('labels.start-time'),
            StartTime: task.StartTime,
            DueTimeLabel: Lang.get('labels.due-time'),
            DueTime: task.DueTime,
            UserLabel: "Assigned to",
            AssignedTo_Id: task.AssignedTo_Id,
            NotifyCreator: task.NotifyCreator ? 'checked' : null,
            SortOrder: task.SortOrder
        };
        if (row) {
            taskObj.Row = 'true';
        }
        //don't let people edit task, not created by themselves . todo subject to change?
        modal.find('.modal-body').loadTemplate(base_url + '/templates/tasks/taskEditForm.html', taskObj,
            {
                overwriteCache: true,
                success: function () {
                    //initalize the users select
                    $.get(base_url + '/users/getList').success((function (data) {
                        var users = JSON.parse(data);
                        var usersSelect = $('#quickTaskEditForm').find('#task-AssignedTo_Id');
                        for (var prop in users) {
                            usersSelect.append($("<option></option>")
                                .attr("value", prop)
                                .text(users[prop]));
                        }
                        usersSelect.val(task.AssignedTo_Id);
                    }));

                    // initalize the datetime pickers
                    $('.datetimepicker').datetimepicker({
                        changeMonth: true,
                        changeYear: true,
                        showButtonPanel: true,
                        minDate: '-1970/01/01',
                        allowTimes: allowedTimes()
                    });
                }
            })
    }));
}

/**
 * opens a modal with list of order types
 */
function openCreateOrderModal() {
    //get a list of orders
    $.ajax({
        url: api_address + "OrderTypes?$select=Id,FormName",
        type: "GET",
        success: function (data) {
            //add a default order
            var orderTypes = [];
            orderTypes.push({ FormName: Lang.get('labels.default'), link: base_url + "/orders/create" });
            data.value.forEach((function (orderType) {
                orderTypes.push({ FormName: orderType.FormName, link: base_url + "/orders/create/" + orderType.Id })
            }));
            //get the default modal
            var modal = $('#defaultModal').modal();
            modal.find('.modal-title').empty().append(Lang.get('labels.create-order'));
            modal.find('.modal-body').empty().loadTemplate(base_url + '/templates/createOrderLink.html', orderTypes, {
                prepend: true
            });
            return false;
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}

/**
 * shows callendar of a selected user
 *
 * @param userName
 * @param placeholder
 */
function openCalendarIFrame(userName, placeholder) {
    if (typeof placeholder == 'undefined') {
        placeholder = $('#calendarIFrame');
    }
    placeholder.empty().append('<iframe src="https://www.google.com/calendar/embed?showTitle=0&amp;showDate=1&amp;showPrint=1&amp;showCalendars=0&amp;showTz=0&amp;height=400&amp;wkst=2&amp;bgcolor=%23FFFFFF&amp;src=' + userName + '@greenclickmedia.dk&amp;color=%232F6309&amp;ctz=Europe%2FCopenhagen&output=embed" style="border-width: 0;width: 100%;height:400px;"></iframe>');
}

/**
 *delete a file and remove it from the table
 *
 * @param event
 */
function deleteFile(event) {
    var fileId = $(event.target).closest('tr').data('file-id');

    bootbox.confirm("Are you sure?", (function (result) {
        if (result) {
            $.ajax({
                url: api_address + "FileStorages(" + fileId + ")",
                type: "DELETE",
                success: function () {
                    $(event.target).closest('tr').remove();
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        }
    }));

    return false;
}
/**
 * file upload from dropzone functionality
 */
function initializeDropzone() {

    /** Don't let dropzone apply automatically */
    Dropzone.autoDiscover = false;

    var model = $('#Model').val();
    var modelId = $('#ModelId').val();
    var dropzone = new Dropzone("#itemFileUploadForm", {
        url: api_address + "FileStorages",
        maxFilesize: 100,
        headers: {
            'Authorization': 'Bearer ' + $.cookie('auth')
        }
    });

    dropzone.on("addedfile", (function (file) {
        file.previewElement.addEventListener("click", (function () {
            dropzone.removeFile(file);
        }));
    }));

    dropzone.on("complete", (function (file) {
        // when the file is uploaded, we need to patch it with correct information
        var response = JSON.parse(file.xhr.response);
        var fileId = response.Id;
        $.ajax({
            url: api_address + "FileStorages(" + fileId + ")",
            type: "PATCH",
            data: JSON.stringify({ Model: model, ModelId: modelId }),
            success: function () {
                // append the file to the table
                //files table
                var tableBody = $('#item-files').find('tbody');
                var uploadDate = new Date(response.Created);
                //append the file only if we have table for it
                if (tableBody.length > 0) {
                    tableBody.loadTemplate(base_url + '/templates/uploadedFile.html', {
                        Created: uploadDate.toDateTime(),
                        FileId: response.Id,
                        PreviewLabel: Lang.get('labels.preview'),
                        Name: response.Name,
                        FullName: $('#user-FullName').val(),
                        UserName: $('#user-UserName').val(),
                        DownloadLabel: Lang.get('labels.download'),
                        DeleteLabel: Lang.get('labels.delete')
                    }, { prepend: true });
                }
            },

            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        });
        dropzone.removeFile(file);
    }));

    dropzone.on('error', (function (error) {
        handleError(error);
    }));

    //select / unselect all checkboxes
    var label = $("label[for='selectAllFiles']");
    label.text('Select all');

    body.on('change', '#selectAllFiles', (function (event) {
        var target = $(event.target);
        var checked = target.prop('checked');
        if (checked) {
            $('.fileCheckBox').prop('checked', true);
            label.text("Unselect all");
        } else {
            $('.fileCheckBox').prop('checked', false);
            label.text("Select all");
        }
    }));

    body.on('change', '.fileCheckBox', (function (event) {
        if ($('.fileCheckBox:checked').length == $('.fileCheckBox').length) {
            $('#selectAllFiles').prop('checked', true);
            label.text("Unselect all");
        }
        else {
            $('#selectAllFiles').prop('checked', false);
            label.text("Select all");
        }
    }));

    //download all selected files
    $('#dlSelectedFiles').on('click', (function (event) {

        var selectedFiles = $('.fileCheckBox:checked').toArray();
        var ids = $.map(selectedFiles, (function (selectedFile) {
            return 'file[]=' + selectedFile.value;
        }));

        window.open(base_url + "/files/bulk-download?" + ids.join('&'), 'myWindow', 'width=500,height=500,scrollbars=yes').focus();
    }));


}


/**
 * delete contact person
 */
function deleteContact(event) {

    var contactId = $(event.target).closest('.deleteContactBtn').data('contact-id');

    bootbox.confirm("Are you sure?", (function (result) {
        if (result) {
            $.ajax({
                url: api_address + "Contacts(" + contactId + ")",
                type: "DELETE",
                success: function () {
                    contactsTable.draw();
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        }
    }));

    return false;
}


/**
 * saves a contact
 * todo use template
 */
function saveContact(event) {
    event.preventDefault();
    var form = $(event.target);
    var btn = form.find(":submit");
    var formData = convertSerializedArrayToHash(form.serializeArray());

    delete (formData['_token']);

    // sets null for all empty input
    for (var prop in formData) {
        if (formData[prop] === "") {
            delete (formData[prop]);
        }
    }
    if (formData.Facebook) {
        formData.Facebook = addhttp(formData.Facebook);
        if (!validateUrl(formData.Facebook)) {
            var fbField = $('#contact-Facebook');
            fbField.focus();
            fbField.closest('.form-group').addClass('has-error');
            new PNotify({
                title: "Enter a valid Facebook link",
                type: "error"
            });
            return;
        }
    }
    if (formData.LinkedIn) {
        formData.LinkedIn = addhttp(formData.LinkedIn);
        if (!validateUrl(formData.LinkedIn)) {
            var linkedinField = $('#contact-LinkedIn');
            linkedinField.focus();
            linkedinField.closest('.form-group').addClass('has-error');
            new PNotify({
                title: "Enter a valid LinkedIn link",
                type: "error"
            });
            return;
        }
    }

    btn.prop('disabled', true);
    $.ajax({
        type: "POST",
        url: api_address + 'Contacts',
        data: JSON.stringify(formData),
        success: function (data) {
            form[0].reset();
            btn.prop('disabled', false);
            contactsTable.draw();
        }, error: function (err) {
            btn.prop('disabled', false);
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}

/**
 * Shows the task creation form on the right menu
 */
$("#openTaskCreation").click((function () {

    if ($('#taskCreate').css('display') == 'none') {
        $('#taskCreate').show();
        $('input[name=quickAddTask]').focus();
    }
    else {
        $('#taskCreate').hide();
    }
    return false;
}));

$("#closeIFrame").click((function () {
    $('#iframe').hide();
    return false;
}));

$('body').on('click', 'button#editTaskIFrame', (function () {
    showIFrame($(this).attr('value'));
}));

function showIFrame(src) {
    $("#iframe").show();
    $("#frame").attr("src", src);
    return false;
}

/**
 * Saves a task from the right menu
 */
$("#quickAddTaskForm").on('submit', (function (event) {

    event.preventDefault();
    var form = $(this);
    //find the submit button and disable it
    form.find('button[type=submit]').attr('disabled', true);

    //get the form data
    var data = form.serializeJSON();
    if (data.ForItem && getModel() && getModelId()) {
        data.Model = getModel();
        data.Item = getModelId();
        delete (data.ForItem);
    }
    data.NotifyCreator = true;

    $.ajax({
        url: api_address + "TaskLists/action.NewTask",
        type: "post",
        data: JSON.stringify(data),
        success: function (task) {
            // refresh the tasks only of the rightbar is shown
            if ($('body').hasClass("show-rightbar")) {
                myTasks();
            }
            taskCountBadge();
            form.find('button[type=submit]').attr('disabled', false);
            form[0].reset();
            // todo check if a model and modelId are equal to the current one OR ForItem is true
            if (task.Model && task.ModelId || data.ForItem) {
                itemTasks();
            }
            quickEditTask(task.Id);

            // if ( dashboard task is rendered) call dashboard tasks function
            if ($('.panel-tasks').length > 0) {
                dashboardTasks();
            }
        },
        error: function (error) {
            form.find('button[type=submit]').attr('disabled', false);
            var notice = new PNotify({
                title: 'Error',
                text: 'Could not save task',
                type: 'error'
            });
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}));


function taskProgress(task, justNumber) {
    var percentage = 0;

    //if the task is completed
    if (task.Value) {
        percentage = 100;
    }
    else {
        var childTasks = task.Children.length;

        //if the task has any subtasks
        if (childTasks > 0) {
            var complSubtasks = 0;
            for (var i = 0; i < childTasks; i++) {
                if (task.Children[i].Value) {
                    complSubtasks++;
                }
            }
            percentage = (complSubtasks * 100) / childTasks;

        }
            //if there  aren't any subtasks
        else {
            percentage = 0;
        }
    }

    if (!justNumber) {
        $(".progressBar[data-task-id='" + task.Id + "']").css("width", percentage.toFixed() + '%');
        $(".progressP[data-task-id='" + task.Id + "']").html(percentage.toFixed() + '%');
    } else {
        return percentage.toFixed();
    }
}

/**
 * Marks a task as completed or not completed
 * @param event
 */
function checkTask(event) {
    var target = $(event.target);
    //should the task be checked or unchecked
    var checked = target.prop('checked');
    var id = target.val();
    var action = checked ? "Check" : "UnCheck";

    //disable the checkbox
    //check if the action is called from the table
    var isInTable = target.hasClass('tableTask');
    var checklist = target.hasClass('checklistTask');
    if (checklist) var checklistParent = target.hasClass('checklistParent');

    target.prop('disabled', true);
    $.ajax({
        url: api_address + "TaskLists(" + id + ")/action." + action,
        type: "POST",
        success: function () {
            // refresh the tasks only of the rightbar is shown
            if ($('body').hasClass("show-rightbar") && !isInTable) {
                myTasks();
            }
            if ($('.panel-tasks').length > 0) {
                target.closest('li').remove();
            }

            taskCountBadge();

            //cross out the element
            if (isInTable) {
                checked ? target.closest('tr').addClass('crossed-through') : target.closest('tr').removeClass('crossed-through');
                target.prop('disabled', false);

                $.get(api_address + 'TaskLists(' + id + ')?$select=Id&$expand=Parent($expand=Children($select=Value);$select=Id,Value)')
                    .success(function (task) {
                        taskProgress(task.Parent);
                    });
            }
            //checklist logic
            // if it's checklist and we are de-checking a parent, enable the inputs
            if (checklist) {
                var childrenContainer = $('.checklistParent[value="' + id + '"]').closest('.form-group').find('.checklistChildren');
                // if we are unchecking parent
                if (action == "UnCheck" && checklistParent) {
                    // find all children and remove their disabled property
                    childrenContainer.find('.checklistTask').prop('disabled', false);
                } else if (action == "Check" && checklistParent) { // if we are checking an input, disable the children
                    childrenContainer.find('.checklistTask').prop('disabled', true);
                }
                target.prop('disabled', false);
            }
        },
        error: function (error) {
            $(event.target).prop('disabled', false);

            new PNotify({ title: 'Error', text: 'Could not check task', type: 'error' });
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}

/**
 * get the displayed item tasks
 */
function itemTasks(model, modelId) {

    /**
     * load tasks for the side menu
     */
    var userId = $('#user-Id').val();
    var userQuery = "AssignedTo_Id eq '" + userId + "'";
    var myTaskBody = $('#itemTasksBody');
    //clear the div
    myTaskBody.empty().addClass('spinner');
    $.ajax({
        url: api_address + "TaskLists?$filter=Value eq false and ModelId eq " + modelId + " and Model eq '" + model + "' and ParentTaskListId eq null&$select=Title,Id,Description,Model,ModelId&$expand=Children($filter=Value eq false),&$orderby=Created",
        type: "GET",
        success: function (data) {
            if (data.value.length > 0) {
                var result = $.map(data.value, (function (value, index) {
                    var task = {
                        TaskId: value.Id,
                        TaskLink: linkToItem('TaskList', value.Id, true),
                        TaskTitle: value.Title,
                        Description: value.Description
                    };
                    if (value.Model && value.ModelId) {
                        task.TaskModel = value.Model;
                        task.TaskModelId = value.ModelId;
                        //task.TaskLink = linkToItem(value.Model,value.ModelId,true)
                    }
                    if (value.Children.length > 0) {
                        task.SubTasksCount = '<i class="fa fa-plus-circle expandTasks"></i>'
                    }
                    return task;
                }));

                myTaskBody.loadTemplate(base_url + '/templates/tasks/task.html', result, {
                    append: true,
                    success: function () {
                        myTaskBody.removeClass('spinner')
                    }
                })
            } else {
                myTaskBody.removeClass('spinner').text('There are no tasks for the current item');
            }
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}

function nextAppointment(model, modelid) {

    var placeholder = $('.next-appointment');

    $.ajax({
        url: api_address + "CalendarEvents/NextAppointment",
        type: "POST",
        data: JSON.stringify({ Model: model, ModelId: modelid }),
        success: function (data) {
            if (data.value[0]) {
                placeholder.html(
                    "<a target='_blank' title='" + (data.value[0].Description || '') + "' href='" + base_url + "/appointments/show/" + data.value[0].Id + "'>" + data.value[0].Summary + " " + toDateTime(data.value[0].Start) + "<a>"
                )
            } else {
                placeholder.html('No future appointments.')
            }
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}

/**
 * ajax that renders the task element and puts it in the left sidebar
 */
function myTasks() {
    /**
     * load tasks for the side menu
     */
    var userId = getUserId();
    var userQuery = "(AssignedTo_Id eq '" + userId + "' and ParentTaskListId eq null) or (ParentTaskListId ne null and AssignedTo_Id eq '" + userId + "' and Parent/AssignedTo_Id ne '" + userId + "' and Value eq false)";
    var myTaskBody = $('#myTaskBody');
    //clear the div
    myTaskBody.empty().addClass('spinner');
    $.ajax({
        url: api_address + "TaskLists?$filter= Value eq false and " + userQuery + "&$select=Title,Id,Description,Model,ModelId,DueTime&$expand=Children($filter=Value eq false)&$orderby=DueTime desc,Created",
        type: "GET",
        success: function (data) {
            if (data.value.length > 0) {
                var result = $.map(data.value, (function (value, index) {
                    var task = {
                        TaskId: value.Id,
                        TaskTitle: value.Title,
                        TaskLink: linkToItem('TaskList', value.Id, true),
                        Description: value.Description,
                        DueTime: value.DueTime,
                        Due: value.DueTime ? "Due: " : null
                    };
                    if (value.Model && value.ModelId) {
                        task.TaskModel = value.Model;
                        task.TaskModelId = value.ModelId;
                        //task.TaskLink = linkToItem(value.Model,value.ModelId,true)
                    }
                    if (value.Children.length > 0) {
                        task.SubTasksCount = '<i class="fa fa-plus-circle expandTasks"></i>'
                    }
                    else {
                        task.SubTasksCount = '<i class="fa fa-plus-circle" style="visibility:hidden;"></i>'
                    }
                    return task;
                }));

                myTaskBody.loadTemplate(base_url + '/templates/tasks/task.html', result, {
                    overwriteCache: true,
                    success: function () {
                        sortMyTasks();
                        myTaskBody.removeClass('spinner')
                    }
                })
            } else {
                myTaskBody.removeClass('spinner').text('You don\'t have any tasks yet. Create one above.');
            }
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}

function expandSubtasks(id) {
    var modal = getDefaultModal(150);
    var body = modal.find('.modal-body');
    body.addClass('spinner');

    $.when($.get(api_address + 'TaskLists(' + id + ')?$expand=AssignedTo($select=FullName),CreatedBy($select=FullName),Children')
        .success((function (result) {
            return result;
        }))
    ).then(function (task) {
        var data = {
            Id: task.Id,
            TaskLink: base_url + '/tasks/show/' + task.Id,
            Model: task.Model,
            Title: task.Title,
            Description: task.Description,
            DueTime: task.DueTime,
            CreatedBy: task.CreatedBy.FullName,
            Created: task.Created
        };
        if (task.Model) {
            data.ModelId = linkToItem(task.Model, task.ModelId, true);
            data.ForItem = "For item: ";
        }
        if (task.AssignedTo) {
            data.AssignedTo = task.AssignedTo.FullName;
        }
        else {
            data.AssignedTo = "--";
        }
        body.loadTemplate(base_url + '/templates/tasks/subtaskModal.html', data,
            {
                success: function () {
                    if (task.Model && task.Model != 'TaskList') {
                        $.when(getCompanyName(task.Model, task.ModelId)).then((function (name) {
                            if (name) {
                                $('.taskModelName').append(name.value);
                            }
                        }));
                    }
                    initalizeSubTasksTable(id, 'modalSubTasksTable');
                    taskProgress(task);
                    body.removeClass('spinner');
                }
            })
    });
}

// add event listener to any element child element of the one you wanna hide
function hideThis(element) {
    element.style.display = 'none';
    return false;
}

function initalizeSubTasksTable(TaskId, Selector) {
    subtaskstable = $('#' + Selector).DataTable(
        {
            responsive: true,
            bPaginate: false,
            aaSorting: [[6, 'asc']], // shows the newest items first
            'filter': "Created ne null",
            "sAjaxSource": api_address + "TaskLists(" + TaskId + ")/Children?$expand=AssignedTo($select=FullName),CreatedBy($select=FullName)",
            'select': 'Id,Value',
            "bProcessing": true,
            "iDisplayLength": "All",
            "bServerSide": true,
            "fnRowCallback": function (nRow, aaData) {
                checkedTasks = 0;
                if (aaData.Value) {
                    $(nRow).addClass('crossed-through');
                    checkedTasks++;
                } else {
                    $(nRow).removeClass('crossed-through');
                }

                if (aaData.Model && aaData.ModelId) {
                    $.when(getCompanyName(aaData.Model, aaData.ModelId))
                        .then((function (name) {
                            var clientName = "View";
                            if (name.value != 'Undefined') clientName = name.value;

                            $(nRow).find('td:nth-child(3)').html("<a href='" + linkToItem(aaData.Model, aaData.ModelId, true) + "' target='_blank'>" + clientName + "</a>");
                        }));

                }
            },
            "aoColumns": [
                {
                    mData: "Title", sType: "string", mRender: function (title, display, obj) {
                        return "<a href='" + base_url + "/tasks/show/" + obj.Id + "'>" + title + "</a>";
                    }
                },
                {
                    mData: "Description",
                    "sClass": "show-more-container"
                },
                {
                    mData: null, oData: "AssignedTo/FullName", mRender: function (obj) {
                        if (obj.AssignedTo) {
                            return obj.AssignedTo.FullName;
                        } else {
                            return ""
                        }
                    }
                },
                {
                    mData: null, oData: "CreatedBy/FullName", mRender: function (obj) {
                        if (obj.CreatedBy) {
                            return obj.CreatedBy.FullName;
                        } else {
                            return ""
                        }
                    }
                },
                {
                    mData: "StartTime", searchable: false, mRender: function (start) {
                        if (start != null) {
                            var date = new Date(start);
                            return date.toDateTime();
                        } else {
                            return "";
                        }
                    }
                },
                {
                    mData: "DueTime", searchable: false, mRender: function (due) {
                        if (due != null) {
                            var date = new Date(due);
                            return date.toDateTime();
                        } else {
                            return "";
                        }
                    }
                },
                // {
                //     mData: "SortOrder",searchable:false
                // },
                {
                    mData: 'EndTime,SortOrder', oData: null, sortable: false, searchable: false, mRender: function (endTime, display, obj) {
                        return '<i class="fa fa-times deleteSubTask" title="Delete"></i>/' +
                            '<span title="Edit the sub task" class="pseudolink"><i class="fa fa-pencil quickEditSubTask"></i></span>/' +
                            '<input class="taskCheck tableTask" ' + (obj.Value ? 'checked="checked"' : '') + ' title="Complete the task" type="checkbox" id="task_' + obj.Id + '" value="' + obj.Id + '">'
                    }
                }
            ],
            "fnServerData": fnServerOData,
            "iODataVersion": 4,
            "bUseODataViaJSONP": false
        }).on('draw.dt', (function () {
            //initiate the more container after the table has loaded
            $('.show-more-container').more({
                length: 40, ellipsisText: ' ...',
                moreText: '<i class="fa fa-search-plus"></i>', lessText: '<i class="fa fa-search-minus"></i>'
            });
        }));
    // Return a helper with preserved width of cells
    var fixHelper = function (e, ui) {
        ui.children().each(function () {
            $(this).width($(this).width());
        });
        return ui;
    };

    $('#' + Selector + " tbody").sortable({
        helper: fixHelper, opacity: 0.8, cursor: 'move', stop: function (event, ui) {
            var Id = subtaskstable.row(ui.item).data().Id;
            var sortOrder = ui.item.index();
            //update each field sort order

            $.ajax({
                type: "PATCH",
                url: api_address + 'TaskLists(' + Id + ')',
                data: JSON.stringify({ SortOrder: sortOrder }),
                success: function () {
                },
                beforeSend: function (request) {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
            //
        }
    });
}

/**
 * function that returns icons for each user role
 *
 */
function userRolesIcons(roles) {
    return "todo map roles to icons";
}


/** TIME REGISTRATION FUNCTIONS */


function stopBreak() {
    $('#overlay, #break-timer').fadeOut(1000);
    if (breakCounter) {
        clearInterval(breakCounter);
    }
    $.removeCookie("breakSeconds", { path: '/' });
}

/**
 * Pads a string with a given value, e.g. 5 => 0005. n = current value, p = total result character count, c = char to use for padding
 *
 * @see http://stackoverflow.com/questions/1267283/how-can-i-create-a-zerofilled-value-using-javascript#answer-9744576
 */
function paddy(n, p, c) {
    var pad_char = (typeof c !== 'undefined' ? c : '0');
    var pad = new Array(1 + p).join(pad_char);

    return (pad + n).slice(-pad.length);
}
breakCounter = '';
function startBreakCounter() {
    var unixTimestamp = Math.floor(new Date().getTime() / 1000);
    var breakStartTime = new Date(unixTimestamp * 1000); // Convert from seconds to milliseconds

    //check if cookie with elapsed break time so far exists to start the timer from there
    if ($.cookie("breakSeconds")) {
        var removeSeconds = $.cookie("breakSeconds");
    } else {
        removeSeconds = 0;
        var date = new Date();
        date.setTime(date.getTime() + (60 * 60 * 1000));
        $.cookie("breakSeconds", removeSeconds, { expires: date, path: '/' });
    }

    breakStartTime.setSeconds(breakStartTime.getSeconds() - removeSeconds);

    // Update counter once per second
    breakCounter = window.setInterval((function () {
        var now = new Date();
        var differenceInMilliSeconds = Math.round(now - breakStartTime);
        var difference = new Date(differenceInMilliSeconds);

        var hours = difference.getUTCHours();
        var minutes = difference.getUTCMinutes();
        $('#counter').text(paddy(hours, 2) + ':' + paddy(minutes, 2) + ':' + paddy(difference.getUTCSeconds(), 2));

        var seconds = (minutes * 60) + difference.getUTCSeconds();
        $.cookie("breakSeconds", seconds, { expires: date, path: '/' });
        // console.log(seconds);

        // Lunch break (30 minutes)
        if (now.getHours() >= 12 && now.getHours() < 14) {
            if (hours >= 1 || minutes >= 30) {
                $('#break-timer #counter').css('color', '#D10026'); // Red
            }
            else if (hours == 0 && minutes >= 20) {
                $('#break-timer #counter').css('color', '#C2AB00'); // Yellow
            }
        }
            // Normal break (10 minutes)
        else {
            if (hours >= 1 || minutes >= 20) {
                $('#break-timer #counter').css('color', '#D10026'); // Red
            }
            else if (hours == 0 && minutes >= 10) {
                $('#break-timer #counter').css('color', '#C2AB00'); // Yellow
            }
        }
    }), 1000);

    setTimeout((function () {
        var overlay = $('<div id="overlay"></div>');
        $(overlay).hide().appendTo('body').fadeIn(1000);

        $('#break-timer').fadeIn(1000);
    }), 750);
}


function loadTimeRegButtons(status) {
    if (!status) {
        status = $.cookie('timeReg');
    }
    var template = '';
    switch (status) {
        case "CheckedIn":
            template = 'beginBreak.html';
            $.removeCookie("breakSeconds", { path: '/' });
            break;
        case "CheckedOut":
        case "Absent":
        case "Sick":
        case "Vacation":
            template = 'beginWork.html';
            $.removeCookie("breakSeconds", { path: '/' });
            break;
        case "Break":
            template = 'endWork.html';
            startBreakCounter();
            break;
        default:
            template = 'beginWork.html';
            $.removeCookie("breakSeconds", { path: '/' });
            break;
    }
    var buttonsDiv = $('.time-registration');
    buttonsDiv.children().remove();
    buttonsDiv.loadTemplate(base_url + "/templates/timeRegistrations/" + template);
}

function endWork() {
    $.ajax({
        type: "POST",
        url: api_address + 'TimeRegistrations/CheckOut',
        suppressErrors: true,
        success: function (data) {
            new PNotify({
                title: Lang.get('labels.success'),
                text: Lang.get('messages.check-out'),
                type: 'success'
            });
            var buttonsDiv = $('.time-registration');
            buttonsDiv.children().remove();
            buttonsDiv.loadTemplate(base_url + "/templates/timeRegistrations/beginWork.html",
                {
                    overwriteCache: true
                });
            stopBreak();
            setTimeRegCookie('CheckedOut');
            $.removeCookie('prevStatus', { path: '/' });
        },
        error: function (err) {
            new PNotify({
                title: "Info",
                text: "You have already checked out",
            });
            loadTimeRegButtons();
        }
    });
}

function beginWork() {
    if ($.cookie('timeReg') != 'Break' && $.cookie('timeReg') != 'CheckedIn') {
        $.ajax({
            type: "POST",
            url: api_address + 'TimeRegistrations/CheckIn',
            suppressErrors: true,
            success: function (data) {
                new PNotify({
                    title: Lang.get('labels.success'),
                    text: Lang.get('messages.check-in'),
                    type: 'success'
                });
                var buttonsDiv = $('.time-registration');
                buttonsDiv.children().remove();
                buttonsDiv.loadTemplate(base_url + "/templates/timeRegistrations/beginBreak.html");
                stopBreak();
                setTimeRegCookie('CheckedIn');
                $.removeCookie('prevStatus', { path: '/' });
                setPrevStatusCookie('CheckedOut');
            },
            error: function (err) {
                new PNotify({
                    title: "Info",
                    text: "You have already checked in",
                });
                loadTimeRegButtons();
            }
        });
    } else {
        $.ajax({
            type: "POST",
            url: api_address + 'TimeRegistrations/EndBreak',
            suppressErrors: true,
            success: function (data) {
                new PNotify({
                    title: Lang.get('labels.success'),
                    text: "Enjoy the rest of your work!",
                    type: 'success'
                });
                var buttonsDiv = $('.time-registration');
                buttonsDiv.children().remove();
                buttonsDiv.loadTemplate(base_url + "/templates/timeRegistrations/beginBreak.html");
                stopBreak();
                setTimeRegCookie("CheckedIn");
                $.removeCookie('prevStatus', { path: '/' });
                setPrevStatusCookie('Break');
            },
            error: function (err) {
                if ($.cookie('prevStatus') == 'Break') {
                    new PNotify({
                        title: "Info",
                        text: "You have already ended your break",
                    });
                    stopBreak();
                }
                else {
                    new PNotify({
                        title: Lang.get('labels.error'),
                        text: "You have already checked in",
                    });
                }
                loadTimeRegButtons();
            }
        });
    }
}

function beginBreak() {
    $.ajax({
        type: "POST",
        url: api_address + 'TimeRegistrations/BeginBreak',
        suppressErrors: true,

        success: function (data) {
            new PNotify({
                title: Lang.get('labels.success'),
                text: 'Enjoy your break',
                type: 'success'
            });
            var buttonsDiv = $('.time-registration');
            buttonsDiv.children().remove();
            buttonsDiv.loadTemplate(base_url + "/templates/timeRegistrations/endWork.html");
            setTimeRegCookie('Break');
            startBreakCounter();
            $.removeCookie('prevStatus', { path: '/' });
        },
        error: function (err) {
            new PNotify({
                title: "Info",
                text: "You have already begun your break",
            });
            loadTimeRegButtons();
        }
    });
}

function setTimeRegCookie(status) {
    var date = new Date();
    date.setTime(date.getTime() + (2 * 60 * 60 * 1000));
    $.cookie('timeReg', status, { expires: date, path: '/' });
}

function setPrevStatusCookie(prevStatus) {
    var date = new Date();
    date.setTime(date.getTime() + (2 * 60 * 60 * 1000));
    $.cookie('prevStatus', prevStatus, { expires: date, path: '/' });
}
/**
 * Gets latest notifications
 */
function getNotifications() {
    $.ajax({
        type: "GET",
        url: api_address + 'Notifications/action.Latest',
        success: function (data) {
            renderNotifications(data);
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}
/**
 * Render notifications
 *
 */
function renderNotifications(data) {

    //get the container for the notifications
    var noti_container = $('.headerNotifications');
    // get the badge for count
    var noti_count_badge = noti_container.find('.notifications-count-badge');
    noti_count_badge.empty();
    // get notifications count message
    var noti_count_message = noti_container.find('.notifications-count-message');
    noti_count_message.empty();
    // get the container for the notifications list
    var noti_list_container = noti_container.find('.notifications-list');
    noti_list_container.empty();
    var unread_notifications = 0;
    var notifications = data.value;
    var notifications_list_html = "";
    // we will use this to find notifications that should be marked as seen;
    var notificationsLists = [];
    notifications.forEach((function (noti) {
        var noti_active = '';
        var noti_id = '';

        if (noti.Read == null) {
            ++unread_notifications;
            noti_active = 'active';
        }
        noti_id = noti.Id;
        var noti_date = new Date(noti.Created);
        if (noti.IconHtml == null) {
            noti.IconHtml = "<i class='fa fa-exclamation'></i>"
        }
        var noti_css_class = (noti.Model == null) ? "notification-default" : "notification-" + noti.Model.toLowerCase();
        var notification = {
            Content: noti.Content,
            NotificationClasses: noti_css_class + ' ' + noti_active,
            NotiId: noti_id,
            NotiTime: noti_date.toDateTime(),
            // iconHTML: noti.IconHtml, todo when we implement icons for each model. For now default exclamation
            Title: noti.Title
        };

        notificationsLists.push(notification);
    }));

    if (unread_notifications > 0) {
        noti_count_badge.append(unread_notifications)
    }
    noti_count_message.append(Lang.get('messages.new-notifications', { count: unread_notifications }, 'en'));

    noti_list_container.loadTemplate(base_url + "/templates/Notifications/notificationsList.html", notificationsLists,
        {
            append: true,
        });
}

/**
 * mark all notifications as read
 * @param event
 */
function markNotificationsAsSeen(event) {
    //find all notification ids
    var ids = $('.notification').children('.active').toArray();

    ids.forEach((function (id) {
        var id1 = $(id).data('noti-id');
        $.ajax({
            type: "POST",
            url: api_address + 'Notifications(' + id1 + ')/action.Read',
            success: function (data) {
                $(id).removeClass('active');
            },
            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        });
    }));
    $('.notifications-count-badge').empty();
    $('.notifications-count-message').empty().append(Lang.get('messages.new-notifications', { count: 0 }));
    return false;
}


/**
 * Show notification in a modal
 *
 *
 */
function showNotification(event) {

    var modal = $('#defaultModal');
    var notification_container = $(event.target).closest('[data-noti-id]');
    var notification_id = notification_container.data('noti-id');

    $.ajax({
        type: "GET",
        url: api_address + 'Notifications(' + notification_id + ')',
        success: function (data) {
            //if the notification hasn't been seen, mark it as when its open
            if (notification_container.hasClass("active")) {
                setNotificationAsSeen(notification_id);
            }
            modal.find('.modal-title').empty().append(data.Title);
            modal.find('.modal-body').addClass('spinner');
            modal.find('.modal-body').loadTemplate(base_url + "/templates/Notifications/showNotification.html",
                {
                    link: linkToItem(data.Model, data.ModelId, true),
                    model: data.Model,
                    modelId: data.ModelId,
                    content: data.Content,
                    notification_id: data.Id
                },
                {
                    success: function () {
                        if (data.Model != 'TaskList') {
                            $.when(getCompanyName(data.Model, data.ModelId)).then((function (name) {
                                if (name) {
                                    $('.notificationName').append(' - ' + (name.value || name));
                                }
                            }));
                            modal.find('.modal-body').removeClass('spinner');
                        }
                        else {
                            $.get(api_address + 'TaskLists(' + data.ModelId + ')')
                                .success((function (obj) {
                                    $('.notificationName').text(' - ' + obj.Title);
                                    $('.notification-content').html(obj.Description + '<br>' + data.Content);
                                    modal.find('.modal-body').removeClass('spinner');
                                }))
                        }
                    }
                });

            modal.modal('show');
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}
/**
 *set singe notification as seen
 *
 * @param notificationId
 */
function setNotificationAsSeen(notificationId) {

    var noti = $(".notifications").find("[data-noti-id='" + notificationId + "']");

    $.ajax({
        type: "POST",
        url: api_address + 'Notifications(' + notificationId + ')/action.Read',
        success: function (data) {

            noti.removeClass('active');
            updateNotificationsCount();
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}

/**
 *set singe notification as NOT seen
 *
 * @param notificationId
 */
function setNotificationAsNotSeen(notificationId) {

    var noti = $(".notifications").find("[data-noti-id='" + notificationId + "']");

    $.ajax({
        type: "POST",
        url: api_address + 'Notifications(' + notificationId + ')/action.UnRead',
        success: function (data) {
            noti.addClass('active');
            updateNotificationsCount();
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}

/**
 * generates a link to an item
 *
 * todo hardcoded a bit until better way is found
 */
function linkToItem(model, modelId, justHref) {

    var modelAddress, action, ItemNumber;
    var models = {
        'Contract': 'contracts',
        "Lead": 'leads',
        "Invoice": "invoices",
        "Order": "orders",
        "Draft": "drafts",
        'Salary': 'salaries',
        'TaskList': 'tasks',
        "ClientAlias": 'clientAlias',
        'Client': "clients",
        "OrderField": 'order-fields',
        "User": "users"
    };

    //if model is not defined, just return empty string
    if (typeof model == "undefined") {
        return "";
    } else {
        if (typeof modelId == "undefined") {
            if (typeof justHref !== 'undefined') {
                return base_url + '/' + models[model];
            } else {
                return '<a class="btn btn-' + models[model] + '' + base_url + '" href="/">' + models[model] + '' + model + '</a>';
            }
        } else {
            if (typeof justHref !== 'undefined') {
                return base_url + '/' + models[model] + '/show/' + modelId;
            } else {
                return '<a class="btn btn-' + models[model] + '" href="' + base_url + '/' + models[model] + '/show/' + modelId + '">' + model + " : " + modelId + '</a>'
            }
        }
    }
}

/**
 * gets information about the requested item, such as company name, contract product, lead website
 * and returns a link to it
 *
 * @param model
 * @param modelId
 */
function infoLink(model, modelId) {

    return $.when($.get(api_address + 'Leads('))

}


/**
 * Updates the nofitications badge and message when some status change
 *
 *
 */
function updateNotificationsCount() {
    var ids = $('.notification').children('.active').toArray();

    if (ids.length > 0) {
        $('.notifications-count-badge').empty().append(ids.length);
    } else {
        $('.notifications-count-badge').empty()
    }
    $('.notifications-count-message').empty().append(Lang.get('messages.new-notifications', { count: ids.length }));
}

/**
 * get item files
 *
 */
function getItemFiles() {

    var model = $('#Model').val();
    var modelId = $('#ModelId').val();

    if (typeof model == 'undefined' || typeof modelId == 'undefined') {
        return false;
    }
    $.ajax({
        type: "GET",
        url: api_address + "FileStorages?$filter=Model eq '" + model + "' and ModelId eq " + modelId + "&$expand=Author($select=UserName,FullName)",
        success: function (data) {
            renderFiles(data);
        },

        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}

/**
 * renders files in the files table
 *
 * @param files
 */
function renderFiles(files) {
    // the request comes from oData
    files = files.value;
    //files table
    var tableBody = $('#item-files').find('tbody');
    var filesToAppend = [];
    files.forEach((function (file) {
        //files table
        var uploadDate = new Date(file.Created);
        filesToAppend.push({
            FileId: file.Id,
            Created: uploadDate.toDateTime(),
            PreviewLabel: Lang.get('labels.preview'),
            Name: file.Name,
            FullName: (file.Author == null) ? "" : file.Author.FullName,
            UserName: (file.Author == null) ? "" : file.Author.UserName,
            DownloadLabel: Lang.get('labels.download'),
            DeleteLabel: Lang.get('labels.delete')
        });
    }));

    tableBody.loadTemplate(base_url + '/templates/uploadedFile.html', filesToAppend, { prepend: true });
}

/**
 *
 *
 * @param event
 */
function downloadFile(event) {
    var fileId = $(event.target).closest('tr').data('file-id');
    window.open(base_url + "/files/download/" + fileId, 'myWindow', 'width=500,height=500,scrollbars=yes').focus();
    return false;
}

/**
 *Preview File
 */
function previewFile(event) {
    var fileId = $(event.target).closest('tr').data('file-id');
    window.open(base_url + "/files/preview/" + fileId, 'myWindow', 'width=500,height=500,scrollbars=yes').focus();
    return false;
}
//returns array of times, 15 minutes intervals
function allowedTimes() {
    //creates array of times for appointment
    var arr = [], i, j;
    for (i = 7; i < 18; i++) {
        for (j = 0; j < 4; j++) {
            arr.push(i + ":" + (j === 0 ? "00" : 15 * j));
        }
    }
    return arr;
}

function initializeAppointments() {

    openCalendarIFrame($('#user-UserName').val());
    //initialize user search in appointments
    // $("#userSearch_appointments").autocomplete(init);

    //Also initialize the calendar for Start-End date
    $('#appointment-Start').datetimepicker({
        timeFormat: 'HH:mm',
        dateFormat: "yy-mm-dd",
        minDate: new Date(),
        minTime: "7:00",
        maxTime: "18:00",
        allowTimes: allowedTimes()
    });

    //submitting an appointment
    $('#createAppointment').on('submit', (function (event) {
        var form = $(this);
        var btn = form.find(':submit');
        // btn.prop('disabled', true);
        event.preventDefault();
        //find the submit button and disable it

        var formData = $(this).serializeJSON();



        // find the attendees from the token input
        var attendees = form.find('.token:not(.invalid)');
        if (attendees.length > 0) {
            formData.Attendees = $.map(attendees, function (element) {
                return { EMail: $(element).data('value') }
            })
        }

        formData.NotifyAttendees = formData.NotifyAttendees ? true : false;
        var createOnCalendar = formData.CreateOnGoogleCalendar ? true : false;
        delete(formData.CreateOnGoogleCalendar);
        if (formData.User_Id == "") {
            new PNotify({
                title: Lang.get('labels.error'),
                text: Lang.get('labels.select-user'),
                type: 'error'
            });
            btn.prop('disabled', false);
            return false;
        } else {
            if (inRole('Meet Booking') && formData.User_Id == getUserId() && formData.EventType == 'HealthCheck' && inRoleNeutral('Meet Booking')) {
                new PNotify({
                    title: Lang.get('labels.error'),
                    text: "You can not book healthcheck for yourself.",
                    type: 'error'
                });
                btn.prop('disabled', false);
                return false;
            }
        }

        formData.Start = new Date(formData.Start);
        // get the Model and ModelId
        formData.Model = getModel();
        formData.ModelId = getModelId();

        formData.Source = { Url: linkToItem(formData.Model, formData.ModelId, true) };
        $.ajax({
            url: api_address + "CalendarEvents",
            type: "POST",
            data: JSON.stringify(formData),
            success: function (data) {
                if (createOnCalendar) {
                    if (formData.Model == "Lead") {
                        $.ajax({
                            url: api_address + "Leads(" + formData.ModelId + ")/Book",
                            type: "POST",
                            data: JSON.stringify({ Calendar_Id: data.Id }),
                            success: function (data) {
                                new PNotify({
                                    title: Lang.get('labels.success'),
                                    text: Lang.get('labels.appointment-created'),
                                    type: 'success'
                                });
                                //reset the form;
                                form[0].reset();
                                btn.prop('disabled', false);
                            },
                            error: function (error) {
                                btn.prop('disabled', false);
                            },
                            beforeSend: function (request) {
                                request.setRequestHeader("Content-Type", "application/json");
                            }
                        });
                    } else {
                        $.ajax({
                            url: api_address + "CalendarEvents(" + data.Id + ")/CreateOnGoogle",
                            type: "GET",
                            success: function (data) {
                                new PNotify({
                                    title: Lang.get('labels.success'),
                                    text: Lang.get('labels.appointment-created'),
                                    type: 'success'
                                });
                                //reset the form;
                                form[0].reset();
                                btn.prop('disabled', false);
                            },
                            error: function (error) {
                                btn.prop('disabled', false);
                            },
                            beforeSend: function (request) {
                                request.setRequestHeader("Content-Type", "application/json");
                            }
                        });
                    }
                } else {
                    if (formData.Model == "Lead") {
                        $.ajax({
                            url: api_address + "Leads(" + formData.ModelId + ")/Book",
                            type: "POST",
                            data: JSON.stringify({ Calendar_Id: data.Id }),
                            success: function (data) {
                                new PNotify({
                                    title: Lang.get('labels.success'),
                                    text: Lang.get('labels.appointment-created'),
                                    type: 'success'
                                });
                                //reset the form;
                                form[0].reset();
                                btn.prop('disabled', false);
                            },
                            error: function (error) {
                                btn.prop('disabled', false);
                            },
                            beforeSend: function (request) {
                                request.setRequestHeader("Content-Type", "application/json");
                            }
                        });
                    }
                    new PNotify({
                        title: Lang.get('labels.success'),
                        text: Lang.get('labels.appointment-created'),
                        type: 'success'
                    });
                    form[0].reset();
                    btn.prop('disabled', false);
                }
            },

            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        });
    }));
}

function updateAppointment(calendarEventId) {

    var modal = getDefaultModal();
    var body = modal.find('.modal-body');
    body.addClass('spinner');

    body.loadTemplate(base_url + '/templates/appointments/appointmentUpdate.html', { CalendarEvent_Id: calendarEventId },
        {
            success: function () {
                if (getModel() != "CalendarEvent") {
                    initializeAppointmentsTimeline(calendarEventId);
                } else {
                    if (!getModelId()) initializeAppointmentsTimeline(calendarEventId);
                }

                var updateAppAct = $('.updateAppointmentActivities');
                var moveApp = $('.moveAppointment');
                var appTime = $('#appointment-time');

                updateAppAct.on('change', (function (event) {
                    var target = $(event.target);
                    var checked = target.prop('checked');
                    var name = target.prop('name');
                    var comGroup = $('#commentGroup');

                    updateAppAct.prop('checked', false);
                    target.prop('checked', checked);

                    if (!checked && name != 'NoAnswer') {
                        moveApp.prop('disabled', false);
                    } else if (name != 'NoAnswer') {
                        moveApp.prop('disabled', true);
                        moveApp.prop('checked', false);
                        appTime.prop('disabled', true).val('');
                    } else {
                        moveApp.prop('disabled', false);
                    }

                    if (checked) {
                        comGroup.removeClass('hidden');
                    }
                    else {
                        comGroup.addClass('hidden');
                    }
                }));

                moveApp.on('change', (function (event) {
                    var target = $(event.target);
                    var checked = target.prop('checked');

                    if (checked) {
                        appTime.prop('disabled', false);

                        //get data for start time of the event
                        $.get(api_address + 'CalendarEvents(' + calendarEventId + ')')
                            .success((function (data) {
                                var today = moment();
                                var startTimeHour = moment(data.Start).hour();
                                var startTimeMinutes = moment(data.Start).minute();
                                var start = moment(today).hour(startTimeHour).minute(startTimeMinutes)

                                //if it's Friday, add three days to move the event to Monday
                                if (moment(today).day() == 5) {
                                    start = moment(start).add(3, 'days');
                                } else {
                                    start = moment(start).add(1, 'days');
                                }

                                var end = moment(start).add(30, 'minutes');

                                //initialize the date picker with predefined chosen dates
                                appTime.daterangepicker(
                                    {
                                        startDate: start,
                                        endDate: end,
                                        "parentEl": "#defaultModal",
                                        minDate: moment(),
                                        timePicker: true,
                                        "timePicker24Hour": true,
                                        locale: {
                                            format: 'YYYY-MM-DD H:mm'
                                        }
                                    }
                                );
                            }))
                    }
                    else {
                        appTime.prop('disabled', true).val('');
                    }
                }));

                appTime.val('');
                body.removeClass('spinner');
            }
        })
}
function initializeAppointmentsTimeline(calendarEventId) {
    var timelinePlaceholder = $('.timelineUpdateAppointment');
    $.get(api_address + "CalendarEvents(" + calendarEventId + ")/Activity?$expand=User($select=FullName),Comment($select=Id,Message)&$orderby=Created+desc")
        .success((function (activities) {
            var cancelled = false;
            var data = $.map(activities.value, (function (activity) {
                var message = '';
                var icon = '';
                var color = '';
                switch (activity.ActivityType) {
                    case "Cancel":
                        message = 'cancelled the appointment';
                        icon = 'fa-times';
                        color = 'activityIndianred';
                        cancelled = true;
                        break;
                    case "Completed":
                        message = 'completed the appointment';
                        icon = 'fa-check';
                        color = 'activityGreen';
                        cancelled = true;
                        break;
                    case "Move":
                        message = 'moved the appointment';
                        icon = 'fa-calendar-o';
                        color = 'activityDodgerblue';
                        break;
                    case "NoAnswer":
                        message = 'did not get an answer';
                        icon = 'fa-frown-o';
                        color = 'activityGrey';
                        break;
                    default:
                        break;
                }

                if (activity.Comment) {
                    var comment = '"' + activity.Comment.Message + '"';
                }
                else {
                    comment = '';
                }

                var aObject = {
                    Type: activity.ActivityType,
                    Icon: icon + ' ' + color,
                    Message: message,
                    Created: activity.Created,
                    User: activity.User.FullName,
                    Comment: comment
                };
                return aObject;
            }));
            if (cancelled) {
                $('#appointmentUpdateForm').find(':submit').prop('disabled', true).val('Appointment was cancelled or completed');
            }
            timelinePlaceholder.loadTemplate(base_url + '/templates/appointments/timeline.html', data,
                {
                    success: function () {
                        setTimeLineBorders();
                    }
                });
        }));
}
//define responsive timeline border height
function setTimeLineBorders() {
    var timelineItems = $('.timelineItem').toArray();
    $.each(timelineItems, (function (index, item) {
        var height = $(item).find('.col-xs-12').height() - 17;
        $(item).find('.borderDiv').css('height', height)
    }))
}
$(window).resize((function () {
    setTimeLineBorders();
}));

/**
 *sorts the timeline logs by the date they were created
 */
function sortTimeline() {
    $(".accordion-title").remove();
    $('.accordion-group .accordion-item').unwrap();
    $('.accordion-item .collapse').unwrap();
    $('.collapse .accordion-body').unwrap();
    $('.accordion-body .timeline-item').unwrap();

    var order = $('#timelineSortingChange').val();
    var up, down;
    if (order == "asc") {
        up = 1;
        down = -1;
    } else {
        up = -1;
        down = 1;
    }
    var $wrapper = $('.panel-timeline');
    $wrapper.find('.timelineItem').sort((function (a, b) {
        var date1 = new Date(a.dataset.date);
        var date2 = new Date(b.dataset.date);
        return date2 < date1 ? up : down;
    })).appendTo($wrapper);
    splitTimelineIntoMonths();
}

function getDefaultModal(width) {
    var modal = $('#defaultModal').modal();
    modal.find('.modal-title').empty();
    modal.find('.modal-body').empty();
    modal.find('.modal-footer').empty();

    if (width) {
        modal.find('.modal-content').css('width', width + '%');
    } else {
        modal.find('.modal-content').css('width', '');
    }

    return modal;
}

function closeDefaultModal() {
    $('#defaultModal').modal('hide');
}
/**
 * renders the badge with amount of task for the user
 */
function taskCountBadge() {
    var userId = $('#user-Id').val();
    var userQuery = "(AssignedTo_Id eq '" + userId + "' and ParentTaskListId eq null) or (ParentTaskListId ne null and AssignedTo_Id eq '" + userId + "' and Parent/AssignedTo_Id ne '" + userId + "' and Value eq false)";

    $.ajax({
        type: "GET",
        url: api_address + "TaskLists/$count?$filter=Value eq false and " + userQuery,
        success: function (data) {
            if (data > 0) {
                $('.taskCount').text(data);
            }
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}

/**
 * renders the badge with amount of task for the current viewing item
 */
function itemTaskCountBadge(model, modelId) {
    $.ajax({
        type: "GET",
        url: api_address + "TaskLists/$count?$filter=Value eq false and ModelId eq " + modelId + ' and Model eq \'' + model + "' and ParentTaskListId eq null",
        success: function (data) {
            if (data > 0) {
                $('.itemTaskCount').text(data);
            }
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}


/**
 * sets this information in the order create view
 * @param id
 * @param name
 * @param homepage
 */
function setAliasId(id, name, homepage) {
    document.getElementById("name").innerHTML = name;
    document.getElementById("ClientAlias_Id").value = id;
    homepage != "" ? document.getElementById("Domain").value = homepage : null;

    return false;
}
/**
 * unsets alias information in the roder create view
 */
function unsetAlias() {
    document.getElementById("name").innerHTML = "";
    document.getElementById("ClientAlias_Id").value = "";
    document.getElementById("searchInput").value = "";
}
/**
 * testing an form field grouping by naming convention "Model-Property" which eventually needs to return {Model:{ Property:value}}
 */
function groupFormFields(fields) {

    var obj = {};
    for (var field in fields) {
        // split the name into Model , Property
        var a = field.split('-');

        // add the model to the object or add the property to it
        //i will eventually get fucked for this code
        if (obj.hasOwnProperty(a[0])) {
            if (fields[field] != "" && typeof a[1] != "undefined") {
                obj[a[0]][a[1]] = fields[field]
            }
        } else {
            obj[a[0]] = {};
            if (fields[field] != "" && typeof a[1] != "undefined") {
                obj[a[0]][a[1]] = fields[field]
            }
        }
    }
    return obj;
}
function getModel() {
    return $('#Model').val();
}
function getModelId() {
    return $('#ModelId').val();
}
/**
 * clears error classes from form
 */
function clearErrors() {
    $('.has-error').removeClass('has-error');
}
function getUserId() {
    return $('#user-Id').val();
}

function getUserName() {
    return $('#user-UserName').val();
}
function getUserLocalNumber() {
    return $('#user-LocalNumber').val();
}
/**
 * returns an array of the current seller
 * period start and end dates
 */
function getSellerPeriod() {
}
/**
 * adds attendee for an appointment
 *
 * @param event
 */
function addEventAttendee(event) {
    event.preventDefault();

    var emailInput = $('#event-addAttendee');
    if (emailInput.val() == "" || !validateEmail(emailInput.val().trim())) {
        emailInput.closest('.form-group')
            .css('color', 'red')
            .animate({ color: 'black' }, 1000);
    } else {
        var email = emailInput.val();
        //check if attendee exists
        var attendeesRaw = $("input[name='attendees[email]']").map((function () {
            return $(this).val();
        })).get();

        if (isInArray(email, attendeesRaw)) {
            new PNotify({
                title: Lang.get('labels.error'),
                text: Lang.get('labels.already-attendee'),
                type: 'error'
            });
            return false;
        }
        $('.attendeesPlaceholder').loadTemplate(base_url + '/templates/calendar/calendarAttendee.html', { AttendeeEmail: email },
            {
                append: true,
                success: function () {
                    $("#event-addAttendee").val('');
                }
            });
    }
}

function checkAdWordsLink(adwordsId) {
    return $.post(base_url + '/app/check-adwords-link', { adwordsId: adwordsId.trim() });
}

function cancelInvitation(adwordsId) {
    return $.post(base_url + '/app/cancel-invitation', { adwordsId: adwordsId.trim() });
}

function sendInvitation(data) {
    return $.post(base_url + '/app/send-invitation', data);
}

function changeAdwordsLinkStatus(status) {
    var adwordsPlaceholder = $('.adwordsIdOptions');
    switch (status) {
        case "pending":
            adwordsPlaceholder.loadTemplate(base_url + '/templates/awords/pendingInvitation.html',
                {
                    PendingTitle: Lang.get('messages.pending-invitation'),
                    CancelInvitationTitle: Lang.get('messages.cancel-invitation')
                }
            );
            break;
        case 'not-linked':
            adwordsPlaceholder.loadTemplate(base_url + '/templates/awords/notLinked.html',
                {
                    SendInvitationTitle: Lang.get('messages.send-invitation'),
                    NotLinkedTitle: Lang.get('messages.not-linked')
                }
            );
            break;
        case 'linked':
            adwordsPlaceholder.loadTemplate(base_url + '/templates/awords/linked.html',
                {
                    LinkedTitle: Lang.get('messages.account-is-linked')
                }
            );
            break;
        default:
            break;
    }
}

/**
 * checks if there is existing startup meeting for the contract
 *
 */
function findStartupMeeting() {
    $.get(api_address + 'CalendarEvents?$filter=Model eq \'Contract\' and ModelId eq ' + getModelId() + ' and EventType eq \'StartUpMeeting\' and Start gt ' + getIsoDate(moment().startOf('day')) + '&$expand=User($select=FullName,Id)')
        .success((function (data) {
            var startupMeetingPlaceholder = $('.startupMeeting');
            // if we don't have events matching the description, give option to create one
            if (data.value.length == 0) {
                startupMeetingPlaceholder.prepend("<button class='makeStartupMeeting'>" + Lang.get('labels.schedule-startup-meeting') + "</button>");
                openCalendarIFrame(getUserName(), startupMeetingPlaceholder.find('.startupMeetingCalendar'));
            } else {
                // show the event information
                startupMeetingPlaceholder.loadTemplate(base_url + '/templates/calendar/eventsTable.html', {},
                    {
                        success: function () {

                            var tbody = $('.eventsTableBody');
                            tbody.loadTemplate(base_url + "/templates/calendar/eventTableRow.html", {
                                EventSummary: data.value[0].Summary,
                                EventHtmlLink: data.value[0].HtmlLink,
                                EventStart: data.value[0].Start,
                                EventDescription: data.value[0].Description,
                                EventCreator: data.value[0].User.FullName
                            })
                        }
                    })
            }
        }))
}

/**
 * returns appropriate label, depending on the given book
 * @param bool
 * @param params
 */
function trueOrFalseIcon(bool, params) {
    var icon = document.createElement('i');
    icon.classList = 'fa fa-' + (bool ? 'check' : "times");
    if (params) {
        if (params.classes) {
            icon.classList.add(params.classes);
        }
        icon.title = (params.title ? params.title : "");
    }
    return icon.outerHTML;
}

/**
 * returns a company name based on a model and model if provided
 */
function getCompanyName(Model, ModelId) {
    return $.ajax({
        type: "POST",
        suppressErrors: true,
        url: api_address + 'ClientAlias/GetCompanyName',
        data: JSON.stringify({ Model: Model, ModelId: ModelId }),
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}
/**
 * finds the next appointment start date for model, modelid
 */
function findNextAppointment(Model, ModelId) {
    // $.post(api_address+'')
}
function initializeTimeVault(model, modelId) {
    $.when(getTimeVault(model, modelId))
        .then((function (data) {
            var placeholder = $('.counselingTime');
            if (model == 'ClientAlias') {
                if (data.Id == 0) {
                    placeholder.removeClass('spinner');
                    placeholder.text('No counseling  time')
                } else {
                    placeholder.removeClass('spinner');
                    placeholder.html(minutesToStr(data.Balance) + ' <i class="fa fa-plus LogTime pseudolink" data-vault-id="' + data.Id + '" title="Log time or see transactions"></i>');
                }
            } else {
                placeholder.html(minutesToStr(data.Balance) + ' <i class="fa fa-plus LogTime pseudolink" data-vault-id="' + data.Id + '" title="Log time or see transactions"></i>');
                placeholder.removeClass('spinner')
            }
        }))
}

function initContacts() {
    var contactsTabLink = $('.loadContactsTab');
    var formPlaceholder = $('#contactFormPlaceholder');
    if (getModel() == "ClientAlias") {
        var id = getModelId();
    } else {
        id = $('#ContactsClientAliasId').val();
    }
    formPlaceholder.loadTemplate(base_url + '/templates/contacts/contactForm.html', { ClientAlias_Id: id }, {
        success: function () {
            $('input.contact-birthdate:text').datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                yearRange: "-100:+0"
            });
        }
    }); // this page should be only at the alias show view. if not, change this to find the alias Id
    var caller = canCall();
    contactsTable = $('#contacts-table-tab').DataTable({
        "bPaginate": false,
        'bInfo': false,
        'bFilter': false,
        "iDisplayLength": "All",
        aaSorting: [[7, "desc"]], // shows the newest items first
        "bProcessing": true,
        "bServerSide": true,
        'filter': 'Email ne null',
        select: "Id,Facebook,LinkedIn,ReceiveReports",
        "sAjaxSource": api_address + "ClientAlias(" + id + ")/Contact",
        "aoColumns": [
            { mData: "Name" },
            {
                mData: "Phone", mRender: function (phone) {
                    return createCallingLink(caller, phone);

                }
            },
            {
                mData: "Email", mRender: function (email) {
                    if (email !== null) {
                        return "<a href='mailto:" + email + "'>" + email + "</a>"
                    } else {
                        return "";
                    }
                }
            },
            { mData: "JobFunction" },
            { mData: 'Department' },
            {
                mData: "Birthdate", sType: "date", mRender: function (bday) {
                    if (bday != null) {
                        return toDate(bday);
                    } else {
                        return "";
                    }
                }
            },
            { mData: 'Description', sClass: "multiline" },
            {
                mData: "MainContact", mRender: function (main, unused, obj) {
                    var links = '';

                    if (obj.Facebook) {
                        links += "<a target='_blank' href='" + obj.Facebook + "'><i class='fa fa-facebook'></i></a>&nbsp; ";
                    }

                    if (obj.LinkedIn) {
                        links += "<a target='_blank' href='" + obj.LinkedIn + "'><i class='fa fa-linkedin'></i></a>&nbsp; ";
                    }

                    if (obj.MainContact) {
                        links += "<i class='fa fa-flag-o' title='This is the main contact'></i> &nbsp; "
                    }

                    if (obj.ReceiveReports) {
                        links += "<i class='fa fa-file' title='This is the contact for reporting'></i>&nbsp; "

                    }
                    links +=
                    '<a href="' + base_url + '/client-contacts/edit/' + obj.Id + '"><i class="fa fa-edit"></i></a> ' + // edit
                    '<a class="deleteContactBtn" data-contact-id="' + obj.Id + '" href="#"><i class="fa fa-times"></i></a>';// delete;
                    return links
                }
            }
        ],
        "fnServerData": fnServerOData,
        "iODataVersion": 4,
        "bUseODataViaJSONP": false
    });

    contactsTabLink.removeClass('loadContactsTab')
}

function getTimeVault(model, modelId) {
    return $.ajax({
        url: api_address + "TimeVaults/ForItem",
        type: "POST",
        data: JSON.stringify({ Model: model, Item: modelId }),
        success: function (d) {
            return d;
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}
