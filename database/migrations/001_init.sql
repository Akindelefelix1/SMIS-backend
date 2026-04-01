CREATE TABLE IF NOT EXISTS institution (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  address TEXT,
  contact VARCHAR(100),
  logo VARCHAR(100),
  inst_date DATE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `status` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(15) NOT NULL,
  status_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  role_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS academic_year (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(20) NOT NULL UNIQUE,
  academic_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS semester (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(20) NOT NULL UNIQUE,
  sem_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS yearost (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(20) NOT NULL UNIQUE,
  year_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS program (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(20) NOT NULL UNIQUE,
  program_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS title (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(20) NOT NULL,
  title_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS marital_status (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(15) NOT NULL,
  marital_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS staff_type (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(20) NOT NULL,
  staff_type_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS course_duration (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(15) NOT NULL,
  duration_date DATE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bank (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bank_acc VARCHAR(20),
  bank_name VARCHAR(20),
  bank_date DATETIME
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS grades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lower_bound DECIMAL(5,2) NOT NULL,
  upper_bound DECIMAL(5,2) NOT NULL,
  grade VARCHAR(5) NOT NULL,
  gp DECIMAL(5,3) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pass_mark (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pass_mark INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS faculty (
  id INT AUTO_INCREMENT PRIMARY KEY,
  institution_id INT,
  name VARCHAR(200) NOT NULL,
  faculty_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (institution_id) REFERENCES institution(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS department (
  id INT AUTO_INCREMENT PRIMARY KEY,
  faculty_id INT,
  name VARCHAR(200) NOT NULL,
  dept_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (faculty_id) REFERENCES faculty(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS course (
  id INT AUTO_INCREMENT PRIMARY KEY,
  faculty_id INT,
  dept_id INT,
  course_code VARCHAR(20) NOT NULL UNIQUE,
  course_name VARCHAR(200) NOT NULL,
  duration_id INT,
  tuition INT,
  course_unit INT NOT NULL DEFAULT 0,
  course_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (faculty_id) REFERENCES faculty(id),
  FOREIGN KEY (dept_id) REFERENCES department(id),
  FOREIGN KEY (duration_id) REFERENCES course_duration(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS staff (
  staff_id VARCHAR(10) PRIMARY KEY,
  staff_type_id INT,
  staff_name VARCHAR(40) NOT NULL,
  status_id INT,
  staff_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (staff_type_id) REFERENCES staff_type(id),
  FOREIGN KEY (status_id) REFERENCES `status`(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(20) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  user_group VARCHAR(25) NOT NULL,
  faculty_id INT,
  group_id INT,
  status VARCHAR(15) NOT NULL DEFAULT 'active',
  status_id INT,
  date_registered DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (faculty_id) REFERENCES faculty(id),
  FOREIGN KEY (group_id) REFERENCES role(id),
  FOREIGN KEY (status_id) REFERENCES `status`(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS assign_roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  username VARCHAR(10),
  role_id INT NOT NULL,
  function VARCHAR(100),
  assigned_date DATE,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (role_id) REFERENCES role(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admission (
  id INT AUTO_INCREMENT PRIMARY KEY,
  institution_id INT,
  faculty_id INT,
  dept_id INT,
  title_id INT,
  first_name VARCHAR(20) NOT NULL,
  surname VARCHAR(20) NOT NULL,
  nationality VARCHAR(50),
  student_no VARCHAR(15) NOT NULL UNIQUE,
  reg_no VARCHAR(20) NOT NULL UNIQUE,
  academic_year_id INT,
  course_id INT,
  program_id INT,
  sponsor_id INT,
  `year` INT,
  sex ENUM('M','F'),
  dob DATE,
  pob VARCHAR(40),
  marital_status_id INT,
  admission_date DATE,
  admission_time TIME,
  FOREIGN KEY (institution_id) REFERENCES institution(id),
  FOREIGN KEY (faculty_id) REFERENCES faculty(id),
  FOREIGN KEY (dept_id) REFERENCES department(id),
  FOREIGN KEY (title_id) REFERENCES title(id),
  FOREIGN KEY (academic_year_id) REFERENCES academic_year(id),
  FOREIGN KEY (course_id) REFERENCES course(id),
  FOREIGN KEY (program_id) REFERENCES program(id),
  FOREIGN KEY (marital_status_id) REFERENCES marital_status(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registration_deadline (
  id INT AUTO_INCREMENT PRIMARY KEY,
  start_date DATE,
  end_date DATE,
  yearost_id INT,
  sem_id INT,
  academic_year_id INT,
  FOREIGN KEY (yearost_id) REFERENCES yearost(id),
  FOREIGN KEY (sem_id) REFERENCES semester(id),
  FOREIGN KEY (academic_year_id) REFERENCES academic_year(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registration (
  id INT AUTO_INCREMENT PRIMARY KEY,
  academic_year_id INT,
  yearost_id INT,
  sem_id INT,
  reg_no VARCHAR(20) NOT NULL,
  student_no VARCHAR(15) NOT NULL,
  course_id INT NOT NULL,
  course_unit_id INT,
  takes_date DATE NOT NULL DEFAULT (CURRENT_DATE),
  takes_time TIME NOT NULL DEFAULT (CURRENT_TIME),
  FOREIGN KEY (academic_year_id) REFERENCES academic_year(id),
  FOREIGN KEY (yearost_id) REFERENCES yearost(id),
  FOREIGN KEY (sem_id) REFERENCES semester(id),
  FOREIGN KEY (course_id) REFERENCES course(id),
  FOREIGN KEY (course_unit_id) REFERENCES course(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teaches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_unit_id INT NOT NULL,
  staff_id VARCHAR(10) NOT NULL,
  teaches_date DATE,
  FOREIGN KEY (course_unit_id) REFERENCES course(id),
  FOREIGN KEY (staff_id) REFERENCES staff(staff_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_unit_id INT NOT NULL,
  course_id INT NOT NULL,
  yearost_id INT,
  sem_id INT,
  name VARCHAR(25),
  type VARCHAR(12),
  out_of INT,
  exam_date DATE,
  FOREIGN KEY (course_unit_id) REFERENCES course(id),
  FOREIGN KEY (course_id) REFERENCES course(id),
  FOREIGN KEY (yearost_id) REFERENCES yearost(id),
  FOREIGN KEY (sem_id) REFERENCES semester(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS result (
  id INT AUTO_INCREMENT PRIMARY KEY,
  staff_id VARCHAR(10),
  course_id INT NOT NULL,
  course_unit_id INT,
  course_work INT NOT NULL,
  exam INT NOT NULL,
  student_no VARCHAR(10) NOT NULL,
  result_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (staff_id) REFERENCES staff(staff_id),
  FOREIGN KEY (course_id) REFERENCES course(id),
  FOREIGN KEY (course_unit_id) REFERENCES course(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS balance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_no VARCHAR(15),
  reg_no VARCHAR(15),
  due_to_us INT,
  due_to_you INT,
  virtual_payment INT,
  balance_date DATE,
  FOREIGN KEY (student_no) REFERENCES admission(student_no),
  FOREIGN KEY (reg_no) REFERENCES admission(reg_no)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tuition (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_no VARCHAR(15) NOT NULL,
  academic_year_id INT,
  yearost_id INT,
  sem_id INT,
  bank_id INT,
  pay_ship_no VARCHAR(24),
  amount_paid INT,
  pay_date DATE,
  tuition_date DATE,
  tuition_time TIME,
  FOREIGN KEY (student_no) REFERENCES admission(student_no),
  FOREIGN KEY (academic_year_id) REFERENCES academic_year(id),
  FOREIGN KEY (yearost_id) REFERENCES yearost(id),
  FOREIGN KEY (sem_id) REFERENCES semester(id),
  FOREIGN KEY (bank_id) REFERENCES bank(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notification (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_no VARCHAR(15),
  reg_no VARCHAR(12),
  academic_year_id INT,
  yearost_id INT,
  sem_id INT,
  date_sent DATE,
  time_sent TIME,
  status VARCHAR(20),
  FOREIGN KEY (student_no) REFERENCES admission(student_no),
  FOREIGN KEY (reg_no) REFERENCES admission(reg_no),
  FOREIGN KEY (academic_year_id) REFERENCES academic_year(id),
  FOREIGN KEY (yearost_id) REFERENCES yearost(id),
  FOREIGN KEY (sem_id) REFERENCES semester(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS faculty_user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  faculty_id INT,
  dept_id INT,
  staff_type_id INT,
  staff_id VARCHAR(10),
  password VARCHAR(255),
  status_id INT,
  faculty_user_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (faculty_id) REFERENCES faculty(id),
  FOREIGN KEY (dept_id) REFERENCES department(id),
  FOREIGN KEY (staff_type_id) REFERENCES staff_type(id),
  FOREIGN KEY (staff_id) REFERENCES staff(staff_id),
  FOREIGN KEY (status_id) REFERENCES `status`(id)
) ENGINE=InnoDB;
