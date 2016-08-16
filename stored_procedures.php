
CREATE OR REPLACE FUNCTION TreasureHunt.verifyUser( name VARCHAR(40),password VARCHAR(20) ) RETURNS INT AS $$
	DECLARE res INT;
	BEGIN
	   SELECT COUNT(*) INTO res
	   FROM TreasureHunt.player 
	   WHERE TreasureHunt.player.name=$1 AND TreasureHunt.player.password=$2;
	   RETURN res;
	END;
  $$  LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION TreasureHunt.getTeam_name( name VARCHAR(40)) RETURNS VARCHAR AS $$
	DECLARE res VARCHAR;
	BEGIN
	   SELECT team INTO res
	   FROM TreasureHunt.memberof
	   WHERE (TreasureHunt.memberof.since<=CURRENT_DATE) AND (TreasureHunt.memberof.player=$1)
	   ORDER BY since desc
	   LIMIT 1;
	   RETURN res;
	END;
  $$  LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION TreasureHunt.getHunts_played( name VARCHAR(40)) RETURNS INT AS $$
	DECLARE res INT;
	BEGIN
	   SELECT COUNT(*) INTO res
	   FROM (((TreasureHunt.player P JOIN TreasureHunt.memberof M ON (P.name=M.player))
								JOIN TreasureHunt.team  T ON (M.team=T.name))
								JOIN TreasureHunt.participates PA ON (PA.team=T.name))
								JOIN TreasureHunt.hunt H ON (H.id=PA.hunt)
	   WHERE H.status='finished' AND P.name=$1;
	   RETURN res;
	END;
  $$  LANGUAGE plpgsql;
 CREATE OR REPLACE FUNCTION TreasureHunt.getnum_visit( team VARCHAR(40)) RETURNS INT AS $$
	DECLARE res INT;
	BEGIN
		SELECT num INTO res
		FROM TreasureHunt.visit
		WHERE TreasureHunt.visit.team=$1
		ORDER BY num desc
		LIMIT 1;

		RETURN res;
	END;
  $$  LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION TreasureHunt.getReview( hunt INT) RETURNS SETOF TreasureHunt.Review AS $$
 DECLARE R TreasureHunt.Review;
 BEGIN
	 FOR R IN SELECT TreasureHunt.Review.id,TreasureHunt.Review.hunt,TreasureHunt.Review.player, TreasureHunt.Review.whendone, TreasureHunt.Review.rating, TreasureHunt.Review.description
	  FROM TreasureHunt.Review
	  WHERE TreasureHunt.Review.hunt=$1 LOOP
	 RETURN NEXT R;
	 END LOOP;
	 RETURN;

 END;
  $$  LANGUAGE plpgsql;
  
/* hard stored procedure*/
CREATE OR REPLACE FUNCTION TreasureHunt.update( team IN VARCHAR(40),num IN INT, code IN INT, correct IN BOOLEAN, hunt IN INT, wp IN INT) RETURNS INT AS $$
 DECLARE res INT;
 BEGIN
	 INSERT INTO TreasureHunt.Visit VALUES($1,$2,$3,CURRENT_TIMESTAMP,$4,$5,$6);

	 UPDATE TreasureHunt.Participates
	 SET currentwp=currentwp+1, score=score+1
	 WHERE TreasureHunt.Participates.team=$1 AND TreasureHunt.Participates.hunt=$5;

	 SELECT currentwp INTO res
	 FROM TreasureHunt.Participates
	 WHERE TreasureHunt.Participates.team=$1 AND TreasureHunt.Participates.hunt=$5;
	 RETURN res;

	 
 END;
  $$  LANGUAGE plpgsql;

/* hard stored procedure*/
CREATE OR REPLACE FUNCTION TreasureHunt.update_rank_duration( team IN VARCHAR(40), hunt IN INT) RETURNS VOID AS $$
 DECLARE elapsed INTERVAL;
 BEGIN
	 update treasurehunt.participates T1
	 SET rank = (SELECT rr
	 FROM	(SELECT T2.team, T2.hunt, rank() over (partition by T2.hunt order by T2.score desc) as rr
			 FROM treasurehunt.participates T2) as foo
		WHERE foo.team=T1.team and foo.hunt=T1.hunt);

     SELECT CURRENT_TIMESTAMP-TreasureHunt.Hunt.starttime INTO elapsed
	 FROM TreasureHunt.Hunt
	 WHERE id=$2;

	 update treasurehunt.participates
	 SET duration = EXTRACT (day from elapsed)*24*60+EXTRACT (hour from elapsed)*60+EXTRACT (minute from elapsed)
	 WHERE treasurehunt.participates.team=$1 and treasurehunt.participates.hunt=$2;
	 
 END;
  $$  LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION TreasureHunt.update_rank_score( ) RETURNS SETOF RECORD AS $$
	BEGIN
					update treasurehunt.participates T1
					SET score=0, currentwp=1
					WHERE T1.score ISNULL;
								
					update treasurehunt.participates T1
					SET rank = (SELECT rr
								FROM	(SELECT T2.team, T2.hunt, rank() over (partition by T2.hunt order by T2.score desc) as rr
										 FROM treasurehunt.participates T2) as foo
								WHERE foo.team=T1.team and foo.hunt=T1.hunt);
	END;
  $$  LANGUAGE plpgsql;
  
/* IMPORTANT TODO: */
/* please replace 'your_login' with the name of your PostgreSQL login */
/* in the following ALTER USER username SET search_path ... command   */
/* this ensures that the carsharing schema is automatically used when you query one of its tables */



