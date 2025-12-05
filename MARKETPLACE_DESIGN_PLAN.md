# Marketplace-Style Staff Listing Design Plan

## Overview
Transform the staff listing into a marketplace-style interface similar to OLA, Uber, and Zomato, making it easy for patients to browse, compare, and book nurses/caregivers.

## Key Features

### 1. Tab-Based Navigation
- **Nurses Tab** - Show all available nurses
- **Caregivers Tab** - Show all available caregivers
- Active tab highlighting
- Count badges showing available staff

### 2. Distance-Based Display
- **Distance Badges:**
  - 🟢 "Nearby" (< 1km)
  - 🟡 "2 km away"
  - 🟠 "5 km away"
  - 🔴 "10+ km away"
- Sort by distance (default)
- Filter by distance range (5km, 10km, 25km, 50km)

### 3. Staff Card Design (Marketplace Style)
Each card shows:
- **Avatar/Photo** with availability indicator
- **Name** and **ID**
- **Distance Badge** (prominent)
- **Availability Status** (Available/Busy/Unavailable)
- **Experience** (e.g., "5-10 years")
- **Qualification** (e.g., "B.Sc Nursing")
- **Starting Price** (e.g., "₹2,000/day")
- **Quick Book Button** (prominent CTA)
- **View Details** link

### 4. Enhanced Filtering
- **Location Filter:** Pincode input with auto-suggest
- **Distance Filter:** 5km, 10km, 25km, 50km
- **Experience Filter:** 1-3, 3-5, 5-10, 10+ years
- **Qualification Filter:** B.Sc, M.Sc, GNM, etc.
- **Availability Filter:** Available only, All
- **Sort Options:** Distance, Price, Experience, Rating (future)

### 5. Search Functionality
- Search by name, ID, qualification
- Real-time filtering
- Clear search button

### 6. Visual Enhancements
- Color-coded distance badges
- Availability status indicators (green/yellow/red)
- Professional card layout with shadows
- Hover effects
- Mobile-optimized touch targets

### 7. Quick Actions
- **Book Now** button on each card
- **Compare** feature (future)
- **Save to Favorites** (future)
- **Share** option (future)

## Implementation Priority

### Phase 1 (Current)
1. ✅ Tab-based navigation
2. ✅ Distance badges and sorting
3. ✅ Enhanced card design
4. ✅ Availability status display
5. ✅ Quick booking buttons

### Phase 2 (Future)
1. Ratings and reviews
2. Staff photos/avatars
3. Map view
4. Comparison feature
5. Favorites/Wishlist

## Technical Implementation

### Backend Changes
- Already has distance calculation (LocationService)
- Already has availability status (Profile model)
- Already has filtering logic (StaffController)

### Frontend Changes
- Redesign staff/index.blade.php with tabs
- Add distance badge component
- Enhance card styling
- Add availability indicators
- Improve mobile responsiveness

