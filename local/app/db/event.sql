DROP Table IF EXISTS event;

CREATE TABLE event
(
id         int(11)     NOT NULL UNIQUE AUTO_INCREMENT,
filename   varchar(40) NOT NULL,
load_date  date,
keylist    text,
eventlist  text,
PRIMARY KEY (id)
);

INSERT INTO event (id, filename, load_date, keylist, eventlist)
VALUES (1,'none', "2017-08-01", "key1, key2, key3", "one, two, three");
