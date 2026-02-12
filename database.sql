-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QR Codes Table
CREATE TABLE IF NOT EXISTS qrcodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL COMMENT 'Internal name for the user',
    type VARCHAR(50) NOT NULL COMMENT 'url, text, wifi, vcard, email, etc.',
    content TEXT NOT NULL COMMENT 'The actual data for the QR code',
    settings JSON DEFAULT NULL COMMENT 'Visual settings (colors, logo, correction level)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- VCards / Mini-sites Table
CREATE TABLE IF NOT EXISTS vcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE COMMENT 'URL identifier for the public page',
    name VARCHAR(255) NOT NULL COMMENT 'Internal name',
    profile_data JSON NOT NULL COMMENT 'Structured data: name, title, company, phones, emails, social links',
    theme_settings JSON DEFAULT NULL COMMENT 'Colors, fonts, banner image',
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Signatures Table
CREATE TABLE IF NOT EXISTS signatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL COMMENT 'Internal name',
    data JSON NOT NULL COMMENT 'Contact info and other signature fields',
    template_id VARCHAR(50) DEFAULT 'default' COMMENT 'Identifier for the HTML template used',
    style_settings JSON DEFAULT NULL COMMENT 'Custom colors, font sizes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
