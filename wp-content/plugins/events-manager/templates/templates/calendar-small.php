<?php 
/*
 * This file contains the HTML generated for small calendars. You can copy this file to yourthemefolder/plugins/events/templates and modify it in an upgrade-safe manner.
 * 
 * There are two variables made available to you: 
 * 
 * 	$calendar - contains an array of information regarding the calendar and is used to generate the content
 *  $args - the arguments passed to EM_Calendar::output()
 * 
 * Note that leaving the class names for the previous/next links will keep the AJAX navigation working.
 */
?>
<table class="em-calendar">
	<thead>
		<tr>
			<td><a class="em-calnav" href="<?php echo $calendar['links']['previous_url']; ?>">&lt;&lt;</a></td>
			<td class="month_name" colspan="5"><?php echo ucfirst(date_i18n('M', $calendar['month_start'])); ?></td>
			<td><a class="em-calnav" href="<?php echo $calendar['links']['next_url']; ?>">&gt;&gt;</a></td>
		</tr>
	</thead>
	<tbody>
		<tr class="days-names">
			<td><?php echo implode('</td><td>',$calendar['row_headers']); ?></td>
		</tr>
		<tr>
			<?php
			$col_count = 1; //this counts collumns in the $calendar_array['cells'] array
			$col_max = count($calendar['row_headers']); //each time this collumn number is reached, we create a new collumn, the number of cells should divide evenly by the number of row_headers
			foreach($calendar['cells'] as $date => $cell_data ){
				$class = ( count($cell_data['events']) > 0 ) ? 'eventful':'eventless';
				if(!empty($cell_data['type'])){
					$class .= "-".$cell_data['type']; 
				}
				?>
				<td class="<?php echo $class; ?>">
					<?php if( count($cell_data['events']) > 0 ): ?>
					<a href="<?php echo $cell_data['link']; ?>" title="<?php echo $cell_data['link_title']; ?>"><?php echo date('j',$cell_data['date']); ?></a>
					<?php else:?>
					<?php echo date('j',$cell_data['date']); ?>
					<?php endif; ?>
				</td>
				<?php
				//create a new row once we reach the end of a table collumn
				$col_count= ($col_count == $col_max ) ? 1 : $col_count+1;
				echo ($col_count == 1) ? '</tr><tr>':''; 
			}
			?>
		</tr>
	</tbody>
</table>