USE smis;

INSERT INTO `status` (name) VALUES ('active'), ('inactive');
INSERT INTO role (name) VALUES ('admin'), ('student');

INSERT INTO academic_year (name) VALUES ('2025/2026'), ('2026/2027');
INSERT INTO semester (name) VALUES ('First Semester'), ('Second Semester');
INSERT INTO yearost (name) VALUES ('Year 1'), ('Year 2'), ('Year 3'), ('Year 4');
INSERT INTO program (name) VALUES ('B.Sc Computer Science'), ('B.Ed Mathematics');
INSERT INTO title (name) VALUES ('Mr'), ('Ms');
INSERT INTO marital_status (name) VALUES ('Single'), ('Married');
INSERT INTO staff_type (name) VALUES ('Lecturer'), ('Admin');
INSERT INTO course_duration (name) VALUES ('4 Years');
INSERT INTO bank (bank_acc, bank_name, bank_date) VALUES ('0001112223', 'Demo Bank', NOW());

INSERT INTO institution (name, address, contact, logo, inst_date)
VALUES ('Demo University', 'Lagos, Nigeria', '+234000000', NULL, CURRENT_DATE);

INSERT INTO faculty (institution_id, name) VALUES (1, 'Faculty of Science');
INSERT INTO department (faculty_id, name) VALUES (1, 'Computer Science');

INSERT INTO users (username, password, user_group, faculty_id, group_id, status, status_id)
VALUES
  ('admin', 'admin123', 'admin', 1, 1, 'active', 1),
  ('20/1234', 'student123', 'student', 1, 2, 'active', 1);

INSERT INTO staff (staff_id, staff_type_id, staff_name, status_id)
VALUES ('STF001', 1, 'Dr. A. Teacher', 1);

INSERT INTO course (faculty_id, dept_id, course_code, course_name, duration_id, tuition, course_unit)
VALUES
  (1, 1, 'CSC 201', 'Data Structures', 1, 120000, 3),
  (1, 1, 'CSC 203', 'Web Development', 1, 120000, 2),
  (1, 1, 'MTH 201', 'Linear Algebra', 1, 90000, 3),
  (1, 1, 'GST 201', 'Entrepreneurship', 1, 50000, 2);

INSERT INTO grades (lower_bound, upper_bound, grade, gp) VALUES
  (70, 100, 'A', 5.0),
  (60, 69.99, 'B', 4.0),
  (50, 59.99, 'C', 3.0),
  (45, 49.99, 'D', 2.0),
  (40, 44.99, 'E', 1.0),
  (0, 39.99, 'F', 0.0);

INSERT INTO pass_mark (pass_mark) VALUES (40);

INSERT INTO admission (institution_id, faculty_id, dept_id, title_id, first_name, surname, nationality, student_no, reg_no, academic_year_id, course_id, program_id, sponsor_id, `year`, sex, dob, pob, marital_status_id, admission_date, admission_time)
VALUES
  (1, 1, 1, 1, 'John', 'Doe', 'Nigerian', '20/1234', 'REG/CS/2026/014', 1, 1, 1, NULL, 2, 'M', '2002-03-10', 'Lagos', 1, '2026-01-15', '09:00:00');

INSERT INTO registration (academic_year_id, yearost_id, sem_id, reg_no, student_no, course_id, course_unit_id)
VALUES
  (1, 2, 1, 'REG/CS/2026/014', '20/1234', 1, 1),
  (1, 2, 1, 'REG/CS/2026/014', '20/1234', 2, 2);

INSERT INTO result (staff_id, course_id, course_unit_id, course_work, exam, student_no)
VALUES
  ('STF001', 1, 1, 25, 62, '20/1234'),
  ('STF001', 2, 2, 22, 58, '20/1234'),
  ('STF001', 3, 3, 18, 52, '20/1234'),
  ('STF001', 4, 4, 20, 50, '20/1234');

INSERT INTO teaches (course_unit_id, staff_id, teaches_date)
VALUES (1, 'STF001', CURRENT_DATE);
