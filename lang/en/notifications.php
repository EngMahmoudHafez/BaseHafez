<?php

return [
    'notifications' => 'Notifications',

    'types' => [
        'general' => 'General',
        'system' => 'System',
        'promotion' => 'Promotion',
    ],

    'status' => [
        'read' => 'Read',
        'unread' => 'Unread',
    ],

    'fields' => [
        'user' => 'User',
        'users' => 'Users',
        'title' => 'Title',
        'title_ar' => 'Arabic title',
        'title_en' => 'English title',
        'body_ar' => 'Arabic content',
        'body_en' => 'English content',
        'type' => 'Type',
        'action_url' => 'Action URL',
        'is_read' => 'Status',
        'sent_at' => 'Sent at',
        'target_type' => 'Recipients',
    ],

    'target_types' => [
        'all' => 'All active users',
        'users' => 'Selected users',
    ],

    'actions' => [
        'send' => 'Send notification',
        'broadcast' => 'Send notification',
        'delete' => 'Delete',
        'delete_all' => 'Delete all',
    ],

    'messages' => [
        'fetched' => 'Notifications fetched successfully.',
        'broadcast_successfully' => 'Notification sent to :count users.',
        'marked_as_read' => 'Notification marked as read.',
        'all_marked_as_read' => ':count notifications marked as read.',
        'deleted_successfully' => 'Notification deleted successfully.',
        'read_deleted' => ':count read notifications deleted.',
        'all_deleted_successfully' => ':count notifications deleted.',
        'confirm_delete_all' => 'Delete every notification? This cannot be undone.',
        'no_notifications' => 'No notifications found.',
    ],

    'dashboard' => [
        'title' => 'Notification management',
        'description' => 'Send and review database notifications from one consistent screen.',
        'broadcast_notification' => 'Send a notification',
        'notification_details' => 'Notification details',
        'total_notifications' => 'Total notifications',
        'sent_today' => 'Sent today',
        'unread_notifications' => 'Unread',
        'select_target' => 'Choose recipients',
        'select_users' => 'Choose users',
    ],
];
