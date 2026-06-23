# PlayStation Shop - E-Commerce Platform

Platform e-commerce native PHP untuk penjualan produk PlayStation dengan fitur keamanan dan management admin yang lengkap.

## 🚀 Fitur Utama

### 👤 **User Features**
- ✅ Registrasi & login dengan CSRF protection
- ✅ **Email verification** (link verifikasi dikirim ke email)
- ✅ **Password reset** (forgot password dengan token link)
- ✅ Password strength validation (min 8 karakter + huruf + angka)
- ✅ Profile management dengan password change
- ✅ Avatar upload (JPG/PNG/GIF, max 2MB)
- ✅ Keranjang belanja
- ✅ Checkout & tracking pesanan
- ✅ **Order status tracking** (pending → processing → shipped → delivered)
- ✅ **Search & Filter produk** (nama, kategori, harga range, sorting)

### 🛡️ **Keamanan**
- ✅ CSRF tokens di semua form POST
- ✅ Rate limiting login (max 5 attempt / 15 menit)
- ✅ Email verification sebelum bisa login
- ✅ Password reset dengan token yang expired 1 jam
- ✅ Password hashing dengan bcrypt
- ✅ Input sanitization (htmlspecialchars)
- ✅ Prepared statements (SQL injection prevention)
- ✅ Session management

### 👨‍💼 **Admin Features**
- ✅ Dashboard dengan statistik
- ✅ Kelola produk (add/delete) **+ gambar upload**
- ✅ Kelola kategori (add/delete)
- ✅ **Order status management** (update status pesanan)
- ✅ **Activity logging** (audit trail lengkap)
- ✅ **Laporan dengan filter aktivitas**
- ✅ Pemantauan pesanan & pembayaran

## 📦 Setup & Installation

### 1. Prasyarat
- PHP 7.4+
- MySQL/MariaDB
- XAMPP (atau web server lainnya)

### 2. Instalasi Database

**Opsi A: Fresh Install (rekomendasi)**
```bash
# Import database.sql ke MySQL
mysql -u root -p ecommerce < database.sql
```

**Opsi B: Existing Database (jalankan migrasi)**
```bash
php migrations/add_avatar_column.php
php migrations/add_admin_logs_table.php
php migrations/add_order_status.php
php migrations/add_email_verification.php
php migrations/add_password_reset.php
```

### 3. Konfigurasi Database
Edit `config/database.php`:
```php
$dsn = 'mysql:host=localhost;dbname=ecommerce;charset=utf8mb4';
$user = 'root';
$password = '';
```

### 4. Jalankan Server
```bash
# XAMPP
- Buka XAMPP Control Panel
- Klik "Start" pada Apache & MySQL

# Atau gunakan PHP built-in server
cd /path/to/projek_uas
php -S localhost:8000
```

**Akses aplikasi:** http://localhost/projek_uas (XAMPP) atau http://localhost:8000 (built-in)

## 👤 Akun Demo

**Admin:**
- Email: `admin@example.com`
- Password: (sesuai hash di database.sql)

**User:**
- Email: `user@example.com`
- Password: (sesuai hash di database.sql)

> **Note:** Password hashes adalah placeholder. Untuk production, ganti dengan password yang diinginkan.

## 🔍 Fitur Search & Filter Produk

Halaman produk (`/produk.php`) memiliki fitur search & filter yang powerful:

### Fitur Filter
1. **Search Text** — cari berdasarkan nama atau deskripsi produk
2. **Filter Kategori** — tampilkan hanya produk dari kategori tertentu
3. **Filter Harga** — set range harga minimum dan maksimum
4. **Sorting** — urutkan hasil:
   - Terbaru (default)
   - Harga: Rendah ke Tinggi
   - Harga: Tinggi ke Rendah
   - Nama: A-Z
   - Nama: Z-A

### Contoh Query
- `/produk.php?search=PS5&category=1` — Cari PS5 di kategori Console
- `/produk.php?min_price=1000000&max_price=9000000&sort=price_asc` — Produk harga 1jt-9jt, urutkan harga
- `/produk.php?search=controller&sort=name_asc` — Cari controller, urutkan nama A-Z

## 📊 Admin Activity Logging

Admin dapat memantau aktivitas di halaman `admin/laporan.php`:

### Tab Laporan
1. **Ringkasan** — statistik penjualan, pesanan, produk, pengguna
2. **Aktivitas Admin** — log semua tindakan admin dengan:
   - Filter berdasarkan tipe aksi
   - Pagination (20 log per halaman)
   - Info: waktu, admin, aksi, keterangan, IP address

### Aksi yang Di-log
- `add_product` — menambah produk
- `delete_product` — menghapus produk
- `add_category` — menambah kategori
- `delete_category` — menghapus kategori
- `update_order_status` — ubah status pesanan

## ✉️ **Email Verification**

User yang baru register harus memverifikasi email mereka sebelum bisa login:

### Alur Proses
1. User register → email verifikasi dikirim
2. User klik link di email → email terverifikasi
3. User bisa login

### Halaman-halaman Terkait
- `/register.php` — Form registrasi (kirim email verifikasi setelah submit)
- `/verify-email.php?token=xxx` — Handle verification link
- `/resend-verification.php` — Kirim ulang email verifikasi jika tidak menerima

### Di Database
- Tabel `email_verification_tokens` — simpan token verification
- Column `email_verified` di users table — flag status verifikasi

**Catatan:** Email akan di-log ke file `logs/emails.log` untuk development. Untuk production, set up SMTP server.

## 🔐 **Password Reset**

User yang lupa password dapat reset password via email:

### Alur Proses
1. User klik "Lupa Password?" di login
2. Input email → link reset dikirim
3. User klik link di email (berlaku 1 jam)
4. Set password baru → password berhasil direset
5. User login dengan password baru

### Halaman-halaman Terkait
- `/forgot-password.php` — Form input email untuk request reset
- `/reset-password.php?token=xxx` — Form reset password baru dengan token

### Di Database
- Tabel `password_reset_tokens` — simpan token reset + expiry time
- Setiap token berlaku max 1 jam

## 📊 **Order Status Tracking**

Admin dapat update status pesanan, user dapat lihat status pesanan mereka:

### Status Tahapan
1. **Pending** — Order baru, menunggu pembayaran
2. **Processing** — Pembayaran sudah diterima, sedang diproses
3. **Shipped** — Pesanan dikirim
4. **Delivered** — Pesanan telah tiba

### Fitur
- **User View** (`/orders.php`) — Lihat status dengan color badge
  - Pending: Orange
  - Processing: Blue
  - Shipped: Purple
  - Delivered: Green
- **Admin Update** (`/admin/pesanan.php`) — Dropdown untuk update status
  - Semua update di-log ke admin_logs

## �️ **Product Image Upload**

Admin dapat upload gambar saat menambah produk baru:

### Fitur
- **Upload di Admin** (`/admin/produk.php`)
  - Form tambah produk punya input file untuk gambar
  - Validasi: JPG/PNG/GIF, max 2MB (sama seperti avatar)
  - File otomatis disimpan ke `assets/images/products/`
  - Thumbnail gambar tampil di admin produk list

- **Display di Frontend**
  - `/produk.php` — Thumbnail gambar di product card (200px height)
  - `/detail-produk.php` — Full size gambar di product detail page
  - Fallback: Placeholder gray jika tidak ada gambar

### Di Database
- Column `gambar` di tabel products — simpan nama file
- Filename format: `img_[timestamp]_[random].jpg`

### Direktori
- `assets/images/products/` — Menyimpan semua gambar produk
- Otomatis dibuat saat pertama kali upload

## �📁 Struktur Folder

```
projek_uas/
├── admin/                 # Halaman admin
│   ├── dashboard.php
│   ├── kategori.php
│   ├── laporan.php
│   ├── pembayaran.php
│   ├── pesanan.php
│   ├── produk.php
│   └── user.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── images/
│   │   ├── avatars/       # Avatar user (auto-created)
│   │   └── products/      # Produk gambar (auto-created)
│   └── js/
├── config/
│   └── database.php       # Konfigurasi DB
├── includes/
│   ├── auth.php           # Helper autentikasi
│   ├── header.php         # Header template
│   ├── footer.php         # Footer template
│   ├── csrf.php           # CSRF protection
│   ├── rate_limit.php     # Rate limiting
│   ├── admin_logger.php   # Admin logging
│   ├── email.php          # Email utility & token generation
│   └── image_upload.php   # ⭐ Image upload helper
├── migrations/
│   ├── add_avatar_column.php
│   ├── add_admin_logs_table.php
│   ├── add_order_status.php
│   ├── add_email_verification.php
│   └── add_password_reset.php
├── logs/
│   └── emails.log         # Email log (development)
├── cart.php
├── checkout.php
├── database.sql           # Database schema & sample data
├── detail-produk.php
├── forgot-password.php    # ⭐ NEW
├── index.php
├── login.php
├── logout.php
├── orders.php
├── payment.php
├── produk.php
├── profile.php
├── register.php
├── resend-verification.php # ⭐ NEW
├── reset-password.php     # ⭐ NEW
├── verify-email.php       # ⭐ NEW
└── README.md
```

## 🔐 Keamanan yang Diimplementasikan

### CSRF Protection
Semua form POST dilindungi dengan token CSRF yang diverifikasi di server:
```php
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
```

### Password Strength
- Minimal 8 karakter
- Harus mengandung huruf (A-Z, a-z)
- Harus mengandung angka (0-9)
- Divalidasi di server

### Rate Limiting
Login dibatasi maksimal 5 attempt dalam 15 menit per IP+email:
```
Terlalu banyak percobaan login gagal. Coba lagi dalam 15 menit.
```

### Input Sanitization
Semua input user di-escape dengan `htmlspecialchars()` untuk prevent XSS

### SQL Injection Prevention
Semua query menggunakan prepared statements

## 📋 Testing Checklist

- [ ] Register akun baru (test password strength)
- [ ] Login & cek rate limiting (5 failed attempts)
- [ ] Upload avatar di profile
- [ ] Lihat avatar di header setelah upload
- [ ] Add produk ke cart
- [ ] Update qty di cart
- [ ] Checkout & lihat order di pesanan
- [ ] Filter/search produk di halaman produk
- [ ] Login admin & lihat log aktivitas
- [ ] Add/delete produk & kategori (lihat di log)

## 🚢 Deploy ke Production

### Pre-deployment Checklist
- [ ] Update database credentials di `config/database.php`
- [ ] Set strong session cookie security (HTTPS only)
- [ ] Enable error logging, disable display errors
- [ ] Set proper file permissions (644 files, 755 dirs)
- [ ] Create `uploads/avatars` folder dengan write permission
- [ ] Backup database.sql

### Security Settings (php.ini)
```ini
session.cookie_secure = 1           # HTTPS only
session.cookie_httponly = 1         # Prevent JS access
session.cookie_samesite = "Strict"  # CSRF protection
display_errors = 0                  # Hide errors
error_reporting = E_ALL             # Log all errors
```

## 📝 Catatan

- Database sample data menggunakan placeholder password hash
- Untuk production, ganti dengan password real
- Email verification belum diimplementasikan
- Payment gateway (Midtrans) masih placeholder
- Upload produk gambar belum ada di UI (tapi DB siap)

## 🤝 Support

Untuk masalah atau pertanyaan, silakan check:
1. Console error (F12 Developer Tools)
2. PHP error log
3. MySQL query error di response

---

**Version:** 1.0  
**Last Updated:** 2026-06-23  
**License:** MIT
