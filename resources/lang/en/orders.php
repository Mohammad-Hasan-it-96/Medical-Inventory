<?php

/**
 * Orders module – all UI strings for the orders feature.
 * Usage: __('orders.key') or __('orders.group.key')
 *
 * Mirrors the labels currently rendered via Helpers::translate() and __('admin.*')
 * so views can be migrated incrementally without breaking anything.
 */
return [

    // ── Titles / headings ────────────────────────────────────────────────────
    'title'        => 'Orders',
    'title_show'   => 'Order #:number',
    'title_create' => 'Create Order',
    'title_edit'   => 'Edit Order',

    // ── Section cards ────────────────────────────────────────────────────────
    'details'    => 'Order Details',
    'items_card' => 'Order Items',

    // ── Fields ───────────────────────────────────────────────────────────────
    'order_number' => 'Order Number',
    'pharmacy'     => 'Pharmacy',
    'rep'          => 'Representative',
    'subtotal'     => 'Subtotal',
    'discount'     => 'Discount',
    'total'        => 'Total',
    'notes'        => 'Notes',
    'status'       => 'Status',
    'created_at'   => 'Created At',
    'confirmed_at' => 'Confirmed At',
    'cancelled_at' => 'Cancelled At',

    // ── Order-item fields ────────────────────────────────────────────────────
    'item_product'    => 'Product',
    'item_qty'        => 'Qty',
    'item_unit_price' => 'Unit Price',
    'item_discount'   => 'Discount',
    'item_total'      => 'Total',

    // ── Statuses ─────────────────────────────────────────────────────────────
    'statuses' => [
        'all'       => 'All',
        'draft'     => 'Draft',
        'pending'   => 'Pending',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
    ],

    // ── Actions ──────────────────────────────────────────────────────────────
    'action_confirm' => 'Confirm Order',
    'action_cancel'  => 'Cancel Order',
    'action_view'    => 'View',

    // ── Confirmation dialogs ─────────────────────────────────────────────────
    'confirm_dialog' => 'Are you sure you want to confirm this order?',
    'cancel_dialog'  => 'Are you sure you want to cancel this order?',

    // ── Success messages ─────────────────────────────────────────────────────
    'created'   => 'Order created successfully.',
    'confirmed' => 'Order confirmed successfully.',
    'cancelled' => 'Order cancelled successfully.',

    // ── Error messages ───────────────────────────────────────────────────────
    'confirm_failed'        => 'Could not confirm order: :error',
    'cancel_failed'         => 'Could not cancel order: :error',
    'insufficient_stock'    => "Insufficient stock for ':product'.",
    'already_cancelled'     => 'This order is already cancelled.',
    'invalid_status_confirm'=> "Cannot confirm an order with status ':status'.",
    'forbidden'             => 'You are not allowed to perform this action on the order.',
    'not_found'             => 'Order not found.',

    // ── Filters ──────────────────────────────────────────────────────────────
    'filter_status'    => 'Status',
    'filter_rep'       => 'Representative',
    'filter_pharmacy'  => 'Pharmacy',
    'filter_date_from' => 'From',
    'filter_date_to'   => 'To',

    // ── Table columns ────────────────────────────────────────────────────────
    'col_id'       => '#',
    'col_number'   => 'Order Number',
    'col_pharmacy' => 'Pharmacy',
    'col_rep'      => 'Rep',
    'col_total'    => 'Total',
    'col_status'   => 'Status',
    'col_date'     => 'Date',
    'col_actions'  => 'Actions',

    // ── Empty states ─────────────────────────────────────────────────────────
    'no_orders' => 'No orders found.',
    'no_items'  => 'No items found.',
];

