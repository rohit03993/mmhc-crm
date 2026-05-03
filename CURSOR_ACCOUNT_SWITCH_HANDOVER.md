# Cursor Account Switch Handover

Use this file after switching to another Cursor account so the new session can resume with full context.

## Current Project State

- Project: `mmhc-crm`
- Main work completed:
  - Incentive engine integration for services/subscriptions
  - Staff incentive details screen with visit-wise numbering
  - Admin and staff drilldown links for incentives
  - Global pagination modernization + default 10 per page
  - Large demo network seeding for realistic incentive testing

## Key Features Implemented

### 1) Incentive Data + Calculation Flow

- Service incentives use:
  - visit kind
  - experience tier
  - subscriber/non-subscriber rate
  - growth + DtA slabs by service count
- Subscription referral incentives use:
  - subscription base amount
  - payment frequency percentage
  - growth + DtA adjustment

### 2) Staff Incentive Unified Screen

- New page: `services::staff.incentive-details`
- Shows in one place:
  - service visit-wise ledger
  - subscription referral incentives
  - staff referral points
  - patient rewards
- Added tabs:
  - Services
  - Subscription
  - Staff Referrals
  - Patient Rewards
- Each tab has pagination.

### 3) Admin + Staff Navigation Wiring

- Admin `Manage Users` now has incentives drilldown button for nurse/caregiver.
- Staff dashboard and related pages include entry point to unified incentive details.

### 4) Pagination Standardization

- Global paginator view configured in `AppServiceProvider`.
- Shared modern pagination views added:
  - `resources/views/pagination/modern.blade.php`
  - `resources/views/pagination/modern-simple.blade.php`
- Most lists standardized to `10` per page.

### 5) Realistic Demo Network Seeder

- New seeder:
  - `database/seeders/IncentiveNetworkDemoSeeder.php`
- Creates:
  - 30 nurses
  - 25 caregivers
  - 120 patients
  - service requests + service ledgers
  - subscription referrals + subscription ledgers
  - staff referrals
  - caregiver rewards
- Integrated in:
  - `database/seeders/DatabaseSeeder.php`

## Latest Validation Snapshot (after fresh reset)

From terminal checks:

- nurses: `30`
- caregivers: `25`
- patients: `120`
- service_requests: `446`
- service_ledgers: `440`
- subscriptions: `110`
- subscription_ledgers: `110`
- staff_referrals_completed: `36`
- caregiver_rewards: `110`

Note: service requests > service ledgers is expected because some demo service requests are non-completed/non-ledger rows from baseline demo data.

## Commands Used for Clean Setup

```bash
php artisan migrate:fresh --seed
php artisan optimize:clear
```

Validation:

```bash
php artisan tinker --execute "dump([
'nurses' => \App\Models\Core\User::where('role','nurse')->count(),
'caregivers' => \App\Models\Core\User::where('role','caregiver')->count(),
'patients' => \App\Models\Core\User::where('role','patient')->count(),
'service_requests' => \App\Modules\Services\Models\ServiceRequest::count(),
'service_ledgers' => \App\Modules\Incentives\Models\IncentiveLedger::where('source_type','service_request')->count(),
'subscriptions' => \App\Modules\Plans\Models\Subscription::count(),
'subscription_ledgers' => \App\Modules\Incentives\Models\IncentiveLedger::where('source_type','subscription_sale')->count(),
'staff_referrals_completed' => \App\Modules\Referrals\Models\Referral::where('status','completed')->count(),
'caregiver_rewards' => \App\Modules\Rewards\Models\CaregiverReward::count(),
]);"
```

## Ready-to-Paste Prompt for New Account

Copy this prompt in the first message after switching account:

---
I am resuming work in `mmhc-crm`.  
Please read `CURSOR_ACCOUNT_SWITCH_HANDOVER.md` and continue from that exact state.

Current focus priorities:
1. Validate incentive screens and tabbed paginated data visibility for admin + staff.
2. Keep pagination consistent at 10 per page globally.
3. Preserve realistic seeded demo network and verify ledger consistency.

Important constraints:
- Do not run terminal commands automatically; provide commands and wait for my output.
- Explain DB-impacting steps before running them.
---

## Optional Cleanup Next (Not Mandatory)

- Reduce duplicated baseline seeder calls inside `IncentiveNetworkDemoSeeder` (for DRY) when invoked through `DatabaseSeeder`.
- Add a compact "showing X to Y of Z" line to pagination bars globally.

