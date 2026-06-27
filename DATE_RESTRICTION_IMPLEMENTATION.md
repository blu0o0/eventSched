# Date Restriction Implementation - 6 Days Advance Booking

## Overview
Implemented a restriction that allows users to select reservation dates from today up to 6 days in advance. Dates beyond 6 days cannot be selected.

## Changes Made

### 1. Frontend - CreateReservation.vue
**File:** `ISUionic/src/views/CreateReservation.vue`

- **Date Picker Restriction:**
  - Added `min` attribute: Prevents selection of past dates
  - Added `max` attribute: Limits selection to 6 days from today
  - Code: `min="${new Date().toISOString().split('T')[0]}" max="${new Date(Date.now() + 6 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]}"`

- **Validation Message:**
  - Updated error message from "Date must be in the future" to "Date must be between today and 6 days from now"

### 2. Backend - StoreReservationRequest.php
**File:** `ISUlaravel/app/Http/Requests/StoreReservationRequest.php`

- **Validation Rules:**
  - Changed: `'date' => 'required|date|after:today'`
  - To: `'start_date' => 'required|date|after_or_equal:today|before_or_equal:+6 days'`
  - Allows today's date (after_or_equal)
  - Limits to maximum 6 days ahead (before_or_equal)

### 3. Backend - UpdateReservationRequest.php
**File:** `ISUlaravel/app/Http/Requests/UpdateReservationRequest.php`

- **Validation Rules:**
  - Changed: `'date' => 'nullable|date|after:today'`
  - To: `'start_date' => 'nullable|date|after_or_equal:today|before_or_equal:+6 days'`
  - Same restrictions apply for updates

### 4. Backend - ReservationController.php
**File:** `ISUlaravel/app/Http/Controllers/Api/ReservationController.php`

- **store() method:**
  - Changed: `'date' => $request->date`
  - To: `'start_date' => $request->start_date`

- **update() method:**
  - Changed: `'date' => $request->date`
  - To: `'start_date' => $request->start_date`

### 5. Backend - AdminReservationController.php
**File:** `ISUlaravel/app/Http/Controllers/Admin/AdminReservationController.php`

- **store() method:**
  - Changed: `'date' => $request->date`
  - To: `'start_date' => $request->start_date`

- **update() method:**
  - Changed: `'date' => $request->date`
  - To: `'start_date' => $request->start_date`

### 6. Backend - AdminCalendarController.php
**File:** `ISUlaravel/app/Http/Controllers/Admin/AdminCalendarController.php`

- **events() method:**
  - Changed: `->where('date', '>=', $request->start)`
  - To: `->where('start_date', '>=', $request->start)`
  - Changed: `->where('date', '<=', $request->end)`
  - To: `->where('start_date', '<=', $request->end)`

## Validation Flow

### Frontend Validation
1. Date picker UI restricts selectable dates
2. Min date: Today
3. Max date: Today + 6 days
4. User cannot select dates outside this range

### Backend Validation
1. **Store Reservation:** Validates start_date is today or future, and not more than 6 days ahead
2. **Update Reservation:** Same validation applies
3. Returns 422 error with clear message if validation fails

## Testing Checklist

- [ ] User can select today's date
- [ ] User can select dates 1-6 days from today
- [ ] User CANNOT select dates 7+ days from today (date picker disabled)
- [ ] User CANNOT select past dates (date picker disabled)
- [ ] Backend rejects attempts to submit dates beyond 6 days
- [ ] Backend rejects attempts to submit past dates
- [ ] Error messages display correctly
- [ ] Admin calendar still functions correctly with start_date column

## Migration Notes

The database migration `2026_06_27_070003_rename_date_to_reservations_table.php` has been created to rename the "date" column to "start_date" for consistency. This migration needs to be run:

```bash
php artisan migrate
```

## Benefits

1. **Consistent Naming:** Using "start_date" instead of "date" is more descriptive
2. **Dual Validation:** Both frontend and backend enforce the 6-day limit
3. **User-Friendly:** Clear error messages guide users
4. **Future-Proof:** Easy to adjust the day limit by changing the multiplier (currently 6)

## Notes

- The restriction applies to both new reservations and updates
- Admin users are also subject to the same date restrictions
- The calendar view continues to work with the renamed column
- All references to the old "date" column have been updated to "start_date"