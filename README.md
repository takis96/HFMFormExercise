# HFM Registration and Login

A small PHP 8.4 application built for the HFM back-end exercise. It provides responsive registration and login pages backed by SQLite.

## Features

- Registration with browser and server-side validation
- Automatic calling codes for Cyprus, Greece, and the United Kingdom
- Clear field-level validation messages
- Secure password hashing and prepared database queries
- Login, protected account page, and logout
- CSRF protection and session-ID rotation
- Responsive layout with no front-end build step

## Requirements

- PHP 8.4
- Composer 2
- SQLite 3
- PHP extensions: PDO SQLite and mbstring

## Quick start

From the project directory:

~~~bash
composer install
composer migrate
composer test
composer serve
~~~

Open <http://127.0.0.1:8000/register>.

Register an account first, then use the same email and password at <http://127.0.0.1:8000/login>.

The built-in PHP server is intended for local development. Stop it with Ctrl+C.

## Installing the requirements on Ubuntu 24.04 / WSL (this is the setup I am using)

Ubuntu 24.04 provides PHP 8.3 by default, so this project uses the maintained PHP package repository for PHP 8.4:

~~~bash
sudo apt update
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update

sudo apt install -y \
  php8.4-cli \
  php8.4-sqlite3 \
  php8.4-mbstring \
  php8.4-xml \
  php8.4-curl \
  php8.4-zip \
  composer \
  sqlite3 \
  unzip
~~~

If you want,you can verify the installation:

~~~bash
php --version
php -m | grep -E 'PDO|sqlite|mbstring'
composer --version
sqlite3 --version
~~~

## Validation rules

Registration applies the same important checks in the browser and on the server:

- First and last names must contain more than 3 characters.
- The country must be one of the three supported options.
- The calling code must be numeric and match the selected country.
- Phone numbers must contain 6 to 15 digits.
- Email addresses must have a valid format.
- Passwords must contain at least 8 characters, one capital letter, one number, and one symbol.
- The privacy and terms checkbox must be accepted (but of course there are no actual pages conected).

The plus sign is displayed beside the calling code, while only its numeric value is submitted and stored.

## Database

The SQLite database is created at storage/database/app.sqlite by the migration command. The database file is excluded from version control.

A different location can be supplied with an absolute path:

~~~bash
export DB_DATABASE=/absolute/path/to/app.sqlite
composer migrate
~~~

## Tests

The project intentionally uses a small test runner instead of a testing framework:

~~~bash
composer test
~~~

It checks registration and login validation, SQLite persistence, password hashing, and user lookup.

## Project layout

~~~text
bin/                    Command-line scripts
bootstrap/              Application startup
config/                 Application configuration
database/migrations/    SQLite schema
public/                 Web entry point, CSS, JavaScript, and images
resources/views/        PHP templates
routes/                 Application routes
src/                    Controllers and application classes
storage/database/       Local SQLite database
tests/                  Lightweight test runner
~~~

## Security notes

Server-side validation remains authoritative even though the forms also validate in the browser. Database writes use prepared statements, passwords are hashed with PHP's password API, output is escaped, and state-changing forms use CSRF tokens.
