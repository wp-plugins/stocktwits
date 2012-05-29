<?php
/*
Plugin Name: StockTwits
Plugin URI: http://www.stocktwits.com/
Version: 1.7
Author: StockTwits
Author URI: http://stocktwits.com/
Description: Plugin shows the most recent posts from www.StockTwits.com website as a widget. Choose to show your stock messages, just the Ticker stream or the All stream.
*/

define ('DEFAULT_WIDGET_TITLE',           'StockTwits Updates');
define ('DEFAULT_SYMBOL',                 'AAPL');
define ('DEFAULT_USERNAME',               '');
define ('DEFAULT_LIMIT',                  '15');

define('WIDGET_HTML_TEMPLATE', <<<WIDGET_HTML
      <!-- StockTwits.com WIDGET CODE START -->
      <div id="stocktwits-widget-news"></div>
      <script language="javascript">
        STWT.Widget({
              container: 'stocktwits-widget-news',  
              symbol: '__SYMBOL__',
              user: '__USERNAME__',
              limit: '__LIMIT__',
              width: '275',
              height: '500', 
              header: '0',
              style: { link_color: '1111cc; text-decoration:underline', link_hover_color: '1111cc; text-decoration:none'
                }});
      </script>
      <!-- StockTwits.com WIDGET CODE END -->
WIDGET_HTML
       );

//===========================================================================
//
//
class StockTwits
{
   private  $stocktwits_op_name = 'stocktwits_options';
   private  $stocktwits_options;

   //------------------------------------------
   public function __construct ()
      {
      $this->LoadOptions();
      }
   //------------------------------------------

   //------------------------------------------
   public function ContentFilter ($content)
      {
      if (is_feed())
         return ($this->ContentFilter_stub ($content));

      if (!preg_match ('|\[stocktwits([^\]]*)\]|i', $content, $matches))
       return $content;

      $widget_title     = 0;
      $username         = 0;
      $limit            = 0;

      if (isset($matches[1]))
         {
         $params = explode (',', $matches[1]);
         if (isset($params[0]))
            $widget_title = trim($params[0]);

         if (isset($params[1]))
            {
            $username = trim($params[1]);
            if ($username[0] == '$')
               $username[0] = '!'; // Replace $ with ! in ticker symbol to avoid conflicts with ticker_links plugin that replaces everything with $ABCD onto links.
            }

         if (isset($params[2]))
            $limit = trim($params[2]);

         }
      $widget_html = $this->GetWidgetHTML ($widget_title, $username, $limit);

      $content = preg_replace ('|\[stocktwits([^\]]*)\]|i', $widget_html, $content);

      return ($content);
      }
   //------------------------------------------

   //------------------------------------------
   // Stub just suppresses [stocktwits] tags. Useful for article excerpts of RSS feeds.
   public function ContentFilter_stub ($content)
      {
      $content = preg_replace ('|\[stocktwits([^\]]*)\]|i', '', $content);
      return ($content);
      }
   //------------------------------------------
   public function PrintAdminPage ()
      {
      $stocktwits_options = $this->LoadOptions();
      if (isset($_POST['stocktwits_update_settings']))
         {
         $stocktwits_options['widget_title']             = apply_filters  ('content_save_pre', $_POST ['widget_title']);
         $stocktwits_options['username']                 = apply_filters  ('content_save_pre', $_POST ['username']);
         $stocktwits_options['limit']                    = apply_filters  ('content_save_pre', $_POST ['limit']);

         update_option($this->stocktwits_op_name, $stocktwits_options);
         $this->stocktwits_options = $stocktwits_options;   // Reinitialize member var with fresh options.
?>
<div class="updated"><p><strong><?php _e("Settings Updated.", "StockTwitsPlugin");?></strong></p></div>
<?php    }      ?>

<div class=wrap>
  <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <h1>StockTwits</h1>

    <h2>Using StockTwits widget</h2>
    <p>There are 3 simple ways to add the StockTwits widget to your Wordpress site. All options are customizable but you can <strong style="color: #720F12">only use 1 type of StockTwits widget on your site</strong>.</p>
    <h3>Simple Tag for Posts</h3>
    <p>The StockTwits widget can be inserted directly inside of any post and page by adding the simple tag [stocktwits] into the content body. Here are some other options you can use to customize the default:</p>

    <span style="line-height:1.55em;">&nbsp;&nbsp;<span style="background:#FFB;">[stocktwits]</span>  - uses all default settings (from this admin panel)</span><br />
    <span style="line-height:1.55em;">&nbsp;&nbsp;<span style="background:#FFB;">[stocktwits Stock Twits]</span>  - uses custom title <u>Stock Twits</u> and other settings at default</span><br />
    <span style="line-height:1.55em;">&nbsp;&nbsp;<span style="background:#FFB;">[stocktwits Intel Pulse,$INTC]</span>  - Set title to <u>Intel Pulse</u> and only shows twits that mention Intel Corporation</span><br />
    <span style="line-height:1.55em;">&nbsp;&nbsp;<span style="background:#FFB;">[stocktwits ,$AAPL]</span>  - Use default title and only show twits that mention Apple Corporation</span><br />
    <span style="line-height:1.55em;">&nbsp;&nbsp;<span style="background:#FFB;">[stocktwits Intel Pulse,$INTC,5]</span>  - shows only 5 most recent twits</span><br />
    <span style="line-height:1.55em;">&nbsp;&nbsp;<span style="background:#FFB;">[stocktwits Intel Pulse,$INTC,5,60]</span>  - refreshes once a minute (every 60 seconds)</span><br />
    <span style="line-height:1.55em;">&nbsp;&nbsp;<span style="background:#FFB;">[stocktwits Market Watch,all]</span>  - shows twits from all StockTwits users</span><br />
    <span style="line-height:1.55em;">&nbsp;&nbsp;<span style="background:#FFB;">[stocktwits ,,,5]</span>  - shows widget with all defaults but set to update rate every 5 seconds</span><br />

    <h3>Embed in a Theme</h3>
    <p>The StockTwits widget can be embedded directly into the theme files via any of the template function calls below.</p>

    <span style="line-height:1.55em;">&nbsp;&nbsp;<span style="background:#FFB;">&lt;?php stocktwits_widget(); ?&gt;</span> - all default settings</span><br />
    <span style="line-height:1.55em;">&nbsp;&nbsp;<span style="background:#FFB;">&lt;?php stocktwits_widget (&quot;&quot;, &quot;&quot;, 0, 10); ?&gt;</span> - use defaults for title, username/ticker and twits number, but set custom refresh</span><br />
    <span style="line-height:1.55em;">&nbsp;&nbsp;<span style="background:#FFB;">&lt;?php stocktwits_widget (&quot;Intel twits&quot;, &quot;$intc&quot;, 5, 60); ?&gt;</span> - use all custom settings</span><br />

    <h3>Sidebar Widget</h3>
    <p>Easily embed the StockTwits widget into your theme&apos;s sidebars via <u>Appearance-&gt;Widgets</u> administrative menu. Your theme must be widget-ready. Just add the widget to your sidebar.</p>

    <h2>StockTwits Widget Settings</h2>

   <table class="niceblue form-table">
      <tbody>
         <tr>
            <th width="245" scope="col">Widget Title:</th>
            <td width="603"><input type="text" name="widget_title" value="<?php _e(apply_filters('format_to_edit',$this->stocktwits_options['widget_title']), 'StockTwitsPlugin') ?>" /></td>
         </tr>
         <tr>
            <th scope="col">Enter a Username or $TICKER:</th>
            <td><input type="text" name="username" value="<?php _e(apply_filters('format_to_edit',$this->stocktwits_options['username']), 'StockTwitsPlugin') ?>" />
            <span class="setting-description"><u>username</u> - for all user messages, <u>$ticker</u> - for all ticker messages</span></td>
         </tr>
         <tr>
            <th scope="col">Number of messages to display:</th>
            <td><input type="text" style="width: 30" name="limit" value="<?php _e(apply_filters('format_to_edit',$this->stocktwits_options['limit']), 'StockTwitsPlugin') ?>" />
            <span class="setting-description">Number of messages to be displayed inside the widget. Use values from: 1 to 30</span></td>
         </tr>
      </tbody>
   </table>
   <div class="submit">
      <input type="submit" name="stocktwits_update_settings" value="<?php _e('Update Settings', 'StockTwitsPlugin') ?>" />
   </div>

   </form>
</div>

<?php
      }
   //------------------------------------------

   //------------------------------------------
   // Loads an array of admin options
   public function LoadOptions()
      {
      // Note: 'premium_content_warning' will be stored in encoded format to avoid HTML chars issues.

      // Set defaults.
      $default_options =
         array (
            'widget_title'             => DEFAULT_WIDGET_TITLE,
            'username'                 => DEFAULT_USERNAME,         
            'limit'                    => DEFAULT_LIMIT
            );

      if (is_array($saved_options = get_option ($this->stocktwits_op_name)))
         $this->stocktwits_options = $saved_options;
      else
         {
         add_option ($this->stocktwits_op_name, $default_options);
         $this->stocktwits_options = $default_options;
         }
      }
   //------------------------------------------

   //------------------------------------------
   public function GetOptions ()
      {
      return ($this->stocktwits_options);
      }
   //------------------------------------------


   //------------------------------------------
   public function GetWidgetHTML ($widget_title=0, $username=0, $limit=0)
      {
      $widget_html = WIDGET_HTML_TEMPLATE;

      $widget_html = preg_replace ('|__TITLE__|',          $widget_title?$widget_title:$this->stocktwits_options['widget_title'], $widget_html); 

      if ($this->stocktwits_options['username'][0] == '$') {
        $username = substr(trim($this->stocktwits_options['username']), 1);
        $widget_html = preg_replace ('|__SYMBOL__|',       $username, $widget_html);
        $widget_html = preg_replace ('|__USERNAME__|',     '', $widget_html);
      }
      else {
        $widget_html = preg_replace ('|__USERNAME__|',     $username?$username:$this->stocktwits_options['username'], $widget_html);
        $widget_html = preg_replace ('|__SYMBOL__|',       '', $widget_html);
      }

      $widget_html = preg_replace ('|__LIMIT__|',          $limit?$limit:$this->stocktwits_options['limit'], $widget_html);

      return ($widget_html);
      }
   //------------------------------------------

}
//===========================================================================


//= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
// Instantiate plugin object.

global $g_StockTwits_Plugin;
$g_StockTwits_Plugin = new StockTwits();

//Initialize the admin panel
add_action ('admin_menu',        'StockTwits_AdminPanel');
add_action ('init',              'init_action' );
add_action ('plugins_loaded',    'RegisterStockTwitsWidget');

add_filter ('the_content',       array(&$g_StockTwits_Plugin, 'ContentFilter'), 9);
add_filter ('the_content_limit', array(&$g_StockTwits_Plugin, 'ContentFilter'), 9);
add_filter ('the_excerpt',       array(&$g_StockTwits_Plugin, 'ContentFilter_stub'), 8);
add_filter ('the_excerpt_rss',   array(&$g_StockTwits_Plugin, 'ContentFilter_stub'), 8);
add_filter ('the_content_rss',   array(&$g_StockTwits_Plugin, 'ContentFilter_stub'), 8);
//= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =


//===========================================================================
function RegisterStockTwitsWidget()
{
   register_sidebar_widget(__('StockTwits Widget'), 'StockTwitsWidget');
}

// Actual widget HTML emitting function.
function StockTwitsWidget ($args)
{
   global $g_StockTwits_Plugin;

   $widget_title = $g_StockTwits_Plugin->GetOptions();
   $widget_title = $widget_title['widget_title'];

   extract($args);
   echo $before_widget;
   echo $before_title;
   echo $widget_title;
   echo $after_title;
   echo $g_StockTwits_Plugin->GetWidgetHTML ();
   echo $after_widget;
}
//===========================================================================

//===========================================================================
// Template function to display StockTwits.com widget
// Could be embedded into index.php, single.php, etc... as:
// Syntax:
//    stocktwits_widget();
//    stocktwits_widget("Intel Inside", "$INTC");
//    stocktwits_widget("Intel Inside", "$INTC", 20, 10);

function stocktwits_widget ($widget_title=0, $username=0, $limit=0)
{
   global $g_StockTwits_Plugin;
   echo $g_StockTwits_Plugin->GetWidgetHTML ($widget_title, $username, $limit);
}
//===========================================================================

//===========================================================================
function StockTwits_AdminPanel ()
{
   global $g_StockTwits_Plugin;
   add_options_page('StockTwits', 'StockTwits Plugin', 9, basename(__FILE__), array(&$g_StockTwits_Plugin, 'PrintAdminPage'));
}
//===========================================================================

//===========================================================================
function init_action ()
{
   wp_enqueue_script('st-widget', 'http://stocktwits.com/addon/widget/2/widget-loader.min.js');
}
//===========================================================================

//===========================================================================
function enforce_values ($input, $min, $max)
{
   if ($input < $min)
      return $min;
   if ($input > $max)
      return $max;
   return $input;
}
//===========================================================================

//===========================================================================
//
// Returns no-slashed WEB URL of directory where this file is.

function get_base_dir_url ()
{
   $base_dir_url = get_bloginfo ('wpurl') . preg_replace ('#^.*[/\\\\](.*?)[/\\\\].*?$#', "/wp-content/plugins/$1", __FILE__);
   return ($base_dir_url);
}
//===========================================================================

//===========================================================================
// log_event (__FILE__, __LINE__, "Message", "extra data");

if (!function_exists('log_event'))
   {
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
}
//===========================================================================

?>