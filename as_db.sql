CREATE DATABASE IF NOT EXISTS as_db;
USE as_db;

CREATE TABLE building (
  building_id INT AUTO_INCREMENT PRIMARY KEY,
  building_name VARCHAR(50) NOT NULL,
  total_flats INT NOT NULL,
  total_floors INT NOT NULL,
  address VARCHAR(150)
);

CREATE TABLE flat_details (
  flat_id INT AUTO_INCREMENT PRIMARY KEY,
  building_id INT NOT NULL,
  flat_number VARCHAR(10),
  floor_number INT,
  flat_type VARCHAR(10),
  FOREIGN KEY (building_id) REFERENCES building(building_id)
);

CREATE TABLE resident_details (
  resident_id INT AUTO_INCREMENT PRIMARY KEY,
  flat_id INT NOT NULL,
  name VARCHAR(100),
  email VARCHAR(100),
  phone VARCHAR(15),
  FOREIGN KEY (flat_id) REFERENCES flat_details(flat_id)
);

CREATE TABLE family_members (
  member_id INT AUTO_INCREMENT PRIMARY KEY,
  flat_id INT NOT NULL,
  member_name VARCHAR(100),
  relation VARCHAR(50),
  age INT,
  phone VARCHAR(15),
  FOREIGN KEY (flat_id) REFERENCES flat_details(flat_id)
);

CREATE TABLE login_credentials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('Admin','Supervisor','Resident') DEFAULT 'Resident'
);

CREATE TABLE visitor_details (
  visitor_id INT AUTO_INCREMENT PRIMARY KEY,
  visitor_name VARCHAR(100),
  purpose VARCHAR(100),
  flat_id INT,
  checkin_time DATETIME,
  checkout_time DATETIME,
  status ENUM('Pending','Approved','Denied') DEFAULT 'Pending',
  approved_by INT,
  FOREIGN KEY (flat_id) REFERENCES flat_details(flat_id),
  FOREIGN KEY (approved_by) REFERENCES resident_details(resident_id)
);

CREATE TABLE regular_visitors (
  regular_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  type ENUM('Maid','Cook','Gardener','Milk Vendor','Driver','Other'),
  security_code VARCHAR(20) UNIQUE,
  flat_id INT,
  checkin_time DATETIME,
  checkout_time DATETIME,
  FOREIGN KEY (flat_id) REFERENCES flat_details(flat_id)
);

CREATE TABLE maintenance_details (
  maint_id INT AUTO_INCREMENT PRIMARY KEY,
  flat_id INT,
  amount DECIMAL(10,2),
  status ENUM('Paid','Due') DEFAULT 'Due',
  payment_date DATE,
  mode ENUM('Cash','Online','Cheque'),
  FOREIGN KEY (flat_id) REFERENCES flat_details(flat_id)
);

CREATE TABLE staff_details (
  staff_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  designation VARCHAR(100),
  phone VARCHAR(15),
  shift_time VARCHAR(50),
  salary DECIMAL(10,2)
);
