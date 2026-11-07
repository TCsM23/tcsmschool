<?php
/**
 * Editable page content: courses, programs, mission/vision and events.
 * Replace the sample text with your school's own.
 */
declare(strict_types=1);

// Course code => full name. Used in the Add student form and Sections.
const COURSES = [
    'BSIT'  => 'Bachelor of Science in Information Technology',
    'BSBA'  => 'Bachelor of Science in Business Administration',
    'BSHM'  => 'Bachelor of Science in Hospitality Management',
    'BEED'  => 'Bachelor of Elementary Education',
    'BSCRIM'=> 'Bachelor of Science in Criminology',
];

const YEAR_LEVELS = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'];

// Programs shown on the dashboards, grouped by accrediting body.
const PROGRAMS = [
    'CHED' => [
        'BS Information Technology',
        'BS Business Administration',
        'BS Hospitality Management',
        'Bachelor of Elementary Education',
        'BS Criminology',
    ],
    'TESDA' => [
        'Computer Systems Servicing NC II',
        'Bookkeeping NC III',
        'Cookery NC II',
        'Food and Beverage Services NC II',
    ],
];

const MISSION = 'To provide accessible, quality education that develops competent, '
    . 'ethical and service-oriented graduates who contribute to the growth of their '
    . 'communities.';

const VISION = 'A leading institution in the region recognized for academic excellence, '
    . 'technical skills development and strong Christian values.';

// Latest events: date (Y-m-d), title, short detail.
const EVENTS = [
    ['2026-11-20', 'Second Semester Enrollment Opens', 'Online enrollment for continuing students runs until December 5.'],
    ['2026-12-12', 'Foundation Day Celebration', 'Campus-wide program, booth exhibits and the alumni homecoming.'],
    ['2027-01-08', 'Classes Resume', 'Start of second-semester classes for all college programs.'],
];
