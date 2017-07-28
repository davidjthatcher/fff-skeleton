DROP Table IF EXISTS user;

CREATE TABLE user
(
id int(11) NOT NULL UNIQUE AUTO_INCREMENT,
username varchar(45) NOT NULL UNIQUE,
password varchar(95) NOT NULL,
access varchar(15) NOT NULL,
order_status varchar(15) DEFAULT 'read',
order_start_date date DEFAULT '2016-09-01',
PRIMARY KEY (id)
);

INSERT INTO user (id, username, password, access, order_status, order_start_date)
VALUES (1,'david','$2y$10$QdRxyYlRFqG0/ltueW1Tj.9RZjN5w4pq/8mZSCUpvzvT58sHQylua',
    'write', 'processing', "2016-09-01");

INSERT INTO user (id, username, password, access, order_status, order_start_date)
VALUES (2,'rider','$2y$10$QdRxyYlRFqG0/ltueW1Tj.9RZjN5w4pq/8mZSCUpvzvT58sHQylua',
    'read', 'processing', "2016-09-01");
