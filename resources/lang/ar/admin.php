<?php

/**
 * وحدة الإدارة – نصوص واجهة المستخدم العامة، تسميات الحالات، العبارات الشائعة.
 * الاستخدام: __('admin.key')
 */
return [

    // ── إحصائيات ولوحات لوحة التحكم ────────────────────────────────────────
    'dash_total_products'   => 'إجمالي المنتجات',
    'dash_active_products'  => 'المنتجات النشطة',
    'dash_total_pharmacies' => 'الصيدليات',
    'dash_total_reps'       => 'مندوبو المبيعات',
    'dash_orders_today'     => 'طلبات اليوم',
    'dash_pending_orders'   => 'الطلبات المعلّقة',
    'dash_confirmed_month'  => 'مؤكد هذا الشهر',
    'dash_sales_month'      => 'مبيعات هذا الشهر',
    'dash_payments_month'   => 'مدفوعات هذا الشهر',
    'dash_low_stock'        => 'منتجات نقص المخزون',
    'dash_recent_orders'    => 'آخر الطلبات',
    'dash_recent_payments'  => 'آخر المدفوعات',
    'dash_low_stock_list'   => 'تنبيه نقص المخزون',
    'dash_current_stock'    => 'المخزون',
    'dash_min_stock'        => 'الحد الأدنى',

    // ── حالات الطلبات / المدفوعات ───────────────────────────────────────────
    'order_status' => [
        'all'        => 'الكل',
        'draft'      => 'مسودة',
        'pending'    => 'قيد الانتظار',
        'confirmed'  => 'مؤكد',
        'cancelled'  => 'ملغى',
        'completed'  => 'مكتمل',
        'processing' => 'قيد المعالجة',
    ],

    // ── أنواع حركات المخزون ─────────────────────────────────────────────────
    'stock_type' => [
        'all'         => 'الكل',
        'opening'     => 'رصيد افتتاحي',
        'purchase'    => 'شراء',
        'sale'        => 'بيع',
        'sale_cancel' => 'إلغاء بيع',
        'adjustment'  => 'تسوية',
        'return_in'   => 'مرتجع وارد',
        'return_out'  => 'مرتجع صادر',
    ],
    'stock_adjust_title'   => 'تسجيل تسوية يدوية',
    'stock_current'        => 'المخزون الحالي',

    // ── صفحة تفاصيل الطلب ─────────────────────────────────────────────────
    'order_title'             => 'طلب رقم :number',
    'order_details'           => 'تفاصيل الطلب',
    'order_items'             => 'عناصر الطلب',
    'confirmed_at'            => 'تاريخ التأكيد',
    'cancelled_at'            => 'تاريخ الإلغاء',
    'no_items'                => 'لا توجد عناصر.',
    'confirm_order'           => 'تأكيد الطلب',
    'cancel_order'            => 'إلغاء الطلب',
    'confirm_order_confirm'   => 'هل أنت متأكد من تأكيد هذا الطلب؟',
    'cancel_order_confirm'    => 'هل أنت متأكد من إلغاء هذا الطلب؟',
    'order_draft'             => 'مسودة',

    // ── نوافذ تأكيد الحذف ──────────────────────────────────────────────────
    'delete_confirm'          => 'هل أنت متأكد من حذف هذا السجل؟',
    'delete_confirm_pharmacy' => 'هل أنت متأكد من حذف هذه الصيدلية؟',
    'delete_confirm_company'  => 'هل أنت متأكد من حذف هذه الشركة؟',
    'delete_confirm_product'  => 'هل أنت متأكد من حذف هذا المنتج؟',

    // ── طرق الدفع ───────────────────────────────────────────────────────────
    'payment_method' => [
        'all'   => 'الكل',
        'cash'  => 'نقدي',
        'bank'  => 'تحويل بنكي',
        'other' => 'أخرى',
    ],
    'payment_create'     => 'تسجيل دفعة',
    'payment_title'      => 'المدفوعات',
    'payment_amount'     => 'المبلغ',
    'payment_method_lbl' => 'طريقة الدفع',
    'payment_paid_at'    => 'تاريخ الدفع',
    'payment_notes'      => 'ملاحظات',
    'payment_order'      => 'طلب مرتبط (اختياري)',
    'select_pharmacy'    => 'اختر صيدلية',
    'select_order'       => 'بدون طلب مرتبط',
    'select_pharmacy_first' => 'اختر صيدلية أولاً لتصفية الطلبات',

    // ── UX الجدول / الترقيم ───────────────────────────────────────────────
    'per_page'           => 'لكل صفحة',
    'low_stock_filter'   => 'نقص المخزون فقط',
    'any_form'           => 'أي شكل',
    'any_status'         => 'أي حالة',
    'filter_is_active'   => 'الحالة',

    // ── صفحة كشف الحساب ────────────────────────────────────────────────────
    'statement_title'         => 'كشف حساب',
    'statement_for'           => 'كشف حساب: :name',
    'statement_opening'       => 'الرصيد الافتتاحي',
    'statement_debit'         => 'إجمالي المديونية',
    'statement_credit'        => 'إجمالي المدفوعات',
    'statement_balance'       => 'الرصيد الحالي',
    'statement_entry_date'    => 'التاريخ',
    'statement_entry_type'    => 'النوع',
    'statement_entry_desc'    => 'الوصف',
    'statement_entry_amount'  => 'المبلغ',
    'statement_entry_order'   => 'الطلب',
    'statement_no_entries'    => 'لا توجد قيود محاسبية للفترة المحددة.',
    'entry_type_debit'        => 'مدين',
    'entry_type_credit'       => 'دائن',

    // ── أعلى الصيدليات في لوحة التحكم ──────────────────────────────────────
    'dash_top_pharmacies'     => 'أعلى الصيدليات بالمدفوعات',

    // ── عناصر نائبة للبحث / الفلاتر ─────────────────────────────────────
    'placeholder_name_phone'      => 'الاسم أو الهاتف…',
    'placeholder_name_phone_area' => 'الاسم / الهاتف / المنطقة…',
    'order_number_search'         => 'رقم الطلب…',
];

