# تقرير تجهيز Laravel Base

**التاريخ:** 2026-07-13  
**الحالة:** الأساس منظم وقابل لإعادة الاستخدام؛ إعادة تشغيل Feature suite النهائية تحتاج database driver متاحًا.  
**النطاق المدعوم:** `Auth`, `Base`, `Notifications`, `Structure`

> **⚠️ تقرير تاريخي (تم تجاوزه):** هذا لقطة نقطة زمنية بتاريخ 2026-07-13. تجاوزته دورة hardening
> لاحقة، فبعض بنوده لم تعد دقيقة — مثل عدد المسارات (`113`)، وادعاء إغلاق XSS، و«صفر advisories».
> للحالة الحالية راجع `CHANGELOG.md`، وقواعد `.ai/rules/`، وناتج `composer check` / `npm audit`.

## النتيجة

أصبح `app/Modules` هو مصدر الحقيقة، ولا توجد مراجع runtime إلى modules المشروع السابق. تم حذف 77 ملف اختبار/fixture غير تابع للنطاق الحالي، ونُقلت الشاشات والـcontrollers والـrequests والـresources والـservices والـrepositories إلى المسارات القياسية المكتوبة في `AGENTS.md`. أصبح `Base` للبنية المشتركة فقط، بينما تملك كل من Auth وNotifications وStructure شاشاتها وسلوكها.

تم أيضًا إخراج `AGENTS.md` و`composer.lock` من `.gitignore` حتى تنتقل القواعد ونسخ التبعيات المقفلة إلى أي clone جديد. ملفات الاختبار المؤقتة داخل `storage/framework/testing` أصبحت ignored ولا تدخل المستودع.

## أهم الإصلاحات

- فصل Auth عن Tracking/CRM، وإزالة الـmiddleware والـmodels والـrepositories والـcommands الميتة.
- حذف أسطح تحديد المستوى والحضور الإلكتروني والنزاهة الأكاديمية كاملة لأنها بقايا تعليمية بلا API أو menu مستهلك في الأساس الحالي.
- إصلاح password recovery الذي كان يعتمد على جدول غير موجود. OTP الآن مربوط بالمستخدم والغرض والقناة، كوده hashed، صلاحيته خمس دقائق، محاولاته محدودة، ويُستهلك مرة واحدة. Reset token منفصل، hashed في cache، ويُستهلك بعد النجاح.
- منع إعادة استخدام OTP بين login وpassword reset، ومنع كشف وجود البريد من استجابة forgot-password، وجعل الكود الثابت `1111` محصورًا في `local/testing`.
- جعل seeders قابلة للتكرار وغير مدمرة، وحذف demo users وكلمات المرور الثابتة. إنشاء أول manager اختياري عبر `BASE_ADMIN_*` فقط.
- تحويل Notifications إلى database-notification module عام بلا ارتباط بالدورات أو الاجتماعات أو الدفع، والإبقاء على cleanup schedule واحد.
- حماية CSV exports من formula injection، وتشديد رفع/استبدال الملفات والـrollback، وإخفاء profile APIs خلف authentication.
- إغلاق stored/reflected XSS في Quill والـpolicies والـflash messages وtheme cookies، واعتماد escaped Blade و`@js` كحدود افتراضية.
- جعل CORS allowlist وcredentials تحت تحكم البيئة، وإزالة debug auth وmanual PUT parser الخطرين.
- تحديث generator لعمل canonical module layout، وتسجيل model seeders تلقائيًا، وإنشاء dashboard requests بحد أدنى من تحقق manager.

## توحيد لوحة التحكم

- كل الشاشات الحالية تستخدم Base dashboard layout، مع `x-dashboard.page-header` وtokens مشتركة للبطاقات والإحصائيات والنماذج والجداول وحالات الفراغ.
- تم توحيد Bootstrap 5 وTabler icons والترجمات العربية والإنجليزية وإزالة استخدامات CDN وdemo build entries والأيقونات غير المستخدمة.
- Vite يبني entry points صريحة فقط. آخر build حوّل 170 module وولّد 36 Tabler icon مستخدمة بدل مكتبة الأيقونات كاملة.
- قواعد العرض، RTL/LTR، responsive behavior، وBlade/JavaScript safety موثقة في `docs/dashboard-design-system.md`.

## بوابات التحقق

| البوابة | النتيجة |
| --- | --- |
| `composer validate --strict` | ناجح؛ `composer.json` صالح |
| Composer optimized autoload + strict PSR | ناجح؛ 8386 class |
| PHP syntax لجميع ملفات التطبيق والاختبارات | ناجح |
| `vendor/bin/pint --test` | ناجح |
| Blade clear/cache | ناجح |
| Unit suite | 26 اختبارًا، 462 assertion، كلها ناجحة |
| آخر full suite على MySQL قبل إصلاحات الـguard النهائية | 59 اختبارًا، 550 assertion، كلها ناجحة |
| محاولة full suite النهائية محليًا | 29 نجحت و45 لم تبدأ بسبب غياب `pdo_sqlite` (`could not find driver`)، وليس بسبب assertion فاشل |
| Routes + menu contracts | 113 route؛ architecture/menu/dashboard-design tests ناجحة |
| Scheduler | مهمة واحدة: `notifications:cleanup` يوميًا |
| `npm run build` | ناجح في 16.19 ثانية |
| `git diff --check` + reference sweep | ناجح؛ لا namespaces أو route surfaces قديمة |
| dependency audits | آخر Composer وnpm audits على الـlocks الحالية: صفر advisories |

اختبار password recovery المركّز نجح سابقًا على MySQL في 5 اختبارات و29 assertion. أضيفت بعده تغطية cross-purpose وOTP cascade ونجح lint/unit architecture، لكنها ضمن الـFeature tests التي تحتاج إعادة تشغيل بعد توفير database driver.

## المطلوب قبل أول مشروع فعلي

1. أنشئ `.env` جديدًا من `.env.example`؛ لا تنسخ `.env` المحلي الحالي لأنه ملف خاص بالبيئة القديمة وغير معتمد كمصدر إعدادات.
2. اضبط قاعدة البيانات و`APP_URL` والبريد والتخزين وCORS، ثم ضع `BASE_ADMIN_EMAIL` وpassword من 12 حرفًا على الأقل لأول seed فقط.
3. شغّل `php artisan migrate --seed` ثم احذف bootstrap admin credentials من بيئة النشر بعد إنشاء الحساب.
4. اربط phone OTP بمزوّد SMS/WhatsApp في spec مستقل قبل production؛ الـbase يولّد ويتحقق من OTP لكنه لا يفترض vendor بعينه.
5. ثبّت `pdo_sqlite` أو عرّف قاعدة اختبار disposable، ثم شغّل `php artisan test --compact` لإغلاق بوابة T016.

## صيانة مؤجلة غير حاجبة

- تحديث لاحق: حُذفت `geniusts/hijri-dates` و`laravel/sanctum` فعليًا (مصادقة API عبر JWT)؛ أما `intervention/image` فأُبقي عليها وثُبّتت على `^3.8` (v3). راجع `AGENTS.md` §7. عند حذف أي حزمة مستقبلًا استخدم أمر Composer في maintenance window فيه network، ولا تعدّل `composer.lock` يدويًا.
- Vuexy/Bootstrap retained intentionally. ملف core CSS ما زال كبيرًا نسبيًا؛ تحسينه أو استبدال القالب قرار منفصل حسب المنتج الجديد.
- لا توجد demo data أو modules كاملة للدفع أو التعليم أو CRM في الأساس. عقد `student/parent` ودور `teacher` وقسم المدرسين لم تعد موجودة — أزالتها دورة الـhardening ضمن de-productization لوحدة Auth (راجع `AGENTS.md` §7 و`CHANGELOG.md`)؛ الأساس يشحن مصادقة عامة وحسابات manager عامة، لا مخطط منتج تعليمي بعينه.

## طريقة العمل بعد ذلك

ابدأ من `AGENTS.md` في كل مهمة، ثم أنشئ `spec.md` و`plan.md` و`tasks.md` داخل رقم جديد تحت `specs/`. أضف كل قاعدة متكررة إلى قسم `Persistent project decisions`، وأضف architecture test معها عندما تكون قابلة للقياس. استخدم generators الحالية، ثم أكمل validation وauthorization والroutes والviews والtests بدل نسخ module قديم.
