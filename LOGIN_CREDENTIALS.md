# 🔐 MMHC CRM - Login Credentials

**Quick Reference for Testing**

---

## 👤 **Patient Login** (For Mobile Testing)

**Email:** `patient@demo.com`  
**Password:** `password123`  
**User ID:** `P-UID-000001`  
**Name:** Shri Ram Kumar Singh

**Dashboard URL:** `/dashboard`  
**My Requests URL:** `/services/my-requests`

---

## 👨‍⚕️ **Nurse Login**

**Email:** `nurse@demo.com`  
**Password:** `password123`  
**User ID:** `N-UID-000001`  
**Name:** Dr. Priya Sharma

**Dashboard URL:** `/staff/dashboard`

---

## 👨‍⚕️ **Caregiver Login**

**Email:** `caregiver@demo.com`  
**Password:** `password123`  
**User ID:** `C-UID-000001`  
**Name:** Ram Prasad Yadav

**Dashboard URL:** `/staff/dashboard`

---

## 👨‍💼 **Admin Login**

**Email:** `mantu@themmhc.com`  
**Password:** `password123`  
**User ID:** `M-UID-000001`  
**Name:** Mantu Kumar

**Dashboard URL:** `/admin/dashboard`

---

## 📱 **How to Test Mobile View**

### **Option 1: Browser Developer Tools (Recommended)**

1. **Open your browser** (Chrome, Firefox, Edge)
2. **Press F12** or **Right-click → Inspect**
3. **Click the device toggle icon** (📱) or press `Ctrl+Shift+M` (Windows) / `Cmd+Shift+M` (Mac)
4. **Select a mobile device** from the dropdown:
   - iPhone 12/13/14 (375px width)
   - Samsung Galaxy S20 (360px width)
   - iPad (768px width)
   - Or set custom dimensions (e.g., 375px × 667px)
5. **Refresh the page** and test the mobile view

### **Option 2: Actual Mobile Device**

1. **Find your local IP address:**
   - Windows: Open CMD → type `ipconfig` → look for "IPv4 Address"
   - Mac/Linux: Open Terminal → type `ifconfig` → look for "inet"
   - Example: `192.168.1.100`

2. **Access from mobile:**
   - Make sure your phone is on the same WiFi network
   - Open browser on phone
   - Go to: `http://YOUR_IP_ADDRESS:8000` (or your port)
   - Example: `http://192.168.1.100:8000`

3. **Login with patient credentials:**
   - Email: `patient@demo.com`
   - Password: `password123`

### **Option 3: Online Mobile Emulators**

- **BrowserStack:** https://www.browserstack.com/
- **Responsive Design Mode:** Built into Chrome DevTools
- **Mobile-Friendly Test:** https://search.google.com/test/mobile-friendly

---

## 🧪 **Testing Checklist for Patient Dashboard**

### **Mobile View (375px width):**

- [ ] Header displays correctly with user name and ID
- [ ] Statistics cards show in 2 columns (6 cards total)
- [ ] Quick action buttons are touch-friendly (4 buttons in grid)
- [ ] Service request cards display in 1 column
- [ ] Each card shows all information clearly
- [ ] Pagination works (max 10 items per page)
- [ ] Staff carousel scrolls horizontally
- [ ] All buttons are easily tappable
- [ ] Text is readable without zooming
- [ ] No horizontal scrolling

### **Tablet View (768px width):**

- [ ] Service request cards show in 2 columns
- [ ] Quick actions show in 4 columns
- [ ] Statistics cards show in 4 columns
- [ ] Layout adapts properly

### **Desktop View (1024px+):**

- [ ] Service request cards show in 3 columns
- [ ] All sections display side-by-side
- [ ] Full layout is visible

---

## 🚀 **Quick Start Commands**

### **If demo data doesn't exist:**

```bash
php artisan db:seed --class=DemoDataSeeder
```

### **Reset demo data:**

```bash
php artisan db:seed --class=DemoDataSeeder
```

### **Start development server:**

```bash
php artisan serve
```

Then visit: `http://localhost:8000`

---

## 📝 **Notes**

- All demo users have the same password: `password123`
- Demo users are created with sample service requests
- Patient has 3 demo service requests for testing
- Mobile view is optimized for screens 320px - 768px wide
- Cards are designed for touch interaction (minimum 44px tap targets)

---

**Last Updated:** January 2025

