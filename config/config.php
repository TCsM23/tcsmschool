<?php
/**
 * Core configuration. Edit these values for your XAMPP install and school.
 */
declare(strict_types=1);

// --- Database (XAMPP defaults) -------------------------------------------
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'enrollment_db';
const DB_PORT = 3306;

// --- App ----------------------------------------------------------------
// URL path to the project folder, no trailing slash.
// If the project lives at C:\xampp\htdocs\tcsmschool, use '/tcsmschool'.
const BASE_URL = '/tcsmschool';

// --- School identity (replace the bracketed placeholders) ----------------
const SCHOOL_NAME    = '[SCHOOL NAME]';
const SCHOOL_ADDRESS = '[SCHOOL ADDRESS]';
const SCHOOL_CONTACT = '[SCHOOL CONTACT NO. / EMAIL]';

// Current term used when students enroll.
const SCHOOL_YEAR = '2026-2027';

// --- Session ------------------------------------------------------------
const SESSION_NAME = 'enroll_sid';

// Default timezone for created_at display.
date_default_timezone_set('Asia/Manila');
