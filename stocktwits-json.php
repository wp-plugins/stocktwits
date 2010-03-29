<?php

    if (isset($_GET['username']) && isset($_GET['jsoncallback']))
        {
        if ($_GET['username'] == 'all')
            $url = "http://api.stocktwits.com/api/streams/{$_GET['username']}.json";
        else
            {
            if ($_GET['username'][0] == '!' || $_GET['username'][0] == '$')
               {
               $ticker = substr ($_GET['username'], 1);
               $url = "http://api.stocktwits.com/api/streams/symbol/{$ticker}.json";
               }
            else
               $url = "http://api.stocktwits.com/api/streams/user/{$_GET['username']}.json";
            }
        $data = @file_get_contents ($url);
        $data = $_GET['jsoncallback'] . "(" . $data . ")";
        }
    else
        {
        $data = "callback1({twits:'json error: username or jsoncallback params are missing. Input request: {$_SERVER['REQUEST_URI']}'})";
        }

    header ('Content-type: text/javascript; charset: UTF-8');
    echo $data;

?>