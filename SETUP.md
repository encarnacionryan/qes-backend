# Q.E.S Backend — Sprint 0 Setup Guide

> **Update (post-Sprint 0, part 1):** exam access is no longer gated by
> class enrollment. Teachers now host an Exam as an **ExamSession** (public
> or private + password), and any logged-in student can browse and join
> open sessions server-wide. Classes still exist for rostering, but the old
> "publish exam to class with a time window" flow (`exam_class` pivot) is
> superseded by sessions for access control. See `app/Models/ExamSession.php`
> and `app/Http/Controllers/Api/V1/SessionController.php`.

> **Update (post-Sprint 0, part 2): now a PWA — `qes-mobile` is retired.**
> Students now use the *same* Inertia web app as teachers — installable as
> a Progressive Web App on desktop, tablet, or phone. The separate React
> Native app (`qes-mobile`) is retired; no further work should go into it.
> The Sanctum token API under `/api/v1` is left in place for now since
> removing it isn't necessary, but it's unused by the current product —
> treat it as dead code rather than a maintained interface. New
> student-facing pages live under `routes/web.php`'s `student.*` route
> group, mirroring the same browse → join → take-exam → score flow the API
> had. See the PWA setup steps (Step 7) below.

This scaffold was generated outside a live PHP environment, so it's a set of
**real Laravel files to drop into a fresh install**, not a runnable project on
its own. Run these commands locally in VS Code (requires PHP 8.2+, Composer,
and Node installed — MySQL is provided by XAMPP, see step 0).

## 0. Database via XAMPP (option 1: XAMPP for the database only)

This project uses XAMPP purely to run MariaDB (MySQL-compatible) locally —
Laravel itself still runs via `php artisan serve`, not through Apache. This
keeps the normal Laravel dev workflow (hot reload, artisan commands) while
letting XAMPP's control panel be the one-click way to get a database running.

1. Install XAMPP if you haven't: https://www.apachefriends.org
2. Open the **XAMPP Control Panel** and click **Start** next to **MySQL**.
   (You do not need to start Apache — Laravel's own server handles that.)
3. Open **phpMyAdmin** at `http://localhost/phpmyadmin`, go to the
   **Databases** tab, and create a database named `qes` with collation
   `utf8mb4_unicode_ci`.
4. Leave the MySQL root password blank (XAMPP's default) — this is reflected
   in `.env.example` below. Fine for local/classroom-network development;
   don't reuse this blank-password setup on anything internet-facing.

When you later move toward classroom deployment, you can revisit running
Laravel through XAMPP's Apache directly (option 2) — for now this keeps
things simple while you build out Sprint 1 onward.

## 1. Create the base Laravel project

```bash
composer create-project laravel/laravel qes-backend
cd qes-backend
```

## 2. Install Inertia (web, React) and Sanctum (mobile API — now unmaintained, see note above)

```bash
composer require inertiajs/inertia-laravel
php artisan inertia:middleware
```

```bash
npm install @inertiajs/react react react-dom
npm install -D @vitejs/plugin-react
```

Frontend stack is **React** (not Vue) — `vite.config.js` and
`resources/js/app.jsx` in this scaffold are already wired for
`@inertiajs/react` + `@vitejs/plugin-react`. If you used a Laravel starter
kit (Breeze/Jetstream) that scaffolded Vue instead, remove its `vue()` Vite
plugin and `@inertiajs/vue3` package to avoid the two conflicting.

```bash
php artisan install:api        # publishes Sanctum config + migration
composer require laravel/sanctum
```

## 3. Copy in these generated files

Copy each file below into the matching path in your fresh `qes-backend/` project
(overwriting the defaults where they already exist):

```
database/migrations/2026_07_14_000001_create_users_table.php
database/migrations/2026_07_14_000002_create_personal_access_tokens_table.php
database/migrations/2026_07_14_000003_create_school_classes_table.php
database/migrations/2026_07_14_000004_create_enrollments_table.php
database/migrations/2026_07_14_000005_create_exams_table.php
database/migrations/2026_07_14_000006_create_exam_class_table.php
database/migrations/2026_07_14_000007_create_questions_table.php
database/migrations/2026_07_14_000008_create_choices_table.php
database/migrations/2026_07_14_000009_create_submissions_table.php
database/migrations/2026_07_14_000010_create_answers_table.php
database/migrations/2026_07_14_000011_create_scores_table.php
database/migrations/2026_07_14_000012_create_leaderboard_entries_table.php
database/migrations/2026_07_14_000013_create_account_action_logs_table.php
database/migrations/2026_07_16_000001_create_exam_sessions_table.php
database/migrations/2026_07_16_000002_add_exam_session_id_to_submissions_table.php
database/migrations/2026_08_09_000001_fix_account_action_logs_foreign_keys.php
database/migrations/2026_08_15_000001_add_shuffle_questions_to_exams_table.php
database/migrations/2026_08_15_000002_add_schedule_to_exam_sessions_table.php

app/Models/User.php               (replace the default one)
app/Models/SchoolClass.php
app/Models/Enrollment.php
app/Models/Exam.php
app/Models/Question.php
app/Models/Choice.php
app/Models/Submission.php
app/Models/Answer.php
app/Models/Score.php
app/Models/LeaderboardEntry.php
app/Models/AccountActionLog.php
app/Models/ExamSession.php

app/Services/GradingService.php   (new folder: app/Services)
app/Services/LeaderboardService.php
app/Services/AnalyticsService.php

tests/Feature/GradingServiceTest.php
tests/Feature/LeaderboardServiceTest.php
tests/Feature/Auth/AuthenticationTest.php  (replaces the stock file)
tests/Feature/Auth/RegistrationTest.php    (replaces the stock file)

app/Http/Controllers/Web/AuthController.php
app/Http/Controllers/Web/DashboardController.php
app/Http/Controllers/Web/SchoolClassController.php
app/Http/Controllers/Web/ExamController.php
app/Http/Controllers/Web/QuestionController.php
app/Http/Controllers/Web/ExamSessionController.php
app/Http/Controllers/Web/Student/SessionController.php
app/Http/Controllers/Web/Student/SubmissionController.php
app/Http/Controllers/Web/Student/LeaderboardController.php
app/Http/Controllers/Web/Admin/TeacherAdminController.php
app/Http/Controllers/Api/V1/AuthController.php
app/Http/Controllers/Api/V1/ClassController.php
app/Http/Controllers/Api/V1/SessionController.php
app/Http/Controllers/Api/V1/SubmissionController.php
app/Http/Controllers/Api/V1/LeaderboardController.php

public/manifest.json
public/sw.js
resources/js/pwa.js
resources/js/app.jsx               (replaces the default app.js/app.jsx from the starter)
resources/js/bootstrap.js
resources/css/app.css
resources/views/app.blade.php      (replaces the default one)
vite.config.js                     (replaces the default one — configured for React)

resources/js/Pages/Auth/Login.jsx
resources/js/Pages/Auth/Register.jsx
resources/js/Pages/Dashboard.jsx
resources/js/Pages/Classes/Index.jsx
resources/js/Pages/Classes/Create.jsx
resources/js/Pages/Classes/Show.jsx
resources/js/Pages/Classes/Edit.jsx
resources/js/Pages/Classes/ImportResults.jsx
resources/js/Pages/Exams/Index.jsx
resources/js/Pages/Exams/Create.jsx
resources/js/Pages/Exams/Edit.jsx
resources/js/Pages/Exams/Sessions.jsx
resources/js/Pages/Exams/Leaderboard.jsx
resources/js/Pages/Exams/Analytics.jsx
resources/js/Pages/Admin/Teachers.jsx
resources/js/Pages/Error.jsx
resources/js/Pages/Student/Sessions/Browse.jsx
resources/js/Pages/Student/Exam/Take.jsx
resources/js/Pages/Student/Exam/Score.jsx
resources/js/Pages/Student/Exam/Leaderboard.jsx
resources/js/Layouts/AuthenticatedLayout.jsx

app/Http/Middleware/EnsureUserHasRole.php
app/Http/Middleware/EnsureUserIsLeadTeacher.php
app/Http/Middleware/HandleInertiaRequests.php
app/Http/Middleware/RedirectIfAuthenticated.php  (replaces the default file
                                                    Laravel already generated
                                                    at this same path — no
                                                    bootstrap/app.php change
                                                    needed, since the `guest`
                                                    alias already points here)

routes/web.php                    (replace the default one)
routes/api.php                    (replace the default one)

.env.example                      (merge with the generated default — keep
                                    APP_KEY blank, it's generated in step 5)
```

> Note: `personal_access_tokens` is normally created by Sanctum's own
> migration (`php artisan install:api` already publishes one) — if you get a
> duplicate-table migration error, just delete the version this scaffold
> provides and keep Sanctum's.

## 4. Register the middleware aliases

In `bootstrap/app.php`, inside `->withMiddleware(function (Middleware $middleware) {...})`, add:

```php
$middleware->alias([
    'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    'lead_teacher' => \App\Http\Middleware\EnsureUserIsLeadTeacher::class,
]);

// Shares auth.user and flash messages with every Inertia page
// (see AuthenticatedLayout.jsx, which reads auth.user from this).
$middleware->web(append: [
    \App\Http\Middleware\HandleInertiaRequests::class,
]);
```

## 5. Configure and migrate

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` are already set correctly for
  a default XAMPP install (`qes` / `root` / blank) if you followed step 0 —
  double check `DB_PORT=3306` matches what XAMPP's MySQL is using (visible
  in the XAMPP Control Panel next to the MySQL row).
- Set `APP_URL` to your machine's **LAN IP** once you're testing on the
  hotspot (not `localhost` — the mobile app and other devices need a
  real reachable address, e.g. `http://192.168.4.1:8000`).

Then:

```bash
php artisan migrate
```

You should see all 13 tables created (`users`, `school_classes`,
`enrollments`, `exams`, `exam_class`, `questions`, `choices`, `submissions`,
`answers`, `scores`, `leaderboard_entries`, `account_action_logs`, plus
Sanctum's `personal_access_tokens`).

## 6. Run it

```bash
php artisan serve --host=0.0.0.0 --port=8000   # --host=0.0.0.0 so it's reachable on the LAN
npm run dev
```

Visit `http://localhost:8000` — you should get Laravel's default page (or the
Breeze login screen, if installed). From here, ticket **QES-7 through QES-13**
(Sprint 1: Authentication) is the next real coding work — the routes in
`routes/web.php` and `routes/api.php` already point at controllers you'll
create for that sprint (`AuthController`, `DashboardController`, etc.).

## 7. PWA: icons and install test

The manifest link, theme-color meta tag, and service worker registration
are already wired into `resources/views/app.blade.php` and
`resources/js/app.jsx` in this scaffold — no manual edits needed there.
Two things left:

**a) Add real icons.** `public/manifest.json` references
`/icons/icon-192.png`, `/icons/icon-512.png`, and two `maskable` variants.
Generate these (e.g. via https://realfavicongenerator.net or any 512×512
PNG logo you have) and drop them in `public/icons/`. The manifest still
works without them, but Chrome/Android won't offer the "Install" prompt
until real icon files exist at those paths.

**b) Test installability.** Run `npm run build` (a production build —
service workers commonly don't activate under `npm run dev`), then visit
the app in Chrome. You should see an install icon in the address bar
(desktop) or an "Add to Home Screen" prompt (Android/iOS Safari).

## 8. Friendly error pages (Sprint 9)

By default, an unhandled exception shows Laravel's raw error page —
completely different visually from the rest of the app, which would be
jarring (and look broken) if it happened mid-exam. `Error.jsx` is already
in this scaffold; wire it up in `bootstrap/app.php`. At the top of the
file, alongside the other `use` statements:

```php
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
```

Then add a `->withExceptions(...)` call to the chain in `bootstrap/app.php`
— it sits alongside the existing `->withMiddleware(...)` call (the one
you already added the `role`/`lead_teacher`/`HandleInertiaRequests`
entries to in Step 4):

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
        if (! config('app.debug') && in_array($response->getStatusCode(), [403, 404, 419, 500, 503])) {
            return Inertia::render('Error', ['status' => $response->getStatusCode()])
                ->toResponse($request)
                ->setStatusCode($response->getStatusCode());
        }

        return $response;
    });
})
```

The `! config('app.debug')` guard is deliberate — in local development
you still want Laravel's normal detailed stack-trace page (`APP_DEBUG=true`
in `.env`), since that's far more useful while you're building. Only when
`APP_DEBUG=false` (how you should run this for an actual exam session —
see Step 9) does the friendly `Error.jsx` page take over. A 419
specifically means a session/CSRF token expired — this can genuinely
happen on a slow-moving exam if `SESSION_LIFETIME` (in `.env`) is shorter
than your longest exam's time limit; if you run exams longer than 2
hours, raise `SESSION_LIFETIME` (in minutes) accordingly.

## 9. Deployment: running this for an actual exam session

This is the non-developer-facing checklist for the day of an exam —
starting the server, verifying the network, and troubleshooting.

**Before class:**
1. Set `.env` to `APP_DEBUG=false` and `APP_ENV=production` (raw stack
   traces should never be shown to students — see Step 8).
2. Set `APP_URL` to this machine's actual LAN IP (not `localhost`) —
   find it with `ipconfig` (Windows) and look for the IPv4 address on
   the Wi-Fi/hotspot adapter, e.g. `http://192.168.43.1:8000`.
3. Build the frontend for production: `npm run build`. Don't run
   `npm run dev` during an actual exam — it's a development server, not
   meant for multiple concurrent real users, and won't have the service
   worker's offline resilience active (see Step 7b).
4. Start XAMPP's MySQL (Control Panel → Start, next to MySQL).
5. Start the Laravel server, bound to all network interfaces so other
   devices on the hotspot can reach it:
   `php artisan serve --host=0.0.0.0 --port=8000`
6. On a second device (phone/tablet), connect to the same Wi-Fi/hotspot
   and visit `http://<the-LAN-IP-from-step-2>:8000` to confirm it's
   actually reachable before students arrive.

**If a device can't connect:**
- Confirm it's on the *same* Wi-Fi network/hotspot as the server, not a
  different one (easy to mix up if the room has multiple networks).
- Windows Firewall commonly blocks incoming connections to a dev server
  by default — allow port 8000 through Windows Defender Firewall →
  Advanced Settings → Inbound Rules → New Rule → Port → TCP 8000.
- Double-check `APP_URL` in `.env` matches the IP you're actually giving
  students — a stale `localhost` value there causes some internal
  redirects to point at the wrong address.

**During the exam:** don't restart the Laravel server or XAMPP's MySQL —
that would drop everyone's sessions and interrupt in-progress exams. If
you must restart for some reason, warn students first; their answers up
to the last autosave (FR-4.5, roughly every 600ms of inactivity while
typing/selecting) are safe in the database regardless.

## 10. Running the automated tests

`tests/Feature/GradingServiceTest.php` and `tests/Feature/LeaderboardServiceTest.php`
cover the two pieces of business logic where a silent bug would be worst:
auto-grading (all 4 question types, plus a regression test for the
skipped-question bug from Sprint 4/5 — see the troubleshooting section
below) and leaderboard ranking (score ordering, the completion-time
tiebreaker, and that a retake updates rather than duplicates an entry).

**Before running them, confirm `phpunit.xml` uses SQLite, not your real
database.** Both test classes use Laravel's `RefreshDatabase` trait,
which wipes the database between tests — safe on a throwaway in-memory
SQLite database, destructive if it's pointed at your actual XAMPP `qes`
database. A fresh `laravel new` install's `phpunit.xml` already includes:

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    ...
</php>
```

If those three lines aren't there, add them before running tests. Then:

```bash
php artisan test
```

No model factories are used in these tests (several models in this
project don't have factory classes defined) — each test builds its own
fixtures directly via `Model::create()` calls, which is a completely
normal alternative to factories for this kind of targeted test.

## Troubleshooting: role-aware redirects and the service worker in dev

Two related bugs, fixed in this version of the scaffold, worth knowing
about if you're debugging something that looks similar:

**"Cannot read properties of undefined (reading 'default')" + a 403 on
`/dashboard` right after registering/logging in as a student.**
Laravel's default `guest` middleware (`RedirectIfAuthenticated`) hardcodes
a redirect to `route('dashboard')` for *anyone* already authenticated,
regardless of role. If a student session was still active and you (or a
tester) revisited `/login` or `/register`, Laravel bounced them into
`/dashboard` before any controller ran — and `dashboard` is teacher-only,
so `role:teacher` middleware returned a plain 403 error page. Inertia then
tried to parse that non-Inertia HTML response as if it were a normal page
visit, which is what threw the `undefined.default` error in the console.
Fixed by replacing `app/Http/Middleware/RedirectIfAuthenticated.php` with
a role-aware version that sends teachers to `/dashboard` and students to
`/student/sessions`, matching the same convention already used in
`AuthController::store`/`register`.

**"Cannot read properties of undefined (reading 'default')" happening
consistently on page navigation, even on a fresh (never-logged-in)
session — not just the already-authenticated case above.**
The original `resolve()` in `app.jsx` used a hand-rolled `import.meta.glob`
lookup that could return `undefined` for a page under some conditions.
Fixed by switching to Laravel's own `resolvePageComponent` helper from
`laravel-vite-plugin/inertia-helpers` — the same pattern Laravel's
official Breeze/Jetstream React starter kits use. No extra install is
needed; `laravel-vite-plugin` is already a dependency of every Laravel
project. If you already copied the old `app.jsx`, replace it with the
current version in this scaffold.

**`sw.js:1 Uncaught TypeError: Failed to convert value to 'Response'` /
"FetchEvent... resulted in a network error".**
The service worker's offline-fallback branch could hand the browser
`undefined` (from a cache miss) instead of a real `Response` object,
which throws. Fixed by returning an explicit fallback `Response` when
nothing is cached. Separately, `registerServiceWorker()` in
`resources/js/pwa.js` now skips registration entirely under
`npm run dev` (`import.meta.env.DEV`) — a service worker intercepting
requests while Vite is hot-reloading modules causes exactly this kind of
stale/broken-fetch confusion. Test offline/PWA behavior with
`npm run build` instead (Step 7b above).

**If you already have a stale service worker registered from earlier
testing**, disabling future registration won't remove it automatically.
In Chrome: DevTools → Application tab → Service Workers → Unregister
(and tick "Update on reload" while developing, or just hard-refresh with
DevTools open and "Disable cache" checked).

**`POST /logout` returning 403 for a student account.**
Logout was originally defined twice: once inside the teacher-only route
group (plain `/logout`) and once inside the student route group
(`/student/logout`, name `student.logout`). `AuthenticatedLayout.jsx`
posts to a hardcoded `/logout` for both roles, so a student's logout
click was hitting the teacher-only route and getting blocked by
`role:teacher` middleware. Fixed by pulling logout out into its own
route group gated only by `auth` (no role restriction) — logging out
isn't something that should ever be role-gated in the first place.

## Troubleshooting: raw abort() calls breaking Inertia's page swap

A recurring bug pattern worth knowing if you add your own restrictions
later: **`abort(422, 'message')` or `abort_unless(..., 403, 'message')`
returns a plain, non-Inertia error page.** Inertia expects every response
to be either a full Inertia-formatted page or a redirect — handed a raw
abort response instead, it fails the page swap silently, logging something
like `Cannot read properties of undefined (reading 'default')` or a bare
`422 (Unprocessable Content)` in the browser console with nothing visible
on screen. This bit both the teacher side (the FR-3.6 exam-lock) and the
student side (joining a closed session, viewing a score before release,
viewing a leaderboard before finishing) before being fixed.

**The fix, applied throughout:** replace the abort with a redirect that
carries a flash message — `redirect()->route(...)->with('error', '...')`
for general failures, or `back()->withErrors([...])` for field-specific
validation failures. `HandleInertiaRequests` shares a generic `error` flash
key (alongside `success`) on every page, and `AuthenticatedLayout.jsx`
renders whichever one is present as a dismissible banner — this is also
what makes every existing `->with('success', ...)` message across the app
(class created, exam updated, session closed, etc.) visible for the first
time; before this fix those were being set server-side but nothing ever
rendered them.

**One case fixed that wasn't just an edge case:** `SubmissionController::
score()` used to `abort_unless($released, 403, ...)` when a teacher had
`show_score_immediately` turned off. Since `submit()` always redirects
straight to this route right after grading, that meant the *normal*
submit flow would 403 on every single submission whenever that setting
was off — not a rare misuse, a guaranteed break. It now redirects to the
session browser with a friendly "your score will be available once the
session closes" message instead.

**Ownership checks were left as hard aborts on purpose** (e.g. a student
requesting someone else's submission by ID) — those are genuine security
boundaries a user shouldn't hit through normal navigation, not flows worth
softening.

## Troubleshooting: true_false/identification questions silently failing to save

If you added questions before this fix and only ever saw MCQ questions
actually appear in the list, this was why: `Exams/Edit.jsx`'s question
form always kept a `choices` array in its state (a leftover default from
the type selector), even for `true_false` and `identification` questions
that don't use choices at all. That stray array — with empty `label`
values — was sent to the backend regardless of type, and Laravel's
`choices.*.label` => `required_with:choices` rule rejected it. The
resulting validation error landed on a nested key (`choices.0.label`)
that the page never checked, so the question just silently failed to
save with no visible error and no console clue either — it looked like
the button did nothing.

**Fixed on both ends:** the frontend now uses Inertia's `transform()` to
strip whichever of `choices`/`answer` doesn't apply before submitting,
and `QuestionController::validated()` now also strips them server-side
before validating, regardless of what the client sends — so a future
frontend bug in this area can't reproduce the same silent failure.

## Troubleshooting: a matching question marked wrong despite a correct answer

Reported after real device testing, and it took three rounds to find the
actual root cause — worth reading in full if you hit something similar,
since the first two fixes were real bugs but NOT what was causing this
specific symptom.

**The actual root cause: a PHP integer/float comparison bug in
`GradingService::gradeMatching`.** PHP's `/` operator returns an int (not
a float) when both operands are integers and divide evenly — e.g. `2/2`
evaluates to `int(1)`, not `float(1.0)`. The correctness check used to be
`$fraction === 1.0` (strict comparison), which is `false` when `$fraction`
happens to be that int(1) — even though it's numerically equal to 1.0.
The exact signature this produces, found by inspecting the `answers`
table directly in phpMyAdmin: `points_earned` computed correctly (full
credit), but `is_correct` stored as `0` (false) on the same row. It only
ever misfires when a matching question is answered *fully* correctly,
which is exactly what both bug reports happened to test. Fixed by
comparing the integer counts directly (`$correctPairs === $choices->count()`)
instead of comparing a division result to a float literal.

A compounding frontend bug in `Score.jsx` made this harder to diagnose:
it hardcoded "0 pt" for any answer where `is_correct` was false, instead
of showing the actual `points_earned` — which would have made partial
credit invisible too, not just this specific bug. Fixed to show the real
point value, with a distinct color for partial credit vs. fully wrong.

**Two other real bugs were found and fixed along the way**, before the
actual root cause was identified — both still valid, worth keeping:

1. **A data-loss bug in the autosave flow (`Take.jsx`).** Every answer
   edit is saved via a 600ms debounced request. `handleSubmit()` used to
   fire the submit request immediately, with no regard for any edit still
   sitting in that debounce window — a student's last edit before
   clicking Submit could be silently dropped. Fixed with
   `flushPendingSaves()`, which cancels every pending debounce timer and
   fires those saves immediately, awaiting completion before submitting.
2. **A too-strict grading rule.** The original `gradeMatching` required
   the submitted pair count to *exactly* equal the choice count, or it
   zeroed the entire question — including pairs that WERE correct. Fixed
   to grade each choice independently via a keyed lookup.

All three are covered by regression tests now in `GradingServiceTest`:
`a_fully_correct_matching_answer_is_marked_correct` (the actual root
cause — deliberately uses 2 choices, since an even split is what
triggers PHP's exact-integer-division behavior),
`a_missing_matching_pair_still_credits_the_pairs_that_were_submitted`,
and `it_awards_partial_credit_for_matching_questions`.

**The broader lesson, if you write more grading logic later**: never
compare a division result to a float literal with `===`. Either compare
the integer counts that produced the fraction directly, or use an
epsilon-tolerant comparison (`abs($a - $b) < 0.0001`) if you must compare
computed floats. This class of bug is easy to miss in code review — it
looks completely correct at a glance — and only surfaces on specific
input values, which is exactly why it survived an earlier round of
"fixes" that were real but didn't address the actual cause.

## Troubleshooting: stock auth tests failing (EmailVerificationTest, ProfileTest, etc.)

`laravel new` generates a set of default test files covering Laravel's
stock Breeze-style auth scaffolding: email verification, a "confirm your
password before this sensitive action" flow, self-service forgot-password
via email, and a self-service profile page. **This app never built any
of those** — intentionally. Per SRS 2.5, there's no internet access on
the local hotspot deployment, so password resets are teacher-initiated
(Sprint 8's admin page), not self-service email links. There's no
profile page or password-confirmation flow either; scope was kept to
what the SRS actually specifies.

**Delete these five files** — they test routes and features that were
deliberately never built, not bugs in what exists:

```
tests/Feature/Auth/EmailVerificationTest.php
tests/Feature/Auth/PasswordConfirmationTest.php
tests/Feature/Auth/PasswordResetTest.php
tests/Feature/Auth/PasswordUpdateTest.php
tests/Feature/ProfileTest.php
```

**`AuthenticationTest.php` and `RegistrationTest.php` are different** —
those test login/logout/registration, which are real, fully-built
features. The stock versions just assume a single generic role (one
`dashboard` redirect, no `role` field on registration, logout redirecting
to `/`), which doesn't match this app's teacher/student role-aware
behavior. **Replace both files’ content** with the versions in this
scaffold rather than deleting them — they cover the same ground,
correctly.

**One real bug was caught while writing the replacement tests**: the
login flow never actually checked `is_active`. That meant Sprint 8's
"Disable" button in the admin page didn't stop a disabled account from
logging in — it only hid the account from some lists. Fixed in
`AuthController::store()`, and `AuthenticationTest`'s
`test_a_disabled_account_cannot_log_in` now guards against a regression.

## Troubleshooting: "SHOW INDEX FROM" breaking every test, not just new ones

If you ran `php artisan test` and saw dozens of unrelated failures (even
Laravel's own default `AuthenticationTest`, `ProfileTest`, etc.) all with
the same `SQLSTATE[HY000]: General error: 1 near "SHOW": syntax error`,
this was why: one migration
(`2026_07_16_000002_add_exam_session_id_to_submissions_table.php`) used a
raw `SHOW INDEX FROM submissions` query to check for an existing index.
That's **MySQL-only syntax** — it doesn't exist in SQLite, which is what
Laravel's default `phpunit.xml` points tests at (`DB_CONNECTION=sqlite`,
`DB_DATABASE=:memory:`). Since `RefreshDatabase` re-runs every migration
before every single test class, one non-portable migration took the
*entire* suite down with it — not just tests that touch submissions or
exam sessions directly.

**Fixed** by switching to `Schema::getIndexes('submissions')`, Laravel's
built-in cross-driver index introspection (added in Laravel 11) — it
returns the same index information regardless of whether the underlying
database is MySQL, SQLite, or Postgres, so the exact same idempotency
checks (has this index already been added, has that one already been
dropped) now work correctly under both your real XAMPP/MySQL database
and the SQLite database the test suite uses. If you write your own
migrations later that need to introspect existing schema state, prefer
`Schema::hasColumn()`, `Schema::hasTable()`, `Schema::getIndexes()`, and
similar `Schema::` facade methods over raw `DB::select("SHOW ...")` or
`information_schema` queries — the former work everywhere, the latter
are usually MySQL-specific and will resurface this exact class of bug.

## Troubleshooting: account_action_logs and cascadeOnDelete

If you're extending the admin/audit-log features, watch out for this
class of bug: the original `account_action_logs` migration used
`cascadeOnDelete()` on both `actor_id` and `target_user_id`. That meant
removing a teacher account (FR-8.2) would silently cascade-delete every
log row referencing that user as either actor or target — including the
very log entry created moments earlier to record the removal itself. An
audit trail that erases the evidence of the action it's auditing defeats
the point.

**Fixed** via a follow-up migration
(`2026_08_09_000001_fix_account_action_logs_foreign_keys.php`): both FKs
now use `nullOnDelete()` instead (the log row survives, the reference
just goes null), and a new `target_label` text column snapshots the
target's name/email at the time of the action, so the log stays
human-readable even after the account is gone. If you add other
FKs pointing at `users` for logging/audit purposes elsewhere, default to
`nullOnDelete()` rather than `cascadeOnDelete()` — cascade is right for
data that's meaningless without its parent (e.g. a Submission without its
Exam), wrong for records whose entire purpose is to outlive the thing
they're about.

## What's implemented vs. still a placeholder

Every controller referenced by the routes now exists with a real
implementation (auth, class CRUD, exam CRUD/publish/duplicate, submission
start/save/submit, grading trigger, leaderboard, and the lead-teacher admin
actions) — this is what fixed the `Invalid route action` error you'd get
from `php artisan migrate` if the classes didn't exist yet, since Laravel
evaluates `routes/web.php`/`routes/api.php` on every artisan command, not
just HTTP requests.

The full loop is now built end to end: register → login → (teacher) create
class/exam/questions → host a session → (student) browse → join →
**take the exam → auto-submit or manual submit → see score**. Two things
worth knowing rather than being surprised by:

1. **Matching questions ask the student to type their answer per row**,
   not pick from a dropdown of right-side terms. The correct `match_value`
   for each choice is deliberately withheld from the student payload
   (it's the answer key), and building a proper shuffled-dropdown UX would
   need a small backend change to expose a safely-shuffled term pool
   separately. Typing per row is fully gradable by the existing
   `GradingService::gradeMatching` logic as-is — just a UX simplification,
   not a functional gap. Worth revisiting later if you want the classic
   two-column matching UI.
2. **Grading bug caught and fixed while wiring this up:** `GradingService`
   originally only graded questions the student had an `Answer` row for.
   A skipped question was silently excluded from `total_points_possible`
   instead of counting as 0 earned out of its full value — meaning the
   max achievable score would shrink for anyone who left something blank.
   Fixed by iterating every question on the exam, not just answered ones.
3. **Leaderboard is live (Sprint 6).** `LeaderboardService` recomputes
   ranks (score descending, completion time as tiebreaker — FR-6.1) every
   time a submission is graded. Teachers see real names always; students
   see anonymized names ("Student #7") when the exam's
   `anonymize_leaderboard` toggle is on, except for their own row, which
   always shows "(you)" so they can find themselves. One entry per
   (exam, student) — a retake, where allowed, replaces the student's prior
   standing rather than adding a second row.
4. **Analytics is live (Sprint 7).** `AnalyticsService` computes class
   average/high/low, a 10-bucket score distribution, and per-question
   percent-correct — all computed on read from graded submissions, not
   stored, since analytics don't need to be "live" the way the leaderboard
   does. Questions under 50% correct are flagged for review (FR-7.3).
5. **Lead-teacher admin is live (Sprint 8).** `Admin/Teachers.jsx`
   (`/admin/teachers`, visible only to the lead teacher — FR-8.1) covers
   creating, disabling, re-enabling, and removing other teacher accounts
   (FR-8.2), resetting any teacher OR student's password (FR-8.3), and a
   recent-activity audit log (FR-8.4). Temp passwords (new account or a
   reset) are shown exactly once in a dedicated banner, since there's no
   email delivery on a local network — the lead teacher has to relay them
   directly. A real bug was caught and fixed while building this: see the
   troubleshooting note below on `account_action_logs`.

Everything from registration through admin management is now built end
to end, and Sprint 9 (QA/deployment hardening) closed out the original
roadmap. Five features were added on top of that, all requiring
`php artisan migrate` for two new migrations (shuffle_questions on
exams, opens_at/closes_at on exam_sessions):

1. **Bulk student import via CSV** — `SchoolClassController::importStudents`,
   a name/email CSV with a header row. New accounts get one-time temp
   passwords shown on a results page; existing student emails just get
   enrolled; a teacher's email or bad rows are reported without failing
   the whole batch.
2. **Randomized question order per student** — `Exam::shuffle_questions`,
   a real anti-cheat measure. Seeded by the submission ID so it's stable
   across a page reload mid-exam but different per student/attempt; also
   shuffles choice order within MCQ/matching questions. Grading is
   entirely unaffected since it operates on `choice_id`, never visual
   order. This also prompted building the first-ever Settings form on
   `Exams/Edit.jsx` — the other 3 exam toggles never had a UI before this.
3. **Gradebook CSV export** — a plain streamed download from the
   leaderboard page, no library needed.
4. **Scheduled exam windows** — optional `opens_at`/`closes_at` on a
   session, deliberately cron-free: `ExamSession::isOpen()` just checks
   the current time against these columns whenever it's called, so
   nothing needs a scheduled command to "flip" a session open or closed.
5. **Matching-question dropdown UX** — replaces the type-your-answer
   simplification from Sprint 4 with a real dropdown, populated from a
   seeded-shuffled pool of the correct terms
   (`SubmissionController::attachMatchOptions`). Revealing the shuffled
   *set* of possible answers is safe — it doesn't reveal which one pairs
   with which row, so the answer key itself never leaves the server.

`php artisan migrate` and `php artisan route:list` should both run cleanly
now, since those only depend on the classes existing, not the Vue pages.
