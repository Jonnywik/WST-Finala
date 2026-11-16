-- AdopIT! - Pet Adoption & Vet Care Registry
-- Import this file in phpMyAdmin (Import tab), or just open setup.php in the browser.
-- WARNING: re-running this file resets the three AdopIT! tables to the sample data.

CREATE DATABASE IF NOT EXISTS adopit_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE adopit_db;

DROP TABLE IF EXISTS adoption_inquiries;
DROP TABLE IF EXISTS vet_logs;
DROP TABLE IF EXISTS pets;

CREATE TABLE pets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pet_code VARCHAR(12) NOT NULL UNIQUE,
  name VARCHAR(60) NOT NULL,
  species VARCHAR(20) NOT NULL,
  breed VARCHAR(60) NOT NULL DEFAULT '',
  age_label VARCHAR(30) NOT NULL DEFAULT '',
  sex VARCHAR(10) NOT NULL DEFAULT 'Unknown',
  vaccination_status VARCHAR(20) NOT NULL DEFAULT 'Pending',
  adoption_status VARCHAR(20) NOT NULL DEFAULT 'Available',
  intake_date DATE NOT NULL,
  notes TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vet_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pet_id INT UNSIGNED NOT NULL,
  care_type VARCHAR(20) NOT NULL,
  procedure_name VARCHAR(100) NOT NULL,
  vet_name VARCHAR(60) NOT NULL DEFAULT '',
  visit_date DATE NOT NULL,
  next_due_date DATE NULL,
  notes TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_vet_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE adoption_inquiries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pet_id INT UNSIGNED NOT NULL,
  applicant_name VARCHAR(80) NOT NULL,
  contact_info VARCHAR(100) NOT NULL,
  living_setup VARCHAR(30) NOT NULL DEFAULT '',
  inquiry_status VARCHAR(20) NOT NULL DEFAULT 'Pending Review',
  inquiry_date DATE NOT NULL,
  remarks TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_inquiry_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pets (id, pet_code, name, species, breed, age_label, sex, vaccination_status, adoption_status, intake_date, notes) VALUES
(1, 'PET-001', 'Bantay', 'Dog', 'Aspin', '2 yrs', 'Male', 'Up to Date', 'Available', '2026-08-14', 'Rescued near the public market. Gentle, leash-trained and great with kids.'),
(2, 'PET-002', 'Mingming', 'Cat', 'Puspin', '8 mos', 'Female', 'Partial', 'Available', '2026-09-02', 'Playful kitten who loves feather toys. Litter-trained.'),
(3, 'PET-003', 'Choco', 'Dog', 'Shih Tzu mix', '4 yrs', 'Male', 'Up to Date', 'Pending Adoption', '2026-07-21', 'Surrendered by a family moving abroad. Calm lap dog.'),
(4, 'PET-004', 'Snowball', 'Rabbit', 'Holland Lop', '1 yr', 'Female', 'Pending', 'Under Vet Care', '2026-10-05', 'Recovering from a mild ear infection. Shy at first.'),
(5, 'PET-005', 'Kiwi', 'Bird', 'Lovebird', '3 yrs', 'Unknown', 'Partial', 'Available', '2026-09-18', 'Found on a balcony, nobody claimed. Whistles in the morning.'),
(6, 'PET-006', 'Luna', 'Cat', 'Siamese mix', '3 yrs', 'Female', 'Up to Date', 'Adopted', '2026-06-11', 'Went home with the Reyes family in October.'),
(7, 'PET-007', 'Pepper', 'Dog', 'Beagle mix', '5 mos', 'Female', 'Partial', 'Available', '2026-10-22', 'High-energy puppy. Needs a home with a yard or daily walks.'),
(8, 'PET-008', 'Tiger', 'Cat', 'Puspin', '2 yrs', 'Male', 'Pending', 'Under Vet Care', '2026-11-03', 'Street rescue with a healing leg wound. Very affectionate.'),
(9, 'PET-009', 'Mochi', 'Other', 'Syrian hamster', '7 mos', 'Male', 'Pending', 'Available', '2026-10-30', 'Comes with his cage and wheel. Most active at night.'),
(10, 'PET-010', 'Bruno', 'Dog', 'Labrador mix', '6 yrs', 'Male', 'Up to Date', 'Available', '2026-05-29', 'Senior sweetheart. House-trained and knows sit, stay and paw.');

INSERT INTO vet_logs (pet_id, care_type, procedure_name, vet_name, visit_date, next_due_date, notes) VALUES
(1, 'Vaccination', 'Anti-rabies + 5-in-1 booster', 'Dr. Santos', '2026-08-20', '2027-08-20', 'No adverse reaction.'),
(1, 'Deworming', 'Broad-spectrum dewormer', 'Dr. Santos', '2026-10-01', '2027-01-01', NULL),
(2, 'Vaccination', '4-in-1 first dose', 'Dr. Lim', '2026-09-10', '2026-11-20', 'Second dose still needed.'),
(3, 'Checkup', 'Pre-adoption wellness exam', 'Dr. Santos', '2026-11-05', NULL, 'Cleared for adoption.'),
(3, 'Grooming', 'Full groom and nail trim', 'Jessa (groomer)', '2026-11-06', NULL, NULL),
(4, 'Medication', 'Ear drops for otitis, 10 days', 'Dr. Lim', '2026-11-08', '2026-11-18', 'Re-check after the course.'),
(5, 'Checkup', 'Avian wellness check', 'Dr. Cruz', '2026-09-25', '2027-03-25', 'Healthy weight and feathers.'),
(6, 'Vaccination', 'Anti-rabies + 4-in-1 booster', 'Dr. Lim', '2026-06-18', '2027-06-18', NULL),
(7, 'Vaccination', '5-in-1 second dose', 'Dr. Santos', '2026-11-02', '2026-11-23', 'Third dose due in three weeks.'),
(8, 'Medication', 'Wound cleaning and antibiotics', 'Dr. Cruz', '2026-11-04', '2026-11-12', 'Wound closing well.'),
(10, 'Checkup', 'Senior blood panel', 'Dr. Santos', '2026-10-14', '2027-04-14', 'Slightly overweight, diet adjusted.');

INSERT INTO adoption_inquiries (pet_id, applicant_name, contact_info, living_setup, inquiry_status, inquiry_date, remarks) VALUES
(3, 'Andrea Villanueva', '0917-555-0142', 'Condo', 'Meet & Greet', '2026-11-07', 'Visit scheduled for Saturday morning.'),
(6, 'Marco Reyes', 'marco.reyes@example.com', 'House with yard', 'Approved', '2026-10-02', 'Home check passed. Adoption completed.'),
(1, 'Jun Dela Cruz', '0918-555-0177', 'House with yard', 'Pending Review', '2026-11-12', 'Has one other dog at home.'),
(7, 'Bea Navarro', 'bea.navarro@example.com', 'Apartment', 'Pending Review', '2026-11-14', 'First-time owner, works from home.'),
(2, 'Carlo Mendoza', '0919-555-0123', 'Apartment', 'Declined', '2026-10-20', 'Building does not allow pets.'),
(10, 'Liza Soriano', '0920-555-0198', 'House with yard', 'Pending Review', '2026-11-15', 'Looking for a calm older dog.');
