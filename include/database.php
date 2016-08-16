<?php
/**
 * Database functions. You need to modify each of these to interact with the database and return appropriate results. 
 */

/**
 * Connect to database
 * This function does not need to be edited - just update config.ini with your own 
 * database connection details. 
 * @param string $file Location of configuration data
 * @return PDO database object
 * @throws exception
 */
function connect($file = 'config.ini') {
	// read database seetings from config file
    if ( !$settings = parse_ini_file($file, TRUE) ) 
        throw new exception('Unable to open ' . $file);
    
    // parse contents of config.ini
    $dns = $settings['database']['driver'] . ':' .
            'host=' . $settings['database']['host'] .
            ((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']) : '') .
            ';dbname=' . $settings['database']['schema'];
    $user= $settings['db_user']['username'];
    $pw  = $settings['db_user']['password'];

	// create new database connection
    try {
        $dbh=new PDO($dns, $user, $pw);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        print "Error Connecting to Database";
        die();
    }
    return $dbh;
}

/**
 * Check login details
 * @param string $name Login name
 * @param string $pass Password
 * @return boolean True is login details are correct
 */
function checkLogin($name,$pass) {
	 $db = connect();
    try {
		$db->beginTransaction();
		$stmt = $db->prepare('SELECT verifyUser(:name, :pass)');
		$stmt->bindValue(':name', $name, PDO::PARAM_STR);
		$stmt->bindValue(':pass', $pass, PDO::PARAM_STR);
    	$stmt->execute();
    	$result = $stmt->fetchColumn();
    	$stmt->closeCursor();
		
		$db->commit();
    } catch (PDOException $e) { 
		$db->rollback;
		print "Error checking login"; 
     	return FALSE;
    }
    return ($result==1);
}

/**
 * Get details of the current user
 * @param string $user login name user
 * @return array Details of user - see index.php
 */
function getUserDetails($user) {

	$db = connect();
	$results = array();
	try {
		$db->beginTransaction();
		$stmt = $db->prepare('SELECT name FROM TreasureHunt.player WHERE name=:name');
		$stmt->bindValue(':name',$user, PDO::PARAM_STR);
		$stmt->execute();
		$results['name']=$stmt->fetchColumn();
		$stmt->closeCursor();
		if (empty($results)) {
			$db->rollback();
			print "<h2>ERROR: Undefined user</h2>";
			die();
		}
		
		$stmt = $db->prepare('SELECT addr FROM TreasureHunt.player WHERE name=:name');
		$stmt->bindValue(':name',$user, PDO::PARAM_STR);
		$stmt->execute();
		$results['address']=$stmt->fetchColumn();
		$stmt->closeCursor();
		
		$stmt = $db->prepare('SELECT team
								FROM TreasureHunt.memberof
								WHERE (since<=CURRENT_DATE) AND (player=:name)
								ORDER BY since desc
								LIMIT 1');
	    $stmt->bindValue(':name', $user, PDO::PARAM_STR);
		$stmt->execute();
		$results['team'] = $stmt->fetchColumn();
		$stmt->closeCursor();

		$stmt = $db->prepare('SELECT COUNT(*) FROM (((TreasureHunt.player P JOIN TreasureHunt.memberof M ON (P.name=M.player))
								JOIN TreasureHunt.team  T ON (M.team=T.name))
								JOIN TreasureHunt.participates PA ON (PA.team=T.name))
								JOIN TreasureHunt.hunt H ON (H.id=PA.hunt)
							  WHERE status=:status AND P.name=:name');
		$stmt->bindValue(':name',$user,PDO::PARAM_STR);
		$stmt->bindValue(':status','finished',PDO::PARAM_STR);
		$stmt->execute();
		$results['nhunts'] = $stmt->fetchColumn();
		$stmt->closeCursor();

		$stmt = $db->prepare('SELECT B.name, B.description FROM (TreasureHunt.player P JOIN TreasureHunt.achievements A ON (P.name=A.player)) JOIN TreasureHunt.badge B ON (A.badge=B.name) 
								WHERE P.name=:name
							AND whenreceived<=CURRENT_TIMESTAMP');
		$stmt->bindValue(':name',$user,PDO::PARAM_STR);
		$stmt->execute();
		$results['badges'] = $stmt->fetchAll();
		$stmt->closeCursor();
		$db->commit();
    } catch (PDOException $e) { 
		$db->rollback();
     	print "Error getting player details "; 
     	die();
    } 
    
    return $results;
}

/**
 * List hunts that are currently available
 * @return array Various details of for available hunts - see hunts.php
 * @throws Exception 
 */
function getAvailableHunts() {
    $db = connect();
	$results = array();
	try {
		$db->beginTransaction();
		$stmt = $db->prepare('SELECT id, title as name, starttime as start, distance, numwaypoints as nwaypoints
							FROM TreasureHunt.hunt WHERE status!=:status
							ORDER BY name asc');
		$stmt->bindValue(':status','under construction', PDO::PARAM_STR);
		$stmt->execute();
		$results=$stmt->fetchAll();
		$stmt->closeCursor();
		$db->commit();

    } catch (PDOException $e) { 
		$db->rollback();
     	print "Error available hunts"; 
     	die();
    } 
    
    return $results;
}

/**
 * Get details for a specific hunt
 * @param integer $hunt ID of hunt
 * @return array Various details of current hunt - see huntdetails.php
 * @throws Exception 
 */
function getHuntDetails($hunt) {

	$db = connect();
	$results=array();
	
	try {
		$db->beginTransaction();
		$stmt = $db->prepare('SELECT title as name, description as desc, distance, starttime as start, numwaypoints as n_wp
							  FROM TreasureHunt.Hunt H
                              WHERE id=:id');
		$stmt->bindValue(':id',$hunt,PDO::PARAM_INT);
		$stmt->execute();
		$results = $stmt->fetch();
		$stmt->closeCursor();
		if (empty($results)) {
			print "<h1>ERROR : Undefined hunt</h1>";
			$db->rollback();
			die();
		} 
		$stmt = $db->prepare('SELECT COUNT(*)
								FROM TreasureHunt.participates P
								WHERE hunt=:id');
		$stmt->bindValue(':id',$hunt,PDO::PARAM_STR);
		$stmt->execute();
		$results['nteams'] = $stmt->fetchColumn();
		$stmt->closeCursor();
		$db->commit();
	} catch (PDOException $e) {
		$db->rollback();
		print "Error getting hunt details" ; 
     	die();
	}
    return $results;
}

/**
 * Show status of user in their current hunt
 * @param string $user
 * @return array Various details of current hunt - see current.php
 * @throws Exception 
 */
function getHuntStatus($user) {
	$db = connect();
	try {
		$db->beginTransaction();
		
		
		$stmt=$db->prepare('SELECT TreasureHunt.update_rank_score();');
		$stmt->execute();
		
		$stmt = $db->prepare('SELECT  H.title as name, H.id,H.numWayPoints as numwp, P.currentwp as waypoint_count, T.name as team, H.starttime as start_time, date_trunc(\'second\',CURRENT_TIMESTAMP-H.starttime) as elapsed, P.score, P.duration, P.rank
							  FROM (( TreasureHunt.hunt H JOIN TreasureHunt.participates P ON (H.id=P.hunt)) JOIN TreasureHunt.team T ON (P.team=T.name)) 
								JOIN TreasureHunt.memberof M ON (M.team= T.name)
							  WHERE (starttime<=CURRENT_TIMESTAMP) AND (M.player=:name)
							  ORDER BY starttime desc
							  LIMIT 1');
		$stmt->bindValue(':name',$user,PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch();
		$stmt->closeCursor();
		if (empty($row)) {
			print "<h2>ERROR : Player is not in any hunt</h2>";
			$db->rollback();
			die();
		}
		if ($row['numwp']==$row['waypoint_count']-1) {
			$row['status']='finished';
		}
		else $row['status']='active';
	    
		if ($row['status']!='active') return $row;
		
		$row['waypoint_count']--;
		
		$stmt = $db->prepare('SELECT clue
								FROM TreasureHunt.VirtualWaypoint
								WHERE (hunt=:hunt) AND (num=:num)
							UNION
								SELECT clue
								FROM TreasureHunt.PhysicalWaypoint
								WHERE (hunt=:hunt) AND (num=:num)');
		$stmt->bindValue(':hunt',$row['id'],PDO::PARAM_INT);
		$stmt->bindValue(':num',$row['waypoint_count']+1,PDO::PARAM_INT);
		$stmt->execute();
		$current= $stmt->fetch();
		$stmt->closeCursor();
		$row['clue']=$current['clue'];
		$db->commit();
	} catch (PDOException $e) {
		$db->rollback();
		print "Error getting hunt status " ; 
     	die();
	}
    return $row;
}

/**
 * Check validation code is for user's next expected waypoint 
 * @param string $user
 * @param integer $code Validation code (e.g. from QR)
 * @return array Various details of current visit - see validate.php
 * @throws Exception 
 */
function validateVisit($user,$code) {

	$row= getHuntStatus($user);
	if (empty($row)) {
		print "Error: Player is not in any hunt";
		die();
	}
	$db= connect();
	try {
		$db->beginTransaction();
		$stmt= $db->prepare('SELECT TreasureHunt.getnum_visit(:team)');
		$stmt->bindValue(':team',$row['team'],PDO::PARAM_STR);
		$stmt->execute();
		$num=$stmt->fetchColumn();
		$stmt->closeCursor();
		
		$stmt = $db->prepare('SELECT 1
									FROM TreasureHunt.VirtualWaypoint
									WHERE (hunt=:hunt) AND (num=:num) AND (verification_code=:code)
							  UNION
							  SELECT 1
							  FROM TreasureHunt.PhysicalWaypoint
							  WHERE (hunt=:hunt) AND (num=:num)AND (verification_code=:code) ');
		$stmt->bindValue(':hunt',$row['id'],PDO::PARAM_INT);
		$stmt->bindValue(':num',$row['waypoint_count']+1,PDO::PARAM_INT);
		$stmt->bindValue(':code',$code,PDO::PARAM_STR);
		$stmt->execute();
		$check_code= $stmt->fetch();
		$stmt->closeCursor();
		
		$db->commit();
	} catch (PDOException $e) {
		$db->rollback();
		print "<h2>Error: your team have not made any visit waypoint OR the code is in wrong format</h2>";
		die();
	}
	if (empty($row['clue'])) {
		echo '<h2>Your team finished this hunt</h2>';
		die();
	}

    if (empty($check_code)) {
		try {
			$db->beginTransaction();
			$stmt=$db->prepare('INSERT INTO TreasureHunt.Visit VALUES(:team,:num,:code,CURRENT_TIMESTAMP,:correct,:hunt,:wp)');
			$stmt->bindValue(':team',$row['team'],PDO::PARAM_STR);
			$stmt->bindValue(':num',$num+1,PDO::PARAM_INT);
			$stmt->bindValue(':code',$code,PDO::PARAM_STR);
			$stmt->bindValue(':correct','f',PDO::PARAM_BOOL);
			$stmt->bindValue(':hunt',$row['id'],PDO::PARAM_INT);
			$stmt->bindValue(':wp',$row['waypoint_count']+1,PDO::PARAM_INT);
			$stmt->execute();
			$db->commit();
		} catch (PDOException $e) {
				$db->rollback();
				print "Error updating visit waypoint";
				die();
		}
		$results['status']='invalid';
    } else {
		try{
			$db->beginTransaction();
			
			$stmt=$db->prepare('SELECT TreasureHunt.update(:team,:num,:code,:correct,:hunt,:wp)');
			$stmt->bindValue(':team',$row['team'],PDO::PARAM_STR);
			$stmt->bindValue(':num',$num+1,PDO::PARAM_INT);
			$stmt->bindValue(':code',$code,PDO::PARAM_INT);
			$stmt->bindValue(':correct','t',PDO::PARAM_BOOL);
			$stmt->bindValue(':hunt',$row['id'],PDO::PARAM_INT);
			$stmt->bindValue(':wp',$row['waypoint_count']+1,PDO::PARAM_INT);
			$stmt->execute();
			$currentwp=$stmt->fetchColumn();
			$stmt->closeCursor();
			
			$stmt=$db->prepare('SELECT treasurehunt.update_rank_duration(:team,:hunt)');
			$stmt->bindValue(':team',$row['team'],PDO::PARAM_STR);
			$stmt->bindValue(':hunt',$row['id'],PDO::PARAM_INT);
			$stmt->execute();
			
			$stmt=$db->prepare('SELECT duration, rank, score
								FROM TreasureHunt.participates
								WHERE team=:team and hunt=:hunt');
			$stmt->bindValue(':team',$row['team'],PDO::PARAM_STR);
			$stmt->bindValue(':hunt',$row['id'],PDO::PARAM_INT);
			$stmt->execute();
			$results=$stmt->fetch();
			$stmt->closeCursor();
			
			if ($currentwp>$row['numwp']) {
				$results['status']='complete';
			} else $results['status']='correct';
			
			$stmt = $db->prepare('SELECT clue
									FROM TreasureHunt.VirtualWaypoint
									WHERE (hunt=:hunt) AND (num=:num)
								UNION
									SELECT clue
									FROM TreasureHunt.PhysicalWaypoint
									WHERE (hunt=:hunt) AND (num=:num)');
			$stmt->bindValue(':hunt',$row['id'],PDO::PARAM_INT);
			$stmt->bindValue(':num',$currentwp,PDO::PARAM_INT);
			$stmt->execute();
			$results['clue']= $stmt->fetchColumn();
			$stmt->closeCursor();
			$db->commit();
		} catch (PDOException $e) {
			$db->rollback();
			print "Error updating visit waypoint";
			die();
		}
		return $results;
	}
}
function addHuntReview($hunt,$user,$rating,$comment) {

	$db = connect();
	$result='false';
	try {	
			$db->beginTransaction();
			$stmt = $db->prepare('insert into treasurehunt.review(hunt,player,whendone,rating,description) 
									values (:hunt,:player,date_trunc(\'second\',CURRENT_TIMESTAMP), :rating,:comment);');
			$stmt->bindValue(':hunt',$hunt,PDO::PARAM_INT);
			$stmt->bindValue(':player',$user, PDO::PARAM_STR);
			$stmt->bindValue(':rating',$rating,PDO::PARAM_INT);
			$stmt->bindValue(':comment',$comment,PDO::PARAM_STR);
			$stmt->execute();
			$result ='true';
			
			$stmt = $db->prepare('SELECT 1
								  FROM TreasureHunt.PlayerStats 
								  WHERE player=:player AND stat_name=:stat_name');
			$stmt->bindValue(':player',$user, PDO::PARAM_STR);
			$stmt->bindValue(':stat_name','number of reviews',PDO::PARAM_STR);
			$stmt->execute();
			$review = $stmt->fetch();
			$stmt->closeCursor();
			if (empty($review)) {
				$stmt = $db->prepare('insert into treasurehunt.playerstats(player,stat_name,stat_value) 
									values (:player,:stat_name,1)');
				$stmt->bindValue(':player',$user, PDO::PARAM_STR);
				$stmt->bindValue('stat_name','number of reviews',PDO::PARAM_STR);
				$stmt->execute();
				
			} else {
					$stmt=$db->prepare('UPDATE TreasureHunt.PlayerStats
								SET stat_value=CAST (stat_value as int )+1
								WHERE player=:player AND stat_name=:stat_name');
					$stmt->bindValue(':player',$user,PDO::PARAM_STR);
					$stmt->bindValue('stat_name','number of reviews',PDO::PARAM_STR);
					$stmt->execute();
					
			}
			
			$db->commit();
	} catch (PDOException $e) {
		$db->rollback();
		echo '<h2>Error: You rated for this hunt</h2>' ;
		die();
	}
	return $result;
}

function getHuntReview($hunt) {

	$db = connect();
	$results = array();
	try {	
			$db->beginTransaction();
			$stmt = $db->prepare('SELECT TreasureHunt.Review.player,TreasureHunt.Review.whendone,TreasureHunt.Review.rating,TreasureHunt.Review.description
									FROM TreasureHunt.Review
									WHERE TreasureHunt.Review.hunt=:hunt');
			$stmt->bindValue(':hunt',$hunt,PDO::PARAM_INT);
			$stmt->execute();
			$results= $stmt->fetchAll();
			$stmt->closeCursor();
			$db->commit();
	} catch (PDOException $e) {
		$db->rollback();
		echo 'Error getting hunt reviews';
		die();
	}
	return $results;
}

function getLatLon() {
    $db = connect();
    try {
        $result = array();
        $db->beginTransaction();
        $stmt = $db->query('
                            SELECT name
                            FROM treasurehunt.team
                            order by name
                            ');

        while ($eachTeam = $stmt->fetch()) {
            $st = $db->prepare('
                                select player
                                from treasurehunt.memberof
                                where team = :tname
                                limit 1;
                                ');
            $st->bindValue(':tname', $eachTeam[0], PDO::PARAM_STR);
            $st->execute();
            $player = $st->fetchColumn();
            $hunt = getHuntStatus($player);
            $st->closeCursor();
            if ($hunt == NULL || $hunt['status'] != 'active')
                continue;

            $st = $db->prepare('
                                select gpslat, gpslon, team
                                from treasurehunt.visit v join treasurehunt.physicalwaypoint p
                                on (submitted_code = verification_code)
                                where team = :tname
                                order by time desc
                                ');
            $st->bindValue(':tname', $eachTeam[0], PDO::PARAM_STR);
            $st->execute();
            $res = $st->fetch();
            $st->closeCursor();

            if ($res[0] == NULL || $res[1] == NULL)
                continue;
            array_push($result, array('lat' => $res[0], 'lon' => $res[1], 'team' => $res[2]));
        }
        $db->commit();

    } catch (PDOException $e) {
        print "Error getting player details: " . $e->getMessage();
        $db->rollback();
        die();
    }
    return $result;
}
?>
