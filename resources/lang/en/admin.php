<?php

/**
 * Admin module – general UI strings, status labels, common phrases.
 * Usage: __('admin.key') or __('admin.group.key')
 */
return [

    // ── Order / payment statuses ────────────────────────────────────────────
    'order_status' => [
        'all'       => 'All',
        'pending'   => 'Pending',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
        'completed' => 'Completed',
        'processing'=> 'Processing',
    ],

    // ── Stock-movement types ────────────────────────────────────────────────
    'stock_type' => [
        'all'        => 'All',
        'in'         => 'In',
        'out'        => 'Out',
        'adjustment' => 'Adjustment',
        'return'     => 'Return',
    ],

    // ── Orders / show page ──────────────────────────────────────────────────
    'order_title'  => 'Order #:number',
    'confirmed_at' => 'Confirmed At',
    'cancelled_at' => 'Cancelled At',
    'no_items'     => 'No items found.',

    // ── Confirm-delete dialogs (used in JS confirm()) ───────────────────────
    'delete_confirm'          => 'Are you sure you want to delete this record?',
    'delete_confirm_pharmacy' => 'Are you sure you want to delete this pharmacy?',
    'delete_confirm_company'  => 'Are you sure you want to delete this company?',
    'delete_confirm_product'  => 'Are you sure you want to delete this product?',

    // ── Search / filter placeholders ────────────────────────────────────────
    'placeholder_name_phone'      => 'Name or phone…',
    'placeholder_name_phone_area' => 'Name / phone / area…',
];

