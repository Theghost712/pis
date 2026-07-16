# PIS - Patient Information System

A PHP-based healthcare management platform for managing patient records, providers, and medical information.

## Requirements

- PHP 8.1+
- MySQL/MariaDB
- Apache with mod_rewrite

## Installation

1. Clone the repository
2. Import the database schema:
   ```bash
   mysql -u pis_user -p pis_production < config/schema.sql
   ```
3. Configure your `.env` file
4. Set proper permissions:
   ```bash
   chown -R www-data:www-data /var/www/pis
   chmod -R 755 /var/www/pis
   ```

## API Endpoints

### Authentication
- `POST /api/auth/login` - Login
- `POST /api/auth/register` - Register
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Get current user

### Patients
- `GET /api/patients` - List patients
- `GET /api/patients/{id}` - Get patient
- `POST /api/patients` - Create patient
- `PUT /api/patients/{id}` - Update patient
- `DELETE /api/patients/{id}` - Delete patient

### Providers
- `GET /api/providers` - List providers
- `GET /api/providers/{id}` - Get provider
- `POST /api/providers` - Create provider
- `PUT /api/providers/{id}` - Update provider
- `DELETE /api/providers/{id}` - Delete provider

### Medical Records
- `GET /api/records` - List records
- `GET /api/records/{id}` - Get record
- `POST /api/records` - Create record
- `PUT /api/records/{id}` - Update record
- `DELETE /api/records/{id}` - Delete record
