# Quick Start: Visual Testing Setup

Follow these steps to get the application ready for visual testing.

## 1. Start Laravel Development Server

```bash
cd c:\xampp_2025\htdocs\lamiai-partners\laravel

# Start the development server
php artisan serve
```

**Expected Output:**

```
Laravel development server started:
http://127.0.0.1:8000
```

**Keep this terminal running** while testing.

---

## 2. Ensure Database is Ready

In a new terminal:

```bash
cd c:\xampp_2025\htdocs\lamiai-partners\laravel

# Run all migrations (if not already done)
php artisan migrate --seed

# OR just seed test data if migrations already ran
php artisan db:seed --class=AiFilmmakerProgramSeeder
```

**Expected Output:**

```
AI Filmmaking program and commission rules created successfully!
```

---

## 3. Open Browser

Navigate to:

```
http://localhost:8000
```

You should see the Laravel welcome page or dashboard.

---

## 4. Create Test Users (Easy Way)

### Option A: Use Tinker (Interactive PHP Shell)

```bash
php artisan tinker
```

Then paste this:

```php
// Create Admin User
$admin = \App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password123'),
]);
$admin->assignRole('super_admin');

// Create Customer 1
\App\Models\User::create([
    'name' => 'John Doe',
    'email' => 'john@customer.com',
    'password' => bcrypt('password123'),
]);

// Create Customer 2
\App\Models\User::create([
    'name' => 'Jane Smith',
    'email' => 'jane@customer.com',
    'password' => bcrypt('password123'),
]);

exit;
```

---

## 5. Open Testing Checklist

Keep this file open while testing:

- [VISUAL_TESTING_PLAN.md](./VISUAL_TESTING_PLAN.md)

---

## 6. Start Testing

**Begin with:** [TEST SCENARIO 1: Customer Purchase Without Referral](#test-scenario-1-customer-purchase-without-referral)

Navigate through scenarios in order:

1. ✅ Customer purchase (no referral)
2. ✅ Partner application
3. ✅ Referral purchase (commissions)
4. ✅ Partner dashboard
5. ✅ Admin order management
6. ✅ Admin commission management
7. ✅ Error handling
8. ✅ Filters & search
9. ✅ Mobile responsiveness

---

## Keyboard Shortcuts for Testing

**Chrome DevTools (for mobile testing):**

- `F12` - Open DevTools
- `Ctrl + Shift + M` - Toggle device toolbar (mobile view)
- `Ctrl + Shift + I` - Open Inspector

**Browser Navigation:**

- `Ctrl + L` - Focus address bar
- `Ctrl + K` - Focus search (if available)
- `Alt + Left Arrow` - Back button

---

## Common URLs for Testing

```
Product Page:
http://localhost:8000/product/ai-filmmaking-masterclass

Product with Referral (use actual code):
http://localhost:8000/product/ai-filmmaking-masterclass?ref=RECRUIT001

Partner Application:
http://localhost:8000/partner/apply

Partner Dashboard:
http://localhost:8000/partner/dashboard

Admin Orders:
http://localhost:8000/admin/orders

Admin Commissions:
http://localhost:8000/admin/commissions

Login:
http://localhost:8000/login
```

---

## Test Data to Copy/Paste

```
ADMIN LOGIN:
Email: admin@example.com
Password: password123

CUSTOMER LOGIN:
Email: john@customer.com
Password: password123

PARTNER REFERRAL CODES:
(These are created during testing - note them down)
Example: RECRUIT001
Example: PARTNER001
```

---

## Tips for Efficient Testing

1. **Open multiple browser tabs:**
    - Tab 1: Customer view
    - Tab 2: Partner view
    - Tab 3: Admin view
    - Keep logging in/out as needed

2. **Use incognito mode for clean sessions:**
    - `Ctrl + Shift + N` to open incognito window
    - No previous cookies/sessions interfere

3. **Take screenshots:**
    - `Windows Key + Shift + S` - Snip screenshot tool
    - Screenshot each major milestone
    - Helpful for documentation

4. **Check browser console for errors:**
    - `F12` → Console tab
    - Look for red errors (should be none)

5. **Verify URLs after each action:**
    - Confirm redirect URL matches expected
    - Watch for parameter changes (?ref=, status=, etc.)

---

## Troubleshooting

### "Page not found" or "404"

- [ ] Is Laravel development server running? (`php artisan serve`)
- [ ] Is the URL correct? (check spelling)
- [ ] Did you create the test data with seeder or Tinker?

### "Access Denied" or "403"

- [ ] Are you logged in as the right user?
- [ ] Are you trying to access admin page as regular user?
- [ ] Try logging out and logging back in

### "Database connection failed"

- [ ] Is XAMPP running (MySQL)?
- [ ] Did you run migrations? (`php artisan migrate`)
- [ ] Check `.env` file has correct database credentials

### Commissions not generating

- [ ] Did you mark order as "Paid" in admin?
- [ ] Are commission rules set up for the program?
- [ ] Check database: `php artisan tinker` → `\App\Models\CommissionRule::all()`

### Referral code invalid

- [ ] Is the partner status "active"?
- [ ] Are you using the exact partner code (case-sensitive)?
- [ ] Did the recruiter get approved?

---

## Database Queries to Verify Data

Open `php artisan tinker` and check:

```php
// Check users
\App\Models\User::all()->pluck('name', 'email');

// Check program partners
\App\Models\ProgramPartner::with('user')->get();

// Check orders
\App\Models\Order::with('customer', 'partner')->get();

// Check commissions
\App\Models\Commission::with('partner.user', 'order')->get();

// Check commission rules
\App\Models\CommissionRule::all();

exit;
```

---

## What to Watch For

### Visual/UI Issues

- [ ] Text is properly aligned
- [ ] No overlapping elements
- [ ] Colors are consistent
- [ ] Buttons are clickable
- [ ] Forms submit without error

### Data Issues

- [ ] Numbers are calculated correctly
- [ ] Commission amounts = rule rate % × order amount
- [ ] Partner hierarchy chains correctly
- [ ] Statuses flow through workflow

### Workflow Issues

- [ ] Referral code appears at checkout
- [ ] Commissions generate after payment
- [ ] Admin can view and approve orders
- [ ] Partner dashboard updates correctly

### Security Issues

- [ ] Customer can't access other customer's orders
- [ ] Regular user can't access admin panel
- [ ] Invalid codes show helpful error messages

---

## Success Criteria

After completing all test scenarios, you should be able to:

✅ Create customer accounts and make purchases  
✅ Generate referral links and track referrals  
✅ Create multi-level partner hierarchies  
✅ Verify commissions calculate correctly at multiple levels  
✅ Approve/manage orders and commissions as admin  
✅ See all information displayed accurately and clearly  
✅ Navigate smoothly between pages  
✅ Experience proper error handling  
✅ Use system on desktop and mobile

---

## Next Steps

- **After testing:** Document any bugs found
- **Before deployment:** Run automated tests: `php artisan test`
- **In production:** Monitor logs for errors: `tail -f storage/logs/laravel.log`
