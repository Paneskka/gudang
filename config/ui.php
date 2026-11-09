<?php
// Komponen presentasi bersama, tanpa dependensi framework.
function ui_icon(string $name, string $class = ""): string
{
    $icons = [
        "grid" => '<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
        "box" => '<path d="m12 3 9 5v8l-9 5-9-5V8l9-5Z"/><path d="m3 8 9 5 9-5M12 13v8M7.5 5.5l9 5"/>',
        "in" => '<path d="M4 14v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5M12 3v12m-5-5 5 5 5-5"/>',
        "out" => '<path d="M4 14v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5M12 15V3m-5 5 5-5 5 5"/>',
        "chart" => '<path d="M4 3v17h17M9 16v-4m5 4V7m5 9v-6"/>',
        "users" => '<circle cx="9" cy="7" r="4"/><path d="M2 21v-3a7 7 0 0 1 14 0v3M16 3a4 4 0 0 1 0 8m3 4a6 6 0 0 1 3 5"/>',
        "arrow" => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
        "external" => '<path d="M14 3h7v7m0-7L10 14M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5"/>',
        "clock" => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        "check" => '<path d="m5 12 4 4L19 6"/>',
        "check-circle" => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        "calendar" => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18M8 15h2m4 0h2m-8 3h2"/>',
        "menu" => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        "close" => '<path d="m6 6 12 12M6 18 18 6"/>',
        "logout" => '<path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4m7-13 5 5-5 5M8 12h13"/>',
        "search" => '<circle cx="10.5" cy="10.5" r="7.5"/><path d="m16 16 5 5"/>',
        "plus" => '<path d="M12 5v14M5 12h14"/>',
        "lock" => '<rect x="4" y="10" width="16" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/>',
        "truck" => '<path d="M3 16V5h11v11H3Zm11-8h4l3 4v4h-7M3 16H1"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
        "pin" => '<path d="M19 10c0 6-7 11-7 11S5 16 5 10a7 7 0 0 1 14 0Z"/><circle cx="12" cy="10" r="2"/>',
        "print" => '<path d="M6 9V3h12v6M6 17H3v-7h18v7h-3M6 14h12v7H6zM17 11h1"/>',
    ];
    return '<svg class="icon ' . htmlspecialchars($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons["box"]) . '</svg>';
}

function ui_asset(string $path): string
{
    return htmlspecialchars($path . "?v=" . filemtime(dirname(__DIR__) . "/" . $path));
}

function ui_date(?string $value = "now"): string
{
    $months = [1 => "Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
    if ($value === null || ($time = strtotime($value)) === false) {
        return "—";
    }
    return date("d", $time) . " " . $months[(int) date("n", $time)] . " " . date("Y", $time);
}
