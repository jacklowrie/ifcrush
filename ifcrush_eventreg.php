<?php
/**
 **  This file contains all the support functions for the table ifcrush_eventreg
 **/
function ifcrush_install_eventreg() {
	$eventregs = array( 
					array('rusheeID' => 'abc123', 	'eventID'=> 1),
					array('rusheeID' => 'www456', 	'eventID'=> 2),
					array('rusheeID' => 'www456', 	'eventID'=> 3),
					array('rusheeID' => 'www456', 	'eventID'=> 4),
				);
	
	foreach ($eventregs as $eventreg)
   		addEventreg($eventreg);
 
}

function ifcrush_eventreg_handle_form() { 

	global $debug;
	if ($debug)
		echo "<pre>"; print_r($_POST); echo "</pre>";
	
	if ( isset($_POST['addEventreg']) ){
		// This is cuz addEvent doesn't take an eventID
		// jbltodo - verify input using javascript
		if (($_POST['rusheeID'] == "none") || ($_POST['eventID'] == "none")) {
			echo "select a rushee and an event";
			return;
		}
		$thiseventreg = array( 
			'rusheeID' =>  $_POST['rusheeID'],
			'eventID'  	=>  $_POST['eventID'],
		); // put the form input into an array
		addEventreg($thiseventreg);
	} else {
			
		//delete
		if ( isset($_POST['deleteEventreg']) ){
			$thiseventreg = array( 
				'rusheeID'	=>  $_POST['rusheeID'],
				'eventID' =>  $_POST['eventID'],
			); // put the form input into an array
			deleteEventreg($thiseventreg);
		}
	}
} // handles changes to db from the front end

/* Event_handler_form helpers */
function addEventreg($thiseventreg) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "ifc_eventreg";
	$rows_affected = $wpdb->insert($table_name, $thiseventreg);
	
	if ($rows_affected == 0) {
		echo "add event failed for rushee: " . $thiseventreg['rusheeID']. 
					" event: " . $thiseventreg['eventID'] ;
	}
	return $rows_affected;
} // adds a event to the table if addEvent is tagged

function deleteEventreg($thiseventreg) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "ifc_eventreg";
	$wpdb->delete( $table_name, $thiseventreg);
} // deletes a event if deleteEvent is tagged


//Display event table
function ifcrush_display_eventreg_table() {

	ifcrush_eventreg_handle_form(); // handle updates, adds, deletes
	
	global $wpdb;
	
	$eventreg_table_name = $wpdb->prefix . "ifc_eventreg";
	$query = "SELECT * FROM $eventreg_table_name";
	$alleventregs = $wpdb->get_results($query);
	
	if ($alleventregs) {
		create_eventreg_table_header(); // make a table header
		foreach ($alleventregs as $eventreg) { // populate the rows with db info
			//echo "<pre>"; print_r($event); echo "</pre>";

			create_eventreg_table_row($eventreg);
		}
		create_eventreg_add_row();
		create_eventreg_table_footer(); // end the table
	} 
	else { 
		?><h2>No event regs!</h2><?php
		create_eventreg_table_header(); // make a table header
		create_eventreg_add_row();
		create_eventreg_table_footer(); // end the table
	}
}

function create_eventreg_table_header() {
	?>
		<table>
		<tr>
			<th>RusheeID</th>
			<th>eventID</th>
		</tr>
	<?php
}
function create_eventreg_table_footer() {
	?></table><?php
}

function create_rushee_netIDs_menu($current){
	global $wpdb;

	$rushee_table_name = $wpdb->prefix . "ifc_rushee";    

	$query = "SELECT netID, lastName, firstName FROM $rushee_table_name";
	$rushees = $wpdb->get_results($query);
?>
	<select name="rusheeID">
<?php
	echo "<option value=\"none\">select rushee</option>\n";

	foreach ($rushees as $rushee) {
		//echo "comparing $guesttype $current";
		if ($rushee->netID == $current) {
			echo "<option value=\"$rushee->netID\" selected=\"selected\">$rushee->lastName, $rushee->firstName</option>\n";
		} else {
			echo "<option value=\"$rushee->netID\">$rushee->lastName, $rushee->firstName</option>\n";
		}
	}
?>
	</select>
<?php
}
function create_event_eventIDs_menu($current){
	global $wpdb;

	$event_table_name = $wpdb->prefix . "ifc_event";    

	$query = "SELECT eventID, title FROM $event_table_name group by eventID";
	$events = $wpdb->get_results($query);
?>
	<select name="eventID">
<?php
	echo "<option value=\"none\">select event</option>\n";
	foreach ($events as $event) {
		//echo "comparing $guesttype $current";
		if ($event->eventID == $current) {
			echo "<option value=\"$event->eventID\" selected=\"selected\">$event->title</option>\n";
		} else {
			echo "<option value=\"$event->eventID\">$event->title</option>\n";
		}
	}
?>
	</select>
<?php
}

function create_eventreg_add_row() {
	?>
		<form method="post">
			<tr>
				<td> 
					<?php create_rushee_netIDs_menu("   "); ?>
				</td>
				<td>
					<?php create_event_eventIDs_menu("   "); ?>
				</td>
				<td>
					<input type="submit" name="addEventreg" value="Add Eventreg"/>
				</td>
			</tr>
		</form>

	<?php
}

function create_eventreg_table_row($eventreg) {
	?>
		<form method="post">
			<tr>
				<td> 
					<?php create_rushee_netIDs_menu($eventreg->rusheeID); ?>
				</td>
				<td> 
					<?php create_event_eventIDs_menu($eventreg->eventID); ?>
				</td>
				<td>			
					<input type="submit" name="deleteEventreg" value="Delete"/>
				</td>
			</tr>
		</form>

	<?php
}
 
 
 
 
 
 ?>