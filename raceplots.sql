DROP TABLE IF EXISTS events;
CREATE TABLE events (
  id   	        INT NOT NULL,
  name		VARCHAR(64),
  location	VARCHAR(64),
  ymd		DATE,
  
  PRIMARY KEY(id),
  INDEX(name),
  INDEX(location),
  INDEX(ymd)
);

DROP TABLE IF EXISTS races;
CREATE TABLE races (
  id   	        INT NOT NULL,
  event_id	INT,
  name		VARCHAR(64),
  
  PRIMARY KEY(id),
  INDEX(event_id),
  INDEX(name)
);

DROP TABLE IF EXISTS race_times;
CREATE TABLE race_times (
  id     	     	INT NOT NULL AUTO_INCREMENT, 
  race_id		INT NOT NULL, 
  place		INT, 
  athlete		VARCHAR(64),
  division	VARCHAR(32),
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