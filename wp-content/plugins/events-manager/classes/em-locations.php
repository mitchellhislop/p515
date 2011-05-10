<?php
/**
 * Static class which will help bulk add/edit/retrieve/manipulate arrays of EM_Location objects. 
 * Optimized for specifically retreiving locations (whether eventful or not). If you want event data AND location information for each event, use EM_Events
 * 
 */
class EM_Locations extends EM_Object implements Iterator {
	/**
	 * Array of EM_Location objects
	 * @var array EM_Location
	 */
	var $locations = array();
	
	function EM_Events( $args = array() ){
		if( is_array($args) ){
			if( is_object(current($args)) && get_class(current($args)) == 'EM_Event' ){
				$this->locations = $args;
			}else{
				$this->locations = EM_Events::get($args);
			}
		}else{
			$this->locations = EM_Events::get();
		}
		do_action('em_events',$this);
	}
	/**
	 * Returns an array of EM_Location objects
	 * @param boolean $eventful
	 * @param boolean $return_objects
	 * @return array
	 */
	function get( $args = array(), $count=false ){
		global $wpdb;
		$events_table = EM_EVENTS_TABLE;
		$locations_table = EM_LOCATIONS_TABLE;
		
		//Quick version, we can accept an array of IDs, which is easy to retrieve
		if( self::array_is_numeric($args) ){ //Array of numbers, assume they are event IDs to retreive
			//We can just get all the events here and return them
			$sql = "SELECT * FROM $locations_table WHERE location_id=".implode(" OR location_id=", $args);
			$results = $wpdb->get_results($sql,ARRAY_A);
			$events = array();
			foreach($results as $result){
				$locations[$result['location_id']] = new EM_Location($result);
			}
			return $locations; //We return all the events matched as an EM_Event array. 
		}elseif( is_numeric($args) ){
			//return an event in the usual array format
			return apply_filters('em_locations_get', array(new EM_Event($args)), $args);
		}elseif( is_array($args) && is_object(current($args)) && get_class((current($args))) == 'EM_Location' ){
			return apply_filters('em_locations_get', $args, $args);
		}	

		//We assume it's either an empty array or array of search arguments to merge with defaults			
		$args = self::get_default_search($args);
		$limit = ( $args['limit'] && is_numeric($args['limit'])) ? "LIMIT {$args['limit']}" : '';
		$offset = ( $limit != "" && is_numeric($args['offset']) ) ? "OFFSET {$args['offset']}" : '';
		
		//Get the default conditions
		$conditions = self::build_sql_conditions($args);
		
		//Put it all together
		$EM_Location = new EM_Location(0); //Empty class for strict message avoidance
		$fields = $locations_table .".". implode(", {$locations_table}.", array_keys($EM_Location->fields));
		$where = ( count($conditions) > 0 ) ? " WHERE " . implode ( " AND ", $conditions ):'';
		
		//Get ordering instructions
		$EM_Event = new EM_Event(); //blank event for below
		$accepted_fields = $EM_Location->get_fields(true);
		$accepted_fields = array_merge($EM_Event->get_fields(true),$accepted_fields);
		$orderby = self::build_sql_orderby($args, $accepted_fields, get_option('dbem_events_default_order'));
		//Now, build orderby sql
		$orderby_sql = ( count($orderby) > 0 ) ? 'ORDER BY '. implode(', ', $orderby) : '';
		
		if( $count ){
			$fields = $locations_table.'.location_id';
		}
		//Create the SQL statement and execute
		$sql = "
			SELECT $fields FROM $locations_table
			LEFT JOIN $events_table ON {$locations_table}.location_id={$events_table}.location_id
			$where
			GROUP BY {$locations_table}.location_id
			$orderby_sql
			$limit $offset
		";
	
		//If we're only counting results, return the number of results
		if( $count ){
			return count($wpdb->get_results($sql));		
		}
		$results = $wpdb->get_results($sql, ARRAY_A);
		
		//If we want results directly in an array, why not have a shortcut here?
		if( $args['array'] == true ){
			return $results;
		}
		
		$locations = array();
		foreach ($results as $location){
			$locations[] = new EM_Location($location);
		}
		return apply_filters('em_locations_get', $locations, $args);
	}	
	
	function count( $args = array() ){
		return apply_filters('em_locations_count', self::get($args, true), $args);
	}
	
	/**
	 * Output a set of matched of events
	 * @param array $args
	 * @return string
	 */
	function output( $args ){
		global $EM_Location;
		$EM_Location_old = $EM_Location; //When looping, we can replace EM_Location global with the current event in the loop
		//Can be either an array for the get search or an array of EM_Location objects
		if( is_object(current($args)) && get_class((current($args))) == 'EM_Location' ){
			$func_args = func_get_args();
			$locations = $func_args[0];
			$args = (!empty($func_args[1])) ? $func_args[1] : array();
			$args = apply_filters('em_locations_output_args', self::get_default_search($args), $locations);
			$limit = ( !empty($args['limit']) && is_numeric($args['limit']) ) ? $args['limit']:false;
			$offset = ( !empty($args['offset']) && is_numeric($args['offset']) ) ? $args['offset']:0;
			$page = ( !empty($args['page']) && is_numeric($args['page']) ) ? $args['page']:1;
		}else{
			$args = apply_filters('em_locations_output_args', self::get_default_search($args) );
			$limit = ( !empty($args['limit']) && is_numeric($args['limit']) ) ? $args['limit']:false;
			$offset = ( !empty($args['offset']) && is_numeric($args['offset']) ) ? $args['offset']:0;
			$page = ( !empty($args['page']) && is_numeric($args['page']) ) ? $args['page']:1;
			$args['limit'] = false;
			$args['offset'] = false;
			$args['page'] = false;
			$locations = self::get( $args );
		}
		//What format shall we output this to, or use default
		$format = ( $args['format'] == '' ) ? get_option( 'dbem_location_list_item_format' ) : $args['format'] ;
		
		$output = "";
		$locations_count = count($locations);
		$locations = apply_filters('em_locations_output_locations', $locations);	
		if ( count($locations) > 0 ) {
			$location_count = 0;
			$locations_shown = 0;
			foreach ( $locations as $EM_Location ) {
				if( ($locations_shown < $limit || empty($limit)) && ($location_count >= $offset || $offset === 0) ){
					$output .= $EM_Location->output($format);
					$locations_shown++;
				}
				$location_count++;
			}
			//Add headers and footers to output
			if( $format == get_option ( 'dbem_location_list_item_format' ) ){
				$single_event_format_header = get_option ( 'dbem_location_list_item_format_header' );
				$single_event_format_header = ( $single_event_format_header != '' ) ? $single_event_format_header : "<ul class='em-locations-list'>";
				$single_event_format_footer = get_option ( 'dbem_location_list_item_format_footer' );
				$single_event_format_footer = ( $single_event_format_footer != '' ) ? $single_event_format_footer : "</ul>";
				$output =  $single_event_format_header .  $output . $single_event_format_footer;
			}
			//Pagination (if needed/requested)
			if( !empty($args['pagination']) && !empty($limit) && $locations_count >= $limit ){
				//Show the pagination links (unless there's less than 10 events
				$page_link_template = preg_replace('/(&|\?)page=\d+/i','',$_SERVER['REQUEST_URI']);
				$page_link_template = em_add_get_params($page_link_template, array('page'=>'%PAGE%'));
				$output .= apply_filters('em_events_output_pagination', em_paginate( $page_link_template, $locations_count, $limit, $page), $page_link_template, $locations_count, $limit, $page);
			}
		} else {
			$output = get_option ( 'dbem_no_locations_message' );
		}
		//FIXME check if reference is ok when restoring object, due to changes in php5 v 4
		$EM_Location_old= $EM_Location;
		return apply_filters('em_locations_output', $output, $locations, $args);		
	}
	
	function delete( $args = array() ){
		if( !is_object(current($args)) && get_class((current($args))) != 'EM_Location' ){
			$locations = self::get($args);
		}else{
			$locations = $args;
		}
		$results = array();
		foreach ( $locations as $EM_Location ){
			$results[] = $EM_Location->delete();
		}		
		return apply_filters('em_locations_delete', in_array(false, $results), $locations);
	}
	
	/**
	 * Builds an array of SQL query conditions based on regularly used arguments
	 * @param array $args
	 * @return array
	 */
	function build_sql_conditions( $args = array() ){
		global $wpdb;
		$events_table = EM_EVENTS_TABLE;
		$locations_table = EM_LOCATIONS_TABLE;
		
		$conditions = parent::build_sql_conditions($args);
		//eventful locations
		if( true == $args['eventful'] ){
			$conditions['eventful'] = "{$events_table}.event_id IS NOT NULL";
		}elseif( true == $args['eventless'] ){
			$conditions['eventless'] = "{$events_table}.event_id IS NULL";
		}
		//owner lookup
		if( !empty($args['owner']) ){
			$conditions['owner'] = "location_owner=".$args['owner'];
		}
		//blog id in events table
		if( is_multisite() && array_key_exists('blog',$args) && is_numeric($args['blog']) ){
			if( is_main_site($args['blog']) ){
				$conditions['blog'] = "(`blog_id`={$args['blog']} OR blog_id IS NULL)";
			}else{
				$conditions['blog'] = "(`blog_id`={$args['blog']})";
			}
		}
		return apply_filters('em_locations_build_sql_conditions', $conditions, $args);
	}
	
	/* Overrides EM_Object method to apply a filter to result
	 * @see wp-content/plugins/events-manager/classes/EM_Object#build_sql_orderby()
	 */
	function build_sql_orderby( $args, $accepted_fields, $default_order = 'ASC' ){
		return apply_filters( 'em_locations_build_sql_orderby', parent::build_sql_orderby($args, $accepted_fields, get_option('dbem_events_default_order')), $args, $accepted_fields, $default_order );
	}
	
	/* 
	 * Generate a search arguments array from defalut and user-defined.
	 * @see wp-content/plugins/events-manager/classes/EM_Object::get_default_search()
	 */
	function get_default_search($array = array()){
		$defaults = array(
			'eventful' => false, //Locations that have an event (scope will also play a part here
			'eventless' => false, //Locations WITHOUT events, eventful takes precedence
			'orderby' => 'name',
			'state' => false,
			'country' => false,
			'scope' => 'all', //we probably want to search all locations by default, not like events
			'blog' => get_current_blog_id()
		);
		$array['eventful'] = ( !empty($array['eventful']) && $array['eventful'] == true );
		$array['eventless'] = ( !empty($array['eventless']) && $array['eventless'] == true );
		if( is_admin() ){
			$defaults['owner'] = !current_user_can('read_others_locations') ? get_current_user_id():false;
		}
		return apply_filters('em_locations_get_default_search', parent::get_default_search($defaults, $array), $array, $defaults);
	}

	//Iteratior methods
    public function rewind(){
        reset($this->locations);
    }
  
    public function current(){
        $var = current($this->locations);
        return $var;
    }
  
    public function key(){
        $var = key($this->locations);
        return $var;
    }
  
    public function next(){
        $var = next($this->locations);
        return $var;
    }
  
    public function valid(){
        $key = key($this->locations);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }
}
?>