/*
We are loading the latest version of JQuery from Google.
Standard Wordpress way:

Add this into header_action() in stocktwits.php file:
    wp_enqueue_script('stocktwits',  '/' . PLUGINDIR . '/stocktwits/stocktwits.js', array('jquery'));

And here - use this:

    jQuery(document).ready (function($){
        alert ('Jquery is loaded and "$" is ready to be used!!!');
        });
*/

var StockTwits = {};

//===========================================================================
StockTwits.LoadWidget = function ()
{
    var url      = StockTwits.json_proxy_dir_url + '/stocktwits-json.php';

    jQuery.getJSON (url + '?username=' + StockTwits.widget_settings.username + '&jsoncallback=?',  function(data)
        {
        StockTwits.Display (data);
        });

    window.setTimeout("StockTwits.LoadWidget()", StockTwits.widget_settings.auto_refresh_in_seconds * 1000);  // In ms
}
//===========================================================================

//===========================================================================
/*
    StockTwits.widget_settings =
        {
        title:                      "StockTwits Widget",
        username:                   "StockTwits",
        number_of_twits:            10,
        auto_refresh_in_seconds:    15
        };
*/
StockTwits.Display = function (json)
{
StockTwits.inner_html =
    '<div id="moduleHeader" class="moduleHeader">'                            +
        '<div id="moduleIcon" class="ico">'                                   +
            '<img height="16" width="16" src="http://' + location.hostname + '/wp-content/plugins/stocktwits/stocktwits-favicon.gif"/>' +
         '</div>'                                                             +
         '<div class="title" id="moduleTitle"> My StockTwits Updates</div>'   +
    '</div>'                                                                  +
    '<div id="moduleContent" class="moduleContent"></div>'                    +
    '<div align="center" id="moduleFooter" class="moduleFooter"></div>' +
    '';

    // Set widget title.
    $("#StockTwits_wrapper").html(StockTwits.inner_html);
    $("#StockTwits_wrapper #moduleFooter").html('powered by <a href="http://www.stocktwits.com/">StockTwits.com</a>');

    var date = new Date();
//  $("#StockTwits_wrapper #moduleHeader #moduleTitle").html(StockTwits.widget_settings.title + "&nbsp;&nbsp;(" + date.toString().match (/\d\d\:\d\d\:\d\d/) + ")");
    $("#StockTwits_wrapper #moduleHeader #moduleTitle").html(StockTwits.widget_settings.title);

    var count = 0;
    var limit = Math.min (StockTwits.widget_settings.number_of_twits, json['stream']['tweets'].length);

    var widget_content = $("#StockTwits_wrapper #moduleContent").html("");

    for (var i=0; i<limit; i++)
        {
        var twit = json['stream']['tweets'][i];
        var when = StockTwits.date (twit.tweet_datetime);
        var text = StockTwits.tweet_text_filter (twit.tweet_text);
        var p = $("<p/>");
        p.attr('class', 'status ' + ((i % 2 == 0) ? 'odd' : 'even'));

        p.html(
            '<a href="http://stocktwits.com/u/' + twit.twitter_username + '"><img width="32" height="32" src="' + twit.avatar_url + '" /></a>'  +
            '<a href="http://stocktwits.com/u/' + twit.twitter_username + '" class="user">' + twit.twitter_username + '</a> ' +
            when.tweet_time() +
            '<a href="http://stocktwits.com/u/' + twit.twitter_username + '/' + twit.status_id + '" class="user">#</a><br />' +
            text
            );

        widget_content.append(p);
        }
}
//===========================================================================

//===========================================================================
StockTwits.tweet_text_filter = function (text)
{
    var link = /((http|https):\/\/[\w?=&.\/-;#~%-]+(?![\w\s?&.\/;#~%"=-]*>))/g;
    var reply = /@([\w-]+)/g;
    var ticker = /\$((?:[0-9]+(?=[a-z])|(?![0-9\.\:\_\-]))(?:[a-z0-9]|[\_\.\-\:](?![\.\_\.\-\:]))*[a-z0-9]+)/ig;
    var financetalk = /(\s|^)\$\$(\s|$)/g;

    return text.replace(link, '<a href="$1" class="link" rel="nofollow" target="_blank">$1</a> ').
        replace (reply, '<a href="http://stocktwits.com/u/$1" class="reply">@$1</a>').
        replace (ticker, '<a href="http://stocktwits.com/t/$1" class="ticker"><span>$$</span>$1</a>').
        replace (financetalk, ' <span class="financetalk">$$$$</span> ');
}
//===========================================================================

//===========================================================================
StockTwits.date = function(date)
{
        var dateArray = date.split(' ');
        var date = new Date(dateArray[0] + ', ' + dateArray[2] + ' ' + dateArray[1] + ' ' + dateArray[3] + ' ' + dateArray[5].substring(0,4));
        date.setTime(date.getTime() - date.getTimezoneOffset()*60*1000);

        return date;
};
//===========================================================================

//===========================================================================
Date.prototype.tweet_time = function()
{
    var hour = (this.getHours() + 12) % 12;
    if (hour == 0) { hour = 12; }

    return ['January','February','March','April','May','June', 'July','August','September','October','November','December'][this.getMonth()] + " " +
    this.getDate() + ", " + hour + ":" + ((this.getMinutes() > 9) ? this.getMinutes() : "0" + this.getMinutes()) + " " + ((this.getHours() < 12) ? 'am' : 'pm');
};
//===========================================================================
