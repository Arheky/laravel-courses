# 🎓 LaravelCourses

> **Modern, güvenli ve performanslı bir kurs yönetim platformu** — Laravel 12, Vue 3 ve Inertia.js ile geliştirilmiştir.

## 🌐 Canlı Demo
🔗 **https://laravel-courses.onrender.com**

---

## 🚀 Özellikler

### 👩‍💻 Admin Paneli

-   🔐 **Rol tabanlı yetkilendirme** (Policy + Gate)
-   🧩 **Güvenli CRUD** (Kurs, Ders, Öğrenci yönetimi)
-   🧱 **Policy yapısı:** `CoursePolicy`, `LessonPolicy`, `StudentPolicy`, `DashboardPolicy`
-   🧾 **Request doğrulama:** `StoreCourseRequest`, `UpdateCourseRequest`, `StoreLessonRequest`, `UpdateLessonRequest`, `ProfileUpdateRequest`, `DashboardRequest`
-   📚 **Kurs & Ders ilişkili yönetim sistemi**
-   📊 **Dashboard & İstatistik görünümü**
-   🔍 Akıllı arama, filtreleme ve pagination
-   💾 CSRF + Axios güvenlik doğrulaması
-   🧠 Yetki bazlı erişim kontrolü ve güvenli yönlendirme

---

### 👨‍🎓 Student Paneli

-   📄 Kurs listesini ve detaylarını görüntüleme
-   🧭 Ders içeriklerine ve **YouTube video bağlantılarına** erişim
-   🟢 Kurslara kayıt olma / kayıttan çıkma sistemi
-   📚 **“MyCourses”** sayfası (kişisel kurs arşivi)
-   📆 Kurs tarih, eğitmen ve ilerleme bilgisi
-   💬 Gelişmiş formlar ve canlı doğrulama mesajları
-   🎨 Responsive, sezgisel ve animasyonlu tasarım

---

### ⚙️ Ortak Özellikler

-   🌙 **Light / Dark tema** desteği (otomatik geçiş)
-   🧠 **Inertia.js + Vue 3 + Ziggy** entegrasyonu
-   🪶 **v-motion + Lottie** ile animasyonlu geçiş efektleri
-   💡 Şifre görünür/gizli butonu (🙈 / 👁️)
-   🔒 Policy & Request tabanlı erişim denetimi
-   💾 **Otomatik ilişki temizleme** & güvenli silme işlemleri
-   📱 Tüm cihazlarda tam **responsive** arayüz
-   ⚡ Optimize edilmiş performans & lazy yükleme
-   🔑 Laravel Breeze + Sanctum auth altyapısı
-   🧭 RateLimiter cache koruması (yenilemeden devam)

---

### 💜 Güvenlik & Bildirim Sistemi

-   📨 **Şifremi Unuttum & Şifre Yenileme** (token + toast + Lottie)
-   🔑 **Şifre politikası:** Minimum 8 karakter, `_` ve `*` destekli
-   ⏳ **Rate Limiter:** Fazla isteklerde otomatik kilit
-   🧩 **LocalStorage** üzerinden oturum ve form yönetimi
-   📣 **Dinamik Toast sistemi:** Başarı / Hata / Uyarı / Bilgi mesajları
-   🎞️ Lottie hourglass animasyonu ile bekleme efekti
-   🧠 403 / 404 / 419 / 422 / 500 hata sayfaları
-   🔐 CSRF Token & güvenli Axios POST işlemleri
-   💬 Şifre sıfırlama sonrası login engelleme
-   🧾 Güvenli profil & parola güncelleme sistemi

---

## 🧱 Kullanılan Teknolojiler

| Katman           | Teknoloji                                                        |
| ---------------- | ---------------------------------------------------------------- |
| **Backend**      | Laravel 12 (PHP 8.2), Sanctum, Policies, Gates, FormRequests     |
| **Frontend**     | Vue 3 (Composition API), Inertia.js, TailwindCSS, Motion, Lottie |
| **Database**     | MySQL / MariaDB                                                  |
| **Auth**         | Laravel Breeze + Sanctum                                         |
| **Animasyonlar** | `@vueuse/motion`, `lottie-web`                                   |
| **Routing**      | Ziggy (Laravel → Vue yönlendirme)                                |
| **UI/UX**        | Dark Mode, Responsive, Toast, Modal, Animasyonlu Formlar         |

---

## 🍩 Kullanıcı Rolleri

| Rol         | Yetkiler                                              |
| ----------- | ----------------------------------------------------- |
| **Admin**   | Kurs & Ders CRUD, Öğrenci Yönetimi, Dashboard erişimi |
| **Student** | Kurs kayıt/çıkış, Ders görüntüleme, Profil güncelleme |
| **Guest**   | Giriş / Kayıt sayfalarına erişim                      |

---

## 🌿 Özel Yapılar

### 🛡️ Policy Sınıfları

-   `CoursePolicy`
-   `LessonPolicy`
-   `StudentPolicy`
-   `DashboardPolicy`

### 📘 Request Sınıfları

-   `StoreCourseRequest`
-   `UpdateCourseRequest`
-   `StoreLessonRequest`
-   `UpdateLessonRequest`
-   `ProfileUpdateRequest`
-   `DashboardRequest`

---

## 💡 Geliştirici Notları

-   🔐 **CSRF ve RateLimiter** sistemi backend ve frontend arasında entegre çalışır.
-   🧠 **Tüm CRUD işlemleri Policy + Request** doğrulamasıyla korunur.
-   🎯 **Kullanıcı deneyimi (UX)**; tema geçişleri, animasyonlar ve toast sistemiyle güçlendirilmiştir.
-   🧩 **Kod yapısı** tamamen modülerdir; her `Entity (Course, Lesson, Student)` kendi policy ve request’ine sahiptir.
-   🪶 **Animasyonlar ve Toast bildirimi**, kullanıcı etkileşimlerinde geri bildirim sağlar.
-   ⚙️ **Request sınıfları**, validasyon ve güvenliği merkezi hale getirir.

---

## 🧾 Lisans

Bu proje **MIT License** ile lisanslanmıştır.  
Dilediğiniz gibi kullanabilir, geliştirebilir veya özelleştirebilirsiniz.

---

## 🌟 Geliştirici

**Oğuzhan Tekcan**  
🔗 [LinkedIn](https://linkedin.com/in/oguzhantekcan)

---

## ⚙️ Kurulum

### 🐳 Docker (Laravel Sail) ile Çalıştırma

```bash
# 1️⃣ Depoyu klonla
git clone https://github.com/Arheky/laravel-courses.git

# 2️⃣ Dizine gir
cd laravel-courses

# 3️⃣ .env dosyasını oluştur
cp .env.example .env

# 4️⃣ Docker servislerini başlat
./vendor/bin/sail up -d

# 5️⃣ Uygulama anahtarını oluştur
./vendor/bin/sail artisan key:generate

# 6️⃣ Veritabanını migrate et ve seed verilerini yükle
./vendor/bin/sail artisan migrate --seed

# 7️⃣ Frontend bağımlılıklarını yükle
./vendor/bin/sail npm ci

# 8️⃣ Frontend’i derle (veya geliştirme için npm run dev)
./vendor/bin/sail npm run build

# 9️⃣ Tarayıcıda projeyi aç
http://localhost
```

---

### 💻 Docker Olmadan (Klasik Local Kurulum)

```bash
# 1️⃣ Depoyu klonla
git clone https://github.com/Arheky/laravel-courses.git

# 2️⃣ Dizine gir
cd laravel-courses

# 3️⃣ .env dosyasını oluştur
cp .env.example .env

# 4️⃣ Backend bağımlılıklarını yükle
composer install

# 5️⃣ Frontend bağımlılıklarını yükle
npm ci

# 6️⃣ Uygulama anahtarını oluştur
php artisan key:generate

# 7️⃣ Veritabanını migrate et ve seed verilerini yükle
php artisan migrate --seed

# 8️⃣ Frontend’i derle (veya geliştirme için npm run dev)
npm run build

# 9️⃣ Local sunucuyu başlat
php artisan serve

# 🔟 Tarayıcıda projeyi aç
http://127.0.0.1:8000
```

---

### 🧩 Notlar

-   `.env.example` dosyasında hem Docker Sail hem klasik local ortam ayarları hazırdır.
-   Axios ve Sanctum yapılandırması `VITE_APP_URL=http://localhost` değeriyle otomatik çalışır.
-   Docker ortamı, kurulumu en hızlı ve tutarlı şekilde tamamlar.
