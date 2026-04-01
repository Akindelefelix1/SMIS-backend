CREATE OR REPLACE VIEW view_course_unit AS
SELECT
  r.id AS takes_id,
  ay.name AS academic_year,
  y.name AS year_of_study,
  s.name AS semester,
  r.reg_no,
  r.student_no,
  c.course_name AS course,
  c.course_code AS course_unit,
  r.takes_date
FROM registration r
JOIN academic_year ay ON ay.id = r.academic_year_id
JOIN yearost y ON y.id = r.yearost_id
JOIN semester s ON s.id = r.sem_id
JOIN course c ON c.id = r.course_id;

CREATE OR REPLACE VIEW view_staff AS
SELECT
  st.staff_id,
  stf.name AS staff_type,
  st.staff_name,
  COALESCE(sts.name, '') AS status,
  st.staff_date
FROM staff st
LEFT JOIN staff_type stf ON st.staff_type_id = stf.id
LEFT JOIN `status` sts ON st.status_id = sts.id;

CREATE OR REPLACE VIEW view_teachers AS
SELECT
  t.id,
  c.course_name AS subject,
  s.staff_name AS staff,
  t.teaches_date AS date
FROM teaches t
JOIN course c ON t.course_unit_id = c.id
JOIN staff s ON t.staff_id = s.staff_id;

CREATE OR REPLACE VIEW view_users AS
SELECT
  id,
  username,
  password,
  user_group,
  status,
  date_registered
FROM users;
