# Anthony's Fast Food Management System

A database-backed staff and roster management application built with PHP and MySQL for a local XAMPP environment.

## Features

- Session-based staff login
- Password hashing and verification
- Role-based access for administrators, managers, and staff
- Staff create, read, update, and delete workflows
- Product create, update, and delete workflows
- Roster and employee availability management
- Prepared SQL statements and server-side input validation
- Responsive Bootstrap interface

## Technology

PHP, MySQL / MariaDB, HTML, CSS, Bootstrap, JavaScript, jQuery, XAMPP

## Run locally with XAMPP

1. Clone this repository into the XAMPP web directory:

       C:\xampp\htdocs\anthony-fastfood-management

2. Start Apache and MySQL in the XAMPP Control Panel.
3. In phpMyAdmin, import database/schema.sql.
4. The default db.php settings use the normal local XAMPP configuration: localhost, root, blank password, and the fastfood database.
5. Create an administrator from PowerShell:

       $env:ADMIN_EMAIL="your-email@example.com"
       $env:ADMIN_PASSWORD="choose-a-strong-password"
       C:\xampp\php\php.exe scripts\create_admin.php

6. Open:

       http://localhost/anthony-fastfood-management/login.php

## Project structure

- login.php and logout.php — authentication lifecycle
- auth.php — authorisation checks
- staff_*.php — staff administration
- product_*.php — product administration
- availability.php — roster availability workflow
- validation.php — reusable server-side validation
- database/schema.sql — reproducible database schema
- scripts/create_admin.php — secure local administrator setup

## Security

This is a learning and portfolio project intended for local use. Before public internet deployment, add CSRF protection, production session-cookie configuration, a web-server environment configuration, automated tests, and a full security review.

No production credentials, environment files, or runtime logs are included.

## Author

Madoka Thomson — Sydney, Australia  
Japanese / English bilingual technical professional
