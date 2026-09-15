CREATE DATABASE IF NOT EXISTS fastfood CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fastfood;

CREATE TABLE role (
  roleID INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL UNIQUE,
  description VARCHAR(120) NOT NULL,
  ratehour DECIMAL(7,2) NOT NULL DEFAULT 0
);

CREATE TABLE staff (
  staffID INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  address VARCHAR(100) NOT NULL,
  dateOfBirth DATE NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  mob VARCHAR(20) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  roleID INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_staff_role FOREIGN KEY (roleID) REFERENCES role(roleID)
);

CREATE TABLE roster (
  rosterID INT AUTO_INCREMENT PRIMARY KEY,
  dateTimeFrom DATETIME NOT NULL,
  dateTimeTo DATETIME NOT NULL,
  notes TEXT
);

CREATE TABLE rosterrole (
  rosterRoleID INT AUTO_INCREMENT PRIMARY KEY,
  qty INT NOT NULL,
  rosterID INT NOT NULL,
  roleID INT NOT NULL,
  CONSTRAINT fk_rosterrole_roster FOREIGN KEY (rosterID) REFERENCES roster(rosterID),
  CONSTRAINT fk_rosterrole_role FOREIGN KEY (roleID) REFERENCES role(roleID),
  UNIQUE KEY uq_roster_role (roleID, rosterID)
);

CREATE TABLE availability (
  availID INT AUTO_INCREMENT PRIMARY KEY,
  staffID INT NOT NULL,
  rosterID INT NOT NULL,
  CONSTRAINT fk_availability_staff FOREIGN KEY (staffID) REFERENCES staff(staffID),
  CONSTRAINT fk_availability_roster FOREIGN KEY (rosterID) REFERENCES roster(rosterID),
  UNIQUE KEY uq_staff_roster (staffID, rosterID)
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  description TEXT
);

INSERT INTO role (roleID, name, description, ratehour) VALUES
  (1, 'Administrator', 'System administration', 0),
  (2, 'Manager', 'Store management', 0),
  (3, 'Staff', 'General staff member', 0);

INSERT INTO roster (dateTimeFrom, dateTimeTo, notes) VALUES
  ('2026-10-01 08:00:00', '2026-10-01 17:00:00', 'Day shift'),
  ('2026-10-01 17:00:00', '2026-10-01 22:00:00', 'Evening shift');

INSERT INTO rosterrole (qty, rosterID, roleID) VALUES
  (2, 1, 3),
  (2, 2, 3);

INSERT INTO products (name, price, description) VALUES
  ('Classic Burger', 12.50, 'Burger with salad and house sauce'),
  ('Hot Chips', 6.00, 'Crispy seasoned chips');
