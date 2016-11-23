/**
 * Created by Dimitar on 4/12/2016.
 */
jQuery(document).ready(function () {

    // object of element ids, referencing to specific part of the page
    var targets ={
        298:"#work", // ourwork - en
        233:"#work", // ourwork - bg
        207:'#contacts', // en
        206:'#lab', //en
        204:'#services', // en
        203:"#team", //en
        202:'#about', // en
        188:'#about',//bg
        141:'#lab',//bg
        137:'#contacts', //bg
        66:'#services', //bg
        54:'#team'//
    };
    // positions of each element we find
    var positions = {};
    // add event listeners to all elements from the menu - used for the scrolling
    for(var prop in targets){
        var found = jQuery('div[id^=text-'+prop).toArray();
        if(found.length > 0){
         jQuery(found[0]).addClass('not-seen');
          positions[jQuery(found[0]).offset().top + jQuery(found[0]).height()]=0;
        }
    }

    // add  event listener to the scroll, that checks if element is visible
    jQuery(window).on('scroll', function () {
        var docViewTop = jQuery(window).scrollTop();
        var docViewBottom = docViewTop + jQuery(window).height();
            console.log('window- '+docViewBottom);
  
    });


    //add already sent pages to this object, so we don't spam analytics
    var sentAlready = {};

    // If they click on a menu item, count it in analytics
    jQuery('body').on('click', '.menu-item-object-custom', function (event) {
        var target = jQuery(event.target);
        var href = target.attr('href');
        if (href != "" && !sentAlready.hasOwnProperty(href)) {
            sendPageview(href);
            //ga('send', 'pageview', {'page': location.pathname + location.search  +location.hash});

        }
    });

    // if they scroll to an element, send a pageview for it, but remove the event listener, so we it's sent only once
    function sendPageview(href){
        // add the viewed page to the object, so we dont' send it again
        sentAlready[href] = 1;
        //ga('send', 'pageview', {'page': location.pathname + location.search  + href});

    }
});
