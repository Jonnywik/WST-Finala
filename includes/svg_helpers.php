<?php
// Inline SVG icons, species avatars and status pills. No image files, no emojis,
// so nothing breaks when the page is opened offline.

function icon(string $name, int $size = 20): string
{
    static $paths = [
        'plus'        => '<path d="M5 12h14M12 5v14"/>',
        'eye'         => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'pencil'      => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
        'trash'       => '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M10 11v6M14 11v6"/>',
        'search'      => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'check'       => '<path d="M20 6 9 17l-5-5"/>',
        'clock'       => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'alert'       => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
        'heart'       => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
        'home'        => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
        'calendar'    => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'user'        => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'x'           => '<path d="M18 6 6 18M6 6l12 12"/>',
        'database'    => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/>',
        'arrow-left'  => '<path d="m12 19-7-7 7-7M19 12H5"/>',
        'clipboard'   => '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M9 12h6M9 16h6"/>',
        'stethoscope' => '<path d="M11 2v2M5 2v2"/><path d="M5 3H4a2 2 0 0 0-2 2v4a6 6 0 0 0 12 0V5a2 2 0 0 0-2-2h-1"/><path d="M8 15a6 6 0 0 0 12 0v-3"/><circle cx="20" cy="10" r="2"/>',
        'paw'         => '<circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="20" cy="16" r="2"/><path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"/>',
        'star'        => '<path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/>',
        'shield'      => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
        'pill'        => '<path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/>',
    ];
    $p = $paths[$name] ?? $paths['paw'];
    return '<svg class="ico" xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size
        . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . $p . '</svg>';
}

function brand_logo(int $size = 38): string
{
    return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 40 40" aria-hidden="true">'
        . '<path d="M20 35S5 26 5 15.5A8.5 8.5 0 0 1 20 10a8.5 8.5 0 0 1 15 5.5C35 26 20 35 20 35z" fill="#0D9488"/>'
        . '<g fill="#fff"><ellipse cx="20" cy="22.5" rx="4.6" ry="3.8"/><circle cx="13.8" cy="17.6" r="2"/><circle cx="20" cy="15" r="2.1"/><circle cx="26.2" cy="17.6" r="2"/></g></svg>';
}

function species_avatar(string $species, int $size = 48): string
{
    static $art = [
        'Dog' => '<circle cx="24" cy="24" r="24" fill="#FFEDD5"/>'
            . '<ellipse cx="11.5" cy="22" rx="5" ry="9" fill="#92400E" transform="rotate(12 11.5 22)"/>'
            . '<ellipse cx="36.5" cy="22" rx="5" ry="9" fill="#92400E" transform="rotate(-12 36.5 22)"/>'
            . '<circle cx="24" cy="25" r="12" fill="#D97706"/><ellipse cx="24" cy="30" rx="6.5" ry="5" fill="#FDE68A"/>'
            . '<circle cx="19.5" cy="22.5" r="1.6" fill="#1F2937"/><circle cx="28.5" cy="22.5" r="1.6" fill="#1F2937"/>'
            . '<ellipse cx="24" cy="28" rx="2.2" ry="1.6" fill="#1F2937"/>'
            . '<path d="M24 29.6v1.6M21.5 32q2.5 1.8 5 0" stroke="#1F2937" stroke-width="1.2" fill="none" stroke-linecap="round"/>',
        'Cat' => '<circle cx="24" cy="24" r="24" fill="#EDE9FE"/>'
            . '<path d="M12 23 13 9l9 7zM36 23 35 9l-9 7z" fill="#6D28D9"/>'
            . '<circle cx="24" cy="26" r="12" fill="#8B5CF6"/>'
            . '<ellipse cx="19.5" cy="24" rx="1.5" ry="2.2" fill="#1F2937"/><ellipse cx="28.5" cy="24" rx="1.5" ry="2.2" fill="#1F2937"/>'
            . '<path d="M22.4 28h3.2L24 30z" fill="#FBCFE8"/>'
            . '<path d="M21.8 31.4q1.1 1 2.2 0 1.1 1 2.2 0M15.5 28.5l-5-1M15.5 30.5l-5 1M32.5 28.5l5-1M32.5 30.5l5 1" stroke="#2E1065" stroke-width="1" fill="none" stroke-linecap="round"/>',
        'Rabbit' => '<circle cx="24" cy="24" r="24" fill="#FBCFE8"/>'
            . '<ellipse cx="18.5" cy="14" rx="3.8" ry="10" fill="#fff" transform="rotate(-8 18.5 14)"/>'
            . '<ellipse cx="29.5" cy="14" rx="3.8" ry="10" fill="#fff" transform="rotate(8 29.5 14)"/>'
            . '<ellipse cx="18.5" cy="14.5" rx="1.6" ry="6.5" fill="#F472B6" transform="rotate(-8 18.5 14.5)"/>'
            . '<ellipse cx="29.5" cy="14.5" rx="1.6" ry="6.5" fill="#F472B6" transform="rotate(8 29.5 14.5)"/>'
            . '<circle cx="24" cy="30" r="11" fill="#fff"/>'
            . '<circle cx="19.5" cy="28" r="1.6" fill="#1F2937"/><circle cx="28.5" cy="28" r="1.6" fill="#1F2937"/>'
            . '<ellipse cx="24" cy="32" rx="1.8" ry="1.3" fill="#DB2777"/><rect x="22.6" y="34" width="2.8" height="3" rx="0.8" fill="#E5E7EB"/>',
        'Bird' => '<circle cx="24" cy="24" r="24" fill="#E0F2FE"/>'
            . '<path d="M19 13q3-5 6 0" stroke="#0369A1" stroke-width="2" fill="none" stroke-linecap="round"/>'
            . '<circle cx="22" cy="25" r="12" fill="#0EA5E9"/><path d="M32.5 21.5 41 25l-8.5 3.5z" fill="#F59E0B"/>'
            . '<circle cx="26.5" cy="21.5" r="1.8" fill="#0F172A"/><path d="M11 27q6-5 12 1-6 7-12-1z" fill="#0369A1"/>',
        'Other' => '<circle cx="24" cy="24" r="24" fill="#CCFBF1"/>'
            . '<g fill="#0D9488"><ellipse cx="24" cy="30.5" rx="7.5" ry="6"/><circle cx="14.5" cy="23" r="3.2"/><circle cx="20.5" cy="16.5" r="3.2"/><circle cx="27.5" cy="16.5" r="3.2"/><circle cx="33.5" cy="23" r="3.2"/></g>',
    ];
    $key = isset($art[$species]) ? $species : 'Other';
    return '<svg class="avatar" xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size
        . '" viewBox="0 0 48 48" role="img" aria-label="' . e($key) . '">' . $art[$key] . '</svg>';
}

// Every status is shown as colour + icon + text, never colour alone.
function status_pill(string $status): string
{
    static $map = [
        'Available'        => ['mint', 'check'],
        'Pending Adoption' => ['amber', 'clock'],
        'Under Vet Care'   => ['lav', 'stethoscope'],
        'Adopted'          => ['coral', 'heart'],
        'Up to Date'       => ['mint', 'shield'],
        'Partial'          => ['amber', 'clock'],
        'Pending'          => ['coral', 'alert'],
        'Pending Review'   => ['amber', 'clock'],
        'Meet & Greet'     => ['lav', 'user'],
        'Approved'         => ['mint', 'check'],
        'Declined'         => ['coral', 'x'],
        'Vaccination'      => ['mint', 'shield'],
        'Checkup'          => ['sky', 'stethoscope'],
        'Deworming'        => ['amber', 'clipboard'],
        'Medication'       => ['lav', 'pill'],
        'Grooming'         => ['coral', 'star'],
    ];
    [$tone, $ico] = $map[$status] ?? ['sky', 'paw'];
    return '<span class="pill pill-' . $tone . '">' . icon($ico, 14) . e($status) . '</span>';
}
