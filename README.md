# TreasureHunt
A PHP Project

The basic idea of our treasure hunt game is that teams have to visit a series of waypoints in
the right order and as fast as possible. At each waypoint, the team receives a clue on how to get
to the next waypoint. The fastest team to reach the final waypoint of a hunt wins.
We want to build a modern treasure hunt game. So instead of using an old, yellowed piece of
map, we rather use our computers (either real computers or tablets or smartphones) that connect
to a website on the central game server. This game server allows us to login with our player name,
to tell the system where we are and, in case that we found the next waypoint, we will receive the
next clue from that server.
Think of an online check-in system for the ’Amazing Race’ (http://wikipedia.org/wiki/
The Amazing Race) for smartphones, but without any additional tasks to be done at each point.
1
Treasure Hunt Management. The game server has to be able to keep track of several different
treasure hunts simultaneously. Each hunt has a unique title, as well as some general specification
such as the number of waypoints and its overall distance. Each hunt has a defined starting time
at which teams receive their first waypoint clue, and a status to distinguish whether the hunt is still
under construction, or open for registrations, or active, or finished.
Each waypoint has a well-defined location (typically consisting of a GPS longitude and latitude),
a short memorisable name which is unique per each treasure hunt (such as ’The Footbridge’), a
verification code, and a clue on how to get here. This clue is a generic free-text description that
the game designers can enter. Each treasure hunt consists of at least two waypoints (start and
finish, plus some points in between). The waypoints of a treasure hunt must be visited in a certain
order which needs to be captured in the database too.
A Credit level model would also include the following: As we rely on computers to verify that
a team visited a certain waypoint, not all waypoints need to be at a physical location. Plan your
model so that some waypoints can be virtual waypoints which simply refer to a web page that has
to be navigated to by the teams and on which they will find some verification code.
Player Management. Your database needs to keep track of the players too, such as their unique
login name, gender and address, and of course a password with which they can authenticate
against the game server. We further would like to store some statistics about each player such as
how many hunts they have done so far and how long they are already a player etc.
All treasure hunts are actually done in teams, not by individual players. So a player must be
able to join one of the existing teams after they have registered. Note that over the duration of their
registration with the game server, a player can be part of multiple different teams. Teams consist
of between two (2) and three (3) players. Each team must have a unique team name, and you also
should keep track of the date when the team was created and when each player joined the team.
Playing Treasure Hunts. A team can participate in multiple treasure hunts, but only one at a
certain point in time. During a treasure hunt, teams visit different locations. Each visit has to be
recorded by the system with its exact visiting time, points awarded for this waypoint, and whether
it was a correct visit or not. A visit is correct if the team is indeed at the next waypoint of its
current treasure hunt. This can be verified either by that the teams GPS position is matching the
waypoints location, or that the team submitted the correct waypoint’s verification code to the game
server (e.g. by scanning a QR code (wikipedia.org/wiki/QR code) with which the waypoint had
been marked).
If a team found the correct next waypoint, it should receive the clue of the subsequent waypoint
of the same treasure hunt. If it is the final waypoint of the whole hunt, it would receive a score such
as its rank and overall time, which are then also stored for the team in the game server’s database.
Besides winning a treasure hunt as part of a team, players can achieve badges for special
achievements during a hunt. For example, a player could receive a ’Fast Runner’ badge for being
in a team that reaches a waypoint first during a run three times, or an ’Experienced Treasure
Hunter’ completing five treasure hunts. Badges have a small descriptive text, as well as a condition
which must be true so that they get awarded to a player. Also keep track of when players received
a badge.
User Reviews and Ratings. Finally, a Credit level and above model should also incorporate the
capability that players review individual treasure hunts. This would allow for full-text comments left
in the system by players about a hunt and should also include a rating on a scale of 1 (don’t like)
2
to 5 (like very much). Other players shall further be able to ’like ’ reviews and rate them on how
useful they found them. For each rating, review or ’usefulness-rating’, the database shall keep
track of who issued those reviews and when.
Extensions. The system described so far is quite simple. Students who strive for Distinction or
High Distinction grades should extend it in some way. You should describe any extensions in an
additional brief (Discussion.txt) as part of your assignment, and model the extension properly.
You will be assessed on how closely your model matches the brief, as well as appropriate use of
EER elements such as aggregation, IsA hierarchies, etc. Possible extensions include:
1. Generic clues (more than just text ) and multiple per waypoint...
2. Teams or players having to pay for treasure hunts (or even clues?) - how, and with what
’currency’ or payment details?
3. A more sophisticated scoring model with more ’features’ of a hunt and some competitive
activities taking into account, than simply being the first to arrive.
4. ... insert your idea here ;)
In order to achieve a Distinction or High Distinction grade, you should extend the given scenario
with at least one own extension, that makes sense for our online treasure hunt game. Which
extension is up-to you.
