-- schema.sql
CREATE DATABASE IF NOT EXISTS it_management CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE it_management;

-- DEPARTMENT
CREATE TABLE department (
  department_id VARCHAR(50) PRIMARY KEY,
  department_name VARCHAR(100) NOT NULL
);

-- EMPLOYEE
CREATE TABLE employee (
  user_id VARCHAR(50) PRIMARY KEY,
  user_name VARCHAR(200) NOT NULL,
  gender VARCHAR(10),
  department_id VARCHAR(50),
  join_date DATE NULL,
  leave_date DATE NULL,
  remark TEXT,
  CONSTRAINT fk_employee_dept FOREIGN KEY (department_id) REFERENCES department(department_id) ON DELETE SET NULL
);

-- DEVICE (inventory)
CREATE TABLE device (
  device_id VARCHAR(100) PRIMARY KEY,
  device_name VARCHAR(200),
  description VARCHAR(500)
);

-- DEVICE_ASSIGNMENT: many-to-many history / current
CREATE TABLE device_assignment (
  assignment_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id VARCHAR(50),
  device_id VARCHAR(100),
  assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  released_at DATETIME NULL,
  note VARCHAR(255),
  CONSTRAINT fk_assign_user FOREIGN KEY (user_id) REFERENCES employee(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_assign_device FOREIGN KEY (device_id) REFERENCES device(device_id) ON DELETE CASCADE
);

-- DOMAIN_ACCOUNT
CREATE TABLE domain_account (
  user_id VARCHAR(50) PRIMARY KEY,
  domain_user VARCHAR(200),
  domain_password VARCHAR(200),
  CONSTRAINT fk_domain_user FOREIGN KEY (user_id) REFERENCES employee(user_id) ON DELETE CASCADE
);

-- EMAIL_ACCOUNT (company)
CREATE TABLE email_account (
  user_id VARCHAR(50) PRIMARY KEY,
  email VARCHAR(200),
  email_password VARCHAR(200),
  CONSTRAINT fk_email_user FOREIGN KEY (user_id) REFERENCES employee(user_id) ON DELETE CASCADE
);

-- GMAIL_ACCOUNT (personal)
CREATE TABLE gmail_account (
  user_id VARCHAR(50) PRIMARY KEY,
  gmail VARCHAR(200),
  gmail_password VARCHAR(200),
  CONSTRAINT fk_gmail_user FOREIGN KEY (user_id) REFERENCES employee(user_id) ON DELETE CASCADE
);

-- ERP_ACCOUNT
CREATE TABLE erp_account (
  user_id VARCHAR(50) PRIMARY KEY,
  erp_user VARCHAR(200),
  erp_password VARCHAR(200),
  CONSTRAINT fk_erp_user FOREIGN KEY (user_id) REFERENCES employee(user_id) ON DELETE CASCADE
);

-- BPM_ACCOUNT
CREATE TABLE bpm_account (
  user_id VARCHAR(50) PRIMARY KEY,
  bpm_user VARCHAR(200),
  bpm_password VARCHAR(200),
  CONSTRAINT fk_bpm_user FOREIGN KEY (user_id) REFERENCES employee(user_id) ON DELETE CASCADE
);

-- Sample data (based on your examples)
INSERT INTO department VALUES 
('IT','Information Technology'),
('GA','General Affairs'),
('IE','Industrial Engineering'),
('HR','Human Resources'),
('QA','Quality Assurance'),
('COMPLIANCE','Compliance');

INSERT INTO employee (user_id,user_name,gender,department_id,join_date,remark) VALUES
('HGOF366','Chan Vannak','Male','IT','2025-03-10',NULL),
('HGOF322','Sam Sina','Male','IT','2025-11-21','Resigned'),
('HGOF331','Ly Sorith','Male','IT',NULL,NULL),
('HGOF314','Choub Kunthea','Male','COMPLIANCE','2025-11-10','Resigned');

INSERT INTO device VALUES
('KHA124EOJ01002B','Monitor','Dell 24 inch S2421HN'),
('KHA121EOJ00008B','PC','Dell OptiPlex 3080 i3 / 8GB / 240GB SSD'),
('KHA117EOJ04026B','UPS','Power-T I700'),
('KHA124EOJ01001B','Monitor','Dell 24 inch S2421HN'),
('KCA522EOJ00040B','PC','Dell OptiPlex 3080 i3 / 8GB / 240GB SSD'),
('KHA120EOJ04001B','UPS','Santak K1200VA');

INSERT INTO device_assignment (user_id, device_id) VALUES
('HGOF366','KHA124EOJ01002B'),
('HGOF366','KHA121EOJ00008B'),
('HGOF366','KHA117EOJ04026B'),
('HGOF314','KHA124EOJ01001B'),
('HGOF314','KCA522EOJ00040B'),
('HGOF314','KHA120EOJ04001B');

INSERT INTO domain_account VALUES
('HGOF366','vannak','Hs3240605'),
('HGOF331','sorith','Kh3230502'),
('HGOF322','samsina','Kh3230302');

INSERT INTO email_account VALUES
('HGOF366','vannak@kh.handseven.com','h3240605'),
('HGOF331','sorith@kh.handseven.com','h3230502'),
('HGOF322','sina@kh.handseven.com','h3230302');

INSERT INTO gmail_account VALUES
('HGOF366','h7ha.vannak@gmail.com','h3240605'),
('HGOF331','h7hg.sorith@gmail.com','h3230502'),
('HGOF322','h7hg.samsina@gmail.com','h3230302');

INSERT INTO erp_account VALUES
('HGOF366','HGOF366','12345'),
('HGOF331','HGOF331','12345'),
('HGOF322','HGOF322','12345');

INSERT INTO bpm_account VALUES
('HGOF366','HGOF366','HGOF366'),
('HGOF331','HGOF331','HGOF331'),
('HGOF322','HGOF322','HGOF322');
