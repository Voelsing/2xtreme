
-- 2xtreme.sql
SET NAMES utf8mb4; SET time_zone = '+00:00';

-- Roles & Permissions
CREATE TABLE IF NOT EXISTS role (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS permission (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(80) NOT NULL UNIQUE,
  label VARCHAR(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  email_verified_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_role (
  user_id INT NOT NULL,
  role_id INT NOT NULL,
  PRIMARY KEY (user_id, role_id),
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_permission (
  role_id INT NOT NULL,
  permission_id INT NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE CASCADE,
  FOREIGN KEY (permission_id) REFERENCES permission(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS password_reset_token (
  user_id INT NOT NULL PRIMARY KEY,
  token CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempt (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(120) NOT NULL,
  ip VARBINARY(16) NULL,
  succeeded TINYINT(1) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (username, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_log (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(80) NOT NULL,
  subject_type VARCHAR(80) NULL,
  subject_id VARCHAR(64) NULL,
  ip VARBINARY(16) NULL,
  ua VARCHAR(255) NULL,
  data JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (action, created_at),
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Core entity x
CREATE TABLE IF NOT EXISTS x (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  status ENUM('draft','open','in_progress','done','archived') NOT NULL DEFAULT 'draft',
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_owner_title (owner_id, title),
  KEY idx_status (status),
  KEY idx_is_deleted (is_deleted),
  FOREIGN KEY (owner_id) REFERENCES user(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS x_comment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  x_id INT NOT NULL,
  user_id INT NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_x (x_id),
  FOREIGN KEY (x_id) REFERENCES x(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS x_file (
  id INT AUTO_INCREMENT PRIMARY KEY,
  x_id INT NOT NULL,
  uploaded_by INT NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_path VARCHAR(255) NOT NULL,
  mime_type VARCHAR(120) NOT NULL,
  size_bytes INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_x (x_id),
  FOREIGN KEY (x_id) REFERENCES x(id) ON DELETE CASCADE,
  FOREIGN KEY (uploaded_by) REFERENCES user(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS x_history (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  x_id INT NOT NULL,
  actor_id INT NOT NULL,
  action VARCHAR(50) NOT NULL,         -- created, updated, status_change, deleted, restored, comment_added, file_added
  field VARCHAR(100) NULL,
  old_value TEXT NULL,
  new_value TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_x (x_id, created_at),
  FOREIGN KEY (x_id) REFERENCES x(id) ON DELETE CASCADE,
  FOREIGN KEY (actor_id) REFERENCES user(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed
INSERT INTO role (id,name) VALUES
(1,'SuperAdmin'),(2,'Admin'),(3,'Manager'),(4,'Benutzer')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO permission (code,label) VALUES
('x.view_any','x: alle sehen'),
('x.view_own','x: eigene sehen'),
('x.create','x: anlegen'),
('x.update','x: ändern'),
('x.delete','x: löschen'),
('user.manage','User verwalten')
ON DUPLICATE KEY UPDATE label=VALUES(label);

-- Rollenrechte
INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT 1, p.id FROM permission p; -- SuperAdmin alles

INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT 2, p.id FROM permission p WHERE p.code <> 'user.manage';

INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT 3, p.id FROM permission p WHERE p.code IN ('x.view_any','x.create','x.update');

INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT 4, p.id FROM permission p WHERE p.code IN ('x.view_own','x.create');
