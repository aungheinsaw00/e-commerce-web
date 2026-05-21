# CarPart E-Commerce Platform

A production-ready e-commerce platform built with Laravel 11. This project features robust inventory management, a secure pay-by-transfer flow with payment proof verification, and a dedicated admin interface.

## Prerequisites
- **WSL (Windows Subsystem for Linux)** running Ubuntu (or your preferred distro).
- **Docker Desktop** (configured with WSL2 integration).
- **Git**

## Quick Start (WSL-Based)

The project relies entirely on Docker via Laravel Sail. To ensure maximum performance and avoid permission issues, store your project directly in the Linux filesystem (e.g., `~/your-project-folder`) and run all commands from your WSL terminal.

### 1. Start the Containers
Open your WSL terminal, navigate to your project directory, and run:
```bash
./vendor/bin/sail up -d
```
*(This starts the Laravel application, MySQL, and Redis in the background.)*

### 2. Initialize the Database
If this is your first time setting up, or if you need to reset the database, run the migrations and seeders:
```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

### 3. NPM Dependencies
Ensure your frontend assets are installed and built (run this natively in WSL, NOT via Windows CMD):
```bash
npm install
npm run build
```

### 4. Access the Application
- **Storefront:** [http://localhost](http://localhost)
- **Admin Dashboard:** [http://localhost/admin](http://localhost/admin)

### Test Accounts
The database seeder automatically creates the following accounts:
- **Admin:** `admin@carpart.test` / `password`
- **Customer:** `user@carpart.test` / `password`

---

## Useful Commands (Run from WSL)

Run the automated test suite:
```bash
./vendor/bin/sail test
```

Access the application container's shell:
```bash
./vendor/bin/sail shell
```

View application logs:
```bash
./vendor/bin/sail logs -f
```

Stop the environment:
```bash
./vendor/bin/sail stop
```
