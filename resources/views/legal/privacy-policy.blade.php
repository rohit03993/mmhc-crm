@extends('layouts.legal')

@section('title', 'Privacy Policy')

@section('content')
@php
    $contactEmail = \App\Models\SiteSetting::get('contact_email', 'Care@themmhc.com');
    $contactPhone = \App\Models\SiteSetting::get('contact_phone', '9113311256');
    $contactAddress = \App\Models\SiteSetting::get('contact_address', 'Udgam Incubation Centre, Rohit Nagar Phase 1 (Near Surya Children School), Bhopal 462023, Madhya Pradesh, India');
    $website = \App\Models\SiteSetting::get('contact_website', 'www.themmhc.com');
@endphp

<div class="gradient-bg py-10 sm:py-14">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center text-white">
        <p class="text-sm uppercase tracking-wider opacity-90 mb-2">Legal</p>
        <h1 class="text-3xl sm:text-4xl font-bold mb-3">Privacy Policy</h1>
        <p class="text-white/90 max-w-2xl mx-auto text-sm sm:text-base">
            How Med Miracle Health Care (MMHC) collects, uses, and protects your information when you use our website, mobile applications, and healthcare services.
        </p>
        <p class="text-white/75 text-xs sm:text-sm mt-4">Last updated: {{ now()->format('F j, Y') }}</p>
    </div>
</div>

<article class="max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-12">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-10 legal-prose text-sm sm:text-base">

        <p class="text-gray-600 mb-6">
            <strong>Med Miracle Health Care</strong> (“<strong>MMHC</strong>”, “<strong>we</strong>”, “<strong>us</strong>”, or “<strong>our</strong>”) operates
            <strong>{{ $website }}</strong> and related platforms that connect patients, caregivers, nursing staff, subscribers, and academic users
            with home healthcare, training, and community services across India. This Privacy Policy explains what personal data we collect,
            why we collect it, how we use and share it, and the choices you have.
        </p>
        <p class="text-gray-600 mb-8">
            By accessing our website, registering an account, booking a service, subscribing to a plan, using our academics portal, or contacting us,
            you agree to the practices described in this policy. If you do not agree, please do not use our services.
        </p>

        <h2>1. Who we are</h2>
        <p>
            <strong>Med Miracle Health Care (MMHC)</strong><br>
            Corporate office: {!! nl2br(e($contactAddress)) !!}<br>
            Email: <a href="mailto:{{ $contactEmail }}" class="text-blue-600 hover:underline">{{ $contactEmail }}</a><br>
            Phone: {{ $contactPhone }}<br>
            Website: {{ $website }}
        </p>
        <p>
            MMHC provides home healthcare coordination, caregiver and nursing services, healthcare membership plans,
            staff referral programmes, and academic training tools for nursing and healthcare institutions.
        </p>

        <h2>2. Information we collect</h2>
        <p>Depending on how you use MMHC, we may collect the following categories of information:</p>

        <h3>2.1 Information you provide directly</h3>
        <ul>
            <li><strong>Identity &amp; contact:</strong> name, email address, phone number, postal address or pincode, date of birth (where required).</li>
            <li><strong>Account credentials:</strong> login email/phone, password (stored in encrypted form), role (patient, nurse, caregiver, student, faculty, institution admin, etc.).</li>
            <li><strong>Profile &amp; professional details:</strong> photograph, qualifications, experience, service areas, institution affiliation, student or staff ID.</li>
            <li><strong>Healthcare &amp; service requests:</strong> care needs, preferred visit dates, contact person at the visit location, medical notes or documents you choose to upload.</li>
            <li><strong>Payments &amp; subscriptions:</strong> plan selection, payment method references, transaction IDs, payment screenshots or receipts you submit for verification.</li>
            <li><strong>Referrals &amp; rewards:</strong> referral codes used or shared, reward claims, and related verification details.</li>
            <li><strong>Academics:</strong> enrollment applications, batch assignments, assignment submissions, attendance records, exam attempts, and mentorship interactions.</li>
            <li><strong>Communications:</strong> messages sent through contact forms, support requests, community posts, comments, and feedback.</li>
        </ul>

        <h3>2.2 Information collected automatically</h3>
        <ul>
            <li><strong>Device &amp; usage data:</strong> IP address, browser type, device identifiers, pages visited, and approximate usage patterns.</li>
            <li><strong>Location data:</strong> when you use “find staff near me” or similar features, we may collect GPS or location coordinates you permit, to match you with available caregivers or nurses.</li>
            <li><strong>Cookies &amp; session data:</strong> see Section 8 below.</li>
            <li><strong>Log files:</strong> server logs for security, troubleshooting, and fraud prevention.</li>
        </ul>

        <h3>2.3 Information from third parties</h3>
        <ul>
            <li><strong>Payment partners</strong> (e.g. Razorpay): payment status, transaction references—we do not store full card or UPI credentials on our servers.</li>
            <li><strong>SMS/OTP providers:</strong> delivery status for one-time passwords used for login or phone verification.</li>
            <li><strong>Referring users or institutions</strong> who invite you to register or enroll.</li>
        </ul>

        <h2>3. How we use your information</h2>
        <p>We use personal data for legitimate business purposes, including to:</p>
        <ul>
            <li>Create and manage your account and authenticate your identity (including OTP verification).</li>
            <li>Process healthcare service bookings, assign staff, and coordinate visits.</li>
            <li>Administer subscription plans, verify payments, and activate or cancel memberships.</li>
            <li>Calculate incentives, referral commissions, staff payouts, and patient rewards where applicable.</li>
            <li>Operate the academics module: enrollments, coursework, assignments, exams, attendance, and reports.</li>
            <li>Provide customer support and respond to contact form or email enquiries.</li>
            <li>Send service-related notifications (booking updates, payment status, enrollment decisions)—not unsolicited marketing unless you have opted in where required by law.</li>
            <li>Improve our website, apps, security, and user experience through analytics and troubleshooting.</li>
            <li>Comply with applicable laws, respond to lawful requests, and protect MMHC, our users, and the public from fraud or harm.</li>
        </ul>

        <h2>4. Legal basis (where applicable)</h2>
        <p>
            We process personal data based on: (a) performance of a contract with you (e.g. providing a booked visit or subscription);
            (b) your consent (e.g. optional marketing, location access, document uploads);
            (c) legitimate interests (e.g. fraud prevention, platform security, improving services); and
            (d) legal obligations (e.g. tax, accounting, or regulatory requirements).
        </p>

        <h2>5. How we share information</h2>
        <p>We do not sell your personal data. We may share information only as needed:</p>
        <ul>
            <li><strong>With healthcare staff you book or are matched with</strong>—limited details required to perform the visit (name, contact, address, care notes).</li>
            <li><strong>With your institution</strong>—for students, faculty, and institute admins enrolled in our academics programme.</li>
            <li><strong>With payment and technology providers</strong> who process transactions or host our infrastructure under confidentiality obligations.</li>
            <li><strong>With administrators</strong> authorised by MMHC to manage operations, payouts, and compliance.</li>
            <li><strong>When required by law</strong>—courts, regulators, or law enforcement, or to protect rights, safety, and property.</li>
            <li><strong>Business transfers</strong>—if MMHC undergoes merger, acquisition, or asset sale, subject to continued protection of your data.</li>
        </ul>

        <h2>6. Sensitive personal data</h2>
        <p>
            Health-related information and identity documents you upload are treated with heightened care.
            We ask that you only share what is necessary for your care, enrollment, or verification.
            Access is restricted to authorised staff, assigned caregivers/nurses, and relevant institution administrators.
        </p>

        <h2>7. Data retention</h2>
        <p>
            We retain personal data for as long as your account is active or as needed to provide services, resolve disputes,
            enforce agreements, and meet legal retention requirements (e.g. financial records).
            When data is no longer required, we delete or anonymise it in accordance with our internal policies,
            except where law requires longer retention.
        </p>

        <h2>8. Cookies and similar technologies</h2>
        <p>
            Our website and applications use cookies and local storage to keep you signed in, remember preferences,
            protect against abuse, and understand how features are used. You can control cookies through your browser settings;
            disabling essential cookies may limit login and booking functionality.
        </p>

        <h2>9. Security</h2>
        <p>
            We implement reasonable technical and organisational measures—including encryption in transit (HTTPS),
            access controls, and secure password storage—to protect your information.
            No method of transmission or storage is 100% secure; please use a strong password and keep your login details confidential.
        </p>

        <h2>10. Your rights and choices</h2>
        <p>Subject to applicable law, you may have the right to:</p>
        <ul>
            <li>Access and receive a copy of personal data we hold about you.</li>
            <li>Correct inaccurate or incomplete information through your profile or by contacting us.</li>
            <li>Request deletion of your account, subject to legal and operational requirements (e.g. unpaid obligations, audit logs).</li>
            <li>Withdraw consent where processing is consent-based (without affecting prior lawful processing).</li>
            <li>Opt out of non-essential marketing communications.</li>
            <li>Disable location sharing via your device or browser settings.</li>
        </ul>
        <p>
            To exercise these rights, email <a href="mailto:{{ $contactEmail }}" class="text-blue-600 hover:underline">{{ $contactEmail }}</a>
            with your registered email or phone and a clear description of your request. We may verify your identity before responding.
        </p>

        <h2>11. Children</h2>
        <p>
            MMHC services are intended for users who can enter into a binding agreement under applicable law.
            Accounts for minors in academic programmes may be created or managed by a parent, guardian, or institution.
            If you believe we have collected a child’s data without appropriate consent, contact us promptly.
        </p>

        <h2>12. International users</h2>
        <p>
            MMHC is based in India and primarily serves users in India.
            If you access our services from outside India, your data may be processed and stored in India
            or on servers operated by our service providers in other jurisdictions with adequate safeguards.
        </p>

        <h2>13. Third-party links</h2>
        <p>
            Our website may link to social media or external sites (e.g. Facebook, Instagram, LinkedIn).
            We are not responsible for the privacy practices of those third parties.
            Please review their policies before providing personal information.
        </p>

        <h2>14. Changes to this policy</h2>
        <p>
            We may update this Privacy Policy from time to time.
            The “Last updated” date at the top will reflect the latest version.
            Material changes may be notified via the website or email where appropriate.
            Continued use after changes constitutes acceptance of the updated policy.
        </p>

        <h2>15. Contact us</h2>
        <p>
            For privacy-related questions, complaints, or requests regarding your personal data:
        </p>
        <p class="bg-gray-50 border border-gray-200 rounded-xl p-5 not-prose">
            <strong class="text-gray-800">Med Miracle Health Care — Privacy</strong><br>
            <span class="text-gray-600">{!! nl2br(e($contactAddress)) !!}</span><br>
            <span class="text-gray-600">Email: <a href="mailto:{{ $contactEmail }}" class="text-blue-600 hover:underline">{{ $contactEmail }}</a></span><br>
            <span class="text-gray-600">Phone: {{ $contactPhone }}</span>
        </p>
        <p class="text-xs text-gray-500 mt-8">
            This policy is provided for general information about MMHC’s privacy practices.
            It does not constitute legal advice. For specific legal concerns, please consult a qualified professional.
        </p>
    </div>
</article>
@endsection
