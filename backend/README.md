# جراج أبو يوسف — Backend (Laravel + MySQL)

الواجهة الخلفية (Backend API) لمتجر وخدمات **جراج أبو يوسف** المتخصص في موتوسيكلات Zontes في مصر.

---

## 1. المتطلبات والتشغيل المحلي

### أ) تشغيل خادم MySQL
- تأكد من تشغيل خادم **MySQL** من خلال لوحة تحكم XAMPP (`C:\xampp\xampp-control.exe`) على المنفذ الافتراضي `3306`.
- أنشئ قاعدة البيانات باسم `aygarage` وترميز `utf8mb4_unicode_ci`:
  ```sql
  CREATE DATABASE aygarage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  ```

### ب) إعداد ملف البيئة (.env)
- انسخ ملف `.env.example` إلى `.env`:
  ```bash
  cp .env.example .env
  ```
- ولّد مفتاح التطبيق:
  ```bash
  php artisan key:generate
  ```
- عيّن كلمات مرور البيانات الأولية في ملف `.env` (إلزامية لعمل الـ Seeders):
  ```env
  SEED_ADMIN_PASSWORD=admin123456
  SEED_DEMO_PASSWORD=123456
  ```
- تأكد من ضبط قرص التخزين:
  ```env
  FILESYSTEM_DISK=public
  ```
- تفعيل رابط ملفات التخزين العامة:
  ```bash
  php artisan storage:link
  ```

### ج) بناء الجداول وتوليد البيانات الأولية (Migration & Seeding)
- قم بتشغيل الأمر التالي لإنشاء الجداول وحشو كل البيانات الأولية:
  ```bash
  php artisan migrate:fresh --seed
  ```
> **ملاحظة بخصوص أقسام المنتجات (Categories):**
> تعرض الواجهة وAPI الأقسام (`/api/categories`) عدد المنتجات الحقيقي الفعلي (`count`) المحسوب من قاعدة البيانات لكل قسم وليس أرقاماً وهمية.

### د) تشغيل السيرفر
- لتشغيل سيرفر الـ API المحلي على المنفذ `8000`:
  ```bash
  php artisan serve
  ```

---

## 2. الحسابات التجريبية (Demo Accounts)

| الحساب | الدور | رقم الموبايل | كلمة المرور | الوصف |
|---|---|---|---|---|
| **المدير (Admin)** | `admin` | `01000000001` | المسجلة في `SEED_ADMIN_PASSWORD` | لوحة التحكم الإدارية وإدارة المتجر والطلبات |
| **العميل (كريم محمود)** | `customer` | `01012345678` | المسجلة في `SEED_DEMO_PASSWORD` | حساب العميل التجريبي المسجل بموتوسيكل ZT350-T وعنوان وسجل صيانة سابق وطلبات |

---

## 3. اختبارات النظام
- لتشغيل الاختبارات الآلية:
  ```bash
  php artisan test
  ```
