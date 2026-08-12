<?php

return [
    'notifications' => 'الإشعارات',

    'types' => [
        'general' => 'عام',
        'system' => 'النظام',
        'promotion' => 'ترويجي',
    ],

    'status' => [
        'read' => 'مقروء',
        'unread' => 'غير مقروء',
    ],

    'fields' => [
        'user' => 'المستخدم',
        'users' => 'المستخدمون',
        'title' => 'العنوان',
        'title_ar' => 'العنوان بالعربية',
        'title_en' => 'العنوان بالإنجليزية',
        'body_ar' => 'المحتوى بالعربية',
        'body_en' => 'المحتوى بالإنجليزية',
        'type' => 'النوع',
        'action_url' => 'رابط الإجراء',
        'is_read' => 'الحالة',
        'sent_at' => 'تاريخ الإرسال',
        'target_type' => 'المستلمون',
    ],

    'target_types' => [
        'all' => 'كل المستخدمين النشطين',
        'users' => 'مستخدمون محددون',
    ],

    'actions' => [
        'send' => 'إرسال الإشعار',
        'broadcast' => 'إرسال إشعار',
        'delete' => 'حذف',
        'delete_all' => 'حذف الكل',
    ],

    'messages' => [
        'fetched' => 'تم جلب الإشعارات بنجاح.',
        'broadcast_successfully' => 'تم إرسال الإشعار إلى :count مستخدم.',
        'marked_as_read' => 'تم تعليم الإشعار كمقروء.',
        'all_marked_as_read' => 'تم تعليم :count إشعار كمقروء.',
        'deleted_successfully' => 'تم حذف الإشعار بنجاح.',
        'read_deleted' => 'تم حذف :count إشعار مقروء.',
        'all_deleted_successfully' => 'تم حذف :count إشعار.',
        'confirm_delete_all' => 'هل تريد حذف كل الإشعارات؟ لا يمكن التراجع عن هذا الإجراء.',
        'no_notifications' => 'لا توجد إشعارات.',
    ],

    'dashboard' => [
        'title' => 'إدارة الإشعارات',
        'description' => 'إرسال ومراجعة إشعارات قاعدة البيانات من شاشة موحدة.',
        'broadcast_notification' => 'إرسال إشعار',
        'notification_details' => 'تفاصيل الإشعار',
        'total_notifications' => 'إجمالي الإشعارات',
        'sent_today' => 'المرسلة اليوم',
        'unread_notifications' => 'غير المقروءة',
        'select_target' => 'اختر المستلمين',
        'select_users' => 'اختر المستخدمين',
    ],
];
