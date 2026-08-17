# Visual Testing Plan - LAMiAI Partners Platform

Complete page-by-page testing guide to verify all system functionality.

---

## Prerequisites

- Laravel development server running: `php artisan serve`
- Database seeded: `php artisan db:seed --class=AiFilmmakerProgramSeeder`
- Test users created (or create new ones as you test)

---

## TEST SCENARIO 1: Customer Purchase Without Referral

### Step 1: View Public Product Page

**URL:** `http://localhost:8000/product/ai-filmmaking-masterclass`

**Expected:**

- ✅ Product name: "AI Filmmaking Masterclass"
- ✅ Price: "₦20,000"
- ✅ Product description and features visible
- ✅ "Add to Cart" / "Buy Now" button at bottom
- ✅ No referral banner (since no ?ref= parameter)
- ✅ Page is public (no login required)

**Visual Check:**

- [ ] Product image/hero section displays
- [ ] Price is clearly visible
- [ ] Trust indicators/reviews section present
- [ ] CTA button is prominent

---

### Step 2: Login as Customer

**URL:** `http://localhost:8000/login`

**Expected:**

- ✅ Login form with email and password fields
- ✅ "Remember me" checkbox
- ✅ "Forgot password?" link

**Test Data (Create new or use existing):**

- Email: `customer@test.com`
- Password: `password123`

**Visual Check:**

- [ ] Form submits successfully
- [ ] Redirects to dashboard after login
- [ ] User name displays in top right

---

### Step 3: Return to Product Page & Create Order

**URL:** `http://localhost:8000/product/ai-filmmaking-masterclass`

**Expected (as logged-in customer):**

- ✅ Same product page visible
- ✅ "Add to Cart" button now works

**Action:** Click "Add to Cart" / "Buy Now"

**Expected Redirect:**

- ✅ Redirects to checkout page with order ID
- ✅ URL: `http://localhost:8000/checkout/{orderId}`

---

### Step 4: Verify Checkout Page (No Referral)

**URL:** `http://localhost:8000/checkout/{orderId}`

**Expected:**

- ✅ Order number visible (e.g., "ORD-123456")
- ✅ Order date/time shown
- ✅ Product listed: "AI Filmmaking Masterclass"
- ✅ Unit price: ₦20,000
- ✅ Total: ₦20,000
- ✅ **No referral partner info** (since no ref code used)
- ✅ Payment method options:
    - [ ] Card
    - [ ] Bank Transfer
    - [ ] Demo Payment

**Visual Check:**

- [ ] Order summary is clear and readable
- [ ] Price calculation is correct
- [ ] Payment methods display properly
- [ ] Status shows "Pending"

---

### Step 5: Confirm Payment (Demo Mode)

**Action:** Click "Confirm Payment" / "Pay with Demo"

**Expected:**

- ✅ Order status changes to "Paid"
- ✅ Redirects to success page
- ✅ URL: `http://localhost:8000/order/{orderId}/success`

---

### Step 6: Verify Order Success Page

**URL:** `http://localhost:8000/order/{orderId}/success`

**Expected:**

- ✅ "Order Confirmed" heading
- ✅ Order number displayed
- ✅ Order date/time shown
- ✅ Product listed with price
- ✅ Payment confirmation with reference number
- ✅ "No referral applied" message (since direct purchase)
- ✅ "Continue Shopping" and "Back to Dashboard" buttons

**Visual Check:**

- [ ] Order details are accurate
- [ ] Payment status shows "Paid"
- [ ] Next steps guidance is clear
- [ ] All action buttons work

---

---

## TEST SCENARIO 2: Become a Partner (Recruiter)

### Step 7: Navigate to Partner Application

**URL:** `http://localhost:8000/partner/apply`

**Expected (unauthenticated):**

- ✅ "Become a Partner" heading
- ✅ Description of partnership benefits
- ✅ Application form visible
- ✅ **No recruiter info banner** (since no ?recruiter_code= parameter)

**Form Fields:**

- ✅ Full Name
- ✅ Email
- ✅ Phone / WhatsApp
- ✅ Password
- ✅ Partnership Program dropdown

**Visual Check:**

- [ ] Form is properly styled
- [ ] All fields have proper labels
- [ ] Required field indicators visible

---

### Step 8: Submit Partner Application (Recruiter)

**Test Data:**

```
Name: Ahmed Ibrahim
Email: ahmed@partner.com
Phone: +234 912 3456789
Password: StrongPassword123!
Program: AI Filmmaking Partnership
Recruiter Code: (leave blank for now)
```

**Action:** Click "Apply as Partner"

**Expected:**

- ✅ Form validation passes
- ✅ Success message: "Your application has been submitted and is awaiting approval"
- ✅ Redirects back to application form

**Database Verification:**

- [ ] New user created in `users` table
- [ ] New ProgramPartner created with status="pending"
- [ ] Partner code starts with "PENDING-"

---

### Step 9: Admin Approves Recruiter Partner

**Login as Admin** (if you have admin account)

**URL:** `http://localhost:8000/admin/partners`

**Expected:**

- ✅ List of pending partner applications
- ✅ "Ahmed Ibrahim" appears in list
- ✅ Status shows "Pending"
- ✅ "Approve" and "Reject" buttons visible

**Action:** Click "Approve" button for Ahmed

**Expected:**

- ✅ Status changes to "Active"
- ✅ Partner code changes from "PENDING-XXXXXX" to real code (e.g., "RECRUIT001")
- ✅ Success message displayed

**Database Verification:**

- [ ] ProgramPartner.status = 'active'
- [ ] ProgramPartner.partner_code is alphanumeric (not PENDING-)

---

---

## TEST SCENARIO 3: Partner Gets Recruited (Multi-Level)

### Step 10: Second Partner Applies with Recruiter Code

**URL:** `http://localhost:8000/partner/apply?recruiter_code=RECRUIT001` (or whatever code assigned)

**Expected:**

- ✅ **Green success banner appears:** "You are applying to become a partner recruited by Ahmed Ibrahim"
- ✅ Recruiter code is pre-filled in hidden field
- ✅ Rest of form is visible

**Test Data:**

```
Name: Fatima Hassan
Email: fatima@partner.com
Phone: +234 807 1234567
Password: AnotherPassword456!
Program: AI Filmmaking Partnership
Recruiter Code: RECRUIT001 (pre-filled)
```

**Action:** Click "Apply as Partner"

**Expected:**

- ✅ Success message displayed
- ✅ New user created
- ✅ New ProgramPartner created with `parent_partner_id = Ahmed's partner_id`

**Visual Check:**

- [ ] Green banner was clear and informative
- [ ] Form submitted successfully
- [ ] Confirmation message visible

---

### Step 11: Admin Approves Second Partner

**URL:** `http://localhost:8000/admin/partners`

**Expected:**

- ✅ Fatima Hassan appears in pending list
- ✅ Already assigned to recruiter (Ahmed)

**Action:** Click "Approve" button for Fatima

**Expected:**

- ✅ Status changes to "Active"
- ✅ Partner code assigned (e.g., "PARTNER001")

**Database Verification:**

- [ ] Fatima's ProgramPartner.parent_partner_id = Ahmed's ProgramPartner.id

---

---

## TEST SCENARIO 4: Referral Purchase (Multi-Level Commissions)

### Step 12: Login as Another Customer

**Create new test user:**

```
Name: John Doe
Email: john@customer.com
Password: JohnPassword123!
```

**URL:** `http://localhost:8000/login`

---

### Step 13: Visit Product Page with Fatima's Referral Code

**URL:** `http://localhost:8000/product/ai-filmmaking-masterclass?ref=PARTNER001`
(Use Fatima's actual partner code from Step 11)

**Expected:**

- ✅ **Green info banner appears:** "You are referred by Fatima Hassan"
- ✅ Referral info displayed prominently
- ✅ Product details still visible
- ✅ "Buy Now" button visible

**Visual Check:**

- [ ] Green banner with referrer name is prominent
- [ ] Product info not obscured by banner
- [ ] Banner is reassuring/trustworthy in appearance

---

### Step 14: Create Order (Referral Captured)

**URL:** Click "Buy Now" button

**Expected:**

- ✅ Redirects to checkout page
- ✅ Session stores referral data invisibly (no visible change yet)

---

### Step 15: Verify Checkout with Referral

**URL:** `http://localhost:8000/checkout/{orderId}`

**Expected:**

- ✅ Order details visible
- ✅ **New section appears:** "Referred by Fatima Hassan"
- ✅ Referral partner code shown
- ✅ Commission info: "Fatima will earn 20% commission on this sale"
- ✅ Price unchanged (₦20,000)

**Visual Check:**

- [ ] Referral info clearly displayed
- [ ] Commission explanation visible
- [ ] Checkout process unchanged

---

### Step 16: Confirm Payment

**Action:** Click "Confirm Payment"

**Expected:**

- ✅ Order status changes to "Paid"
- ✅ Redirects to success page

---

### Step 17: Verify Success Page with Commission Info

**URL:** `http://localhost:8000/order/{orderId}/success`

**Expected:**

- ✅ Order confirmed
- ✅ **Commission section visible:**
    - Level 1 Commission: Fatima Hassan receives ₦4,000 (20%)
    - Level 2 Commission: Ahmed Ibrahim receives ₦1,000 (5%)
- ✅ Commission amounts displayed with partner names
- ✅ Status shows "Available" or "Pending" for commissions

**Visual Check:**

- [ ] Commission calculations correct (20% = 4000, 5% = 1000)
- [ ] Hierarchy chain is clear (Fatima → Ahmed)
- [ ] Partner names properly displayed

---

---

## TEST SCENARIO 5: Partner Dashboard

### Step 18: Login as Fatima (First-Level Partner)

**URL:** `http://localhost:8000/login`

**Test Data:**

```
Email: fatima@partner.com
Password: AnotherPassword456!
```

**Expected:**

- ✅ Logged in successfully
- ✅ Redirects to dashboard or home

---

### Step 19: Navigate to Partner Dashboard

**URL:** `http://localhost:8000/partner/dashboard`

**Expected (Partner Dashboard):**

- ✅ **Overall Statistics Section (4 cards):**
    - Pending Commission: (amount or count)
    - Paid Commission: ₦0 (none paid yet)
    - Active Programs: 1 (AI Filmmaking)
    - Total Sales: 1
- ✅ **Per-Program Section:**
    - Program Name: "AI Filmmaking Partnership"
    - Partner Code: "PARTNER001"
    - Status Badge: "Active"
    - **Program Statistics (4 cards):**
        - Pending: ₦4,000 (from the purchase)
        - Paid: ₦0
        - Sales: 1
        - Recruited Partners: 1 (if Fatima recruited anyone)

- ✅ **Referral Link Section:**
    - Link: `http://localhost:8000/product/ai-filmmaking-masterclass?ref=PARTNER001`
    - Copy button that works
- ✅ **Recruitment Link Section:**
    - Link: `http://localhost:8000/partner/apply?recruiter_code=PARTNER001`
    - Copy button that works
- ✅ **Commission Structure:**
    - Level 1: 20%
    - Level 2: 5%

**Visual Check:**

- [ ] All statistics cards properly formatted
- [ ] Numbers are accurate and aligned with test data
- [ ] Copy buttons work (show feedback)
- [ ] Links are correctly formatted
- [ ] Overall layout is professional and clear

---

### Step 20: Test Referral Link Copy

**Action:** Click copy button on Referral Link

**Expected:**

- ✅ Visual feedback (button changes color, shows "Copied!")
- ✅ Link copied to clipboard
- ✅ Can paste in new browser tab and verify it works

---

### Step 21: Login as Ahmed (Second-Level Partner/Recruiter)

**URL:** `http://localhost:8000/login`

**Test Data:**

```
Email: ahmed@partner.com
Password: StrongPassword123!
```

---

### Step 22: View Ahmed's Dashboard

**URL:** `http://localhost:8000/partner/dashboard`

**Expected:**

- ✅ **Overall Statistics:**
    - Pending Commission: ₦1,000 (from the purchase via Fatima)
    - Paid Commission: ₦0
    - Active Programs: 1
    - Total Sales: 1
- ✅ **Program Section:**
    - Recruited Partners: 1 (Fatima)
    - Commission Structure still shows 20% and 5%

**Visual Check:**

- [ ] Ahmed's commission is ₦1,000 (5% of 20,000)
- [ ] Fatima is listed as recruited partner
- [ ] All statistics correct

---

---

## TEST SCENARIO 6: Admin Order Management

### Step 23: Login as Admin

**URL:** `http://localhost:8000/login`

**Test Data:**

```
Email: admin@example.com (or existing admin)
Password: (admin password)
```

**Expected:**

- ✅ Logged in successfully
- ✅ Top navigation has "Admin" link

---

### Step 24: View Admin Dashboard

**URL:** `http://localhost:8000/admin`

**Expected:**

- ✅ Admin dashboard loads
- ✅ Menu or navigation shows:
    - Partners
    - Orders
    - Commissions

**Visual Check:**

- [ ] Navigation is clear
- [ ] Admin section accessible

---

### Step 25: View Orders List

**URL:** `http://localhost:8000/admin/orders`

**Expected:**

- ✅ **Filter Section:**
    - Search (by order # or customer email)
    - Status dropdown (Pending, Paid, Failed, etc.)
    - Program dropdown
    - Min Amount input
- ✅ **Orders Table:**
    - Order #: "ORD-XXXXX" (clickable)
    - Customer: John Doe / john@customer.com
    - Program: AI Filmmaking Partnership
    - Partner: Fatima Hassan / PARTNER001
    - Amount: ₦20,000
    - Status: "Paid" (green badge)
- ✅ **Pagination** (if multiple orders)

**Visual Check:**

- [ ] Table is well-formatted
- [ ] Status badge color is appropriate (green for paid)
- [ ] All columns visible
- [ ] Responsive on smaller screens

---

### Step 26: View Order Detail

**URL:** Click on order number in table

**Expected - Order Summary Section:**

- ✅ Order Number (large, prominent)
- ✅ Order Date
- ✅ Status: "Paid" (green badge)
- ✅ Payment Provider: "admin_manual" (or "demo")

**Expected - Customer Information Section:**

- ✅ Name: John Doe
- ✅ Email: john@customer.com

**Expected - Referral Information Section (Blue Box):**

- ✅ Partnership Program: AI Filmmaking Partnership
- ✅ Partner Name: Fatima Hassan
- ✅ Partner Code: PARTNER001

**Expected - Products Section:**

- ✅ Product: AI Filmmaking Masterclass
- ✅ Quantity: 1
- ✅ Unit Price: ₦20,000
- ✅ Total: ₦20,000
- ✅ Order Total: ₦20,000

**Expected - Right Sidebar (Admin Actions):**

- ✅ **Commissions Section:**
    - Level 1: Fatima Hassan - ₦4,000 - Available (yellow badge)
    - Level 2: Ahmed Ibrahim - ₦1,000 - Available (yellow badge)
    - Total Commissions: ₦5,000

**Expected - No Action Buttons (Order Already Paid):**

- ✅ Refund Order button (orange)

**Visual Check:**

- [ ] All order details clear and accurate
- [ ] Commission calculations correct
- [ ] Partner hierarchy visible
- [ ] Status badges appropriate colors
- [ ] Layout organized and readable

---

---

## TEST SCENARIO 7: Admin Commission Management

### Step 27: View Commissions List

**URL:** `http://localhost:8000/admin/commissions`

**Expected - Statistics Cards (4 top cards):**

- ✅ Available: 2 commissions, ₦5,000 total
- ✅ Payable: 0 commissions, ₦0 total
- ✅ Paid: 0 commissions, ₦0 total
- ✅ Reversed: 0 commissions, ₦0 total

**Expected - Filter Section:**

- ✅ Status dropdown
- ✅ Level dropdown (1, 2, 3, 4)
- ✅ Program dropdown
- ✅ Min Amount input
- ✅ Filter button

**Expected - Commissions Table:**

- ✅ Row 1 (Level 1):
    - Partner: Fatima Hassan / PARTNER001
    - Order: ORD-XXXXX (clickable)
    - Level: 1
    - Program: AI Filmmaking Partnership
    - Amount: ₦4,000 | 20% of ₦20,000
    - Status: Available (yellow badge)
    - Actions: "View"
- ✅ Row 2 (Level 2):
    - Partner: Ahmed Ibrahim / RECRUIT001
    - Order: ORD-XXXXX (clickable)
    - Level: 2
    - Program: AI Filmmaking Partnership
    - Amount: ₦1,000 | 5% of ₦20,000
    - Status: Available (yellow badge)
    - Actions: "View"

**Visual Check:**

- [ ] Statistics cards accurate
- [ ] Table formatted clearly
- [ ] Status badges colored correctly
- [ ] Commission calculations visible (rate % of amount)
- [ ] Hierarchy visible (Level 1 vs Level 2)

---

### Step 28: View Commission Detail

**URL:** Click "View" on a commission

**Expected - Commission Summary:**

- ✅ Status: Available (yellow badge)
- ✅ Level: 1 or 2
- ✅ Commission Amount: ₦4,000 or ₦1,000 (large, prominent)
- ✅ Base Amount: ₦20,000
- ✅ Rate: 20% or 5%

**Expected - Partner Information:**

- ✅ Partner Name: Fatima Hassan or Ahmed Ibrahim
- ✅ Partner Code: PARTNER001 or RECRUIT001
- ✅ Email: fatima@partner.com or ahmed@partner.com
- ✅ Partnership Status: Active (green badge)

**Expected - Order Information:**

- ✅ Order Number (clickable link)
- ✅ Customer: John Doe
- ✅ Order Total: ₦20,000
- ✅ Order Date

**Expected - Right Sidebar (Admin Actions):**

- ✅ Approve button (blue)
- ✅ Mark Payable button (purple)
- ✅ Reverse button (red)
- ✅ Commission Status Flow info box

**Visual Check:**

- [ ] All details clear and correct
- [ ] Action buttons properly colored
- [ ] Status flow explanation is helpful
- [ ] Partner and order links work

---

### Step 29: Approve Commission

**Action:** Click "Approve" button

**Expected:**

- ✅ Success message: "Commission approved"
- ✅ Page refreshes
- ✅ Status changes to "Approved" (blue badge)
- ✅ Buttons change:
    - "Approve" disappears
    - "Mark Payable" still available

---

### Step 30: Mark Commission as Payable

**Action:** Click "Mark Payable" button

**Expected:**

- ✅ Success message: "Commission marked as payable"
- ✅ Status changes to "Payable" (purple badge)
- ✅ Buttons update:
    - "Mark Payable" disappears
    - "Reverse" still available

---

### Step 31: Verify Updated Commission List

**URL:** `http://localhost:8000/admin/commissions`

**Expected:**

- ✅ Statistics updated:
    - Available: 1 commission, ₦X,XXX
    - Payable: 1 commission, ₦X,XXX
- ✅ First commission status: "Payable" (purple badge)

**Visual Check:**

- [ ] Statistics refreshed correctly
- [ ] Status badges updated
- [ ] All changes reflected in list

---

---

## TEST SCENARIO 8: Error Handling & Edge Cases

### Step 32: Test Invalid Referral Code

**URL:** `http://localhost:8000/product/ai-filmmaking-masterclass?ref=INVALIDCODE123`

**Expected:**

- ✅ **Red error banner appears:** "Invalid or inactive referral code"
- ✅ Product page still loads
- ✅ Buy button still works
- ✅ Order created WITHOUT partner attribution

**Action:** Create order and verify no partner assigned

---

### Step 33: Test Self-Referral Prevention

_If you can test this:_

**Create an order using your own partner code as referral:**

**URL:** `http://localhost:8000/product/ai-filmmaking-masterclass?ref={YOUR_PARTNER_CODE}`

**Expected:**

- ✅ Checkout works normally
- ✅ (System prevents self-commission at application level)
- ✅ Commission should not be generated for self

---

### Step 34: Test Unauthorized Access

**Step 1:** Login as Customer A

**URL:** `http://localhost:8000/checkout/{ORDER_ID_OF_CUSTOMER_B}`

**Expected:**

- ✅ 403 Forbidden error
- ✅ Cannot access another customer's order

**Step 2:** Try accessing admin panel as regular customer

**URL:** `http://localhost:8000/admin/orders`

**Expected:**

- ✅ Redirected or 403 error
- ✅ Cannot access admin section

---

---

## TEST SCENARIO 9: Filter & Search Functions

### Step 35: Test Order Filters

**URL:** `http://localhost:8000/admin/orders`

**Test 1 - Search by Order Number:**

- [ ] Type order number in search
- [ ] Click Filter
- [ ] Only that order appears

**Test 2 - Filter by Status:**

- [ ] Select "Paid" status
- [ ] Click Filter
- [ ] Only paid orders show

**Test 3 - Filter by Program:**

- [ ] Select "AI Filmmaking Partnership"
- [ ] Click Filter
- [ ] Only orders from that program show

**Test 4 - Multiple Filters:**

- [ ] Select status + program
- [ ] Click Filter
- [ ] Results match all criteria

---

### Step 36: Test Commission Filters

**URL:** `http://localhost:8000/admin/commissions`

**Test 1 - Filter by Level:**

- [ ] Select "Level 1"
- [ ] Click Filter
- [ ] Only Level 1 commissions show

**Test 2 - Filter by Status:**

- [ ] Select "Available"
- [ ] Click Filter
- [ ] Only available commissions show

**Test 3 - Filter by Amount Range:**

- [ ] Enter Min Amount: 2000
- [ ] Click Filter
- [ ] Only commissions ≥ 2000 show

---

---

## TEST SCENARIO 10: Mobile Responsiveness

### Step 37: Test on Mobile (Chrome DevTools)

**Open any page and resize to mobile (375px width):**

**Check:**

- [ ] Text readable (no tiny fonts)
- [ ] Buttons clickable (adequate spacing)
- [ ] Tables scroll horizontally (not broken)
- [ ] Forms stack vertically
- [ ] Navigation collapses if menu exists
- [ ] Badges and buttons still visible

**Test Pages:**

- Product page
- Checkout page
- Partner dashboard
- Admin orders list
- Admin commissions list

---

---

## Summary Checklist

### Customer Journey

- [ ] View product page (public)
- [ ] Login as customer
- [ ] Create order
- [ ] Checkout page shows order details
- [ ] Payment confirmation works
- [ ] Success page displays with order info

### Referral Journey

- [ ] Visit product with referral code
- [ ] Green banner shows referrer name
- [ ] Checkout shows referral info
- [ ] Success page shows commissions
- [ ] Commission amounts calculated correctly

### Partner Journey

- [ ] Apply as new partner
- [ ] Recruiter code shows green banner
- [ ] Admin approves
- [ ] Partner code assigned
- [ ] Dashboard shows correct statistics
- [ ] Referral/recruitment links copy correctly

### Admin Order Management

- [ ] List orders with filters
- [ ] View order details
- [ ] See customer info
- [ ] See partner attribution
- [ ] See commissions generated
- [ ] Mark order as paid (if pending)
- [ ] Refund order (if paid)

### Admin Commission Management

- [ ] List commissions with statistics
- [ ] View commission details
- [ ] Approve commission
- [ ] Mark as payable
- [ ] Reverse commission
- [ ] Status workflow correct

### Security & Errors

- [ ] Invalid referral code shows error
- [ ] Customer can't access others' orders
- [ ] Unauthorized access blocked
- [ ] Form validation works

### Responsive Design

- [ ] Desktop layout correct
- [ ] Tablet layout correct
- [ ] Mobile layout correct
- [ ] All text readable
- [ ] All buttons clickable

---

## Test Data Quick Reference

```
ADMIN ACCOUNT:
Email: admin@example.com
Password: (your admin password)

RECRUITER (AHMED):
Email: ahmed@partner.com
Password: StrongPassword123!
Partner Code: RECRUIT001

AFFILIATE (FATIMA):
Email: fatima@partner.com
Password: AnotherPassword456!
Partner Code: PARTNER001
Recruiter: Ahmed (parent_partner_id = Ahmed's partner_id)

CUSTOMER (JOHN):
Email: john@customer.com
Password: JohnPassword123!

PRODUCT:
Name: AI Filmmaking Masterclass
Price: ₦20,000
Slug: ai-filmmaking-masterclass

PROGRAM:
Name: AI Filmmaking Partnership
Commission Level 1: 20%
Commission Level 2: 5%
```

---

## Notes

- **Session-Based Referral:** Referral data stored in session, not database. Customer can navigate away and still complete purchase.
- **Commission Generation:** Triggered when order marked as "Paid" by admin.
- **Hierarchy:** Commissions walk up `parent_partner_id` chain automatically.
- **Idempotency:** Marking same order as paid twice won't duplicate commissions.
- **Currency:** All amounts in Nigerian Naira (₦).

---

## If Issues Found

Document:

1. **URL** where issue occurred
2. **What you did** (step-by-step)
3. **What you expected**
4. **What actually happened**
5. **Browser/Device** used
6. **Screenshots** if helpful
