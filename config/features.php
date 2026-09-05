<?php
/**
 * Feature flags for Local Event Hub.
 *
 * Toggle a flag here to enable/disable a feature across the whole app.
 * Include this file (require_once) in any page that needs to check a flag.
 */

if (!function_exists('feature_enabled')) {
    function feature_enabled(string $feature): bool
    {
        // ── EDIT THESE FLAGS ──────────────────────────────────────
        $flags = [
            // Shopping cart: nav link, cart pages, "Add To Cart" button.
            'shopping_cart' => false,
        ];

        return (bool) ($flags[$feature] ?? false);
    }
}
