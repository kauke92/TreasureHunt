<?php 
/**
 * Web page to display available hunts
 */
require_once('include/common.php');
require_once('include/database.php');
startValidSession();
htmlHead();
?>
<h1>Review</h1>
<?php 
if (!isset($_REQUEST['hunt'])) {
		$hunts=getAvailableHunts();
		echo '<form action="review.php" method="post">
			  <label><h3>Choose the hunt\'s name</h3>
			  <select name="hunt">';
		foreach($hunts as $hunt) {
			echo '<option value=',$hunt['id'],'>',$hunt['name'],'</option><br />';
		}
		echo'</select><input type=submit value="View" /><br />
			 </label><br />
			 </form>';

} else {
		$hunt =  getHuntDetails($_REQUEST['hunt']);
		$reviews= getHuntReview($_REQUEST['hunt']);
		echo '<h2>Comments for: ', '<a href="huntdetails.php?hunt=',$_REQUEST['hunt'],'">',$hunt['name'],'</a></h2>';
		if (empty($reviews)) {
			echo '<h2>No comments made</h2>';
			die();
		} 
		$sum=0;
		$count=0;
		echo '<table>
				<thead>
					<tr><th>Player</th><th>Time</th><th>Rating</th><th>Comment</th></tr>
				</thead>
				<tbody>';
					foreach($reviews as  $review) {
						echo '<tr></td><td>',$review['player'],'</td>',
								'<td>',$review['whendone'],'</td><td>',$review['rating'],'</td>',
								'<td>',$review['description'],'</td></tr>';
						
						$sum=$sum+$review['rating'];
						$count=$count+1;					
					}
		echo	'</tbody>
			</table>';
		echo '<h2>Avarage rating : ', floor(($sum*1.0/$count)*100+0.5)*0.01,'</h2>';
		echo '<form action="addreview.php?hunt=', $_REQUEST['hunt'], '" method="post">	  
						<input type=submit value="Add comment" /><br />
			  </form>';
}
htmlFoot();
?>
