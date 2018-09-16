ALTER TABLE users ADD auth_type varchar(32) NULL after user_id;
ALTER TABLE users ADD auth_id int NULL after auth_type;
DROP INDEX username ON users;
CREATE UNIQUE INDEX username ON users (auth_type, username);
UPDATE users SET auth_id = user_id;
