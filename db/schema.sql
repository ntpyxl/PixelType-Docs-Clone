CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(128) NOT NULL,
    lastname VARCHAR(128) NOT NULL,
    user_role ENUM('REGULAR', 'ADMIN') NOT NULL,
    is_suspended BOOLEAN DEFAULT 0 NOT NULL,
    date_registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_accounts (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL,
    userpass VARCHAR(256) NOT NULL
);

CREATE TABLE document (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(256) DEFAULT 'Untitled Document' NOT NULL,
    user_owner INT NOT NULL,
    content LONGTEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP 
);

CREATE TABLE user_shared_access (
    user_id INT NOT NULL,
    document_id INT NOT NULL,
    can_edit BOOLEAN DEFAULT 0 NOT NULL,
    date_shared TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE document_messages (
    on_document_id INT NOT NULL,
    sender_id INT NOT NULL,
    content TEXT NOT NULL,
    date_sent TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    action_name ENUM('CREATED', 'UPDATED', 'DELETED') NOT NULL,
    done_by INT NOT NULL,
    content_affected ENUM('ACCOUNT', 'DOCUMENT', 'ACCESS', 'MESSAGE') NOT NULL,
    content_type INT NOT NULL,
    content_owner INT NOT NULL,
    date_logged TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);