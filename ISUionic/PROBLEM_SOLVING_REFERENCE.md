# Project Setup Problem-Solving Reference

## Overview of the Two Projects

```
ion_event/
├── ISUionic-20260526T120529Z-3-001/   → Vue.js + Ionic Frontend
│   └── ISUionic/
└── ISUlaravel-20260526T120557Z-3-001/  → Laravel PHP Backend
    └── ISUlaravel/
```

---

## Problem 1: "Where is the database connected?"

**Question:** In the Ionic project, find where the database is connected.

**Investigation:**
1. Looked at the Ionic frontend file structure — it's a Vue.js + Ionic app, not a backend. It doesn't connect to a database directly.
2. Found `src/config/env.ts` — contains `API_BASE_URL = http://localhost:8000/api`
3. Found `src/api/client.ts` — creates an Axios HTTP client that sends requests to the Laravel API at that URL.
4. Found `src/api/` folder with service files (auth.ts, venues.ts, reservations.ts, etc.) that make API calls.

**Solution:**
The database is **not** connected in the Ionic frontend. The frontend communicates with a Laravel backend via REST API at `http://localhost:8000/api`. The Laravel backend (in the separate `ISUlaravel` folder) is what connects to the database.

The Laravel `.env` file contains the real database connection:
```
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=isularaveladminfinal
DB_USERNAME=root
DB_PASSWORD=
```

---

## Problem 2: "How do I connect the Ionic frontend and the Laravel backend?"

**Question:** There are two folders — how do they connect?

**Solution:**
They are already configured to connect. No code changes needed.

| Component | Configuration | Value |
|-----------|--------------|-------|
| Ionic frontend | `src/config/env.ts` | `API_BASE_URL = http://localhost:8000/api` |
| Laravel backend | `.env` | `APP_URL=http://localhost:8000` |

The frontend's Axios client sends HTTP requests to `http://localhost:8000/api/*` and the Laravel backend's `routes/api.php` defines endpoints that handle those requests and query the database.

**To run both:**
1. Start Laravel backend: `php artisan serve` (runs at localhost:8000)
2. Start Ionic frontend: `npm run dev`

---

## Problem 3: "'php' is not recognized as a command"

**Error:** Running `php artisan serve` gives: *"php is not recognized as the name of a cmdlet, function, script file, or operable program."*

**Investigation:**
1. Checked if PHP is installed using `where php` — not found.
2. Checked `C:\` directory and found `xampp` folder.
3. Checked `C:\xampp\php\php.exe` — confirmed it exists.
4. Ran `C:\xampp\php\php.exe -v` — PHP 8.2.12 is installed and working.

**Root Cause:** PHP is installed via XAMPP but the `C:\xampp\php` folder is **not in the system PATH**, so the terminal doesn't know where to find the `php` command.

**Solution (2 options):**

**Option A (Quick):** Use the full path every time:
```powershell
C:\xampp\php\php.exe artisan serve
```

**Option B (Permanent):** Add PHP to PATH:
1. Open System Properties → Advanced → Environment Variables
2. Under System variables, select `Path` → Edit
3. Click New → Add: `C:\xampp\php`
4. Click OK on all dialogs
5. Restart terminal — then `php -v` works globally

---

## Problem 4: "I have made the database but not its tables, columns and rows"

**Question:** The database exists but has no tables or data.

**Solution:** Laravel uses **migrations** to create tables and **seeders** to add data.

**Step 1 — Check available migrations:**
Listed files in `database/migrations/` — found 13 migration files that define all tables.

**Step 2 — Run migrations (creates tables and columns):**
```powershell
Set-Location "C:\Users\Lenovo Thinkpad\ion_event\ISUlaravel-20260526T120557Z-3-001\ISUlaravel"
C:\xampp\php\php.exe artisan migrate
```
Output: All 13 migrations ran successfully. Tables created:
- users, password_reset_tokens, sessions
- cache, cache_locks
- jobs, job_batches, failed_jobs
- personal_access_tokens
- venues
- reservations
- emergency_reports
- notifications
- Plus modification migrations for adding roles, photo, status, postponement columns

**Step 3 — Check available seeders (for initial data/rows):**
Found 3 seeder files in `database/seeders/`:
- `AdminUserSeeder.php` — creates 4 user accounts
- `VenueSeeder.php` — creates 1 venue
- `DatabaseSeeder.php` — calls all seeders

**Step 4 — Run seeders (adds data rows to tables):**
```powershell
C:\xampp\php\php.exe artisan db:seed
```
Output: AdminUserSeeder and VenueSeeder both ran successfully.

**Result — Seed data added:**

| User Role | Email | Password |
|-----------|-------|----------|
| Administrator | admin@isu.edu.ph | password |
| OSAS | osas@isu.edu.ph | password |
| Main Proponent | proponent@isu.edu.ph | password |
| General User | user@isu.edu.ph | password |

| Venue | Location | Capacity |
|-------|----------|----------|
| Admin Building | Santiago Campus | 50 |

---

## Quick Reference: All Commands Used

```powershell
# 1. Start Laravel backend
Set-Location "C:\Users\Lenovo Thinkpad\ion_event\ISUlaravel-20260526T120557Z-3-001\ISUlaravel"
C:\xampp\php\php.exe artisan serve

# 2. In another terminal, start Ionic frontend
cd "C:\Users\Lenovo Thinkpad\ion_event\ISUionic-20260526T120529Z-3-001\ISUionic"
npm run dev

# 3. Create tables
C:\xampp\php\php.exe artisan migrate

# 4. Add sample data
C:\xampp\php\php.exe artisan db:seed

# 5. Reset everything (fresh tables + data)
C:\xampp\php\php.exe artisan migrate:fresh --seed
``` 

**Important:** Always start XAMPP's MySQL service before running the Laravel backend.

---

## Problem 5: "Add a CAPTCHA API to the login"

**Question:** Add Google reCAPTCHA v2 (checkbox) to both frontend and backend login.

**Solution:** Used Google's test keys for development. 8 files changed total (4 backend, 4 frontend).

### Backend (Laravel) — 4 files changed

| # | File | What was done |
|---|------|---------------|
| 1 | `.env` | Added `RECAPTCHA_SITE_KEY` and `RECAPTCHA_SECRET_KEY` (Google test keys) |
| 2 | `config/services.php` | Added `recaptcha` config section that reads keys from `.env` |
| 3 | `app/Rules/Recaptcha.php` | **Created new file** — Custom Laravel validation rule. Sends the user's token to `https://www.google.com/recaptcha/api/siteverify` with the secret key. Fails if Google says the token is invalid. |
| 4 | `app/Http/Controllers/AuthController.php` | Added `'recaptcha_token' => ['required', 'string', new Recaptcha]` to the login validation. Login now requires a valid reCAPTCHA token. |

### Frontend (Ionic) — 4 files changed

| # | File | What was done |
|---|------|---------------|
| 1 | `src/config/env.ts` | Added `RECAPTCHA_SITE_KEY` export so the frontend knows which site key to use |
| 2 | `src/stores/auth.ts` | Modified `login()` to accept optional `recaptchaToken` parameter and include it in the API call |
| 3 | `src/composables/useAuth.ts` | Modified `login()` to pass `recaptchaToken` through to the auth store |
| 4 | `src/views/Login.vue` | Imported `vue-recaptcha` package. Added the reCAPTCHA checkbox widget between password field and login button. Added event handlers: `onCaptchaVerified` (stores token), `onCaptchaExpired` (clears token), `onCaptchaError` (clears token). Login button is disabled until user completes reCAPTCHA. |

### Package Installed
```bash
npm install vue-recaptcha@2.0.3
```

### How It Works End-to-End
1. User fills in email and password
2. User ticks the "I'm not a robot" checkbox → Google generates a token
3. User clicks Login → frontend sends email, password, AND the reCAPTCHA token to Laravel
4. Laravel receives the request → validates email, password, and the reCAPTCHA token
5. Laravel sends the token to Google's server for verification
6. If Google says the token is valid → login proceeds (user authenticated)
7. If Google says the token is invalid → login is rejected with an error

### For Production
The current keys are **Google test keys** that always pass validation. To use real reCAPTCHA:
1. Go to https://www.google.com/recaptcha/admin
2. Register a new site (reCAPTCHA v2 "I'm not a robot")
3. Replace the keys in both:
   - Laravel `.env`: `RECAPTCHA_SITE_KEY` and `RECAPTCHA_SECRET_KEY`
   - Ionic `src/config/env.ts`: `RECAPTCHA_SITE_KEY`
