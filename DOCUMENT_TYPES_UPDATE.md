# 📄 Document Types Update - Role-Specific Implementation

**Date:** January 2025  
**Status:** ✅ Complete

---

## 🎯 **Problem Identified**

The document upload form was showing generic document types (Certificate, ID Proof, Medical License) which were more relevant for staff (nurses/caregivers) rather than patients. Patients need document types relevant to their medical needs.

---

## ✅ **Solution Implemented**

### **Role-Specific Document Types**

#### **For Patients:**
1. **Medical Report** - General medical reports and test results
2. **Aadhaar Card** - Government ID proof
3. **Past Medical History** - Historical medical records
4. **Prescription** - Doctor's prescriptions
5. **Lab Report** - Laboratory test results
6. **Insurance Card** - Health insurance card/policy
7. **Other** - Any other medical documents

#### **For Staff (Nurses/Caregivers):**
1. **Certificate** - Professional certificates
2. **ID Proof** - Identity documents
3. **Medical License** - Medical licenses
4. **Insurance** - Insurance documents
5. **Other** - Other professional documents

#### **For Admin:**
- All document types available (for management purposes)

---

## 📝 **Files Modified**

### **1. Migration Created**
- **File:** `database/migrations/2025_01_28_000000_update_documents_table_document_type.php`
- **Change:** Changed `document_type` from ENUM to VARCHAR(50) to allow flexible document types
- **Action Required:** Run migration: `php artisan migrate`

### **2. Controller Updated**
- **File:** `app/Modules/Profiles/Controllers/DocumentController.php`
- **Changes:**
  - Added `getAllowedDocumentTypes()` method to return role-specific document types
  - Updated `upload()` method to validate based on user role
  - Updated `index()` method to pass allowed document types to view

### **3. View Updated**
- **File:** `app/Modules/Profiles/Views/documents/index.blade.php`
- **Changes:**
  - Document type dropdown now dynamically shows options based on user role
  - Placeholder text changes based on user role (e.g., "Blood Test Report" for patients vs "Nursing Certificate" for staff)

### **4. Model Updated**
- **File:** `app/Modules/Profiles/Models/Document.php`
- **Changes:**
  - Updated `getDocumentTypeDisplayAttribute()` to handle all new document types
  - Added display names for patient-specific document types

---

## 🚀 **Implementation Details**

### **Controller Logic:**
```php
protected function getAllowedDocumentTypes($user)
{
    if ($user->isPatient()) {
        return [
            'medical_report' => 'Medical Report',
            'aadhaar_card' => 'Aadhaar Card',
            'past_medical_history' => 'Past Medical History',
            'prescription' => 'Prescription',
            'lab_report' => 'Lab Report',
            'insurance_card' => 'Insurance Card',
            'other' => 'Other',
        ];
    } elseif ($user->isStaff()) {
        return [
            'certificate' => 'Certificate',
            'id_proof' => 'ID Proof',
            'medical_license' => 'Medical License',
            'insurance' => 'Insurance',
            'other' => 'Other',
        ];
    }
    // Admin gets all types
}
```

### **Validation:**
- Document type validation is now dynamic based on user role
- Only allowed document types can be uploaded
- Prevents invalid document types from being submitted

---

## 📋 **Migration Instructions**

### **Step 1: Run Migration**
```bash
php artisan migrate
```

This will:
- Change `document_type` column from ENUM to VARCHAR(50)
- Allow storing any document type string
- Preserve existing data

### **Step 2: Clear Cache (Optional)**
```bash
php artisan config:clear
php artisan view:clear
```

---

## ✅ **Testing Checklist**

- [ ] Patient can see patient-specific document types
- [ ] Nurse can see staff-specific document types
- [ ] Caregiver can see staff-specific document types
- [ ] Admin can see all document types
- [ ] Document upload works for all roles
- [ ] Document type validation works correctly
- [ ] Document display shows correct type names
- [ ] Existing documents still display correctly

---

## 🔄 **Backward Compatibility**

- ✅ Existing documents with old types (certificate, id_proof, etc.) will still display correctly
- ✅ The model's `getDocumentTypeDisplayAttribute()` handles both old and new types
- ✅ No data loss - migration preserves existing data

---

## 📊 **Benefits**

1. **Better UX:** Patients see relevant document types
2. **Logical Organization:** Document types match user needs
3. **Future-Proof:** Easy to add new document types per role
4. **Flexible:** VARCHAR allows any document type without migration changes

---

## 🎯 **Future Enhancements**

1. **Document Categories:** Group document types by category
2. **Required Documents:** Mark certain document types as required for specific roles
3. **Document Expiry:** Track expiry dates for licenses/certificates
4. **Bulk Upload:** Allow multiple document uploads at once

---

**Status:** ✅ **Ready for Testing**

