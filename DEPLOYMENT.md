# رفع موقع جراج أبو يوسف على الإنترنت

الملف ده للي هيرفع الموقع (مبرمج أو شركة استضافة). فيه اللي محتاجه بالظبط، وآخره الحاجات اللي صاحب الجراج لازم يعملها بنفسه.

## الموقع متكوّن من إيه

| الجزء | الفولدر | بيشتغل إزاي |
|---|---|---|
| واجهة الموقع (Vue) | جذر المشروع، والناتج في `dist/` | ملفات ثابتة بعد `npm run build` |
| السيرفر والـ API ولوحة التحكم (Laravel + Filament) | `backend/` | PHP 8.3 أو أحدث |
| قاعدة البيانات | — | MySQL 8 أو MariaDB 10.4 أو أحدث، ترميز `utf8mb4` |

الواجهة بتكلم الـ API على نفس الدومين (`/api/...`)، فأسهل حل إن الاتنين يبقوا على **نفس الدومين** على سيرفر واحد. ده بيلغي مشاكل CORS والكوكيز.

## 1. متطلبات السيرفر

- **PHP 8.3+** بالإضافات دي: `intl`، `pdo_mysql`، `mbstring`، `openssl`، `fileinfo`، `gd`، `zip`، `bcmath`، `curl`، `xml`. لوحة التحكم مش هتفتح من غير `intl`.
- **OPcache مفعّل**، عشان سرعة لوحة التحكم.
- Composer 2.
- Node.js 20 أو أحدث، لبناء الواجهة بس. ينفع تبنيها على جهازك وترفع `dist/`.
- Nginx أو Apache. الإعدادات تحت لـ Nginx.
- شهادة HTTPS، ومجانية من Let's Encrypt.

استضافة VPS صغيرة (1 CPU و2 GB رام) كفاية جداً للبداية.

## 2. خطوات الرفع

```bash
# 1. الكود
git clone <repo> /var/www/aygarage
cd /var/www/aygarage

# 2. الواجهة
npm ci
npm run build                      # يطلع dist/

# 3. السيرفر
cd backend
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

عدّل `backend/.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://الدومين-بتاعك.com
APP_TIMEZONE=Africa/Cairo
APP_LOCALE=ar
CORS_ALLOWED_ORIGINS=https://الدومين-بتاعك.com

DB_DATABASE=aygarage
DB_USERNAME=aygarage
DB_PASSWORD=كلمة-سر-قوية

# كلمة سر حساب المدير. اختار واحدة جديدة، ما تستخدمش القديمة اللي كانت على جهاز التطوير.
SEED_ADMIN_PASSWORD=...
SEED_DEMO_PASSWORD=...
```

بعدها:

```bash
php artisan migrate --seed --force   # أول مرة بس
php artisan storage:link             # عشان الصور المرفوعة تظهر
php artisan optimize                 # cache للإعدادات والروابط والصفحات
php artisan filament:optimize        # cache للوحة التحكم

chown -R www-data:www-data storage bootstrap/cache
```

> **مهم:** `APP_URL` لازم يبقى الدومين الحقيقي بـ `https`. روابط صور المنتجات بتتبني منه، ولو غلط الصور مش هتظهر.

بعد أي تحديث للكود: `git pull`، ثم `npm run build`، ثم `composer install --no-dev`، ثم `php artisan migrate --force`، ثم `php artisan optimize`، ثم `php artisan filament:optimize`.

**ما تشغّلش `migrate:fresh` على السيرفر الحقيقي أبداً.** الأمر ده بيمسح كل الطلبات والعملاء.

## 3. إعدادات Nginx (دومين واحد)

```nginx
server {
    listen 443 ssl http2;
    server_name الدومين-بتاعك.com;
    # ssl_certificate ... (من Let's Encrypt)

    root /var/www/aygarage/dist;        # الواجهة
    index index.html;
    client_max_body_size 10M;           # رفع الصور من لوحة التحكم

    # كل اللي يخص Laravel يروح لـ backend/public
    location ~ ^/(api|admin|livewire[^/]*|storage|filament|js/filament|css/filament|fonts/filament|up)(/|$) {
        root /var/www/aygarage/backend/public;
        try_files $uri /index.php?$query_string;
    }

    location = /index.php {
        root /var/www/aygarage/backend/public;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # أي صفحة تانية في الواجهة (/p/...، /account، ...) ترجع index.html
    location / {
        try_files $uri $uri/ /index.html;
    }

    location ~* \.(js|css|svg|png|jpg|jpeg|webp|woff2)$ {
        expires 30d;
        try_files $uri =404;
    }
}
```

## 4. اتأكد إن كل حاجة شغالة

- `https://الدومين/api/settings` بيرجّع JSON.
- الصفحة الرئيسية بتفتح، وصفحة منتج بتفتح، و"ريفريش" على `/account` ما بيطلعش 404.
- **لوحة التحكم:**
  - `https://الدومين/admin` بتفتح صفحة الدخول.
  - الدخول برقم المدير `01000000001` أو بالإيميل `admin@aygarage.com`، مع كلمة السر اللي في `SEED_ADMIN_PASSWORD`.
- ارفع صورة لمنتج من لوحة التحكم، واتأكد إنها ظهرت في صفحة المنتج.
- اعمل طلب تجريبي وحجز تجريبي، وبعدها الغيهم من لوحة التحكم. لما الطلب يتلغي المخزون بيرجع لوحده.

## 5. النسخ الاحتياطي (Backup)

لازم backup يومي لقاعدة البيانات ولفولدر الصور:

```bash
# كل يوم الساعة 3 الفجر (crontab -e)
0 3 * * * mysqldump -u aygarage -p'كلمة-السر' aygarage | gzip > /backups/db-$(date +\%F).sql.gz
0 3 * * * tar czf /backups/images-$(date +\%F).tgz -C /var/www/aygarage/backend/storage/app public
```

انقل النسخ لمكان تاني بره السيرفر (Google Drive مثلاً)، وامسح اللي عدّى عليه أكتر من 30 يوم.

## 6. مطلوب من صاحب الجراج

1. **كلمة السر:** اختار كلمة سر جديدة للمدير قبل الرفع. القديمة كانت مكتوبة في سكريبت تجربة داخل git على جهاز التطوير (اتشالت من الملف، بس لسه موجودة في التاريخ القديم للـ git).
2. **الصور:** الموقع فيه صور عرض توضيحية (صور موتوسيكلات Zontes حقيقية وصور قطع غيار من مواقع صور مجانية)، مش صور البضاعة اللي عندك. زجاجة الزيت في الصورة ممكن تكون ماركة تانية، وصورة الموتوسيكل المستعمل مش هي الموتوسيكل المعروض. قبل الافتتاح بدّلها بصور حقيقية من لوحة التحكم، من **المنتجات ← تعديل ← صور المنتج** (وكذلك **الموتوسيكلات المستعملة**). امسح صورة العرض وارفع صورتك. الحد الأقصى 2 ميجا للصورة. لو فضلت أي صورة من Wikimedia، سيب صفحة "مصادر الصور" (`/credits`) زي ما هي، لأن الرخصة بتطلب ذكر المصوّر. التفاصيل في [IMAGE_CREDITS.md](IMAGE_CREDITS.md).
3. **الإعدادات:** من **لوحة التحكم ← الإعدادات العامة** راجع:
   - رقم التليفون والواتساب: لسه `010 0000 0000`، ولازم يتغيروا للرقم الحقيقي.
   - سعر التوصيل وحد التوصيل المجاني.
   - مواعيد الحجز: ميعاد 12 الضهر و5 العصر مقفولين دلوقتي كاستراحة. لو مش استراحة، شيل علامة "مغلق / استراحة".
   - المحافظات والمناطق اللي بتوصّل لها.
4. **التأكيد للعميل:** الموقع بيقول للعميل "هنكلمك نأكد". مفيش رسايل بتتبعت أوتوماتيك، فلازم حد من الجراج يتابع **الطلبات الجديدة** و**حجوزات النهارده** في الصفحة الرئيسية للوحة التحكم ويكلم العميل.
5. **بيانات التجربة:** حساب "كريم محمود" (`01012345678`) وطلباته وحجوزاته بيانات تجريبية. امسحه من **العملاء** قبل الافتتاح، أو سيبه للتجربة.

## ملحوظة عن جهاز التطوير

المشروع على جهاز التطوير موجود جوه OneDrive، وده بيبطّأ Laravel جداً: أول فتحة للوحة التحكم ممكن تاخد أكتر من 30 ثانية. ده مش هيحصل على السيرفر. لو هتشتغل على الجهاز ده، انقل المشروع لفولدر بره OneDrive (زي `C:\dev\aygarage`)، أو شغّل السيرفر بالأمر ده من جوه `backend/public`:

```bash
PHP_CLI_SERVER_WORKERS=4 php -d max_execution_time=180 -S 127.0.0.1:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
```
