<?php

    if (isset($_GET['username']) && isset($_GET['jsoncallback']))
        {
        if ($_GET['username'] == 'all')
            $url = "http://stocktwits.com/streams/{$_GET['username']}.json";
        else
            {
            if ($_GET['username'][0] == '!' || $_GET['username'][0] == '$')
               {
               $ticker = substr ($_GET['username'], 1);
               $url = "http://stocktwits.com/t/{$ticker}.json";
               }
            else
               $url = "http://stocktwits.com/u/{$_GET['username']}.json";
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



//===========================================================================
// log_event (__FILE__, __LINE__, "Message", "extra data");

function log_event ($filename, $linenum, $message, $extra_text="")
{
   $log_filename   = dirname(__FILE__) . '/__log.php';
   $logfile_header = '<?php header("Location: /"); exit();' . "\r\n" . '/* =============== LOG file =============== */' . "\r\n";
   $logfile_tail   = "\r\n?>";

   // Delete too long logfiles.
   if (@file_exists ($log_filename) && @filesize($log_filename)>1000000)
      unlink ($log_filename);

   $filename = basename ($filename);

   if (file_exists ($log_filename))
      {
      // 'r+' non destructive R/W mode.
      $fhandle = fopen ($log_filename, 'r+');
      if ($fhandle)
         fseek ($fhandle, -strlen($logfile_tail), SEEK_END);
      }
   else
      {
      $fhandle = fopen ($log_filename, 'w');
      if ($fhandle)
         fwrite ($fhandle, $logfile_header);
      }

   if ($fhandle)
      {
      fwrite ($fhandle, "\r\n// " . $_SERVER['REMOTE_ADDR'] . ' -> ' . date("Y-m-d, G:i:s.u") . "|$filename($linenum)|: " . $message . ($extra_text?"\r\n//    Extra Data: $extra_text":"") . $logfile_tail);
      fclose ($fhandle);
      }
}
//===========================================================================


?>