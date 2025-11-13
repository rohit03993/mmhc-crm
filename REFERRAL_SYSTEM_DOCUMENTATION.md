# 📋 Referral System Documentation - MMHC CRM

## 🎯 Overview

The Referral System allows nurses and caregivers to share unique referral links with other healthcare professionals. When someone registers using a referral link, the referrer earns reward points (1 point = ₹10).

## ✅ Features Implemented

### 1. **Unique Referral Links**
- Each nurse/caregiver gets one unique, reusable referral code
- Format: `/register?ref=REFERRAL_CODE`
- Code is generated based on user's name and ID
- Links can be shared multiple times (one referral record per successful registration)

### 2. **Referral Registration**
- When someone clicks a referral link, they see a special message
- Patient registration tab is hidden (only nurse/caregiver can register via referral)
- Registration form shows who referred them
- Only nurses and caregivers can register using referral links

### 3. **Reward Points System**
- Referrer earns **1 reward point (₹10)** for each successful referral
- Points are automatically added to referrer's account
- Points are added to existing reward points system
- Same reward point value: 1 point = ₹10

### 4. **Staff Dashboard Integration**
- Referral link displayed in staff dashboard
- Copy-to-clipboard functionality
- Referral statistics (completed referrals, total earned)
- Recent referrals history
- Shows referral status and reward points earned

### 5. **Database Structure**
- `referrals` table stores referral records
- Each successful referral creates a new record
- Tracks referrer, referred user, status, reward points, and completion date
- Supports multiple referrals per referrer

## 📊 Database Schema

### `referrals` Table
```sql
- id: Primary key
- referral_code: Unique referral code (20 chars, indexed)
- referrer_id: Foreign key to users (who shared the link)
- referred_id: Foreign key to users (who registered via link)
- status: 'pending', 'completed', 'cancelled'
- reward_points: Points earned (default: 1)
- reward_amount: Amount earned (default: ₹10.00)
- completed_at: Timestamp when referral was completed
- created_at, updated_at: Timestamps
```

## 🔄 Workflow

### Step 1: Generate Referral Link
1. Nurse/Caregiver logs into staff dashboard
2. System automatically generates or retrieves their referral code
3. Referral link is displayed: `http://domain.com/register?ref=REFERRAL_CODE`
4. User can copy the link to share

### Step 2: Share Referral Link
1. Nurse/Caregiver shares the link via WhatsApp, email, SMS, etc.
2. Link can be shared multiple times
3. Each successful registration creates a new referral record

### Step 3: Registration via Referral Link
1. New user clicks referral link
2. Registration page shows referral message
3. Patient registration tab is hidden
4. Only Nurse/Caregiver registration is available
5. User fills registration form
6. System validates referral code
7. User registers as nurse or caregiver

### Step 4: Process Referral
1. System creates new referral record
2. Links new user to referrer
3. Marks referral as 'completed'
4. Awards 1 reward point (₹10) to referrer
5. Updates referrer's reward points
6. Shows success message to new user

## 📁 File Structure

### Models
- `app/Modules/Referrals/Models/Referral.php` - Referral model

### Services
- `app/Modules/Referrals/Services/ReferralService.php` - Referral business logic

### Controllers
- `app/Modules/Referrals/Controllers/ReferralController.php` - Referral API endpoints

### Migrations
- `database/migrations/2025_01_15_000000_create_referrals_table.php` - Referrals table

### Providers
- `app/Modules/Referrals/Providers/ReferralServiceProvider.php` - Service provider

### Views
- Updated `app/Modules/Auth/Views/register-tabbed.blade.php` - Registration form with referral support
- Updated `app/Modules/Services/Views/staff/dashboard.blade.php` - Staff dashboard with referral section

### Controllers (Updated)
- `app/Modules/Auth/Controllers/AuthController.php` - Registration with referral support
- `app/Modules/Services/Controllers/StaffDashboardController.php` - Dashboard with referral data

### Models (Updated)
- `app/Models/Core/User.php` - Added referral relationships

## 🔧 Key Methods

### ReferralService Methods

#### `generateReferralCode(User $user): string`
- Generates unique referral code for user
- Format: First 3 letters of name + User ID + 4 random characters
- Ensures uniqueness

#### `getOrCreateReferralCode(User $user): string`
- Gets existing referral code or creates new one
- Each user has one reusable referral code
- Returns the code

#### `getReferralLink(User $user): string`
- Gets full referral link for user
- Returns: `route('auth.register', ['ref' => $referralCode])`

#### `validateReferralCode(string $referralCode): ?Referral`
- Validates referral code
- Checks if referrer is active staff
- Returns referral record if valid

#### `processReferral(string $referralCode, User $newUser): bool`
- Processes referral when new user registers
- Creates new referral record
- Awards reward points to referrer
- Returns true if successful

#### `getReferralStats(User $user): array`
- Gets referral statistics for user
- Returns: total referrals, completed, pending, reward points, reward amount

#### `getReferralHistory(User $user, int $limit = 10)`
- Gets referral history for user
- Returns recent referrals with referred user info

## 🎨 UI Components

### Staff Dashboard - Referral Section
- **Referral Link Card**: Shows referral link with copy button
- **Referral Stats**: Shows completed referrals and total earned
- **Recent Referrals**: Shows recent referral history with status

### Registration Page - Referral View
- **Referral Alert**: Shows who referred the user
- **Hidden Patient Tab**: Patient registration hidden for referral links
- **Active Nurse/Caregiver Tabs**: Only staff registration available

## 🔒 Security Features

1. **Validation**: Referral codes are validated before processing
2. **Staff Only**: Only active nurses/caregivers can generate referral links
3. **Duplicate Prevention**: Prevents same user from being referred twice by same referrer
4. **Transaction Safety**: All referral processing wrapped in database transactions
5. **Role Validation**: Only nurses/caregivers can register via referral links

## 📈 Statistics & Tracking

### Referral Stats
- Total referrals made
- Completed referrals
- Pending referrals
- Total reward points earned
- Total reward amount earned (₹)

### Referral History
- List of all referrals
- Referred user information
- Referral status
- Completion date
- Reward points earned

## 🚀 Usage Instructions

### For Nurses/Caregivers (Referrers)

1. **Get Your Referral Link**
   - Login to staff dashboard
   - Find "Referral Program" section
   - Copy your referral link

2. **Share Your Link**
   - Share via WhatsApp, email, SMS, social media
   - Link can be shared multiple times
   - Each successful registration earns you ₹10

3. **Track Your Referrals**
   - View referral statistics in dashboard
   - See recent referrals and status
   - Track reward points earned

### For New Users (Referred)

1. **Click Referral Link**
   - Click the referral link shared by a nurse/caregiver
   - You'll see a message showing who referred you

2. **Register as Nurse/Caregiver**
   - Fill out registration form
   - Select nurse or caregiver role
   - Complete registration

3. **Referrer Gets Rewarded**
   - Referrer automatically gets 1 reward point (₹10)
   - Points are added to referrer's account
   - Referrer can see the reward in their dashboard

## 🧪 Testing

### Test Scenarios

1. **Generate Referral Link**
   - Login as nurse/caregiver
   - Check if referral link is generated
   - Verify link format

2. **Share Referral Link**
   - Copy referral link
   - Share with someone
   - Verify link works

3. **Register via Referral**
   - Click referral link
   - Verify patient tab is hidden
   - Register as nurse/caregiver
   - Verify referral is processed

4. **Reward Points**
   - Check referrer's reward points
   - Verify points are increased by 1
   - Verify reward amount is ₹10

5. **Multiple Referrals**
   - Share same link multiple times
   - Register multiple users
   - Verify each registration creates new referral record
   - Verify referrer gets points for each

## 📝 Notes

1. **One Code Per User**: Each nurse/caregiver has one reusable referral code
2. **Multiple Referrals**: Same code can be used multiple times
3. **Reward Points**: 1 point = ₹10 (same as existing reward system)
4. **Staff Only**: Only nurses and caregivers can generate and use referral links
5. **Automatic Processing**: Referral is processed automatically during registration
6. **Transaction Safe**: All operations wrapped in database transactions

## 🔄 Future Enhancements

1. **Referral Levels**: Multi-level referral system
2. **Referral Bonuses**: Bonus points for multiple referrals
3. **Referral Analytics**: Detailed analytics and reporting
4. **Email Notifications**: Notify referrer when someone registers
5. **Referral Campaigns**: Time-limited referral campaigns
6. **Referral Leaderboard**: Leaderboard for top referrers

## 🐛 Troubleshooting

### Issue: Referral link not working
- **Solution**: Check if referrer is active staff
- **Solution**: Verify referral code exists in database

### Issue: Reward points not added
- **Solution**: Check if referral was processed successfully
- **Solution**: Verify referral status is 'completed'

### Issue: Can't register via referral link
- **Solution**: Verify you're registering as nurse or caregiver
- **Solution**: Check if referral code is valid

### Issue: Patient tab still visible
- **Solution**: Clear browser cache
- **Solution**: Verify referral code is in URL

## 📞 Support

For issues or questions about the referral system, please contact the development team.

---

**Last Updated:** January 2025  
**Status:** ✅ Complete and Ready for Use

