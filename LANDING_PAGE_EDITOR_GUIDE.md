# 📝 Landing Page Editor Guide - MMHC CRM

## 🎯 Overview
This guide will help your client edit the MMHC landing page content directly from the admin dashboard without touching any code.

---

## 🚀 Quick Start

### Step 1: Login as Admin
1. Go to `http://localhost:8000/auth/login`
2. Login with your admin credentials
3. You'll be redirected to the Admin Dashboard

### Step 2: Access the Landing Page Editor
1. From the Admin Dashboard, click on **"Edit Landing Page"** button
2. Or navigate to: `http://localhost:8000/admin/page-content`

### Step 3: Edit Page Sections
1. You'll see all available sections (Hero, Plans, About Us, Contact, etc.)
2. Click **"Edit Section"** on any section you want to modify
3. Update the content in the form
4. Click **"Save Changes"**
5. Preview your changes by clicking **"Preview Landing Page"**

---

## 📋 Available Sections

### 1. **Hero Section**
**What you can edit:**
- Main title (e.g., "Your Health, Our Priority")
- Subtitle/description
- Button text
- Section status (Active/Inactive)

**Location on page:** Top banner section with blue/green gradient

### 2. **Plans Section**
**What you can edit:**
- Section title
- Section subtitle
- Section status

**Note:** Individual plan details are managed in the Plans module

### 3. **Star Performers Section**
**What you can edit:**
- Section title (e.g., "Meet Our Star Performers")
- Section subtitle
- Section status

**Note:** Individual caregiver profiles are managed separately

### 4. **Why Choose MMHC Section**
**What you can edit:**
- Section title
- Section subtitle
- Feature cards (6 features)
- Each feature's icon, title, and description
- Section status

### 5. **About Us Section**
**What you can edit:**
- Section title
- Section subtitle
- Company story
- Mission statement
- Vision statement
- Section image
- Section status

### 6. **Testimonials Section**
**What you can edit:**
- Section title
- Section subtitle
- Section status

**Note:** Individual testimonials can be added later

### 7. **Contact Section**
**What you can edit:**
- Section title
- Section subtitle
- Address
- Phone number
- Emergency phone number
- Email address
- Support email address
- Business hours
- Social media links
- Section status

### 8. **Footer Section**
**What you can edit:**
- Company name
- Company description
- Copyright text
- Section status

---

## 🛠️ How to Edit Content

### Text Fields
1. Click in the input box
2. Type your new content
3. Text will be saved when you click "Save Changes"

### Textarea Fields
1. Click in the larger text area
2. Type your content (can be multiple paragraphs)
3. Press Enter for line breaks

### Status Toggle
- **Active** (Green): Section will be visible on the landing page
- **Inactive** (Red): Section will be hidden from the landing page

### Image Upload
1. Click the "Choose File" button
2. Select an image from your computer (Max 5MB)
3. Supported formats: JPG, PNG, GIF, WebP
4. Old image will be replaced with the new one

---

## 💡 Best Practices

### Writing Content
✅ **DO:**
- Keep titles short and impactful (5-10 words)
- Write clear, concise descriptions
- Use professional language
- Check for spelling and grammar
- Preview before saving

❌ **DON'T:**
- Use overly long paragraphs
- Include HTML tags or code
- Use special characters excessively
- Make frequent changes without previewing

### Images
✅ **DO:**
- Use high-quality images (minimum 1200px width)
- Optimize images before uploading (compress them)
- Use relevant, professional photos
- Check image orientation

❌ **DON'T:**
- Upload very large files (over 5MB)
- Use copyrighted images without permission
- Use low-resolution images
- Use images with watermarks

---

## 🎨 Section-Specific Tips

### Hero Section
- **Title**: Should be your main value proposition
- **Subtitle**: Explain what MMHC does in 1-2 sentences
- Keep it simple and compelling

### About Us
- **Story**: Tell your company's origin story (2-3 paragraphs)
- **Mission**: What you aim to achieve (1 paragraph)
- **Vision**: Where you want to be in the future (1 paragraph)

### Contact Section
- **Address**: Use your actual business address
- **Phone**: Include country code (+91 for India)
- **Email**: Use professional email addresses
- **Hours**: Be specific and accurate

### Why Choose MMHC
- Each feature should highlight a unique benefit
- Keep descriptions to 2-3 sentences
- Focus on what makes MMHC different

---

## 🔍 Preview Your Changes

### Before Publishing
1. Click the **"Preview Landing Page"** link
2. Opens in a new tab
3. Check all sections look correct
4. Test on mobile (resize browser window)
5. Check for typos or formatting issues

### After Publishing
- Changes are live immediately
- No need to restart the server
- Refresh the landing page to see updates

---

## 🚨 Troubleshooting

### Changes Not Showing?
1. Hard refresh the page: `Ctrl+F5` (Windows) or `Cmd+Shift+R` (Mac)
2. Clear browser cache
3. Check if the section is marked as "Active"
4. Check if you clicked "Save Changes"

### Image Not Uploading?
1. Check file size (must be under 5MB)
2. Check file format (JPG, PNG, GIF, WebP only)
3. Try compressing the image
4. Check file permissions on server

### Can't Access Editor?
1. Make sure you're logged in as admin
2. Check your role in the system
3. Contact technical support

---

## 📞 Support

If you encounter any issues or need help:

1. **Technical Issues**: Contact your developer
2. **Content Questions**: Refer to this guide
3. **Training**: Request a live walkthrough

---

## 🔐 Security Tips

✅ **DO:**
- Keep your admin password secure
- Log out after editing
- Only give editor access to trusted team members
- Regularly backup your content

❌ **DON'T:**
- Share admin credentials
- Leave your session open on public computers
- Make drastic changes without backup
- Delete sections unless absolutely necessary

---

## 📊 Content Management Workflow

### Recommended Process:
1. **Plan**: Decide what content needs to change
2. **Draft**: Write new content in a document first
3. **Review**: Have someone proofread
4. **Edit**: Make changes in the admin panel
5. **Preview**: Check how it looks
6. **Publish**: Save changes
7. **Verify**: Check the live site
8. **Monitor**: Get feedback from users

---

## 🎓 Training Checklist

Your client should be able to:
- [ ] Login to admin dashboard
- [ ] Access the page content editor
- [ ] Edit text content
- [ ] Upload images
- [ ] Toggle section visibility
- [ ] Preview changes
- [ ] Save changes successfully
- [ ] View the updated landing page

---

## 📝 Notes

- All changes are saved to the database
- No coding knowledge required
- Changes are instant (no deployment needed)
- Multiple sections can be edited at once
- Original content is backed up in the database

---

## 🎉 Benefits for Your Client

✅ **Independence**: Edit content without developer help  
✅ **Flexibility**: Update anytime, anywhere  
✅ **Cost Savings**: No developer fees for content updates  
✅ **Speed**: Changes go live instantly  
✅ **Control**: Full control over landing page content  
✅ **Professional**: Maintain a modern, updated website

---

*Last Updated: {{ date('Y-m-d') }}*  
*MMHC CRM v1.0*

