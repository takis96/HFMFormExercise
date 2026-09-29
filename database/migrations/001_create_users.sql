CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL CHECK (length(first_name) > 3),
    last_name TEXT NOT NULL CHECK (length(last_name) > 3),
    country TEXT NOT NULL,
    country_code TEXT NOT NULL CHECK (
        length(country_code) > 0
        AND country_code NOT GLOB '*[^0-9]*'
    ),
    phone TEXT NOT NULL,
    email TEXT NOT NULL COLLATE NOCASE UNIQUE,
    password_hash TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

