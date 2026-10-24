USE college_event_db;

ALTER TABLE events
  ADD COLUMN image_path VARCHAR(255) DEFAULT NULL,
  ADD COLUMN rules_regulation TEXT DEFAULT NULL,
  ADD COLUMN registration_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00;
