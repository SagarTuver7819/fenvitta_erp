-- Fenvitta Lifesciences LLP - Inventory Software
-- Database schema + master data (MySQL / MariaDB)
-- Create the database first (phpMyAdmin / cPanel), select it, then import this file.

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS users, settings, parties, items, purchases, purchase_items, sales, sale_items, payments,
  stock_ledger, rm_utilities, production_batches, batch_rm, batch_data, batch_outputs,
  dispatches, dispatch_items, packing_labels;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE settings (
  skey VARCHAR(60) PRIMARY KEY,
  svalue TEXT
) ENGINE=InnoDB;

CREATE TABLE parties (
  id INT AUTO_INCREMENT PRIMARY KEY,
  party_type ENUM('Supplier','Customer','Both','Job Worker','Transporter') NOT NULL DEFAULT 'Supplier',
  name VARCHAR(150) NOT NULL,
  address VARCHAR(255) NULL,
  city VARCHAR(80) NULL,
  state VARCHAR(80) NULL DEFAULT 'Gujarat',
  gstin VARCHAR(20) NULL,
  contact_person VARCHAR(100) NULL,
  phone VARCHAR(40) NULL,
  email VARCHAR(120) NULL,
  payment_terms INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (party_type), INDEX (name)
) ENGINE=InnoDB;

CREATE TABLE items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_type ENUM('RM','PM','FG','CONSUMABLE','SERVICE') NOT NULL DEFAULT 'RM',
  code VARCHAR(40) NULL,
  name VARCHAR(150) NOT NULL,
  cas_no VARCHAR(40) NULL,
  grade VARCHAR(60) NULL,
  hsn VARCHAR(20) NULL,
  unit VARCHAR(15) NOT NULL DEFAULT 'kg',
  gst_rate DECIMAL(5,2) NOT NULL DEFAULT 18,
  std_rate DECIMAL(14,2) NOT NULL DEFAULT 0,
  reorder_level DECIMAL(14,3) NOT NULL DEFAULT 0,
  opening_qty DECIMAL(14,3) NOT NULL DEFAULT 0,
  opening_rate DECIMAL(14,2) NOT NULL DEFAULT 0,
  opening_date DATE NULL,
  shelf_life_months INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (item_type)
) ENGINE=InnoDB;

CREATE TABLE purchases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  po_no VARCHAR(40) NULL,
  invoice_no VARCHAR(60) NULL,
  purchase_date DATE NOT NULL,
  party_id INT NOT NULL,
  category VARCHAR(40) NOT NULL DEFAULT 'Raw Material',
  payment_terms INT NOT NULL DEFAULT 0,
  due_date DATE NULL,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  gst_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  freight DECIMAL(14,2) NOT NULL DEFAULT 0,
  other_charges DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  delivery_at VARCHAR(120) NULL,
  transport VARCHAR(120) NULL,
  freight_terms VARCHAR(60) NULL,
  remarks TEXT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (purchase_date), INDEX (party_id)
) ENGINE=InnoDB;

CREATE TABLE purchase_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  purchase_id INT NOT NULL,
  item_id INT NOT NULL,
  description VARCHAR(200) NULL,
  qty DECIMAL(14,3) NOT NULL DEFAULT 0,
  unit VARCHAR(15) NULL,
  rate DECIMAL(14,2) NOT NULL DEFAULT 0,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  gst_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
  gst_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  INDEX (purchase_id), INDEX (item_id)
) ENGINE=InnoDB;

CREATE TABLE sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(40) NOT NULL,
  sale_date DATE NOT NULL,
  party_id INT NOT NULL,
  location VARCHAR(100) NULL,
  agent_name VARCHAR(100) NULL,
  po_ref VARCHAR(60) NULL,
  payment_terms INT NOT NULL DEFAULT 0,
  due_date DATE NULL,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  freight DECIMAL(14,2) NOT NULL DEFAULT 0,
  gst_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  remarks TEXT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (invoice_no), INDEX (sale_date), INDEX (party_id)
) ENGINE=InnoDB;

CREATE TABLE sale_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sale_id INT NOT NULL,
  item_id INT NOT NULL,
  grade VARCHAR(60) NULL,
  qty DECIMAL(14,3) NOT NULL DEFAULT 0,
  unit VARCHAR(15) NULL,
  rate DECIMAL(14,2) NOT NULL DEFAULT 0,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  gst_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
  gst_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  INDEX (sale_id), INDEX (item_id)
) ENGINE=InnoDB;

-- RECEIPT = against sales, PAYMENT = against purchases
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pay_type ENUM('RECEIPT','PAYMENT') NOT NULL,
  ref_id INT NOT NULL,
  pay_date DATE NOT NULL,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  tds DECIMAL(14,2) NOT NULL DEFAULT 0,
  mode VARCHAR(20) NOT NULL DEFAULT 'Bank',
  ref_no VARCHAR(80) NULL,
  remarks VARCHAR(255) NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (pay_type, ref_id)
) ENGINE=InnoDB;

CREATE TABLE production_batches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  batch_no VARCHAR(40) NOT NULL UNIQUE,
  item_id INT NOT NULL,
  plant VARCHAR(100) NULL,
  batch_size DECIMAL(14,3) NOT NULL DEFAULT 0,
  start_date DATE NULL,
  end_date DATE NULL,
  mfg_date DATE NULL,
  exp_date DATE NULL,
  status ENUM('Planned','In Process','Completed','Cancelled') NOT NULL DEFAULT 'Planned',
  remarks TEXT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (item_id), INDEX (status)
) ENGINE=InnoDB;

CREATE TABLE batch_rm (
  id INT AUTO_INCREMENT PRIMARY KEY,
  batch_id INT NOT NULL,
  issue_date DATE NOT NULL,
  item_id INT NOT NULL,
  qty DECIMAL(14,3) NOT NULL DEFAULT 0,
  rate DECIMAL(14,2) NOT NULL DEFAULT 0,
  remarks VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (batch_id), INDEX (item_id)
) ENGINE=InnoDB;

CREATE TABLE batch_data (
  id INT AUTO_INCREMENT PRIMARY KEY,
  batch_id INT NOT NULL,
  entry_date DATE NOT NULL,
  stage VARCHAR(80) NULL,
  activity VARCHAR(150) NULL,
  start_time TIME NULL,
  end_time TIME NULL,
  parameter VARCHAR(100) NULL,
  observed_value VARCHAR(100) NULL,
  done_by VARCHAR(80) NULL,
  remarks VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (batch_id)
) ENGINE=InnoDB;

CREATE TABLE batch_outputs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  batch_id INT NOT NULL,
  output_date DATE NOT NULL,
  qty DECIMAL(14,3) NOT NULL DEFAULT 0,
  grade VARCHAR(60) NULL,
  containers INT NOT NULL DEFAULT 0,
  pack_type VARCHAR(60) NULL,
  qc_status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
  remarks VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (batch_id)
) ENGINE=InnoDB;

CREATE TABLE rm_utilities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entry_date DATE NOT NULL,
  item_id INT NOT NULL,
  entry_type VARCHAR(30) NOT NULL,
  qty DECIMAL(14,3) NOT NULL DEFAULT 0,
  rate DECIMAL(14,2) NOT NULL DEFAULT 0,
  area VARCHAR(100) NULL,
  ref_no VARCHAR(60) NULL,
  remarks VARCHAR(255) NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (item_id), INDEX (entry_date)
) ENGINE=InnoDB;

CREATE TABLE dispatches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dispatch_no VARCHAR(40) NOT NULL UNIQUE,
  dispatch_date DATE NOT NULL,
  sale_id INT NULL,
  party_id INT NOT NULL,
  invoice_no VARCHAR(60) NULL,
  destination VARCHAR(150) NULL,
  transporter VARCHAR(120) NULL,
  vehicle_no VARCHAR(40) NULL,
  lr_no VARCHAR(60) NULL,
  driver_name VARCHAR(80) NULL,
  driver_mobile VARCHAR(30) NULL,
  remarks TEXT NULL,
  qr_token VARCHAR(40) NOT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (dispatch_date), INDEX (party_id), INDEX (sale_id)
) ENGINE=InnoDB;

CREATE TABLE dispatch_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dispatch_id INT NOT NULL,
  item_id INT NOT NULL,
  batch_id INT NULL,
  grade VARCHAR(60) NULL,
  qty DECIMAL(14,3) NOT NULL DEFAULT 0,
  packs INT NOT NULL DEFAULT 0,
  pack_type VARCHAR(60) NULL,
  INDEX (dispatch_id), INDEX (batch_id), INDEX (item_id)
) ENGINE=InnoDB;

CREATE TABLE packing_labels (
  id INT AUTO_INCREMENT PRIMARY KEY,
  batch_id INT NOT NULL,
  label_count INT NOT NULL DEFAULT 1,
  start_no INT NOT NULL DEFAULT 1,
  net_wt DECIMAL(10,3) NOT NULL DEFAULT 0,
  tare_wt DECIMAL(10,3) NOT NULL DEFAULT 0,
  pack_type VARCHAR(60) NULL,
  storage VARCHAR(200) NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (batch_id)
) ENGINE=InnoDB;

-- Every stock movement (RM / PM / FG). FG movements carry batch_id.
CREATE TABLE stock_ledger (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trans_date DATE NOT NULL,
  item_id INT NOT NULL,
  batch_id INT NULL,
  ref_type VARCHAR(20) NOT NULL,
  ref_id INT NOT NULL DEFAULT 0,
  qty_in DECIMAL(14,3) NOT NULL DEFAULT 0,
  qty_out DECIMAL(14,3) NOT NULL DEFAULT 0,
  rate DECIMAL(14,2) NOT NULL DEFAULT 0,
  remarks VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (item_id), INDEX (batch_id), INDEX (ref_type, ref_id), INDEX (trans_date)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------
-- Default data
-- ---------------------------------------------------------------
-- Default login: admin / admin123  (change after first login)
INSERT INTO users (name, username, password, role) VALUES
('Administrator', 'admin', '$2y$10$U5PgxkxufaMcw9.IVASCnO0eaE90lPccPjG.hlQWZT13otW.sbwHq', 'admin');

INSERT INTO settings (skey, svalue) VALUES
('company_name', 'Fenvitta Lifesciences LLP'),
('company_tagline', 'The Core of Every Cure'),
('office_address', 'Patel Nagar, Street No. 2, Behind Ramji Mandir, Opp. Divyang Ind., Rajkot 360002, Gujarat, India'),
('mfg_address', 'Survey No. 346/P3, Vill. Kothariya, Hadala-Kothariya Road, Rajkot 360003, Gujarat, India'),
('phone', '+91 9558406695'),
('email', 'fenvitta@fenvitta.com'),
('website', 'www.fenvitta.com'),
('gstin', ''),
('sale_prefix', 'FNV'),
('po_prefix', 'FNV'),
('dispatch_prefix', 'DSP'),
('label_storage', 'Store in a well closed container at a temperature not exceeding 30Â°C. Protect from light & moisture.'),
('po_terms', 'Material shelf life should not be more than 3 months. Invoice, Packing list, LR copy, original COA with sign and stamp must come with material. Packing label must contain Product Name, CAS No., Batch No., Mfg. Date, Exp. Date, Net Wt., Tare Wt., Gross Wt. & QR Code. All terms and conditions as per Rajkot jurisdiction.');

INSERT INTO items (item_type, code, name, cas_no, grade, hsn, unit, gst_rate, std_rate, reorder_level, shelf_life_months) VALUES
('FG','FG001','Sucralfate','54182-58-0','IP/USP','29400000','kg',18,925,500,60),
('FG','FG002','Vonoprazan Fumarate','1260141-27-2','IH','29339900','kg',18,0,0,36),
('FG','FG003','Bempedoic Acid','738606-46-7','IH','29181900','kg',18,0,0,36),
('FG','FG004','Ferric Carboxymaltose','9007-72-1','IH','29400000','kg',18,0,0,36),
('RM','RM001','Pyridine (Fresh)','110-86-1','','29333100','kg',18,338.5,500,0),
('RM','RM002','Pyridine (Recovered)','110-86-1','','29333100','kg',18,0,0,0),
('RM','RM003','Chloro Sulphonic Acid (CSA)','7790-94-5','','28061000','kg',18,81,1000,0),
('RM','RM004','Sucrose [Multi-compendial Grade] #20','57-50-1','','17019990','kg',5,63,500,0),
('RM','RM005','Soda Ash (Sodium Carbonate)','497-19-8','','28362010','kg',18,32,2000,0),
('RM','RM006','Chloroform (Fresh)','67-66-3','','29031300','kg',18,14,1000,0),
('RM','RM007','Chloroform (Recovered)','67-66-3','','29031300','kg',18,0,0,0),
('RM','RM008','Aluminium Chloride (AlCl3)','7446-70-0','','28273200','kg',18,90,1000,0),
('PM','PM001','50 Ltr HDPE Carboy (Open Mouth, Metal Ring)','','','39233090','Nos',18,290,50,0),
('PM','PM002','Fibre Drum 25 kg','','','48191010','Nos',18,0,50,0),
('PM','PM003','Sticker Sheet with Lamination','','','49119990','Nos',18,2,500,0),
('SERVICE','SV001','Transport / Freight','','','996511','Nos',18,0,0,0),
('SERVICE','SV002','Analysis / Testing Charges','','','998346','Nos',18,0,0,0),
('SERVICE','SV003','Job Work Charges','','','998842','kg',18,0,0,0),
('CONSUMABLE','CN001','Machinery / Equipment','','','84798999','Nos',18,0,0,0);

INSERT INTO parties (party_type, name, address, city, state, contact_person, phone, email, payment_terms) VALUES
('Supplier','Pramukh Chemicals','Plot No 3817, Opp. ETL Plant, GIDC Estate','Ankleshwar','Gujarat','','96241 04560','',60),
('Supplier','Rudra Chemicals','','Ankleshwar','Gujarat','','97277 85247','',60),
('Supplier','Nirva Speciality Chemicals','65-Haridham Society, Near Children Park, Ambatalavadi','Surat','Gujarat','','95375 68204','info.nirvachemicals@gmail.com',90),
('Supplier','Dhanraj Sugars Pvt Ltd','238-242, 2nd Floor, Mega Mall','Surendranagar','Gujarat','Mr. Ashik Mirani','98750 89790','support@dhanrajsugar.com',60),
('Supplier','Dev Polymers & Containers','','Surat','Gujarat','','94145 11811','vikaschachan@yahoo.co.in',45),
('Supplier','True Chem Enterprise','Plot No. 1411, Office No S27, Global Trade Center, GIDC','Ankleshwar','Gujarat','Mr. Ankit','74900 56818','contact@truchementerprise.com',60),
('Supplier','Aceto Chem Agencies','212, 2nd Floor, Konark Complex, Nr. Ambalal Park Char Rasta, Water Tank Road, Karelibaug','Vadodara','Gujarat','Mr. Kalpesh Shah','98795 12726','office@acetogroup.com',0),
('Supplier','Shree Bhagavati Pharma Machinery Company','','Bharuch','Gujarat','','70690 01605','contact@bhagwatipharma.co.in',0),
('Supplier','Kavya Chemical','Plot No. 621-P/23, GIDC Panoli, Ankleshwar Panoli Umarwada Alonj Road','Panoli','Gujarat','Mr. Hiren','81406 21181','',60),
('Supplier','Tivan Sciences Pvt Ltd','Plot 1, S.R. 117, Village Amreli, Taluka Padadhari','Rajkot','Gujarat','Mr. Sanjay Biswal','92279 90975','busdev3@tivansciences.com',30),
('Supplier','Accuprec Research Labs','','Ahmedabad','Gujarat','','','',15),
('Transporter','Bhavna Roadways','','','Gujarat','','','',0),
('Job Worker','Altitude Interchem LLP','Plot No 2222, Nr. Luna Chowkadi, GIDC Estate','Panoli','Gujarat','','99096 53330','',0),
('Customer','JRS Oryx Pharma','','Karnal','Haryana','','','',30),
('Customer','Farma Fusion','','Mumbai','Maharashtra','','','',20),
('Customer','RX Pharmachem','','Ahmedabad','Gujarat','','','',30),
('Customer','Sahika Pharma','','Sirmour','Himachal Pradesh','','','',60),
('Both','Believe Pharma Pvt Ltd','','Surat','Gujarat','','','',15),
('Customer','Jain Pharma','','','Gujarat','','','',90),
('Customer','Aurachem Life Sciences','','','Gujarat','','','',100),
('Customer','HL Healthcare','','','Gujarat','','','',39),
('Customer','Ariheet Pharmachem','','','Gujarat','','','',30),
('Customer','Regal Laboratories','','','Gujarat','','','',30),
('Customer','Zigamed','','','Gujarat','','','',30),
('Customer','Mednova Pharma','','','Gujarat','','','',30),
('Customer','Walter Healthcare','','','Gujarat','','','',30),
('Customer','Zee Laboratories','','','Gujarat','','','',30),
('Customer','Shree Raj Chem','','','Gujarat','','','',30),
('Customer','Maan Pharma','','','Gujarat','','','',30),
('Customer','Solunex Healthcare','','','Gujarat','','','',30);
