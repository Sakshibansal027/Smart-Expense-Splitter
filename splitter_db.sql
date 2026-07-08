CREATE DATABASE splitter_db;
USE splitter_db;

CREATE TABLE friends (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);


CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    paid_by_id INT,
    FOREIGN KEY (paid_by_id) REFERENCES friends(id) ON DELETE CASCADE
);

select * from friends;

select * from expenses;

