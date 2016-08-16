<?php 
/**
 * Web page to display information about a specific current hunt
 */
require_once('include/common.php');
require_once('include/database.php');
startValidSession();
htmlHead();
?>
<h1>Hunt Details</h1>
<?php 
try {
	if (!isset($_GET['hunt'])) {
		echo '<h2>Invalid Hunt</h2>';
		die();
	}
    $hunt = getHuntDetails($_GET['hunt']);
    echo '<h2>Name</h2> ',$hunt['name'];
    echo '<h2>Description</h2> ',$hunt['desc'];
    echo '<h2>Start Time</h2> ',$hunt['start'];
    echo '<h2>Distance</h2> ',$hunt['distance'];
    echo '<h2>Teams</h2> ',$hunt['nteams'];
    echo '<h2>Waypoints</h2> ',$hunt['n_wp'];
	echo '<form action="addreview.php?hunt=', $_GET['hunt'], '" method="post">	  
						<input type=submit value="Add comment" /><br />
	 </form>';
    // TODO: Maybe show a map of the bounding box of all the waypoints
} catch (Exception $e) {
    echo '<h2>Cannot get hunt details</h2>';
}
htmlFoot();
?>
