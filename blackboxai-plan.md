# Edit Plan: Fix /history 500 (Unable to cast value to a decimal)

## Information Gathered
- Error originates from `resources/views/itinerary/history.blade.php` when rendering `{{ $trip->budget }}`.
- `Trip` model casts `budget` as `decimal:2` (strict) → invalid/non-numeric DB values crash with `MathException`.
- `TravelPlannerService` currently saves `budget` directly from the request (`'budget' => $request->budget`) without normalization.
- Existing migrations indicate `budget` was originally `string`, later changed to `decimal(10,2)`; older rows may contain non-numeric content.

## Plan (code changes)
1. **app/Models/Trip.php**
   - Replace strict `budget => 'decimal:2'` cast with a safe accessor (e.g., `getBudgetAttribute`) that parses numeric values and returns `null` or `0` instead of throwing.

2. **app/Services/TravelPlannerService.php**
   - Normalize `$request->budget` before saving (strip currency symbols, commas, whitespace; fallback to `null` if empty).

3. **(Verification)**
   - Re-run the `/history` page to confirm the 500 is gone.

## Dependent Files to be edited
- `app/Models/Trip.php`
- `app/Services/TravelPlannerService.php`

## Followup steps
- If any existing rows still contain bad values, optionally add a one-off DB cleanup/migration to update `trips.budget` to numeric.

## ask_followup_question
Proceed with implementing steps 1 and 2 as described (safe budget parsing + normalization before save)?

