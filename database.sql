CREATE DATABASE IF NOT EXISTS college_event_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE college_event_db;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  roll_no VARCHAR(40) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_roll_no (roll_no)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  `date` DATE NOT NULL,
  `time` TIME NOT NULL,
  venue VARCHAR(180) NOT NULL,
  seats_available INT UNSIGNED NOT NULL DEFAULT 0,
  image_path VARCHAR(255) DEFAULT NULL,
  rules_regulation TEXT DEFAULT NULL,
  registration_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (id),
  KEY idx_events_date (`date`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registrations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  event_id INT UNSIGNED NOT NULL,
  registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_registration_user_event (user_id, event_id),
  KEY idx_registrations_event (event_id),
  CONSTRAINT fk_registrations_user
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_registrations_event
    FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO events
  (title, description, `date`, `time`, venue, seats_available, image_path, rules_regulation, registration_fee)
VALUES
  (
    'National Hackathon',
    'A 24-hour team hackathon to build practical solutions for campus life. Mentors, meals, and a demo showcase are included.',
    DATE_ADD(CURDATE(), INTERVAL 14 DAY),
    '09:00:00',
    'Innovation and Incubation Centre',
    120,
    'uploads/events/hackathon-sample.jpg',
    CONCAT(
      'Teams may have 2-4 currently enrolled students.', CHAR(10),
      'Bring your own laptop and charger.', CHAR(10),
      'All code must be created during the event; clearly disclose third-party libraries and APIs.', CHAR(10),
      'Projects must be respectful, legal, and safe. Judges'' decisions are final.'
    ),
    100.00
  ),
  (
    'Cultural Dance Fest',
    'An evening celebrating dance traditions and contemporary performance from across the college community.',
    DATE_ADD(CURDATE(), INTERVAL 21 DAY),
    '17:30:00',
    'Open Air Theatre',
    250,
    'uploads/events/dance-fest-sample.jpg',
    CONCAT(
      'Solo and group entries are welcome.', CHAR(10),
      'Performances must be no longer than 8 minutes, including setup.', CHAR(10),
      'Use only licensed or original music and submit the track before the event.', CHAR(10),
      'Costumes and choreography must be suitable for a campus audience.'
    ),
    150.00
  ),
  (
    'RoboWars',
    'Design, build, and pilot a robot in a supervised arena challenge. Safety checks and a technical briefing are mandatory.',
    DATE_ADD(CURDATE(), INTERVAL 28 DAY),
    '10:00:00',
    'Mechanical Engineering Workshop',
    80,
    'uploads/events/robowars-sample.jpg',
    CONCAT(
      'Teams may have up to 5 students.', CHAR(10),
      'Robots must pass the pre-match inspection and meet the published weight limit.', CHAR(10),
      'Projectiles, flames, liquids, and exposed high-voltage components are prohibited.', CHAR(10),
      'Only event staff may enter the arena during a match; follow all safety instructions.'
    ),
    250.00
  ),
  (
    'Photography Workshop',
    'A hands-on beginner-friendly session covering composition, natural light, and photo storytelling around campus.',
    DATE_ADD(CURDATE(), INTERVAL 35 DAY),
    '14:00:00',
    'Media Lab, Arts Block',
    40,
    'uploads/events/photography-workshop-sample.jpg',
    CONCAT(
      'Please bring a camera or a smartphone with a camera.', CHAR(10),
      'Do not photograph anyone who has not given permission.', CHAR(10),
      'Arrive 10 minutes early; spaces are limited to registered participants.', CHAR(10),
      'The workshop fee includes printed practice sheets but not equipment rental.'
    ),
    0.00
  );
