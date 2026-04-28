<?php

/**
 * Payments module – all UI strings for the payments feature.
 * Usage: __('payments.key') or __('payments.group.key')
 *
 * Mirrors labels currently rendered via Helpers::translate() and __('admin.*')
 * so views can be migrated incrementally without breaking anything.
 */
return [

    // ── Titles / headings ────────────────────────────────────────────────────
    'title'        => 'Payments',
    'title_create' => 'Record Payment',

    // ── Fields ───────────────────────────────────────────────────────────────
    'pharmacy'   => 'Pharmacy',
    'order'      => 'Related Order (optional)',
    'amount'     => 'Amount',
    'method'     => 'Payment Method',
    'paid_at'    => 'Paid At',
    'notes'      => 'Notes',
    'created_by' => 'Recorded By',

    // ── Payment methods ──────────────────────────────────────────────────────
    'methods' => [
        'all'   => 'All',
        'cash'  => 'Cash',
        'bank'  => 'Bank Transfer',
        'other' => 'Other',
    ],

    // ── Actions ──────────────────────────────────────────────────────────────
    'action_create' => 'Record Payment',

    // ── Placeholders / hints ─────────────────────────────────────────────────
    'select_pharmacy'       => 'Select Pharmacy',
    'select_order'          => 'No related order',
    'select_pharmacy_first' => 'Select a pharmacy first to filter orders',

    // ── Success messages ─────────────────────────────────────────────────────
    'created' => 'Payment recorded successfully.',

    // ── Error messages ───────────────────────────────────────────────────────
    'failed'                  => 'Could not record payment: :error',
    'order_pharmacy_mismatch' => 'The selected order does not belong to this pharmacy.',
    'amount_must_be_positive' => 'Amount must be greater than zero.',
    'forbidden'               => 'This pharmacy is not assigned to you.',
    'not_found'               => 'Payment not found.',

    // ── Filters ──────────────────────────────────────────────────────────────
    'filter_pharmacy'  => 'Pharmacy',
    'filter_date_from' => 'From',
    'filter_date_to'   => 'To',

    // ── Table columns ────────────────────────────────────────────────────────
    'col_id'         => '#',
    'col_pharmacy'   => 'Pharmacy',
    'col_order'      => 'Order Number',
    'col_amount'     => 'Amount',
    'col_method'     => 'Method',
    'col_paid_at'    => 'Paid At',
    'col_created_by' => 'Recorded By',

    // ── Footer / summary ─────────────────────────────────────────────────────
    'total_footer' => 'Total',

    // ── Empty state ──────────────────────────────────────────────────────────
    'no_payments' => 'No payments found.',
];

