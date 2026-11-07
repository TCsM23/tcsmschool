<?php
/**
 * Small shared helpers: escaping, URLs, redirects, flash toasts, money, fees.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/fees.php';
require_once __DIR__ . '/../config/content.php';
require_once __DIR__ . '/icons.php';

/** Escape for HTML output. Use on every value echoed into a page. */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** App-relative URL: url('admin/students.php') → /tcsmschool/admin/students.php */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** URL for a file in /assets with a cache-busting version. */
function asset(string $path): string
{
    $file = __DIR__ . '/../assets/' . ltrim($path, '/');
    $v = is_file($file) ? filemtime($file) : 0;
    return url('assets/' . ltrim($path, '/')) . '?v=' . $v;
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

// --- Flash messages (shown as toasts on the next page) ---------------------

/** $type: success | error | info */
function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

function take_flashes(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

// --- Request input --------------------------------------------------------

/** Trimmed string from $_POST (or '' if missing / not a string). */
function post_str(string $key, int $maxLen = 255): string
{
    $v = $_POST[$key] ?? '';
    if (!is_string($v)) {
        return '';
    }
    return mb_substr(trim($v), 0, $maxLen);
}

function post_int(string $key, int $default = 0): int
{
    $v = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);
    return $v === false || $v === null ? $default : $v;
}

function get_str(string $key, int $maxLen = 100): string
{
    $v = $_GET[$key] ?? '';
    return is_string($v) ? mb_substr(trim($v), 0, $maxLen) : '';
}

function get_int(string $key, int $default = 0): int
{
    $v = filter_var($_GET[$key] ?? null, FILTER_VALIDATE_INT);
    return $v === false || $v === null ? $default : $v;
}

/** Empty string → null, for optional DB columns. */
function nullable(string $v): ?string
{
    return $v === '' ? null : $v;
}

// --- Formatting -----------------------------------------------------------

function peso(float|string $amount): string
{
    return FEE_CURRENCY . number_format((float) $amount, 2);
}

function format_time(string $time): string
{
    return date('g:i A', strtotime($time));
}

/** "MWF · 8:00 AM – 9:00 AM" */
function format_schedule(array $subject): string
{
    return $subject['schedule_day'] . ' · ' . format_time($subject['time_start'])
        . ' – ' . format_time($subject['time_end']);
}

function format_date(?string $date, string $fmt = 'M j, Y'): string
{
    return $date ? date($fmt, strtotime($date)) : '';
}

function semester_label(int $sem): string
{
    return $sem === 1 ? '1st Semester' : '2nd Semester';
}

function year_label(int $year): string
{
    return YEAR_LEVELS[$year] ?? ($year . ' Year');
}

/** "Dela Cruz, Juan P." */
function student_name(array $s): string
{
    $mi = trim((string) ($s['middle_initial'] ?? ''));
    return $s['last_name'] . ', ' . $s['first_name'] . ($mi !== '' ? ' ' . rtrim($mi, '.') . '.' : '');
}

// --- Fees -----------------------------------------------------------------

/** Fee for one subject line on the assessment: units × rate + lab + tutorial. */
function subject_fee(array $subject): float
{
    return (int) $subject['units'] * FEE_PER_UNIT
        + ((int) $subject['has_lab'] ? FEE_LAB : 0)
        + ((int) $subject['has_tutorial'] ? FEE_TUTORIAL : 0);
}

/**
 * Full fee breakdown for a list of subjects (each needs units, has_lab, has_tutorial).
 * Returns units, tuition, lab, tutorial, entrance, internet, misc, fixed, total.
 */
function compute_fees(array $subjects): array
{
    $units = 0;
    $labs = 0;
    $tutorials = 0;
    foreach ($subjects as $s) {
        $units     += (int) $s['units'];
        $labs      += (int) $s['has_lab'] ? 1 : 0;
        $tutorials += (int) $s['has_tutorial'] ? 1 : 0;
    }

    $tuition  = $units * FEE_PER_UNIT;
    $lab      = $labs * FEE_LAB;
    $tutorial = $tutorials * FEE_TUTORIAL;
    $fixed    = FEE_ENTRANCE + FEE_INTERNET + FEE_MISC;

    return [
        'units'     => $units,
        'labs'      => $labs,
        'tutorials' => $tutorials,
        'tuition'   => $tuition,
        'lab'       => $lab,
        'tutorial'  => $tutorial,
        'entrance'  => FEE_ENTRANCE,
        'internet'  => FEE_INTERNET,
        'misc'      => FEE_MISC,
        'fixed'     => $fixed,
        'total'     => $tuition + $lab + $tutorial + $fixed,
    ];
}
