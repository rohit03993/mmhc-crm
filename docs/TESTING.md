# Testing the MMHC CRM

## What can be automated here

- **PHPUnit (Laravel)** runs in the terminal. It does **not** open a browser. It can:
  - Hit URLs (GET/POST) and assert status codes, redirects, session errors.
  - Log in as a user (`$this->actingAs($user)`) and hit protected routes.

- **No browser automation** is set up in this project. To “click through every screen as each role” you need either:
  - Manual testing in a browser, or
  - Adding something like Laravel Dusk / Playwright and writing browser tests.

## Run existing tests

From project root:

```bash
php artisan test
```

Or only smoke tests:

```bash
php artisan test tests/Feature/SmokeTest.php
```

- **SmokeTest** checks: login page 200, register page 200, and that invalid login redirects back with errors.
- **ExampleTest** checks: home page `/` returns 200.  
  If your home page uses the DB and PHPUnit uses SQLite in-memory, that test may fail (e.g. missing tables) unless you run migrations in tests or switch tests to MySQL.

## Running the app and testing manually

1. **Start the app** (from project root):

   ```bash
   php artisan serve
   ```

2. Open `http://127.0.0.1:8000` in your browser.

3. **Test by role** (use the checklist in `docs/FUNCTIONALITY-ANALYSIS-BY-ROLE.md`):

   - **Guest:** Home, Login, Register.
   - **Patient:** Login → Dashboard, Staff list, Book staff, My Requests, Profile, Documents, Plans, Subscriptions.
   - **Nurse/Caregiver:** Login → Staff dashboard, open one assignment, Start service, Complete service, Accept/Reject booking, Rewards, Staff/Subscription referrals, Payment settings, Payment history.
   - **Admin:** Login → Admin dashboard, Users, Service requests (assign, approve payment), Staff payments (list → open form by type → process), Subscriptions, Plans, Subscription settings, Referrals, Rewards, Profiles.

4. **Cross-role:** After staff completes a service, admin approves payment → staff should see “Approved by Admin”. Admin processes staff payment → staff should see it in Payment History.

## Adding more automated tests

To test protected routes (e.g. dashboards) without a browser:

1. In a test, create or fetch a user (e.g. with the correct `role`: patient, nurse, caregiver, admin).
2. Use `$this->actingAs($user)`.
3. Call `$this->get(route('staff.dashboard'))` (or the appropriate route) and assert `assertStatus(200)`.

Example:

```php
use App\Models\Core\User;

public function test_nurse_can_reach_staff_dashboard(): void
{
    $user = User::factory()->create(['role' => 'nurse', 'is_active' => true]);
    $response = $this->actingAs($user)->get(route('staff.dashboard'));
    $response->assertStatus(200);
}
```

For this to work you need:

- Migrations to run in tests (e.g. `RefreshDatabase`), and  
- Either SQLite-compatible migrations or PHPUnit configured to use MySQL for testing.

Your app is built for **MySQL**. The default `phpunit.xml` uses **SQLite in-memory**; some migrations may use MySQL-only features and fail on SQLite. To run a full test suite with the real DB, you can add a `phpunit.mysql.xml` that sets `DB_CONNECTION=mysql` and `DB_DATABASE=your_test_db`, then run:

```bash
php artisan test --configuration=phpunit.mysql.xml
```

(Use a dedicated test database and run migrations on it; do not use production.)

## Summary

| Goal | How |
|------|-----|
| Run automated tests | `php artisan test` (and/or `tests/Feature/SmokeTest.php`) |
| Run the app | `php artisan serve` |
| Test every functionality by role | Manual browser testing + the checklist in `FUNCTIONALITY-ANALYSIS-BY-ROLE.md` |
| Test protected routes in PHPUnit | Add feature tests with `actingAs($user)` and optional MySQL test config |
