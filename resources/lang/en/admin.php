<?php

/**
 * Admin module – general UI strings, status labels, common phrases.
 * Usage: __('admin.key') or __('admin.group.key')
 */
return [

    // ── Dashboard stats & panels ────────────────────────────────────────────
    'dash_total_products'   => 'Total Products',
    'dash_active_products'  => 'Active Products',
    'dash_total_pharmacies' => 'Pharmacies',
    'dash_total_reps'       => 'Sales Reps',
    'dash_orders_today'     => 'Orders Today',
    'dash_pending_orders'   => 'Pending Orders',
    'dash_confirmed_month'  => 'Confirmed This Month',
    'dash_sales_month'      => 'Sales This Month',
    'dash_payments_month'   => 'Payments This Month',
    'dash_low_stock'        => 'Low Stock Products',
    'dash_recent_orders'    => 'Latest Orders',
    'dash_recent_payments'  => 'Latest Payments',
    'dash_low_stock_list'   => 'Low Stock Alert',
    'dash_current_stock'    => 'Stock',
    'dash_min_stock'        => 'Min',

    // ── Order / payment statuses ────────────────────────────────────────────
    'order_status' => [
        'all'       => 'All',
        'draft'     => 'Draft',
        'pending'   => 'Pending',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
        'completed' => 'Completed',
        'processing'=> 'Processing',
    ],

    // ── Stock-movement types ────────────────────────────────────────────────
    'stock_type' => [
        'all'         => 'All',
        'opening'     => 'Opening',
        'purchase'    => 'Purchase',
        'sale'        => 'Sale',
        'sale_cancel' => 'Sale Cancel',
        'adjustment'  => 'Adjustment',
        'return_in'   => 'Return In',
        'return_out'  => 'Return Out',
    ],
    'stock_adjust_title'   => 'Record Manual Adjustment',
    'stock_current'        => 'Current Stock',

    // ── Orders / show page ──────────────────────────────────────────────────
    'order_title'             => 'Order #:number',
    'order_details'           => 'Order Details',
    'order_items'             => 'Order Items',
    'confirmed_at'            => 'Confirmed At',
    'cancelled_at'            => 'Cancelled At',
    'no_items'                => 'No items found.',
    'confirm_order'           => 'Confirm Order',
    'cancel_order'            => 'Cancel Order',
    'confirm_order_confirm'   => 'Are you sure you want to confirm this order?',
    'cancel_order_confirm'    => 'Are you sure you want to cancel this order?',
    'order_draft'             => 'Draft',

    // ── Confirm-delete dialogs (used in JS confirm()) ───────────────────────
    'delete_confirm'          => 'Are you sure you want to delete this record?',
    'delete_confirm_pharmacy' => 'Are you sure you want to delete this pharmacy?',
    'delete_confirm_company'  => 'Are you sure you want to delete this company?',
    'delete_confirm_product'  => 'Are you sure you want to delete this product?',

    // ── Payment methods ─────────────────────────────────────────────────────
    'payment_method' => [
        'all'   => 'All',
        'cash'  => 'Cash',
        'bank'  => 'Bank Transfer',
        'other' => 'Other',
    ],
    'payment_create'     => 'Record Payment',
    'payment_title'      => 'Payments',
    'payment_amount'     => 'Amount',
    'payment_method_lbl' => 'Payment Method',
    'payment_paid_at'    => 'Paid At',
    'payment_notes'      => 'Notes',
    'payment_order'      => 'Related Order (optional)',
    'select_pharmacy'    => 'Select Pharmacy',
    'select_order'       => 'No related order',
    'select_pharmacy_first' => 'Select a pharmacy first to filter orders',

    // ── Table / pagination UX ───────────────────────────────────────────────
    'per_page'           => 'Per page',
    'low_stock_filter'   => 'Low stock only',
    'any_form'           => 'Any form',
    'any_status'         => 'Any status',
    'filter_is_active'   => 'Status',

    // ── Statement page ──────────────────────────────────────────────────────
    'statement_title'         => 'Account Statement',
    'statement_for'           => 'Statement: :name',
    'statement_opening'       => 'Opening Balance',
    'statement_debit'         => 'Total Debits',
    'statement_credit'        => 'Total Credits',
    'statement_balance'       => 'Current Balance',
    'statement_entry_date'    => 'Date',
    'statement_entry_type'    => 'Type',
    'statement_entry_desc'    => 'Description',
    'statement_entry_amount'  => 'Amount',
    'statement_entry_order'   => 'Order',
    'statement_no_entries'    => 'No ledger entries found for the selected period.',
    'entry_type_debit'        => 'Debit',
    'entry_type_credit'       => 'Credit',

    // ── Dashboard top pharmacies ─────────────────────────────────────────────
    'dash_top_pharmacies'     => 'Top Pharmacies by Payments',

    // ── Search / filter placeholders ────────────────────────────────────────
    'placeholder_name_phone'      => 'Name or phone…',
    'placeholder_name_phone_area' => 'Name / phone / area…',
    'order_number_search'         => 'Order number…',
    'placeholder_address' => 'address',
];

