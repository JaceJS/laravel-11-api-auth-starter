# Laravel 11 API Auth Starter

Welcome to the Laravel 11 API Auth Starter template! This project provides a solid foundation for building API backends with Laravel 11. It comes pre-configured with authentication using Laravel Passport, local development tools with Laravel Sail, and debugging capabilities through Laravel Telescope.

## Authentication Features

This starter template includes a comprehensive authentication system with the following capabilities:

-   Register
-   Login
-   Verify Email
-   Resend Email Verification
-   Send Reset Password Link
-   Reset Password

## Getting Started

### 1. Clone the Repository

### 2. Install Dependencies via Composer

```bash
composer install
```

### 3. Setup the Environment Variables

Copy the `.env.example` file to create a new `.env` file:

```bash
cp .env.example .env
```

Then, configure the database connection in the `.env` file. Make sure the `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` are set up correctly.

If you need to change the database setup later, it's essential to first bring down the containers and remove the volumes with the following command:

```bash
./vendor/bin/sail down -v
```

After that, modify the `.env` file with the correct database credentials.

### 5. Start the Sail Containers

After configuring the `.env` file, you can start the application with Laravel Sail:

```bash
./vendor/bin/sail up -d
```

### 6. Generate Application Key

```bash
./vendor/bin/sail artisan key:generate
```

### 7. Migrating the Database

Run the migrations to set up the database structure:

```bash
./vendor/bin/sail artisan migrate
```

### 8. Setup Laravel Passport

Laravel Passport requires you to create encryption keys for API authentication. Run the following commands to generate the keys and create a personal access client:

```bash
./vendor/bin/sail artisan passport:keys
```

```bash
./vendor/bin/sail artisan passport:client --personal
```

When you run the `passport:client --personal` command, it will output a `client_id` and a `client_secret`. You need to copy those values and add them to your `.env` file as follows:

```bash
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=<your_client_id_here>
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=<your_client_secret_here>
```

For example:

```bash
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=9d5a22a6-89a6-4b6b-9ffd-6f305183a954
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=eDLszP4aksy7IUvRf27Y516wSk9sVusuKEK8zQtN
```

## Debugging with Laravel Telescope

Laravel Telescope is a debugging tool that provides insights into your application's requests, exceptions, database queries, and more. You can access Telescope by visiting:

```bash
http://localhost/telescope
```
