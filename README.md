# CarPart E-Commerce Platform

A production-ready e-commerce platform built with Laravel 11. This project features robust inventory management, a secure pay-by-transfer flow with payment proof verification, and a dedicated admin interface.

## Prerequisites
- **WSL (Windows Subsystem for Linux)** if you are developing on Windows.
- **Docker Desktop** (configured with WSL2 integration).
- **Git**

## Quick Start 

The project relies entirely on Docker. You can run it either from **Windows (PowerShell/CMD)** or **WSL**.

### 1. Start Docker Desktop
Ensure Docker Desktop is running on Windows.

### 2. Start the Containers
**Option A: Using Windows (PowerShell/CMD)**
If you are using a standard Windows terminal, Sail's bash script won't work natively. Use standard Docker Compose commands instead:
```powershell
docker compose up -d
```

**Option B: Using WSL (Ubuntu)**
If you prefer WSL, use the Sail script:
```bash
./vendor/bin/sail up -d
```
> [!WARNING]
> **Docker command not found in WSL?**
> Open Docker Desktop on Windows → Go to **Settings (Gear icon)** → **Resources** → **WSL Integration** → Check the box next to your Ubuntu distribution and click "Apply & Restart".

### 3. Initialize the Database
If this is your first time setting up, or if you need to reset the database, run the migrations and seeders.

**From Windows:**
```powershell
docker compose exec laravel.test php artisan migrate:fresh --seed
```

**From WSL:**
```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

### 4. Access the Application
- **Storefront:** [http://localhost](http://localhost)
- **Admin Dashboard:** [http://localhost/admin](http://localhost/admin)

### Test Accounts
The database seeder automatically creates the following accounts:
- **Admin:** `admin@carpart.test` / `password`
- **Customer:** `user@carpart.test` / `password`

---

## Useful Commands

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
