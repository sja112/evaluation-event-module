jQuery("#event-subscribe-button .event-subscribe-link").once().on('click', function () {
    var nid = jQuery('.event-subscribe-link').attr('data-event-nodeId');
    var flag = jQuery('.event-subscribe-link').attr('data-event-flag');
    var serviceUrl = '/event-subscribe/' + nid + '?flag=' + flag;
    jQuery.ajax({
        method: "POST",
        url: serviceUrl,
        success: function (data) {
            jQuery('#event-subscribe-button .event-subscribe-link').text(data[0].data).css('background','#cccccc');
            jQuery('#event-subscribe-button .event-subscribe-link').css('color','#ffffff');
        },
    });
});
