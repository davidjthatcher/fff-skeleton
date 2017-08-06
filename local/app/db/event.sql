DROP Table IF EXISTS event;

CREATE TABLE event
(
id         int(11)       NOT NULL UNIQUE AUTO_INCREMENT,
dayofweek 	varchar(3)   NOT NULL,
timeofday 	varchar(10)  NOT NULL,
area 		varchar(20)  NOT NULL,
grp 		varchar(50)  NOT NULL,
address 	varchar(20)  NOT NULL,
city 		varchar(20)  NOT NULL,
state 		varchar(2)   NOT NULL,
zip 		varchar(5)   NOT NULL,
type 		varchar(40)  NOT NULL,
misc 		varchar(40),
geocode		varchar(20),
PRIMARY KEY (id)
);

INSERT INTO event (id, dayofweek, timeofday, area, grp, address, city, state, zip, type, misc, geocode)
VALUES (1,"SUN","07:00 AM","E","Beaches Unity Group","322 Penman Rd","Jacksonville Beach","FL","32250","O-DR-HF","NULL","NULL");
INSERT INTO event (id, dayofweek, timeofday, area, grp, address, city, state, zip, type, misc, geocode)
VALUES (2,"MON","07:00 AM","E","Beaches Unity Group","322 Penman Rd","Jacksonville Beach","FL","32250","O-DR-HF","NULL","NULL");
