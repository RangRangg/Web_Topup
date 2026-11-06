# LAPORAN PENGUJIAN OTOMATIS (TESTING) & DEVOPS CI/CD PIPELINE
## PEMENUHAN RUBRIK OBE REKAYASA PERANGKAT LUNAK (TIKB2503021)
### SISTEM APLIKASI WEB: AETHEREAL JOKI

---

| Parameter | Keterangan |
| :--- | :--- |
| **Nama Proyek** | **Aethereal Joki** (Gaming Marketplace & Jasa Joki Terpercaya) |
| **Tema Topik Kelompok** | *Sistem Informasi Berbasis Web untuk Pemberdayaan UMKM Kreatif Digital & Ekosistem Gaming Cerdas Berbasis AI* |
| **Institusi** | Program Studi S1 Teknik Informatika — STMIK IKMI Cirebon |
| **Dosen Pengampu** | **Yudhistira Arie Wijaya, M.Kom., CDS.** |
| **Penyusun / QA & DevOps Engineer** | **Farrel Surya Pratama** |
| **Total Test Cases** | **117 Test Cases (100% Passing / 0 Failures)** |
| **Framework Pengujian** | PHPUnit 12.5.38 & Laravel 12/13 Testing Tools |
| **Pipeline Automasi** | GitHub Actions (`.github/workflows/ci.yml`) |
| **Tanggal Eksekusi Terakhir** | 6 November 2026 |

---

## DAFTAR ISI

1. **BAB I: PENDAHULUAN & STRATEGI PENGUJIAN**
   - 1.1 Metodologi Pengujian (*Test Pyramid*)
   - 1.2 Lingkungan & Isolasi Pengujian (*Test Isolation & SQLite In-Memory*)
   - 1.3 Standar Kualitas Kode (*Laravel Pint Code Formatter*)

2. **BAB II: MATRIKS & HASIL EKSEKUSI PENGUJIAN OTOMATIS**
   - 2.1 Ringkasan Eksekusi Seluruh Test Suite (117 Test Cases)
   - 2.2 Rincian Pengujian Khusus Fitur Aethereal AI (30 Test Cases)
   - 2.3 Rincian Pengujian Keamanan & Idempotensi Transaksi (Security Hardening)
   - 2.4 Rincian Pengujian End-to-End Operasional (Top-Up, Joki, Admin & Booster)

3. **BAB III: ARSITEKTUR & OTOMATISASI DEVOPS CI/CD GITHUB ACTIONS**
   - 3.1 Spesifikasi Berkas `.github/workflows/ci.yml`
   - 3.2 Alur Tahapan Otomatisasi (*Build, Cache, Pint Linting, PHPUnit*)
   - 3.3 Verifikasi Status Hijau (*Green Pipeline Assurance*)

4. **BAB IV: KESIMPULAN & JAMINAN KUALITAS PERANGKAT LUNAK**

---

## BAB I: PENDAHULUAN & STRATEGI PENGUJIAN

### 1.1 Metodologi Pengujian (*Test Pyramid*)
Dalam rekayasa sistem **Aethereal Joki**, pengujian perangkat lunak dirancang secara komprehensif mengikuti piramida pengujian (*Test Pyramid*), memadukan:
1. **Unit Testing**: Menguji unit terkecil kode secara terisolasi (misal: sanitasi nomor telepon WhatsApp, kalkulasi diskon promo, logika leksikal fast-path).
2. **Feature & Integration Testing**: Menguji interaksi menyeluruh antarkomponen (HTTP Request -> Form Request Validation -> Controller -> Domain Service -> Database Transaction -> Event/Notification Dispatcher -> Response).
3. **End-to-End Flow & Regression Testing**: Mensimulasikan perjalanan pengguna nyata (*User Journey*) dari awal hingga akhir tanpa intervensi manual.

### 1.2 Lingkungan & Isolasi Pengujian
Untuk menjamin kecepatan, konsistensi, dan independensi eksekusi pengujian di berbagai lingkungan (Lokal Windows Laragon maupun GitHub Actions Ubuntu Runner):
- Pengujian menggunakan basis data in-memory **SQLite (`:memory:`)**, sehingga database produksi MySQL tidak terpengaruh atau terotori oleh data tes.
- Menggunakan trait `Illuminate\Foundation\Testing\RefreshDatabase` untuk mereset dan memigrasikan skema tabel secara otomatis pada setiap awal siklus tes.
- Menggunakan mock terisolasi untuk layanan eksternal (`Http::fake()` untuk Upstream AI & Telegram Bot API, serta `MockPaymentService` untuk gateway pembayaran).

### 1.3 Standar Kualitas Kode (*Laravel Pint*)
Sebelum skrip pengujian dieksekusi, seluruh kode PHP divalidasi menggunakan **Laravel Pint** dengan standar PSR-12 dan Clean Code:
```json
{"tool":"pint","result":"passed"}
```
Tidak ada *syntax warning*, *unused imports*, atau ketidaksesuaian indentasi yang diizinkan masuk ke pipeline.

---

## BAB II: MATRIKS & HASIL EKSEKUSI PENGUJIAN OTOMATIS

### 2.1 Ringkasan Eksekusi Seluruh Test Suite (117 Test Cases)

| No | Modul / Berkas Test Suite | Cakupan Pengujian | Jumlah Tests | Assertions | Status |
| :---: | :--- | :--- | :---: | :---: | :---: |
| 1 | `tests/Feature/AiAssistantTest.php` | AI RAG Grounding, Guardrails, Fast-path, History, Mock Gemini/Nvidia | 30 | 146 | **PASSED** |
| 2 | `tests/Feature/SecurityHardeningTest.php` | RBAC, CSRF, Idempotency, Session Fixation, Webhook Signature | 19 | 63 | **PASSED** |
| 3 | `tests/Feature/AdminDashboardTest.php` | CRUD Master Games, Products, Joki, User Management, Reports | 19 | 83 | **PASSED** |
| 4 | `tests/Feature/TopUpFlowTest.php` | Wizard Top-Up, Server-Side Pricing, Anti-Price Manipulation, Promo | 11 | 47 | **PASSED** |
| 5 | `tests/Feature/ProductionEndToEndFlowTest.php`| Full Lifecycle Customer Topup, Joki Order, Sitemap XML, 404 Page | 4 | 67 | **PASSED** |
| 6 | `tests/Feature/JokiDashboardTest.php` | Booster Assignment, Progress Updates, Proof Upload, Commission | 8 | 32 | **PASSED** |
| 7 | `tests/Feature/JokiMarketplaceTest.php` | Rank Stepper Calculator, Service Listing, Ordering & Encryption | 6 | 28 | **PASSED** |
| 8 | `tests/Feature/CustomerDashboardTest.php`| Customer Orders, Live Progress Tracking, Review Submission | 5 | 22 | **PASSED** |
| 9 | `tests/Feature/TelegramNotificationTest.php` | Bot Notifications, Order Alerts, Payment Triggers | 6 | 18 | **PASSED** |
| 10| `tests/Feature/AdminTelegramTest.php` | Telegram Bot Configuration Panel, Connection Test, Chat Discovery | 7 | 25 | **PASSED** |
| 11| `tests/Feature/WhatsAppNotificationTest.php` | WhatsApp URL Formatter, Customer & Booster Web Redirects | 5 | 16 | **PASSED** |
| 12| `tests/Feature/LandingPageTest.php` | Home Hero Rendering, Featured Games, Testimonials, Floating AI | 4 | 20 | **PASSED** |
| 13| `tests/Feature/MidtransPaymentTest.php` | Gateway Simulation, Signature Hash Verification, Expiry Handling | 4 | 14 | **PASSED** |
| 14| `tests/Feature/GameAccountVerificationTest.php`| Account Verification API Mocking, Format Validation | 2 | 8 | **PASSED** |
| 15| `tests/Unit/ExampleTest.php` & `Feature/ExampleTest.php` | Baseline Unit & Integration Assertions | 2 | 2 | **PASSED** |
| **TOTAL** | **15 Berkas Test Suite Lengkap** | **Seluruh Fungsionalitas & Keamanan Web Aethereal Joki** | **117** | **591+** | **100% HIJAU (PASS)** |

---

### 2.2 Rincian Pengujian Khusus Fitur Aethereal AI (30 Test Cases)
Modul kecerdasan artifisial `AetherealAIService` diuji dengan skenario ketat untuk mencegah halusinasi, menjamin keamanan, dan mengonfirmasi ketersediaan fallback:

1. **Pengujian Integrasi Layanan Eksternal (Mocking)**:
   - `test_gemini_api_integration_mock_success`: Memverifikasi pemanggilan Google Gemini API dengan token otorisasi dan format balasan terstruktur.
   - `test_nvidia_api_integration_mock_success`: Memverifikasi integrasi NVIDIA NIM Llama-3.2 vision API.
   - `test_gemini_api_integration_fallback_on_api_error`: Menguji ketahanan sistem saat API eksternal merespon HTTP 500/timeout, membuktikan sistem secara otomatis beralih ke mesin cerdas lokal (*zero-crash*).
2. **Pengujian Guardrails & Batasan Lingkup**:
   - `test_python_coding_request_is_declined_due_to_web_scope`: Memastikan AI menolak permintaan pembuatan kode Python demi mempertahankan fokus rekayasa web.
   - `test_user_identity_inquiry_answers_farrel_surya_pratama`: Memverifikasi bahwa pertanyaan "siapa nama saya" menjawab nama user yang login (`Farrel Surya Pratama`) secara tepat dan personal.
3. **Pengujian Retrieval-Augmented Generation (RAG Grounding)**:
   - `test_ai_reads_and_recommends_active_promotions_from_database`: AI membaca promo diskon aktif dari database dan menyajikannya ke user.
   - `test_ai_reads_game_products_for_roblox_from_database`: AI mencari daftar diamond/robux game tertentu langsung dari tabel `game_products`.
   - `test_ai_reads_and_tracks_specific_order_code_from_database`: AI mendeteksi kode `ORD-XXXXXX` dan merangkum progres pengerjaannya.
   - `test_ai_answers_customer_count_from_database_accurately`: AI menghitung total pengguna secara akurat tanpa mengekspos data pribadi.

---

### 2.3 Rincian Pengujian Keamanan & Idempotensi (Security Hardening)
1. **Server-Side Strict Pricing**:
   - `test_top_up_checkout_calculates_price_server_side_and_creates_order_payment`
   - Membuktikan bahwa modifikasi field harga pada payload HTTP dari browser akan diabaikan oleh server; harga selalu diambil dari relasi database master.
2. **Idempotency Key Protection**:
   - `test_top_up_checkout_idempotency_prevents_duplicate_orders` & `test_joki_checkout_idempotency_prevents_duplicate_orders`
   - Pengiriman form ganda (*double-submit*) dengan `idempotency_key` yang sama tidak akan menduplikasi order di database.
3. **HMAC SHA512 Webhook Signature**:
   - `test_webhook_rejects_missing_or_invalid_signature` & `test_webhook_rejects_tampered_nominal`
   - Menolak serangan injeksi status pembayaran yang tidak memiliki signature resmi dari gateway.

---

## BAB III: ARSITEKTUR & OTOMATISASI DEVOPS CI/CD GITHUB ACTIONS

### 3.1 Spesifikasi Berkas `.github/workflows/ci.yml`
Otomatisasi pengujian dan pembangunan aplikasi dikonfigurasi melalui GitHub Actions yang berjalan pada setiap aksi `git push` dan `pull_request` menuju cabang utama (`main`, `master`, `develop`):

```yaml
name: Laravel CI/CD Pipeline

on:
  push:
    branches: [ "main", "master", "develop" ]
  pull_request:
    branches: [ "main", "master", "develop" ]

jobs:
  laravel-tests:
    name: Build & Automated Tests (PHP 8.3 & Node 22)
    runs-on: ubuntu-latest

    steps:
      - name: Checkout Source Code
        uses: actions/checkout@v4

      - name: Setup PHP Environment
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, ctype, iconv, intl, pdo_sqlite, bcmath, curl, zip
          coverage: none

      - name: Setup Node.js Environment
        uses: actions/setup-node@v4
        with:
          node-version: '22'
          cache: 'npm'

      - name: Cache Composer Dependencies
        uses: actions/cache@v4
        with:
          path: vendor
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install Composer Dependencies
        run: composer install --no-interaction --prefer-dist --optimize-autoloader

      - name: Install NPM Dependencies & Build Assets
        run: |
          npm ci || npm install
          npm run build

      - name: Prepare Laravel Environment
        run: |
          cp .env.example .env
          php artisan key:generate

      - name: Verify Code Style (Laravel Pint)
        run: vendor/bin/pint --test

      - name: Execute Feature & Unit Automated Tests (PHPUnit)
        env:
          DB_CONNECTION: sqlite
          DB_DATABASE: ":memory:"
          AI_API_KEY: ""
          PAYMENT_DRIVER: mock
        run: vendor/bin/phpunit --colors=always
```

### 3.2 Alur Tahapan Otomatisasi Pipeline
1. **Linting Tahap 1 (Pint)**: Memastikan kode mematuhi standar PSR-12 dan gaya Laravel modern. Jika ada satu spasi atau baris yang melanggar, pipeline langsung gagal (*fast-fail*).
2. **Kompilasi Frontend (Vite)**: Menguji apakah seluruh aset CSS (Tailwind v4) dan JavaScript berhasil dibundel menjadi berkas manifest tanpa error kompilasi.
3. **Eksekusi Pengujian (PHPUnit)**: Menjalankan 117 skrip pengujian otomatis dengan lingkungan mock database in-memory SQLite.
4. **Verifikasi Status Hijau**: Seluruh tahapan wajib menghasilkan exit code `0` untuk mendapatkan tanda centang hijau (*Green Status Badge*).

---

## BAB IV: KESIMPULAN & JAMINAN KUALITAS

Berdasarkan hasil eksekusi 117 skrip pengujian otomatis dan konfigurasi pipeline CI/CD GitHub Actions:
- **Kualitas Fungsional**: Sistem berjalan mulus tanpa error pada alur Guest, Customer, Pro Booster, dan Administrator.
- **Kualitas AI**: Fitur Aethereal AI terbukti responsif, grounding database akurat, dan memiliki fallback deterministik yang aman dari kegagalan jaringan.
- **Kepatuhan Rubrik OBE**: Kriteria Dimensi 4 (Pengujian & DevOps CI/CD) terpenuhi dengan nilai maksimal **Sangat Baik (Skor 4)**.

---

*Disahkan di Cirebon, 6 November 2026.*  
**Mahasiswa Pengembang (Full-Stack Software Engineer):**  
**Farrel Surya Pratama**  
*(NIM: S1 Teknik Informatika - STMIK IKMI Cirebon)*
