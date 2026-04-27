<?php

/**
 * Products module translations.
 * Usage: __('products.key') or __('products.group.key')
 */
return [

    // ── Page titles ─────────────────────────────────────────────────────────
    'title'      => 'Products',
    'manage'     => 'Product Management',
    'add'        => 'Add New Product',
    'create'     => 'Create Product',
    'edit'       => 'Edit Product',
    'import'     => 'Import Products',
    'export_all' => 'Export All Products',
    'export_mine'=> 'Export Your Products',
    'no_records' => 'No products found in database.',

    // ── Form field labels ────────────────────────────────────────────────────
    'fields' => [
        'name'             => 'Product Name',
        'company'          => 'Company',
        'form'             => 'Pharmaceutical Form',
        'net_price_syp'    => 'Net Price (SYP)',
        'public_price_syp' => 'Public Price (SYP)',
        'quantity'         => 'Quantity',
        'price'            => 'Price',
        'description'      => 'Description',
        'unit'             => 'Unit',
        'barcode'          => 'Barcode',
        'min_stock'        => 'Minimum Stock',
        'is_active'        => 'Active',
        'in_stock'         => 'in stock',
        'created_by'       => 'Created By',
    ],

    // ── Pharmaceutical form options ─────────────────────────────────────────
    'forms' => [
        'tablet'     => 'Tablet',
        'capsule'    => 'Capsule',
        'syrup'      => 'Syrup',
        'injection'  => 'Injection',
        'cream'      => 'Cream',
        'ointment'   => 'Ointment',
        'drops'      => 'Drops',
        'spray'      => 'Spray',
        'powder'     => 'Powder',
        'gel'        => 'Gel',
        'solution'   => 'Solution',
        'suspension' => 'Suspension',
        'other'      => 'Other',
    ],

    // ── Import / export page ────────────────────────────────────────────────
    'template' => [
        'download'     => 'Download Template',
        'description'  => 'Download an Excel template file with the correct format for importing products.',
        'columns_info' => 'The template includes columns for Name, Price, Details, and Quantity.',
        'upload'       => 'Upload Products',
        'upload_desc'  => 'Upload your completed Excel file to import products.',
        'association'  => 'All products will be associated with your account.',
        'file_label'   => 'Excel File',
        'submit'       => 'Upload and Import',
    ],

    // ── Filter labels ────────────────────────────────────────────────────────
    'filters' => [
        'by_user'       => 'Filter by User',
        'by_company'    => 'Filter by Company',
        'all_users'     => 'All Users',
        'all_companies' => 'All Companies',
        'options'       => 'Filter Options',
        'apply'         => 'Apply Filters',
        'clear'         => 'Clear Filters',
    ],

    // ── Buttons ──────────────────────────────────────────────────────────────
    'buttons' => [
        'save'   => 'Save Product',
        'update' => 'Update Product',
        'back'   => 'Back to Products',
    ],
];

