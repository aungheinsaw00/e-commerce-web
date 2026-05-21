# Workflow Guideline

This document outlines the standard development workflow and implementation principles for the CarPart E-Commerce Platform.

## 1. Development Workflow

### Starting the Local Environment
The project relies on Docker (via Laravel Sail). Always develop inside the Sail environment to ensure parity with production dependencies.
1. Open your WSL terminal.
2. Navigate to the project root: `cd /mnt/c/Users/herik/Desktop/e-commerce-web`
3. Bring up the containers: `./vendor/bin/sail up -d`
4. Access the app via `http://localhost`.

### Artisan & Composer Commands
Never run `php artisan` or `composer` directly on your host machine. Always prefix with `./vendor/bin/sail`:
- Adding a package: `./vendor/bin/sail composer require some/package`
- Making a controller: `./vendor/bin/sail artisan make:controller SomeController`
- Running tests: `./vendor/bin/sail test`

### Database Changes
1. Create a migration: `./vendor/bin/sail artisan make:migration create_some_table`
2. Update the corresponding Model and Factory.
3. If necessary, update seeders in `database/seeders/`.
4. Apply the change: `./vendor/bin/sail artisan migrate` (or `migrate:fresh --seed` if destroying data is acceptable).

---

## 2. Implementation Principles

### Thin Controllers, Fat Services
Controllers should only handle HTTP logic (Requests and Responses).
- **Validation**: Use FormRequests or `$request->validate()` for simple rules.
- **Business Logic**: Delegate to Service classes (e.g., `CartService`, `OrderService`, `InventoryService`).
- **Responses**: Return views, redirects, or JSON resources. Do not perform DB transactions directly inside the controller.

### Safe Inventory Management
Inventory is managed strictly through the `InventoryService`. 
- Always use `lockForUpdate()` within a `DB::transaction()` when altering stock.
- Never manipulate the `stock_quantity` or `reserved_quantity` fields directly from a controller. Use the provided service methods (`reserveStock`, `releaseStock`, `deductStock`) to ensure ACID compliance and prevent overselling.

### File Uploads & Duplication Detection
Payment proofs (and other user-uploaded images) must be securely verified.
- The `PaymentService` automatically generates a SHA-256 hash of uploaded files.
- This hash is checked against existing records to prevent users from reusing old payment receipts for new orders.

### Authorization
Do not rely on UI hiding alone to secure routes.
- **Admin Routes**: Must be protected by the `auth` and `is_admin` middleware. Group admin routes under a `prefix('admin')` in `routes/web.php`.
- **User Actions**: Use Laravel Policies (e.g., `OrderPolicy`) to ensure a user can only view or modify their own orders and carts.

### Frontend Integration (Future)
While the current application renders standard Blade stubs, it is structured to easily transition into an API.
- Keep data preparation logically isolated from view rendering.
- When transitioning to React/Vite, replace `return view(...)` in controllers with `return response()->json(...)` or use API Resources.
