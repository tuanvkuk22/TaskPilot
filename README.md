# Project Manager

Project Manager is a simple **Project Management System** built with **Laravel 13**. It is designed for companies to manage users, companies, projects, tasks, roles, and comments in one place.

## Features

* User management
* Role management
* Company management
* Project management
* Task management
* Project members
* Task members
* Comments
* Authentication
* Password reset

## Requirements

Before installing the project, make sure you have:

* PHP 8.4 or higher
* Composer
* MySQL 8.0 or higher
* Git

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd ProjectManager
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Configure environment

Copy the `.env` file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

### 4. Configure the database

Update the database settings in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=projectmanager
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

If MySQL is running in Docker, make sure the host and port match your Docker configuration.

### 5. Run database migrations

```bash
php artisan migrate
```

### 6. Create the storage link

```bash
php artisan storage:link
```

### 7. Start the application

```bash
php artisan serve
```

The application will be available at:

```text
http://127.0.0.1:8000
```

## Testing

Run the test suite with:

```bash
php artisan test
```

## Useful Commands

Clear application caches:

```bash
php artisan optimize:clear
```

Check the current Laravel version:

```bash
php artisan --version
```

Check migration status:

```bash
php artisan migrate:status
```

List application routes:

```bash
php artisan route:list
```

## Project Structure

```text
```

## Main Modules

### Users

Manage users and user information.

### Roles

Manage user roles and permissions.

### Companies

Manage companies within the system.

### Projects

Create and manage projects and assign users to projects.

### Tasks

Create and manage tasks and assign users to tasks.

### Comments

Users can add comments to support communication and collaboration around the project management workflow.

## Database

The project uses MySQL as the primary database.

The main database tables include:


## Development

For local development:

```bash
php artisan serve
```

For frontend assets, install the required dependencies and run the project's configured build commands if applicable.

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
