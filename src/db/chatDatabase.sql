CREATE TABLE users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT
	username TEXT NOT NULL,
);

CREATE TABLE chat_groups (
	chat_group_id INTEGER PRIMARY KEY NOT NULL,
	chat_group_name TEXT NOT NULL UNIQUE
);
CREATE TABLE messages (
	message_id INTEGER PRIMARY KEY NOT NULL,
    user_id INTEGER NOT NULL,
    chat_group_user_id INTEGER NOT NULL,
	message TEXT NOT NULL ,
    sent_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ,
    FOREIGN KEY (chat_group_user_id) REFERENCES chat_group_user(chat_group_user_id) 
);

CREATE TABLE chat_group_user (
    chat_group_user_id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	user_id INTEGER NOT NULL,
    chat_group_id INTEGER NOT NULL,
    join_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    left_time TIMESTAMP  CURRENT_TIMESTAMP,
    status TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ,
    FOREIGN KEY (chat_group_id) REFERENCES chat_groups(chat_group_id)
);
