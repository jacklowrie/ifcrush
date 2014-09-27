<?php
/**
 **  This file contains all the support functions for the table ifcrush_eventreg
 **/

/**
 * Display event table - short code entry point
 **/
function ifcrush_display_eventreg_placeholder() {

	/* sort out who is logged in */
	if (!is_user_logged_in()) {
		echo "sorry you must be logged use register pnms for events";
		return;
	}
	
	$current_user = wp_get_current_user();
	
	if (is_user_an_rc($current_user)){
		/* get the frat of the rc
		 */
		$fratLetters =  get_frat_letters($current_user);
		ifcrush_display_eventreg($fratLetters);
	} else {
		/* assume its an admin */
		ifcrush_display_eventreg("");
	}
	ifcrush_eventreg_handle_form(); // handle updates, adds, deletes
}

/**
 * Display the table of event registrations
 * if fratLetters are set, then only display event registrations for 
 * events for that particular frat.  Otherwise, display all of them.
 */
function ifcrush_display_eventreg_frat($fratLetters){
	
	global $wpdb;
	
	$eventreg_table_name = $wpdb->prefix . "ifc_eventreg";
	$event_table_name = $wpdb->prefix . "ifc_event";    
	$query = "SELECT * FROM $eventreg_table_name as er join $event_table_name as e on e.eventID=er.eventID";
	if ($fratLetters != "")
		$query .= " where fratID='$fratLetters'";
	$alleventregs = $wpdb->get_results($query);
	
	if ($alleventregs) {
		create_eventreg_table_header(); // make a table header
		create_eventreg_add_row($fratLetters);
		foreach ($alleventregs as $eventreg) { // populate the rows with db info
			create_eventreg_table_row($eventreg);
		}
		create_eventreg_add_row($fratLetters);
		create_eventreg_table_footer(); // end the table
	} 
	else { 
		?><h2>No event regs!</h2><?php
		create_eventreg_table_header(); // make a table header
		create_eventreg_add_row($fratLetters);
		create_eventreg_table_footer(); // end the table
	}
}

/**
 * Display event registrations for a PNM
 **/
function ifcrush_event_reg_for_pnm($pnm_netID){
// 	global $debug;
// 	if ($debug) {
// 		echo "[ifcrush_event_reg_for_pnm]";
// 	}
	
	global $wpdb;
	$eventreg_table_name = $wpdb->prefix . "ifc_eventreg";
	$event_table_name = $wpdb->prefix . "ifc_event";    
	$query = "SELECT * FROM $eventreg_table_name as er join $event_table_name as e on e.eventID=er.eventID";
	$query .= " where pnm_netID='$pnm_netID'";
	
	$alleventregs = $wpdb->get_results($query);
	
	if ($alleventregs) {
		create_eventreg_table_header(); // make a table header
		foreach ($alleventregs as $eventreg) { // populate the rows with db info
			create_eventreg_table_row($eventreg);
		}
		create_eventreg_table_footer(); // end the table
	} 
	else { 
		?><h2>You haven't been registered at any events</h2><?php
	}

}
function ifcrush_display_register_pnm_at_event($eventID){
// 	global $debug;
// 	if ($debug) {
// 		echo "[ifcrush_display_register_pnm_at_event]";
// 	}
	
	global $wpdb;
	$eventreg_table_name = $wpdb->prefix . "ifc_eventreg";
	$event_table_name = $wpdb->prefix . "ifc_event";    
	$query = "SELECT * FROM $eventreg_table_name as er join $event_table_name as e on e.eventID=er.eventID
				where e.eventID=$eventID";
	
	$alleventregs = $wpdb->get_results($query);
	
	// if ($debug) { 
// 		echo "<pre>"; 
// 		echo "query: $query
// 		";
// 		print_r($alleventregs); 
// 		echo "</pre>";
// 	}

	
	if ($alleventregs) {
		create_eventreg_table_header(); // make a table header
		create_eventreg_add_row($eventID);

		foreach ($alleventregs as $eventreg) { // populate the rows with db info
			create_eventreg_table_row($eventreg);
		}
		create_eventreg_table_footer(); // end the table
	} 
	else { 
		?><h2>No registered PNMs at this event</h2><?php
		create_eventreg_table_header(); // make a table header
		create_eventreg_add_row($eventID);
		create_eventreg_table_footer(); // end the table
	}

}

function ifcrush_eventreg_add($pnm_netID, $eventID){
		if (($_POST['pnm_netID'] == "none") || ($_POST['eventID'] == "none")) {
			echo "select a pnm and an event";
			return;
		}
		$thiseventreg = array( 
			'pnm_netID' =>  $pnm_netID,
			'eventID'  	=>  $eventID,
		); 
		addEventreg($thiseventreg);
}

function ifcrush_eventreg_handle_form($action) { 

// 	global $debug;
// 	if ($debug) {
// 		echo "[ifcrush_eventreg_handle_form]";
// 		echo "<pre>"; print_r($_POST); echo "</pre>";
// 	}
	
	switch ($action) {
		case "add registration":
			if (($_POST['pnm_netID'] == "none") || ($_POST['eventID'] == "none")) {
				echo "select a pnm and an event";
				return;
			}
			$thiseventreg = array( 
				'pnm_netID' =>  $_POST['pnm_netID'],
				'eventID'  	=>  $_POST['eventID'],
			); 
			addEventreg($thiseventreg);
			break;
		case "delete registration":
			$thiseventreg = array( 
				'pnm_netID'	=>  $_POST['pnm_netID'],
				'eventID' =>  $_POST['eventID'],
			); // put the form input into an array
			deleteEventreg($thiseventreg);
			break;
		default:
			echo "invalid eventreg action";
			break;
	}
} // handles changes to db from the front end

/* Event_handler_form helpers */
function addEventreg($thiseventreg) {
	global $wpdb;
	/* kbl todo - should not allow duplicate insert */
	$table_name = $wpdb->prefix . "ifc_eventreg";
	$rows_affected = $wpdb->insert($table_name, $thiseventreg);
	
	if ($rows_affected == 0) {
		echo "add event failed for pnm: " . $thiseventreg['pnm_netID']. 
					" event: " . $thiseventreg['eventID'] ;
	}
	return $rows_affected;
} // adds a event to the table if addEvent is tagged

function deleteEventreg($thiseventreg) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "ifc_eventreg";
	$wpdb->delete( $table_name, $thiseventreg);
} // deletes a event if deleteEvent is tagged

function create_eventreg_table_header() {
	?>
		<table>
		<tr>
			<th>PNM</th>
			<th>Fraternity-Event Title</th>
		</tr>
	<?php
}
function create_eventreg_table_footer() {
	?></table><?php
}

function create_pnm_netIDs_menu($current){
	$allpnms = get_all_pmns();
?>
	<select name="pnm_netID">
<?php
	echo "<option value=\"none\">select PNM</option>\n";

	foreach ($allpnms as $pnm) {
		$pnm_netID = $pnm['ifcrush_netID']; 
		$last_name = $pnm['last_name']; 
		$first_name = $pnm['first_name']; 

		if ($pnm_netID == $current) {
			echo "<option value=\"$pnm_netID\" selected=\"selected\">$pnm_netID</option>\n";
		} else {
			echo "<option value=\"$pnm_netID\">$pnm_netID</option>\n";
		}
	}
?>
	</select>
<?php
}
function create_event_eventIDs_menu($current, $fratLetters){
	global $wpdb;
	$event_table_name = $wpdb->prefix . "ifc_event";    
	$query = "SELECT eventID, fratID, title FROM $event_table_name";

	$query .= 	($fratLetters != "") ? " where fratID='$fratLetters' group by eventID":
									   " group by eventID";
	$events = $wpdb->get_results($query);
?>
	<select name="eventID">
<?php
	echo "<option value=\"none\">select event</option>\n";
	foreach ($events as $event) {
		//echo "comparing $guesttype $current";
		if ($event->eventID == $current) {
			echo "<option value=\"$event->eventID\" selected=\"selected\">$event->fratID-$event->title</option>\n";
		} else {
			echo "<option value=\"$event->eventID\">$event->fratID-$event->title</option>\n";
		}
	}
?>
	</select>
<?php
}

function create_eventreg_add_row($eventID) {
	global $wpdb;
	$event_table_name = $wpdb->prefix . "ifc_event";    
	$query = "SELECT eventID, fratID, title FROM $event_table_name 
					where eventID=$eventID";
	$event = $wpdb->get_results($query);
	
// 	global $debug;
// 	if ($debug) {
// 		echo "<pre>".print_r($event)."</pre>";
// 	}
	?>
	
		<form method="post">
			<tr>
				<td> 
					<?php create_pnm_netIDs_menu("   "); ?>
				</td>
				<td>
					<?php echo $event[0]->title; ?>
				</td>
				<td>
					<input type="hidden" name="eventID" value="<?php echo $event[0]->eventID; ?>"/>
					<input type="submit" name="action" value="Register this PMN"/>
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
					<?php echo get_pnm_name_by_netID($eventreg->pnm_netID); ?>
				</td>
				<td> 
					<?php echo $eventreg->fratID."-".$eventreg->title; ?>
				</td>
				<td>
					<input type="hidden" name="eventID" value="<?php echo $eventreg->eventID; ?> ">		
					<input type="hidden" name="pnm_netID" value="<?php echo $eventreg->pnm_netID; ?> ">		
					<input type="submit" name="action" value="Delete Event Reg"/>
				</td>
			</tr>
		</form>

	<?php
}
 ?>