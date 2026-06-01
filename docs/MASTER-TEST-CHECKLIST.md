# MMHC CRM — Master test checklist (run once development is complete)

Use **https://themmhc.com** (or staging). Hard refresh or clear app cache before testing.

---

## Patient (healthcare CRM)

- [ ] Login → dashboard loads
- [ ] Quick actions: Find staff, My requests, **Refer**, Plans, Profile
- [ ] **Refer friends** teaser → `/my-referrals` → Copy / WhatsApp
- [ ] Find staff → GPS / location → book flow
- [ ] My requests → FREE (plan) or visit fee (no “balance due”)
- [ ] Plans → subscribe (Razorpay if enabled)

## Staff (nurse / caregiver)

- [ ] Staff dashboard → quick actions + **Share & earn** (both links)
- [ ] Copy + WhatsApp on dashboard and on Staff refs / Plan refs pages
- [ ] Patient rewards → submit + OTP flow
- [ ] Accept booking → start → complete visit
- [ ] Earnings strip: paid / awaiting admin / approved unpaid / upcoming
- [ ] Profile mobile verify → rewards/referrals payable

## Admin (MMHC)

- [ ] Dashboard earning / payout drill-downs
- [ ] Service requests → Plan (free) / Per-visit fee filters (no balance due)
- [ ] Staff payout (not confused with patient payment)
- [ ] Pending payments queue

## Academics (institution admin + faculty on phone)

- [ ] Academics dashboard: mobile header, quick links in 2-column grid
- [ ] Bottom nav: **Faculty** = Academics | Topics | Tasks | Attend | Reports
- [ ] Bottom nav: **Institution admin** = Academics | Students | Enroll | Batches | Reports
- [ ] Inner pages: back arrow + title bar; tables scroll horizontally
- [ ] Mark attendance form: full-width fields on phone
- [ ] Student: assignments, attendance, SPI (unchanged)

## Mobile

- [ ] Phone browser: layouts, 44px tap targets, no horizontal scroll on share cards
- [ ] Android app (Capacitor): same URLs, WhatsApp opens

---

## Do not break (regression)

- Visit payment model: **plan = free visits**, **non-plan = full visit fee at booking** (no balance due)
- Three money flows stay separate: **visit fees**, **plan subscription**, **staff payout**
- GPS find staff (not pincode-only)
- Student membership gate / community (if enabled)

---

## After deploy (server)

```bash
php artisan view:clear
php artisan route:clear
php artisan cache:clear
```
