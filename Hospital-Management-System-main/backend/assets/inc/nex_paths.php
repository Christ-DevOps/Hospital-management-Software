<?php
/**
 * Admin portal path helpers for sector subfolders.
 *
 * Pages in backend/admin/ leave $nex_admin_base unset or ''.
 * Pages in backend/admin/{sector}/ set $nex_admin_base = '../' before including layout.
 */

function nex_admin_base() {
    return isset($GLOBALS['nex_admin_base']) ? (string) $GLOBALS['nex_admin_base'] : '';
}

/** Web path relative to current script, prefixed for sector depth. */
function nex_admin_url($path) {
    $path = ltrim((string) $path, '/');
    return nex_admin_base() . $path;
}

/** Filesystem path to backend/ from a page under admin/ or admin/{sector}/. */
function nex_backend_fs($from_dir = null) {
    $from_dir = $from_dir ?: (dirname($_SERVER['SCRIPT_FILENAME'] ?? __DIR__));
    // admin/*.php → .. ; admin/sector/*.php → ../..
    $base = nex_admin_base();
    if ($base === '../' || substr($base, -3) === '../') {
        return $from_dir . '/../..';
    }
    return $from_dir . '/..';
}
