---
paths:
  - 'app/Modules/Notifications/**'
---

# Notifications

## Notifications are polymorphic — keep the engine model-agnostic
Notifications are stored **polymorphically** (`notifications.notifiable_type` + `notifiable_id`, `Notification::notifiable()` is a `MorphTo`). `PushNotifier::send($notifiable, ...)` builds the row and `->notifiable()->associate($notifiable)` — any Eloquent model (User, Manager, or a project's Vendor/Driver/Customer) can receive notifications with no change here. Repository/service methods take `Model $notifiable` and scope with `whereMorphedTo('notifiable', $notifiable)` (`paginateFor/unreadFor/countUnreadFor/markAllAsReadFor/deleteReadFor/findForOrFail`). **The notification ENGINE must never import `Auth\Models\User`/`Manager`** — enforced by `FoundationArchitectureTest` (scanned surfaces: `Models`, `Services`, `Repositories`, `DTOs`, `Http/Services/Api`, `Http/Resources`). The *dashboard broadcast-to-users* admin feature (`Http/{Services,Controllers}/Dashboard`) and the factory legitimately reference User and are excluded from that test.

## Notifications actually deliver (push + broadcast + channel auth)
Dashboard broadcast persists a bilingual DB row then calls PushNotifier::deliverExisting() so it actually pushes (queued FCM) AND fires NotificationSent on the recipient's private channel — not just DB rows. routes/channels.php authorizes notifications.{type}.{id} by identity and is wrapped in try/catch: registering a channel resolves the default broadcaster and the base ships no driver, so a missing driver must not break boot. `HasDeviceTokens` lives in `App\Modules\Notifications\Concerns` (not Base). FcmClient uses connectTimeout(5)/timeout(10)/retry(2). unreadFor is bounded (limit 100) — use countUnreadFor() for the exact total. GAPS to note: send-all still inserts per-user in one transaction (chunk for very large user bases); prod needs a queue worker + scheduler (README Background processing).
