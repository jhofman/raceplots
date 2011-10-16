DROP TABLE IF EXISTS race_times;
CREATE TABLE race_times (

id     	     	INT NOT NULL AUTO_INCREMENT, 
race_id		INT NOT NULL, 
place		INT, 
athlete		VARCHAR(32),
division	VARCHAR(16),
division_place	INT, 
swim		TIME,
t1		TIME,
bike		TIME,
t2		TIME,
run		TIME,
penalty		TIME,
total		TIME,

PRIMARY KEY(id),
INDEX(race_id),
INDEX(place),
INDEX(athlete),
INDEX(division),
INDEX(division_place)

);