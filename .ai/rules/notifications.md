---
paths:
  - 'app/Modules/Notifications/**'
---

# Notifications

## Notifications actually deliver (push + broadcast + channel auth)
Dashboard broadcast persists a bilingual DB row then calls PushNotifier::deliverExisting() so it actually pushes (queued FCM) AND fires NotificationSent on the recipient's private channel — not just DB rows. PushNotifier::storeNotification() throws if $notifiable is not on the users table (notifications.user_id FK; prevents cross-table id collision with Managers). routes/channels.php authorizes notifications.{type}.{id} by identity and is wrapped in try/catch: registering a channel resolves the default broadcaster and the base ships no driver, so a missing driver must not break boot. FcmClient uses connectTimeout(5)/timeout(10)/retry(2). unreadForUser is bounded (limit 100) — use countUnreadForUser() for the exact total. GAPS to note: send-all still inserts per-user in one transaction (chunk for very large user bases); prod needs a queue worker + scheduler (README Background processing).
