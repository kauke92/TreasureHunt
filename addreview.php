<?php 
/**
 * Web page to display available hunts
 */
require_once('include/common.php');
require_once('include/database.php');
startValidSession();
htmlHead();
$hunt =  getHuntDetails($_REQUEST['hunt']);
echo '<h1>Add Review</h1>';
echo '<h2>You\'re rating for : ', '<a href="huntdetails.php?hunt=',$_GET['hunt'],'">',$hunt['name'],'</a></h2>';

if (!isset($_REQUEST['comment']) OR !isset($_REQUEST['rating'])) {
	echo '<form action="addreview.php?hunt=', $_GET['hunt'], '" method="post">	
		Choose your rating
		<select name="rating">
					<option value=1>1</option>
					<option value=2>2</option>
					<option value=3>3</option>
					<option value=4>4</option>
					<option value=5>5</option>
		</select> <br />	
		Comment:<br />
		<textarea name="comment" id="comment">Enter your comment
		</textarea><br />
		<input type="submit" value="Submit" />
		</form>'; 
} else 
{
	try {
		$status = addHuntReview($_GET['hunt'],$_SESSION['player'],$_REQUEST['rating'],$_REQUEST['comment']);
		if ($status=='true') {
			echo 'You have successully added comment and rating';
		} else {
			echo '<h2>Error: You rated for this hunt</h2>';
		}
	} catch (Exception $e) {
        echo '<h2>Could not add comments</h2>';
	}
}
htmlFoot();
?>
