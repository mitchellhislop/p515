<?php
/*
 * This file contains the event related hooks in the front end, as well as some event template tags
 */

/**
 * Filters for page content and if an event replaces it with the relevant event data.
 * @param $data
 * @return string
 */
function em_content($page_content) {
	$events_page_id = get_option ( 'dbem_events_page' );
	if ( get_the_ID() == $events_page_id && $events_page_id != 0 ) {
		/*
		echo "<h2>WP_REWRITE</h2>";
		echo "<pre>";
		global $wp_rewrite;
		print_r($wp_rewrite);
		echo "</pre>";
		echo "<h2>WP_QUERY</h2>";
		echo "<pre>";
		global $wp_query;
		print_r($wp_query->query_vars);
		echo "</pre>";
		die();
		*/
		global $wpdb, $wp_query, $EM_Event, $EM_Location, $EM_Category;
		//general defaults
		$args = array(				
			'orderby' => get_option('dbem_events_default_orderby'),
			'order' => get_option('dbem_events_default_order'),
			'owner' => false,
			'pagination' => 1
		);
		$content = apply_filters('em_content_pre', '', $page_content);
		if( empty($content) ){
			ob_start();
			if ( !empty($_REQUEST['calendar_day']) ) {
				//Events for a specific day
				em_locate_template('templates/calendar-day.php',true, array('args'=>$args));
			} elseif ( is_object($EM_Location) ) {
				//Just a single location
				em_locate_template('templates/location-single.php',true);
			}elseif ( is_object($EM_Category) ) {
				//Just a single category
				em_locate_template('templates/category-single.php',true);
			} elseif ( $wp_query->get('bookings_page') ) {
				//Bookings Page
				em_locate_template('templates/my-bookings.php',true);
			} elseif ( is_object($EM_Event) ) {
				// single event page
				em_locate_template('templates/event-single.php',true, array('args'=>$args));	
			}elseif ( !empty($_REQUEST['event_locations']) ){
				em_locate_template('templates/locations-list.php',true, array('args'=>$args));
			}elseif ( !empty($_REQUEST['event_categories']) ){
				em_locate_template('templates/categories-list.php',true, array('args'=>$args));
			} else {
				// Multiple events page
				if (get_option ( 'dbem_display_calendar_in_events_page' )){
					em_locate_template('templates/events-calendar.php',true, array('args'=>$args));
				}else{
					//Intercept search request, if defined
					$args['scope'] = get_option('dbem_events_page_scope');
					if( !empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'search_events') && get_option('dbem_events_page_search') ){
						$args = EM_Events::get_post_search($args);
					}	
					em_locate_template('templates/events-list.php', true, array('args'=>$args));
				}
			}
			$content = ob_get_clean();
		}
		//If disable rewrite flag is on, then we need to add a placeholder here
		if( get_option('dbem_disable_title_rewrites') == 1 ){
			$content = str_replace('#_PAGETITLE', em_content_page_title(''),get_option('dbem_title_html')) . $content;
		}
		//Now, we either replace CONTENTS or just replace the whole page
		if( preg_match('/CONTENTS/', $page_content) ){
			$content = str_replace('CONTENTS',$content,$page_content);
		}
		//TODO FILTER - filter em page content before display
		return apply_filters('em_content', '<div id="em-wrapper">'.$content.'</div>');
	}
	return $page_content;
}
add_filter ( 'the_content', 'em_content' );

/**
 * Filter for titles when on event pages
 * @param $data
 * @return string
 */
function em_content_page_title($content) {
	global $EM_Event, $EM_Location, $EM_Category, $wp_query, $post;
	$events_page_id = get_option ( 'dbem_events_page' );
	
	if ( $post->ID == $events_page_id && $events_page_id != 0 ) {
		$content = apply_filters('em_content_page_title_pre', '', $content);
		if( empty($content) ){
			if ( !empty( $_REQUEST['calendar_day'] ) ) {
				$events = EM_Events::get(array('limit'=>2,'scope'=>$_REQUEST['calendar_day'],'owner'=>false));
				if ( count($events) != 1 || get_option('dbem_display_calendar_day_single') == 1 ) {
					//We only support dates for the calendar day list title, so we do a simple filter for the supplied calendar_day
					$content = get_option ('dbem_list_date_title');
					preg_match_all("/#[A-Za-z0-9]+/", $content, $placeholders);
					foreach($placeholders[0] as $placeholder) {
						// matches all PHP date and time placeholders
						if (preg_match('/^#[dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU]$/', $placeholder)) {
							$content = str_replace($placeholder, mysql2date(ltrim($placeholder, "#"), $_REQUEST['calendar_day']),$content );
						}
					}
				}else{
					$event = array_shift($events);
					$content =  $event->output( get_option('dbem_event_page_title_format') );
				}
			}elseif ( is_object($EM_Location) ) {
				$location = new EM_Location( EM_Object::sanitize($_REQUEST ['location_id']) );
				$content =  $location->output(get_option( 'dbem_location_page_title_format' ));
			}elseif ( is_object($EM_Category) ) {
				//Just a single location
				$content =  $EM_Category->output(get_option( 'dbem_category_page_title_format' ));
			}elseif ( $wp_query->get('bookings_page') ) {
				//Bookings Page
				$content = sprintf(__('My %s','dbem'),__('Bookings','dbem'));
			}elseif ( is_object($EM_Event) && !empty($_REQUEST['book']) ) {
				//bookings page
				$content = $EM_Event->output( get_option('dbem_bookings_page_title') );
			}elseif ( is_object($EM_Event) ) {
				// single event page
				if( $EM_Event->status == 1 ){
					$content =  $EM_Event->output ( get_option ( 'dbem_event_page_title_format' ) );
				}else{
					$content = get_option('dbem_events_page_title');
				}
			} elseif ( !empty($_REQUEST['event_categories']) ){
				$content =  get_option ( 'dbem_categories_page_title' );
			}elseif ( !empty($_REQUEST['event_locations']) ){
				$content =  get_option ( 'dbem_locations_page_title' );
			}else{
				// Multiple events page
				$content =  get_option ( 'dbem_events_page_title' );
			}
		}
		return apply_filters('em_content_page_title', $content);
	}
	return $content;
}
//add_filter ( 'single_post_title', 'em_content_page_title',1,1 ); //Filter for the wp_title of page, can directly reference page title function
add_filter ( 'wp_title', 'em_content_page_title',10,1 );

/**
 * Makes sure we're in "THE Loop", which is determinied by a flag set when the_post() (start) is first called, and when have_posts() (end) returns false.
 * @param string $data
 * @return string
 */
function em_wp_the_title($data){
	//This is set by the loop_start and loop_end actions
	global $post;
	global $wp_query;
	if( get_option('dbem_disable_title_rewrites') != 1 && get_option('dbem_events_page') == $post->ID ){
		if ( $wp_query->in_the_loop ) {
			return apply_filters('em_wp_the_title', em_content_page_title($data)) ;
		}
	}
	return $data;
}
add_filter ( 'the_title', 'em_wp_the_title',10,1 );

/**
 * Filters the get_pages functions so it includes the event pages?
 * @param $data
 * @return array
 */
function em_filter_get_pages($data) {
	global $em_disable_filter; //Using a flag here instead
	$show_events_menu = get_option ( 'dbem_list_events_page' );
	if ( !$show_events_menu && $em_disable_filter !== true ) {
		$output = array(); 
		$events_page_id = get_option ( 'dbem_events_page' );
		foreach( $data as $data_id => $page ) {
			if($page->ID != $events_page_id){
				$output[] = $page;
			}
		}
		return apply_filters('em_filter_get_pages', $output);
	}
	return apply_filters('em_filter_get_pages', $data);
}
add_filter ( 'get_pages', 'em_filter_get_pages' );

?>