<?php

/**
 * Pharmacies module – English translations.
 * Usage: __('pharmacies.key') or __('pharmacies.group.key')
 */
return [

    // ── Page titles ─────────────────────────────────────────────────────────
    'title'      => 'Pharmacies',
    'add'        => 'Add Pharmacy',
    'edit'       => 'Edit Pharmacy',
    'show'       => 'Pharmacy Details',
    'manage'     => 'Manage Pharmacies',
    'no_records' => 'No pharmacies found.',

    // ── Form field labels ────────────────────────────────────────────────────
    'fields' => [
        'name'            => 'Pharmacy Name',
        'phone'           => 'Phone',
        'address'         => 'Address',
        'area'            => 'Area',
        'rep'             => 'Sales Rep',
        'credit_limit'    => 'Credit Limit',
        'opening_balance' => 'Opening Balance',
        'notes'           => 'Notes',
        'is_active'       => 'Active',
        'status'          => 'Status',
        'created_at'      => 'Member Since',
    ],

    // ── Card section headings ────────────────────────────────────────────────
    'sections' => [
        'contact'   => 'Contact Information',
        'financial' => 'Financial Information',
        'orders'    => 'Recent Orders',
        'payments'  => 'Recent Payments',
        'statement' => 'Account Ledger',
    ],

    // ── Filter labels & placeholders ─────────────────────────────────────────
    'filters' => [
        'search'            => 'Search',
        'search_placeholder'=> 'Name / phone / area…',
        'by_rep'            => 'Filter by Rep',
        'all_reps'          => 'All Reps',
        'by_status'         => 'Status',
        'all'               => 'All',
        'apply'             => 'Apply Filters',
        'clear'             => 'Clear',
    ],

    // ── Flash / confirm messages ─────────────────────────────────────────────
    'messages' => [
        'created'        => 'Pharmacy created successfully.',
        'updated'        => 'Pharmacy updated successfully.',
        'deleted'        => 'Pharmacy deleted successfully.',
        'delete_confirm' => 'Are you sure you want to delete this pharmacy?',
    ],

    // ── Buttons ──────────────────────────────────────────────────────────────
    'buttons' => [
        'add'    => 'Add Pharmacy',
        'save'   => 'Save Pharmacy',
        'update' => 'Update Pharmacy',
        'view'   => 'View Details',
        'edit'   => 'Edit',
        'delete' => 'Delete',
        'back'   => 'Back to Pharmacies',
        'cancel' => 'Cancel',
    ],

    // ── Show page stats ──────────────────────────────────────────────────────
    'stats' => [
        'total_orders'   => 'Total Orders',
        'total_payments' => 'Total Payments',
        'balance'        => 'Account Balance',
        'no_orders'      => 'No orders yet.',
        'no_payments'    => 'No payments yet.',
        'view_all'       => 'View All',
    ],
];

