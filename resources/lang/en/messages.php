<?php

/**
 * Flash / toast / feedback messages.
 * Usage: __('messages.key') or __('messages.key', ['param' => $value])
 */
return [
    // ── Generic CRUD ─────────────────────────────────────────────────────────
    'created_successfully' => ':resource created successfully.',
    'updated_successfully' => ':resource updated successfully.',
    'deleted_successfully' => ':resource deleted successfully.',

    // ── Companies ────────────────────────────────────────────────────────────
    'company_created' => 'Company created successfully.',
    'company_updated' => 'Company updated successfully.',
    'company_deleted' => 'Company deleted successfully.',

    // ── Pharmacies ───────────────────────────────────────────────────────────
    'pharmacy_created' => 'Pharmacy created successfully.',
    'pharmacy_updated' => 'Pharmacy updated successfully.',
    'pharmacy_deleted' => 'Pharmacy deleted successfully.',

    // ── Products ─────────────────────────────────────────────────────────────
    'product_created' => 'Product created successfully.',
    'product_updated' => 'Product updated successfully.',
    'product_deleted' => 'Product deleted successfully.',

    // ── Import ───────────────────────────────────────────────────────────────
    'import_success' => 'Successfully imported :count products.',
    'import_partial' => 'Import completed with errors. :count products imported.',
    'import_error'   => 'Error importing file: :error',

    // ── Users ────────────────────────────────────────────────────────────────
    'user_created' => 'User created successfully.',
    'user_updated' => 'User updated successfully.',
    'user_deleted' => 'User deleted successfully.',

    // ── Stock ─────────────────────────────────────────────────────────────────
    'adjustment_created' => 'Stock adjustment recorded successfully.',
    'adjustment_failed'  => 'Could not record adjustment: :error',

    // ── Orders ───────────────────────────────────────────────────────────────
    'order_confirmed' => 'Order confirmed successfully.',
    'order_cancelled' => 'Order cancelled successfully.',
    'order_confirm_failed' => 'Could not confirm order: :error',
    'order_cancel_failed'  => 'Could not cancel order: :error',

    // ── Payments ─────────────────────────────────────────────────────────────
    'payment_created' => 'Payment recorded successfully.',
    'payment_failed'  => 'Could not record payment: :error',

    // ── System configs ───────────────────────────────────────────────────────
    'configs_updated' => 'Configurations updated successfully.',
    'config_created'  => 'Configuration created successfully.',
    'config_deleted'  => 'Configuration deleted successfully.',
];
