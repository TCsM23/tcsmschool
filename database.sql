-- ------------------------------------------------------------------------
-- Online Enrollment System — schema + seed data
-- Import in phpMyAdmin (Import tab) or:  mysql -u root < database.sql
--
-- Seeded logins
--   admin      / admin123     (change after first login)
--   jdelacruz  / student123
--   mreyes     / student123
--   asantos    / student123
-- ------------------------------------------------------------------------

DROP DATABASE IF EXISTS enrollment_db;
CREATE DATABASE enrollment_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE enrollment_db;

-- ------------------------------------------------------------------------
-- users: one row per login (admin or student)
-- ------------------------------------------------------------------------
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  fullname      VARCHAR(150) NOT NULL,
  user_type     ENUM('admin','student') NOT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------
-- sections: created by the admin, picked in the Add student form
-- ------------------------------------------------------------------------
CREATE TABLE sections (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(30)       NOT NULL,
  course       VARCHAR(20)       NOT NULL,
  year_level   TINYINT UNSIGNED  NOT NULL,
  min_students SMALLINT UNSIGNED NOT NULL DEFAULT 15,
  max_students SMALLINT UNSIGNED NOT NULL DEFAULT 40,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_section (course, year_level, name),
  CONSTRAINT chk_section_size CHECK (min_students <= max_students),
  CONSTRAINT chk_section_year CHECK (year_level BETWEEN 1 AND 4)
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------
-- students: profile data, 1:1 with a users row
-- ------------------------------------------------------------------------
CREATE TABLE students (
  id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id               INT UNSIGNED NOT NULL UNIQUE,
  -- College
  student_no            VARCHAR(20)  NOT NULL UNIQUE,
  course                VARCHAR(20)  NOT NULL,
  year_level            TINYINT UNSIGNED NOT NULL,
  section_id            INT UNSIGNED NULL,
  -- Personal
  first_name            VARCHAR(60)  NOT NULL,
  middle_initial        VARCHAR(3)   NULL,
  last_name             VARCHAR(60)  NOT NULL,
  gender                ENUM('Male','Female') NOT NULL,
  birth_date            DATE         NOT NULL,
  birth_place           VARCHAR(150) NOT NULL,
  civil_status          ENUM('Single','Married','Widowed','Separated') NOT NULL DEFAULT 'Single',
  home_address          VARCHAR(255) NOT NULL,
  contact_no            VARCHAR(20)  NOT NULL,
  email                 VARCHAR(120) NULL,
  -- Parents & guardian
  father_name           VARCHAR(120) NULL,
  father_occupation     VARCHAR(80)  NULL,
  mother_name           VARCHAR(120) NULL,
  mother_occupation     VARCHAR(80)  NULL,
  guardian_name         VARCHAR(120) NULL,
  guardian_relationship VARCHAR(40)  NULL,
  guardian_contact      VARCHAR(20)  NULL,
  -- Educational background
  elementary_school     VARCHAR(150) NULL,
  elementary_year       YEAR NULL,
  high_school           VARCHAR(150) NULL,
  high_school_year      YEAR NULL,
  last_school           VARCHAR(150) NULL,
  last_school_year      YEAR NULL,
  last_school_address   VARCHAR(255) NULL,
  created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_students_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_students_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  KEY idx_students_name (last_name, first_name)
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------
-- subjects: offered subjects, shown on Curriculum and Enroll
-- ------------------------------------------------------------------------
CREATE TABLE subjects (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code         VARCHAR(20)  NOT NULL UNIQUE,
  title        VARCHAR(150) NOT NULL,
  units        TINYINT UNSIGNED NOT NULL,
  description  TEXT NULL,
  semester     TINYINT UNSIGNED NOT NULL,
  schedule_day VARCHAR(20)  NOT NULL,
  time_start   TIME NOT NULL,
  time_end     TIME NOT NULL,
  room         VARCHAR(30)  NOT NULL,
  instructor   VARCHAR(120) NOT NULL,
  has_lab      TINYINT(1) NOT NULL DEFAULT 0,
  has_tutorial TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT chk_subject_sem   CHECK (semester IN (1,2)),
  CONSTRAINT chk_subject_units CHECK (units BETWEEN 1 AND 10),
  CONSTRAINT chk_subject_time  CHECK (time_start < time_end)
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------
-- enrollments: one submitted assessment form per student per term.
-- Fee columns are a snapshot so old forms still reprint correctly
-- after fees.php changes. A pending form (approved = 0) is replaced
-- if the student re-submits for the same term.
-- ------------------------------------------------------------------------
CREATE TABLE enrollments (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id    INT UNSIGNED NOT NULL,
  school_year   VARCHAR(9)  NOT NULL,
  semester      TINYINT UNSIGNED NOT NULL,
  subject_codes VARCHAR(255) NOT NULL,
  total_units   SMALLINT UNSIGNED NOT NULL,
  tuition_fee   DECIMAL(10,2) NOT NULL,
  lab_fee       DECIMAL(10,2) NOT NULL,
  tutorial_fee  DECIMAL(10,2) NOT NULL,
  fixed_fees    DECIMAL(10,2) NOT NULL,
  total_fee     DECIMAL(10,2) NOT NULL,
  approved      TINYINT(1) NOT NULL DEFAULT 0,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_one_per_term (student_id, school_year, semester),
  CONSTRAINT fk_enroll_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT chk_enroll_sem CHECK (semester IN (1,2))
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------
-- enrollment_subjects: per-subject lines of an enrollment (snapshot)
-- ------------------------------------------------------------------------
CREATE TABLE enrollment_subjects (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  enrollment_id INT UNSIGNED NOT NULL,
  subject_id    INT UNSIGNED NULL,
  code          VARCHAR(20)  NOT NULL,
  title         VARCHAR(150) NOT NULL,
  units         TINYINT UNSIGNED NOT NULL,
  has_lab       TINYINT(1) NOT NULL DEFAULT 0,
  has_tutorial  TINYINT(1) NOT NULL DEFAULT 0,
  fee           DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_es_enrollment FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
  CONSTRAINT fk_es_subject    FOREIGN KEY (subject_id)    REFERENCES subjects(id)    ON DELETE SET NULL,
  UNIQUE KEY uq_es (enrollment_id, code)
) ENGINE=InnoDB;

-- ========================================================================
-- Seed data
-- ========================================================================

INSERT INTO users (id, username, password_hash, fullname, user_type) VALUES
  (1, 'admin',     '$2y$10$FWJnbwNX0M73Ef6hbpnRJeTBPFYxmY37otni64854OFj6urml2eum', 'Registrar Office',      'admin'),
  (2, 'jdelacruz', '$2y$10$GjxLf8GMmIBUog73sX8LOOQ0kVUo0pcwH.Ec8kT7ZjyCE5kYi5f8S', 'Juan P. Dela Cruz',     'student'),
  (3, 'mreyes',    '$2y$10$GjxLf8GMmIBUog73sX8LOOQ0kVUo0pcwH.Ec8kT7ZjyCE5kYi5f8S', 'Maria Clara S. Reyes',  'student'),
  (4, 'asantos',   '$2y$10$GjxLf8GMmIBUog73sX8LOOQ0kVUo0pcwH.Ec8kT7ZjyCE5kYi5f8S', 'Angelo R. Santos',      'student');

INSERT INTO sections (id, name, course, year_level, min_students, max_students) VALUES
  (1, 'A', 'BSIT', 1, 20, 40),
  (2, 'B', 'BSIT', 2, 20, 40),
  (3, 'A', 'BSBA', 1, 20, 45);

INSERT INTO students (
  user_id, student_no, course, year_level, section_id,
  first_name, middle_initial, last_name, gender, birth_date, birth_place, civil_status,
  home_address, contact_no, email,
  father_name, father_occupation, mother_name, mother_occupation,
  guardian_name, guardian_relationship, guardian_contact,
  elementary_school, elementary_year, high_school, high_school_year,
  last_school, last_school_year, last_school_address
) VALUES
  (2, '2026-00101', 'BSIT', 1, 1,
   'Juan', 'P', 'Dela Cruz', 'Male', '2007-03-14', 'Lipa City, Batangas', 'Single',
   '128 Rizal St., Brgy. Sabang, Lipa City, Batangas', '0917 845 2231', 'juan.delacruz@example.com',
   'Pedro Dela Cruz', 'Jeepney Operator', 'Lorna P. Dela Cruz', 'Public School Teacher',
   'Lorna P. Dela Cruz', 'Mother', '0918 223 4410',
   'Sabang Elementary School', 2019, 'Lipa City National High School', 2023,
   'Lipa City Senior High School', 2025, 'Ayala Highway, Brgy. Marawoy, Lipa City, Batangas'),

  (3, '2025-00342', 'BSIT', 2, 2,
   'Maria Clara', 'S', 'Reyes', 'Female', '2006-08-02', 'Tanauan City, Batangas', 'Single',
   'Blk 4 Lot 9, Villa Esperanza Subd., Tanauan City, Batangas', '0927 310 6654', 'mariaclara.reyes@example.com',
   'Roberto Reyes', 'Electrician', 'Cecilia S. Reyes', 'Sari-sari Store Owner',
   'Cecilia S. Reyes', 'Mother', '0927 310 6600',
   'Tanauan Central School', 2018, 'Tanauan City Integrated High School', 2022,
   'Tanauan Institute Senior High', 2024, 'J.P. Laurel Highway, Tanauan City, Batangas'),

  (4, '2026-00117', 'BSBA', 1, 3,
   'Angelo', 'R', 'Santos', 'Male', '2007-11-21', 'Sto. Tomas, Batangas', 'Single',
   '45 Mabini Ave., Poblacion 2, Sto. Tomas, Batangas', '0995 487 1290', 'angelo.santos@example.com',
   'Ramon Santos', 'OFW (Seafarer)', 'Teresita R. Santos', 'Housewife',
   'Corazon Santos', 'Grandmother', '0915 667 0832',
   'Sto. Tomas South Central School', 2019, 'Sto. Tomas National High School', 2023,
   'Sto. Tomas Senior High School', 2025, 'Maharlika Highway, San Antonio, Sto. Tomas, Batangas');

INSERT INTO subjects (code, title, units, description, semester, schedule_day, time_start, time_end, room, instructor, has_lab, has_tutorial) VALUES
  ('IT 101',  'Introduction to Computing',           3, 'Computer fundamentals, hardware and software concepts, and an overview of the IT profession.', 1, 'MWF', '08:00', '09:00', 'Rm 201',  'Engr. Ramil C. Mendoza',  1, 0),
  ('IT 102',  'Computer Programming 1',              3, 'Problem solving and structured programming using C: variables, control flow, functions and arrays.', 1, 'TTh', '09:00', '10:30', 'ComLab 1', 'Ms. Kristine A. Villanueva', 1, 1),
  ('GE 1',    'Understanding the Self',              3, 'Nature of identity and the factors that shape the self, drawn from philosophy, psychology and sociology.', 1, 'MWF', '10:00', '11:00', 'Rm 105',  'Dr. Nelia B. Castillo',    0, 0),
  ('MATH 1',  'Mathematics in the Modern World',     3, 'Patterns, logic, statistics and mathematical problem solving applied to everyday life.', 1, 'TTh', '13:00', '14:30', 'Rm 107',  'Mr. Jerome T. Aquino',     0, 1),
  ('IT 103',  'Computer Programming 2',              3, 'Object-oriented programming in Java: classes, inheritance, collections and file handling.', 2, 'MWF', '08:00', '09:00', 'ComLab 2', 'Ms. Kristine A. Villanueva', 1, 1),
  ('IT 104',  'Introduction to Human-Computer Interaction', 3, 'Usability principles, user research, prototyping and evaluating interface designs.', 2, 'TTh', '10:30', '12:00', 'Rm 203',  'Engr. Ramil C. Mendoza',  0, 0),
  ('GE 5',    'Purposive Communication',             3, 'Writing and speaking for academic, professional and multicultural audiences.', 2, 'MWF', '13:00', '14:00', 'Rm 110',  'Ms. Rowena D. Garcia',     0, 1),
  ('PE 2',    'Rhythmic Activities',                 2, 'Folk and social dance fundamentals for fitness and cultural appreciation.', 2, 'Sat', '08:00', '10:00', 'Gym',     'Mr. Danilo F. Ramos',      0, 0);
