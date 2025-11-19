-- Core tables
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS venues (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  address VARCHAR(255),
  city VARCHAR(120),
  state VARCHAR(60),
  venue_url VARCHAR(2048),
  image_url VARCHAR(2048),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  venue_id INT NOT NULL,
  user_id INT NOT NULL,
  rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (venue_id),
  INDEX (user_id),
  UNIQUE KEY uniq_user_venue (venue_id, user_id), -- 1 review per user/venue
  FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- (example)
INSERT INTO users (name, email, password_hash) VALUES 
('Alice','alice@example.com', '$2b$10$examplehashedpassword123'),
('Bob','bob@example.com',   '$2b$10$examplehashedpassword456'),
('Charlie','charlie@example.com','$2b$10$examplehashedpassword789');

INSERT INTO venues (name, address, city, state) VALUES
('Hollywood Palladium','6215 Sunset Blvd','Los Angeles','CA'),
('YouTube Theater', '1011 Stadium Dr', 'Inglewood', 'CA'),
('The Forum', '3900 W Manchester Blvd', 'Inglewood', 'CA'),
('The Comedy Store', '8433 Sunset Blvd', 'West Hollywood', 'CA');

INSERT INTO reviews (venue_id, user_id, rating, comment) VALUES
(1, 1, 5, 'Incredible sound and great atmosphere!'),
(2, 2, 4, 'Beautiful new venue, seating could be better.'),
(3, 3, 5, 'Classic LA spot, always delivers.');
