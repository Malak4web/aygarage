# مصادر الصور

صور العرض اللي في الموقع (الصفحة الرئيسية، المنتجات، الموتوسيكلات المستعملة) جاية من مصدرين مجانيين:

- **Wikimedia Commons**: صور حقيقية لموتوسيكلات Zontes. رخصتها CC BY أو CC BY-SA، ومحتاجة ذكر المصوّر ورابط الرخصة. عشان كده فيه صفحة `/credits` في الموقع (لينك "مصادر الصور" في الفوتر). **ما تشيلهاش** طول ما الصور دي موجودة.
- **Unsplash**: رخصة Unsplash، ببلاش للاستخدام التجاري ومش لازم ذكر المصدر. ذكرناه برضه للأمانة.

> **مهم قبل الافتتاح:** دي صور توضيحية، مش صور المنتجات والموتوسيكلات اللي عندك فعلاً. زجاجة الزيت في الصورة ممكن تكون ماركة تانية، وصورة الموتوسيكل المستعمل مش هي الموتوسيكل المعروض للبيع. بدّل كل صورة بصورة حقيقية من لوحة التحكم (تعديل المنتج ← صور المنتج)، وخصوصاً الموتوسيكلات المستعملة، لأن الزبون بيشتري على أساس الصورة.

الملفات: صور الصفحة الرئيسية في `public/images/`، وصور المنتجات والموتوسيكلات في `backend/database/seeders/images/`، وبيحطها `DemoImageSeeder` في التخزين مع `db:seed`. الـ seeder ما بيلمسش أي منتج عنده صور فعلاً.

| الصورة | المصدر | المصوّر | الرخصة | مستخدمة في |
|---|---|---|---|---|
| [Zontes motorcycle in Athens on 9-29-2023.jpg](https://commons.wikimedia.org/wiki/File:Zontes_motorcycle_in_Athens_on_9-29-2023.jpg) | Wikimedia Commons | George E. Koronaios | [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0) (مقصوصة ومصغّرة) | `seed: bikes/zt350t-2023-1.jpg` |
| [Zontes 125 Scrambler (2).jpg](https://commons.wikimedia.org/wiki/File:Zontes_125_Scrambler_(2).jpg) | Wikimedia Commons | Cjp24 | [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0) (مقصوصة ومصغّرة) | `seed: bikes/zt125u-2024-1.jpg` |
| [Zontes 125 Scrambler (1).jpg](https://commons.wikimedia.org/wiki/File:Zontes_125_Scrambler_(1).jpg) | Wikimedia Commons | Cjp24 | [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0) (مقصوصة ومصغّرة) | `seed: bikes/zt125u-2024-2.jpg` |
| [Zontes 125 G1.jpg](https://commons.wikimedia.org/wiki/File:Zontes_125_G1.jpg) | Wikimedia Commons | 802xr | [CC BY 4.0](https://creativecommons.org/licenses/by/4.0) (مقصوصة ومصغّرة) | `seed: bikes/zt350gk-2023-1.jpg` |
| [Zontes 703RR 2027.jpg](https://commons.wikimedia.org/wiki/File:Zontes_703RR_2027.jpg) | Wikimedia Commons | 802xr | [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0) (مقصوصة ومصغّرة) | `seed: bikes/zt310r-2022-1.jpg` |
| [Zontes 310X.jpg](https://commons.wikimedia.org/wiki/File:Zontes_310X.jpg) | Wikimedia Commons | Sporty76 | [CC BY 3.0](https://creativecommons.org/licenses/by/3.0) (مقصوصة ومصغّرة) | `seed: bikes/zt310x-2021-1.jpg` |
| [Zontes 350D.jpg](https://commons.wikimedia.org/wiki/File:Zontes_350D.jpg) | Wikimedia Commons | Corvettec6r | [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0) (مقصوصة ومصغّرة) | `seed: bikes/zt350d-2024-1.jpg` |
| [Zontes 350D in Avellino.jpg](https://commons.wikimedia.org/wiki/File:Zontes_350D_in_Avellino.jpg) | Wikimedia Commons | Corvettec6r | [CC BY 4.0](https://creativecommons.org/licenses/by/4.0) (مقصوصة ومصغّرة) | `seed: bikes/zt350d-2024-2.jpg` |
| [1767480350744-cedbd2757cdd](https://unsplash.com/photos/1767480350744-cedbd2757cdd) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/hero/kit.jpg` |
| [1642075223291-f9ec545889fa](https://unsplash.com/photos/1642075223291-f9ec545889fa) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/hero/oils.jpg`<br>`seed: products/motul-5100-15w50-1.jpg` |
| [1650569663338-f6921d483868](https://unsplash.com/photos/1650569663338-f6921d483868) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/hero/inspection.jpg` |
| [1611004061856-ccc3cbe944b2](https://unsplash.com/photos/1611004061856-ccc3cbe944b2) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/banners/helmets.jpg` |
| [1781036725578-aba4c81f27b6](https://unsplash.com/photos/1781036725578-aba4c81f27b6) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/banners/bikes.jpg` |
| [1636761358783-209512dccd98](https://unsplash.com/photos/1636761358783-209512dccd98) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/banners/workshop.jpg` |
| [1746014995126-7371261b371d](https://unsplash.com/photos/1746014995126-7371261b371d) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/categories/oils.jpg` |
| [1777118947168-b6e806cb80cf](https://unsplash.com/photos/1777118947168-b6e806cb80cf) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/categories/filters.jpg`<br>`seed: products/zontes-oil-filter-zt350-1.jpg` |
| [1779391220731-454e5c23c453](https://unsplash.com/photos/1779391220731-454e5c23c453) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/categories/brakes.jpg`<br>`seed: products/brembo-front-brake-pads-1.jpg` |
| [1765211003026-f7666ea3a948](https://unsplash.com/photos/1765211003026-f7666ea3a948) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/categories/batteries.jpg`<br>`seed: products/yuasa-ytz10s-battery-1.jpg` |
| [1558980394-4c7c9299fe96](https://unsplash.com/photos/1558980394-4c7c9299fe96) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/categories/tires.jpg`<br>`seed: products/pirelli-angel-st-rear-1.jpg` |
| [1525207106105-b340f7384b30](https://unsplash.com/photos/1525207106105-b340f7384b30) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/categories/transmission.jpg`<br>`seed: products/did-520-vx3-chain-2.jpg` |
| [1627530980937-b8721b91506a](https://unsplash.com/photos/1627530980937-b8721b91506a) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/categories/helmets.jpg`<br>`seed: products/ls2-rapid-ii-helmet-1.jpg` |
| [1782885044133-27c4df887d96](https://unsplash.com/photos/1782885044133-27c4df887d96) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `images/categories/accessories.jpg`<br>`seed: products/ngk-iridium-cr8eix-2.jpg` |
| [1571920335261-499ca164a5a9](https://unsplash.com/photos/1571920335261-499ca164a5a9) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/motul-7100-10w40-1.jpg` |
| [1613214293055-5678e2f6d7de](https://unsplash.com/photos/1613214293055-5678e2f6d7de) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/motul-300v-factory-line-10w40-1.jpg` |
| [1590227763209-821c686b932f](https://unsplash.com/photos/1590227763209-821c686b932f) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/castrol-power1-racing-10w40-1.jpg` |
| [1746014995485-e8a698f39804](https://unsplash.com/photos/1746014995485-e8a698f39804) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/liqui-moly-street-race-10w40-1.jpg` |
| [1746014995174-4cad395e151f](https://unsplash.com/photos/1746014995174-4cad395e151f) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/liqui-moly-street-race-10w40-2.jpg` |
| [1762604462421-fff920b0c418](https://unsplash.com/photos/1762604462421-fff920b0c418) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/shell-advance-ultra-10w40-1.jpg` |
| [1766933181278-16bf0bb27e13](https://unsplash.com/photos/1766933181278-16bf0bb27e13) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/mobil-1-racing-4t-15w50-1.jpg` |
| [1487754180451-c456f719a1fc](https://unsplash.com/photos/1487754180451-c456f719a1fc) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/repsol-moto-racing-10w40-1.jpg` |
| [1785575152310-ea47b0e0f95d](https://unsplash.com/photos/1785575152310-ea47b0e0f95d) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/zontes-oil-filter-zt350-2.jpg` |
| [1762012507780-060fe0bcc783](https://unsplash.com/photos/1762012507780-060fe0bcc783) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/brembo-front-brake-pads-2.jpg` |
| [1787832285754-14bec8337be0](https://unsplash.com/photos/1787832285754-14bec8337be0) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/ls2-rapid-ii-helmet-2.jpg` |
| [1769537754999-724044ec2e32](https://unsplash.com/photos/1769537754999-724044ec2e32) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/did-520-vx3-chain-1.jpg` |
| [1782885044249-863562f7e93e](https://unsplash.com/photos/1782885044249-863562f7e93e) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/ngk-iridium-cr8eix-1.jpg` |
| [1792591965213-0df73aff658d](https://unsplash.com/photos/1792591965213-0df73aff658d) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/alpinestars-smx-1-air-1.jpg` |
| [1763919417453-dea1172b98fa](https://unsplash.com/photos/1763919417453-dea1172b98fa) | Unsplash | — | [Unsplash License](https://unsplash.com/license) | `seed: products/alpinestars-smx-1-air-2.jpg` |
