<?php
/*
Plugin Name: Wordpress Font Uploader Free
Plugin URI: http://pippinspages.com/plugins/font-uploader
Description: A custom font upload plugin for Wordpress allowing you to use any font anywhere you wish.
Version: 1.2.2
Author: Pippin Williamson
Author URI: http://pippinspages.com
*/

      
$baseFontDir = WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), "" ,plugin_basename(__FILE__));
$fontDirectoryPath = WP_PLUGIN_DIR . '/font-uploader/fonts';
$fontURL = $baseFontDir . 'fonts';
$fontDir = opendir($fontDirectoryPath);
$fontExtensions = 'oft' && 'ttf';
$fontList = array();
while(($font = readdir($fontDir)) !== false)
	{
		if($font != '.' && $font != '..' && !is_file($font) && $font != '.htaccess' && !is_dir($font) && $font != 'resource.frk' && !eregi('^Icon',$font))
			{
				// $fontURL."/".
				$fontList[$font] = $font;
			}
	}
closedir($fontDir);
array_unshift($fontList, "Choose a font");

$fontUploaderName = 'Font Uploader';
$sn = fu;
$fontOptions = array (

    array( "name" => $fontUploaderName." Options",
        "type" => "title"),
   
    array( "name" => "Fonts",
        "type" => "section"),
    array( "type" => "open"),

	 array( "name" => "Headers",
		"desc" => "Font for header elements, such as h1, h2.",
		"id" => $sn."_header_font",
		"class" => "fu_font_list",
		"type" => "select",
		"options" => $fontList),
	 array( "name" => "Lists",
		"desc" => "Font for list items",
		"id" => $sn."_lists_font",
		"class" => "fu_font_list",
		"type" => "select",
		"options" => $fontList),
	 array( "name" => "Main Body",
		"desc" => "Font for the main body text of the website",
		"id" => $sn."_body_font",
		"class" => "fu_font_list",
		"type" => "select",
		"options" => $fontList),

		   
    array( "type" => "close"),
    
    array( "name" => "Advanced - Custom Elements",
        "type" => "section"),
    array( "type" => "open"), 
    
	 array( "name" => "Element",
		"desc" => "Enter the ID or class selector for the element you'd like to <em>fontify</em>. For example, <em>#navigation</em>, or <em>.element p</em>",
		"id" => $sn."_custom_one",
		"type" => "text"),    
	 array( "name" => "Element Font",
		"id" => $sn."_custom_one_font",
		"class" => "fu_font_list",
		"type" => "select",
		"options" => $fontList), 

	 array( "name" => "Element",
		"desc" => "Enter the ID or class selector for the element you'd like to <em>fontify</em>. For example, <em>#navigation</em>, or <em>.element p</em>",
		"id" => $sn."_custom_two",
		"type" => "text"),    
	 array( "name" => "Element Font",
		"id" => $sn."_custom_two_font",
		"class" => "fu_font_list",
		"type" => "select",
		"options" => $fontList), 
		
	 array( "name" => "Element",
		"desc" => "Enter the ID or class selector for the element you'd like to <em>fontify</em>. For example, <em>#navigation</em>, or <em>.element p</em>",
		"id" => $sn."_custom_three",
		"type" => "text"),    
	 array( "name" => "Element Font",
		"id" => $sn."_custom_three_font",
		"class" => "fu_font_list",
		"type" => "select",
		"options" => $fontList), 
		
	 array( "name" => "Element",
		"desc" => "Enter the ID or class selector for the element you'd like to <em>fontify</em>. For example, <em>#navigation</em>, or <em>.element p</em>",
		"id" => $sn."_custom_four",
		"type" => "text"),    
	 array( "name" => "Element Font",
		"id" => $sn."_custom_four_font",
		"class" => "fu_font_list",
		"type" => "select",
		"options" => $fontList), 
		
	 array( "name" => "Element",
		"desc" => "Enter the ID or class selector for the element you'd like to <em>fontify</em>. For example, <em>#navigation</em>, or <em>.element p</em>",
		"id" => $sn."_custom_five",
		"type" => "text"),    
	 array( "name" => "Element Font",
		"id" => $sn."_custom_five_font",
		"class" => "fu_font_list",
		"type" => "select",
		"options" => $fontList),

		   
    array( "type" => "close"),
          
);


function fontAddAdmin()
{

    global $fontUploaderName, $sn, $fontOptions;

    if ( $_GET['page'] == basename(__FILE__) )
    {
        if ( 'save' == $_REQUEST['action'] )
        {
            foreach ($fontOptions as $value)
            {
                update_option( $value['id'], $_REQUEST[ $value['id'] ] );
            }

            foreach ($fontOptions as $value)
            {
                if( isset( $_REQUEST[ $value['id'] ] ) )
                {
                    update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
                }
                else
                {
                    delete_option( $value['id'] );
                }
            }

            header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=font-uploader-free.php&saved=true');
            exit;

        }
        else if( 'reset' == $_REQUEST['action'] )
        {

            foreach ($fontOptions as $value)
            {
                delete_option( $value['id'] );
            }

            header('Location: ' . get_bloginfo('wpurl') . '/admin.php?page=font-uploader-free.php&saved=true');
            exit;

        }
    }
	 $baseFontDir = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
    add_menu_page($fontUploaderName, $fontUploaderName, 'administrator', basename(__FILE__), 'fontAdmin', $baseFontDir . 'font-uploader-icon.png');
}

function fontInit()
{
    $fontFileDir= WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
    wp_enqueue_style("fontFreeFunctions", $fontFileDir."fontFunctions/fontFreeFunctions.css", false, "1.0", "all");
    wp_enqueue_script("fu_script", $fontFileDir."fontFunctions/fu_script.js", false, "1.0");
}



function fontAdmin()
{

    global $fontUploaderName, $sn, $fontOptions;
    $i=0;

    if ( $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.$fontUploaderName.' settings saved.</strong></p></div>';
    if ( $_REQUEST['reset'] ) echo '<div id="message" class="updated fade"><p><strong>'.$fontUploaderName.' settings reset.</strong></p></div>';
	 $fuVersion = '1.2.2';
    ?>

<div class="fu_wrap">
<h2>Font Uploader</h2>
<div class="credits">
	<p>Wordpress Font Uploader Free Plugin by <a href="http://pippinspages.com">Pippin Williamson</a></p>	
	<small><?php echo 'Version ' . $fuVersion; ?></small><br />
	<small>Want support for IE?</small>
	<h4><a href="http://codecanyon.net/item/font-uploader/110175?ref=mordauk" title="Get Premium Font Uploader">Get Premium Version</a></h4>
	<h4><a href="http://pippinspages.com">From Pippin's Pages</a></h4>
		<?php if(function_exists('fetch_feed')) {
		
			include_once(ABSPATH.WPINC.'/feed.php');
			$feed = fetch_feed('http://feeds.feedburner.com/pippinspages/XXtm');
		
			$limit = $feed->get_item_quantity(2); // specify number of items
			$items = $feed->get_items(0, $limit); // create an array of items
		
		}
		if ($limit == 0) echo '<div>The feed is either empty or unavailable.</div>';
		else foreach ($items as $item) : ?>
		
		<div>
			<a href="<?php echo $item->get_permalink(); ?>"
			  title="<?php echo $item->get_date('j F Y @ g:i a'); ?>">
				<?php echo $item->get_title(); ?>
			</a>
		</div>
		<div>
			<?php echo substr($item->get_description(), 0, 200); ?>
			<span>[...]</span>
		</div>
		
		<?php endforeach; ?>
		<p>Help me buy a beer?</p>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="72HGD7SA97KPE">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>		
</div>
<p><em>Filetypes accepted: <strong>.ttf</strong>, and <strong>.otf</strong></em></p>
<p>Uploaded fonts will appear in the menus below</p>
<?php $baseFontDir = WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), "" ,plugin_basename(__FILE__)); ?>
	<form name="newad" method="post" enctype="multipart/form-data" action="<?php echo $baseFontDir; ?>font-upload.php">
	 <table>
	 	<tr><td><input type="file" name="font"></td></tr>
	 	<tr><td><input name="Submit" type="submit" value="Upload" class="fu_upload"></td></tr>
	 </table>	
	</form>
        <form method="post">

<?php 
                foreach ($fontOptions as $value):
                    switch ( $value['type'] ):
                        case "open":
                            break;

                        case "close":
?>
    </div>
</div>
<br />
<?php                       break;

                        case "title":
?>
 
<p>Apply your uploaded fonts to elements below:</p>
<?php                       break;

                        case 'text':
?>
<div class="fu_input fu_text">
    <label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
    <input name="<?php echo $value['id']; ?>"
    		  class="<?php echo $value['class']; ?>"
           id="<?php echo $value['id']; ?>"
           type="<?php echo $value['type']; ?>"
           value="<?php if ( get_settings( $value['id'] ) != ""){ echo htmlentities(stripslashes(get_settings( $value['id']))); } else { echo htmlentities($value['std']); } ?>" />
    <small><?php echo $value['desc']; ?></small><div class="clearfix"></div>
</div>
<?php
                            break;

                        case 'textarea':
?>
<div class="fu_input fu_textarea">
    <label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
    <textarea name="<?php echo $value['id']; ?>" cols="" rows="" class="<?php echo $value['class']; ?>"><?php if ( get_settings( $value['id'] ) != ""){ echo htmlentities(stripslashes(get_settings( $value['id'] ))); } else { echo $value['std']; } ?></textarea>
    <small><?php echo $value['desc']; ?></small><div class="clearfix"></div>
</div>
<?php
                            break;

                        case 'select':
?>
<div class="fu_input fu_select">
    <label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>

    <select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" class="<?php echo $value['class']; ?>">
    <?php foreach ($value['options'] as $option) { ?>
        <option <?php if (get_settings( $value['id'] ) == $option){ echo 'selected="selected"'; } ?>><?php echo htmlentities($option); ?></option>
    <?php } ?>
    </select>

    <small><?php echo $value['desc']; ?></small><div class="clearfix"></div>
</div>
<?php
                            break;

                        case "checkbox":
?>
<div class="fu_input fu_checkbox">
    <label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>

    <input type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="true" <?php if(get_option($value['id'])) echo 'checked="checked"'; ?> />

    <small><?php echo $value['desc']; ?></small><div class="clearfix"></div>
</div>
<?php                       break;

                        case "section":
                            $i++;
?>

<div class="fu_section">
    <div class="fu_title"><h3><img src="<?php echo $baseFontDir; ?>fontFunctions/images/trans.gif" class="inactive" alt=""><?php echo $value['name']; ?></h3><span class="submit"><input name="save<?php echo $i; ?>" type="submit" value="Save changes" />
        </span><div class="clearfix"></div></div>
    <div class="fu_options">

                        <?php break;

                endswitch;
            endforeach;
?>

        <input type="hidden" name="action" value="save" />
        </form>
        <form method="post">
            <p class="submit">
                <input name="reset" type="submit" value="Reset" />
                <input type="hidden" name="action" value="reset" />
            </p>
        </form>
 
    </div>
</div>
<?php
} //end fontAdmin

add_action('admin_init', 'fontInit');
add_action('admin_menu', 'fontAddAdmin');
 
function addGoogleFonts()
{
	echo strip_tags(stripslashes(get_option('fu_google_font_urls')), '<link>');

}
add_action('wp_head', 'addGoogleFonts'); 

function addStyles()	{
	global $fontURL;
	echo '<style type="text/css" media="screen">';
			if (get_option('fu_header_font') != 'Choose a font' && get_option('fu_header_font') != null)
			{	echo '
				@font-face {
				  font-family: "header-font";
				  src: url("'; echo $fontURL . '/' . get_option('fu_header_font'); echo '");
				}';
			}
			if (get_option('fu_body_font') != 'Choose a font'  && get_option('fu_body_font') != null)
			{	echo '
				@font-face {
				  font-family: "body-font";
				  src: url("'; echo $fontURL . '/' . get_option('fu_body_font'); echo '");
				}';
			}
			if (get_option('fu_lists_font') != 'Choose a font'  && get_option('fu_lists_font') != null)
			{	echo '
				@font-face {
				  font-family: "lists-font";
				  src: url("'; echo $fontURL . '/' . get_option('fu_lists_font'); echo '");
				}';
			}
			if (get_option('fu_custom_one_font') != 'Choose a font'  && get_option('fu_custom_one') != null)			
			{	echo '
				@font-face {
				  font-family: "custom-one";
				  src: url("'; echo $fontURL . '/' . get_option('fu_custom_one_font'); echo '");
				}';
			}
			if (get_option('fu_custom_two_font') != 'Choose a font'  && get_option('fu_custom_two') != null)			
			{	echo '
				@font-face {
				  font-family: "custom-two";
				  src: url("'; echo $fontURL . '/' . get_option('fu_custom_two_font'); echo '");
				}';
			}
			if (get_option('fu_custom_three_font') != 'Choose a font'  && get_option('fu_custom_three') != null)			
			{	echo '
				@font-face {
				  font-family: "custom-three";
				  src: url("'; echo $fontURL . '/' . get_option('fu_custom_three_font'); echo '");
				}';
			}
			if (get_option('fu_custom_four_font') != 'Choose a font'  && get_option('fu_custom_four') != null)			
			{	echo '
				@font-face {
				  font-family: "custom-four";
				  src: url("'; echo $fontURL . '/' . get_option('fu_custom_four_font'); echo '");
				}';
			}
			if (get_option('fu_custom_five_font') != 'Choose a font'  && get_option('fu_custom_five') != null)			
			{	echo '
				@font-face {
				  font-family: "custom-five";
				  src: url("'; echo $fontURL . '/' . get_option('fu_custom_five_font'); echo '");
				}';
			}
			if (get_option('fu_header_font') != 'Choose a font' && get_option('fu_header_font') != null)
			{
				echo	'h1, h2, h3, h4, h5, h6, h7	{
				font-family: "header-font"!important;
				}';
			}
			if (get_option('fu_body_font') != 'Choose a font'  && get_option('fu_body_font') != null)
			{
				echo 'p, em, div	{
					font-family: "body-font"!important;
				}';
			}
			if (get_option('fu_lists_font') != 'Choose a font'  && get_option('fu_lists_font') != null)
			{
				echo '
				li	{
					font-family: "lists-font"!important;
				}';
			}

			if (get_option('fu_custom_one_font') != 'Choose a font'  && get_option('fu_custom_one') != null)			
			{
				echo get_option('fu_custom_one'); echo '	{
					font-family: "custom-one"!important;
				}';
			}
			if (get_option('fu_custom_two_font') != 'Choose a font'  && get_option('fu_custom_two') != null)			
			{
				echo get_option('fu_custom_two'); echo '	{
					font-family: "custom-two"!important;
				}';
			}
			if (get_option('fu_custom_three_font') != 'Choose a font'  && get_option('fu_custom_three') != null)			
			{
				echo get_option('fu_custom_three'); echo '	{
					font-family: "custom-three"!important;
				}';
			}
			if (get_option('fu_custom_four_font') != 'Choose a font'  && get_option('fu_custom_four') != null)			
			{
				echo get_option('fu_custom_four'); echo '	{
					font-family: "custom-four"!important;
				}';
			}
			if (get_option('fu_custom_five_font') != 'Choose a font'  && get_option('fu_custom_five') != null)			
			{
				echo get_option('fu_custom_five'); echo '	{
					font-family: "custom-five"!important;
				}';
			}
			echo '
			</style>';

}			
add_action('wp_head','addStyles');
?>