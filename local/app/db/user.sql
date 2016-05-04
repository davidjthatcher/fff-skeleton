DROP Table IF EXISTS user;

CREATE TABLE user
(
id int(11) NOT NULL AUTO_INCREMENT,
username varchar(45) NOT NULL,
password varchar(95) NOT NULL,
PRIMARY KEY (id)
);

INSERT INTO user (id, username, password)
VALUES (1,'david','$2y$10$QdRxyYlRFqG0/ltueW1Tj.9RZjN5w4pq/8mZSCUpvzvT58sHQylua');

INSERT INTO user (id, username, password)
VALUES (2,'luke','$2y$10$QdRxyYlRFqG0/ltueW1Tj.9RZjN5w4pq/8mZSCUpvzvT58sHQylua');

INSERT INTO user (id, username, password)
VALUES (3,'judy','$2y$10$QdRxyYlRFqG0/ltueW1Tj.9RZjN5w4pq/8mZSCUpvzvT58sHQylua');
