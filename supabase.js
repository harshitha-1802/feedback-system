// Supabase Configuration
const SUPABASE_URL = 'https://sxwcofbbfqqhwqurpbgk.supabase.co';
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InN4d2NvZmJiZnFxaHdxdXJwYmdrIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzQ1MjM4ODYsImV4cCI6MjA5MDA5OTg4Nn0.YhmR8WDV7HnQBngZVr0QannME9d_M7HAys_hHntlJt8';

// EmailJS Configuration
const EMAILJS_SERVICE_ID  = 'service_yww8wxe';
const EMAILJS_TEMPLATE_ID = 'template_fd264pg';
const EMAILJS_PUBLIC_KEY  = 'HKhx43qkIhDUqyBog';

// Initialize Supabase client
const { createClient } = supabase;
const db = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

// ─── Session Helpers ───────────────────────────────────────────────────────────

function setSession(userType, userData) {
    localStorage.setItem('user_type', userType);
    localStorage.setItem('user_data', JSON.stringify(userData));
}

function getSession() {
    return {
        user_type: localStorage.getItem('user_type'),
        user_data: JSON.parse(localStorage.getItem('user_data') || 'null')
    };
}

function clearSession() {
    localStorage.removeItem('user_type');
    localStorage.removeItem('user_data');
}

function requireAuth(expectedType) {
    const session = getSession();
    if (!session.user_type || session.user_type !== expectedType) {
        window.location.href = expectedType + '-login.html';
        return null;
    }
    return session.user_data;
}

// ─── Password Helpers ──────────────────────────────────────────────────────────

async function hashPassword(password) {
    const encoder = new TextEncoder();
    const data = encoder.encode(password);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

function validatePassword(password) {
    return /^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{6,}$/.test(password);
}

function validateEmail(email) {
    return /^[^\s@]+@bvrit\.ac\.in$/.test(email);
}

function validatePhone(phone) {
    return /^[0-9]{10}$/.test(phone);
}

// Send OTP via EmailJS
async function sendOTPEmail(to_email, otp) {
    return emailjs.send(EMAILJS_SERVICE_ID, EMAILJS_TEMPLATE_ID, {
        to_email: to_email,
        passcode: otp
    }, EMAILJS_PUBLIC_KEY);
}
