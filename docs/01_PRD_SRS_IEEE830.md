# DOKUMEN SPESIFIKASI KEBUTUHAN PERANGKAT LUNAK (SRS / PRD)
## STANDARD IEEE 830 / ISO 29148
### PROYEK AKHIR MANDIRI REKAYASA PERANGKAT LUNAK (TIKB2503021) — SEMESTER 3

---

| Parameter | Keterangan |
| :--- | :--- |
| **Nama Produk / Aplikasi** | **Aethereal Joki** (Futuristic Dark Gaming Marketplace & Jasa Joki Terpercaya) |
| **Tema Topik Kelompok** | *Sistem Informasi Berbasis Web untuk Pemberdayaan UMKM Kreatif Digital & Ekosistem Gaming Cerdas Berbasis AI* |
| **Institusi** | Program Studi S1 Teknik Informatika — STMIK IKMI Cirebon |
| **Dosen Pengampu** | **Yudhistira Arie Wijaya, M.Kom., CDS.** |
| **Penyusun / Pengembang** | **Farrel Surya Pratama** |
| **Sifat Pengerjaan** | Mandiri / Perorangan (100% Individual Ownership & Full End-to-End Build) |
| **Versi Dokumen** | 1.0.0 (Final Release) |
| **Tanggal Terbit** | 6 November 2026 |

---

## DAFTAR ISI

1. **BAB I: PENDAHULUAN**
   - 1.1 Tujuan Pembuatan Dokumen
   - 1.2 Ruang Lingkup Produk (*Product Scope*)
   - 1.3 Definisi, Akronim, dan Singkatan
   - 1.4 Referensi Standar Industri & Akademik
   - 1.5 Ikhtisar Dokumen (*Document Overview*)

2. **BAB II: DESKRIPSI KESELURUHAN SISTEM (*OVERALL DESCRIPTION*)**
   - 2.1 Perspektif Produk (*Product Perspective*)
   - 2.2 Fungsi-Fungsi Utama Produk (*Product Functions*)
   - 2.3 Karakteristik & Klasifikasi Pengguna (*User Classes & Roles*)
   - 2.4 Lingkungan Operasional (*Operating Environment*)
   - 2.5 Batasan Desain & Implementasi (*Design & Implementation Constraints*)
   - 2.6 Asumsi dan Ketergantungan (*Assumptions & Dependencies*)

3. **BAB III: SPESIFIKASI KEBUTUHAN FUNGSIONAL (*FUNCTIONAL REQUIREMENTS*)**
   - 3.1 Modul Autentikasi, Profil & Keamanan (AUTH-01 s/d AUTH-04)
   - 3.2 Modul Katalog Game & Produk Top-Up Instan (CAT-01 s/d CAT-03)
   - 3.3 Modul Transaksi & Checkout Anti-Manipulasi (ORD-01 s/d ORD-04)
   - 3.4 Modul Integrasi Payment Gateway & Webhook Idempoten (PAY-01 s/d PAY-03)
   - 3.5 Modul Joki Game Marketplace & Lifecycle Eksekusi (JOKI-01 s/d JOKI-05)
   - 3.6 Modul Customer Dashboard & Pelacakan Real-Time (CUST-01 s/d CUST-03)
   - 3.7 Modul Administrator Operations & Audit Logging (ADM-01 s/d ADM-05)
   - 3.8 Modul Notifikasi Otomatis Multi-Channel (NOTIF-01 s/d NOTIF-03)

4. **BAB IV: SPESIFIKASI KEBUTUHAN NON-FUNGSIONAL (*NON-FUNCTIONAL REQUIREMENTS*)**
   - 4.1 Kebutuhan Performa & Latensi (*Performance Requirements*)
   - 4.2 Kebutuhan Keamanan Data & Privasi (*Security Requirements*)
   - 4.3 Kebutuhan Keandalan & Ketersediaan (*Reliability & Availability*)
   - 4.4 Kebutuhan Kemudahan Penggunaan & Aksesibilitas (*Usability & Accessibility*)
   - 4.5 Kebutuhan Perawatan & Skalabilitas (*Maintainability & Scalability*)

5. **BAB V: SPESIFIKASI FITUR KECERDASAN ARTIFISIAL (*AI FEATURE SPECIFICATION*)**
   - 5.1 Arsitektur AI Dual-Engine (*Hybrid Upstream LLM & Intelligent Local Fallback*)
   - 5.2 Retrieval-Augmented Generation (RAG) Grounding Database Real-Time
   - 5.3 Sistem Pengamanan Prompt (*Strict Guardrails & Scope Enforcement*)
   - 5.4 Mesin Rekomendasi Cerdas & Analisis Kebutuhan Gamer
   - 5.5 Fast-Path Query Resolution untuk Optimalisasi Latensi

6. **BAB VI: KRITERIA PENERIMAAN & MATRIKS KETERLACAKAN (*VERIFICATION & TRACEABILITY*)**
   - 6.1 Matriks Keterlacakan Kebutuhan (*Traceability Matrix*)
   - 6.2 Standar Penerimaan Proyek Akhir (*Final Sign-Off Criteria*)

---

## BAB I: PENDAHULUAN

### 1.1 Tujuan Pembuatan Dokumen
Dokumen Spesifikasi Kebutuhan Perangkat Lunak (*Software Requirements Specification* - SRS / *Product Requirement Document* - PRD) ini disusun sebagai landasan rekayasa formal dan acuan teknis berstandar **IEEE 830 / ISO 29148** dalam perancangan, pengembangan, pengujian, serta penerapan sistem aplikasi web **Aethereal Joki**. 

Dokumen ini menjadi bukti kepatuhan terhadap rubrik penilaian kurikulum *Outcome-Based Education* (OBE) pada mata kuliah Rekayasa Perangkat Lunak (TIKB2503021) di Program Studi S1 Teknik Informatika, STMIK IKMI Cirebon.

### 1.2 Ruang Lingkup Produk (*Product Scope*)
**Aethereal Joki** adalah platform *web application* modern berorientasi masa depan (*futuristic dark gaming ecosystem*) yang dirancang untuk mengatasi kesenjangan kepercayaan dan efisiensi dalam transaksi ekonomi kreatif digital, khususnya industri mikro jasa joki game (*game boosting*) dan *top-up voucher game* instan di Indonesia.

Ruang lingkup produk mencakup:
1. **Pemberdayaan UMKM Gamers Mandiri**: Mewadahi para pemain berbakat (*pro boosters*) menjadi penyedia jasa profesional terverifikasi dengan sistem pembagian kerja transparan, garansi keamanan akun, dan rekam jejak performa terukur (*winrate*, testimoni, dan catatan waktu pengerjaan).
2. **Transaksi Top-Up Instan Terotomatisasi**: Pembelian mata uang premium (Diamond, VP, Genesis Crystal, Robux) dengan validasi akun game otomatis (*Game Account ID Verification*), integrasi payment gateway berstandar enkripsi HMAC SHA512, serta mekanisme anti-manipulasi harga berbasis server.
3. **Pusat Asisten Cerdas Terintegrasi (Aethereal AI)**: Agen kecerdasan artifisial percakapan real-time yang tertanam langsung pada sistem, memiliki kemampuan analisis kebutuhan gamer, rekomendasi paket hemat berdasarkan anggaran, pelacakan status pesanan secara personal, serta grounding data berbasis basis data (*Retrieval-Augmented Generation* / RAG).

### 1.3 Definisi, Akronim, dan Singkatan
- **SRS**: *Software Requirements Specification* (Dokumen Spesifikasi Kebutuhan Perangkat Lunak).
- **PRD**: *Product Requirement Document* (Dokumen Kebutuhan Produk).
- **OBE**: *Outcome-Based Education* (Sistem Pembelajaran Berbasis Capaian).
- **RAG**: *Retrieval-Augmented Generation* (Teknik kecerdasan artifisial yang memadukan model bahasa generatif dengan basis data kontekstual lokal untuk menghasilkan jawaban faktual dan bebas halusinasi).
- **SOLID**: Lima prinsip desain berorientasi objek (*Single Responsibility, Open-Closed, Liskov Substitution, Interface Segregation, Dependency Inversion*).
- **Idempotency Key**: Parameter unik berbasis UUID/Hash yang menjamin sebuah permintaan transaksi HTTP tidak akan dieksekusi berulang kali saat terjadi gangguan jaringan atau pengiriman ganda (*double submission*).
- **HMAC SHA512**: Algoritma kriptografi berbasis *Hash-based Message Authentication Code* menggunakan fungsi hash SHA-512 untuk memverifikasi integritas dan autentisitas payload webhook pembayaran.
- **RBAC**: *Role-Based Access Control* (Pengendalian akses pengguna berdasarkan hak peran: Guest, Customer, Joki/Booster, dan Administrator).

### 1.4 Referensi Standar Industri & Akademik
1. IEEE Std 830-1998: *IEEE Recommended Practice for Software Requirements Specifications*.
2. ISO/IEC/IEEE 29148:2018: *Systems and software engineering — Life cycle processes — Requirements engineering*.
3. OWASP Top 10 Web Application Security Risks (2021/2026 Edition).
4. Panduan Tugas Proyek Akhir Mandiri & Rubrik Penilaian OBE Rekayasa Perangkat Lunak (TIKB2503021), STMIK IKMI Cirebon.

---

## BAB II: DESKRIPSI KESELURUHAN SISTEM

### 2.1 Perspektif Produk
Aethereal Joki diposisikan sebagai platform mandiri (*self-contained full-stack system*) yang mengintegrasikan tiga pilar utama:
1. **Frontend Interaktif**: Antarmuka berbasis Blade Templating Engine terkompilasi modern, dikombinasikan dengan Tailwind CSS v4 untuk styling futuristik responsif dan Alpine.js untuk reaktivitas dinamis tanpa beban berat SPA.
2. **Backend Berorientasi Objek**: Arsitektur Laravel 12/13 berbasis PHP 8.3 dengan pemisahan lapisan tugas (*Layered Architecture*): Controllers, Form Requests, Domain Services, Policies, Enums, dan Eloquent ORM.
3. **Infrastruktur Kecerdasan Artifisial**: Layanan hybrid upstream LLM (NVIDIA NIM Meta Llama 3.2 Vision / Google Gemini API) yang disokong secara tangguh oleh mesin inferensi berbasis aturan deterministik dan pencarian kontekstual (*Context-Grounded Rule-Engine Fallback*) saat jaringan eksternal luring.

### 2.2 Fungsi-Fungsi Utama Produk
1. **Katalog Multi-Game**: Menampilkan game terpopuler di Indonesia (Mobile Legends, Valorant, Genshin Impact, Free Fire, PUBG Mobile, Honor of Kings, Roblox) beserta paket top-up dan tingkatan rank joki.
2. **Validasi User ID & Zone ID**: Pengecekan keabsahan akun pemain sebelum melakukan pembayaran guna mencegah salah transfer.
3. **Pemesanan Jasa Joki**: Pengguna memilih rank awal, rank target, joki spesifik/otomatis, instruksi khusus hero, dan upload bukti akun terlindungi enkripsi.
4. **Lifecycle Order Tracking**: Pelacakan status pesanan secara real-time melalui 5 tahapan transisi: `PENDING` -> `PROCESSING` -> `ASSIGNED` -> `COMPLETED` -> `CANCELLED`.
5. **Booster Workbench**: Dashboard khusus joki untuk memantau pesanan yang ditugaskan, memperbarui catatan pengerjaan (*progress note*), mengunggah bukti penyelesaian (*proof screenshot*), dan menarik komisi pengerjaan.
6. **Admin Command Center**: Dashboard eksekutif administrator untuk pengawasan omzet, verifikasi transaksi manual/otomatis, penugasan booster, audit logging, dan manajemen konfigurasi notifikasi.
7. **Asisten AI Interaktif**: Widget percakapan terapung (*floating AI widget*) dan halaman penuh asisten cerdas yang siap menjawab pertanyaan operasional, legalitas, keamanan anti-banned, pencarian promo aktif, dan kalkulasi hemat paket diamond.

### 2.3 Karakteristik & Klasifikasi Pengguna
| Role | Deskripsi & Hak Akses | Tingkat Keterampilan |
| :--- | :--- | :--- |
| **Guest (Pengunjung Umum)** | Dapat melihat katalog, menghitung simulasi harga joki, mengecek ulasan pelanggan, menggunakan kalkulator asisten AI, dan melakukan pelacakan pesanan publik melalui Order ID. | Pengguna Internet Umum |
| **Customer (Pelanggan Terdaftar)** | Memiliki seluruh hak Guest ditambah riwayat pesanan personal, penyimpanan data game id favorit, ulasan terverifikasi, konsultasi AI berbasis identitas pribadi, dan notifikasi transaksi WhatsApp/Telegram. | Pengguna Aplikasi Web |
| **Pro Booster (Joki Terverifikasi)** | Memiliki akses ke `/joki/dashboard`. Mengelola antrean pesanan yang ditugaskan oleh admin/sistem, memperbarui progres pengerjaan pertandingan demi pertandingan, mengunggah tangkapan layar bukti kemenangan, dan memantau pendapatan bersih. | Pengalaman Teknis Menengah |
| **Administrator (Pengelola Sistem)** | Memiliki akses penuh ke `/admin`. Mengelola master data game, produk, paket joki, pengguna dan hak role, penugasan pesanan kepada joki, verifikasi webhook pembayaran, konfigurasi bot notifikasi Telegram, dan inspeksi jejak audit keandalan sistem. | Mahir / Administrator Sistem |

---

## BAB III: SPESIFIKASI KEBUTUHAN FUNGSIONAL

### 3.1 Modul Autentikasi, Profil & Keamanan
- **AUTH-01: Registrasi Akun Pelanggan & Joki**
  - *User Story*: Sebagai pengguna baru, saya ingin mendaftarkan akun dengan memasukkan nama lengkap, alamat email valid, nomor WhatsApp aktif, dan kata sandi minimal 8 karakter, agar saya dapat memiliki riwayat pesanan dan fitur personal.
  - *Acceptance Criteria*:
    - **Given** pengguna berada pada form registrasi `/register`,
    - **When** pengguna mengisikan formulir dengan data valid dan menekan tombol "Daftar Akun",
    - **Then** sistem mengenkripsi password dengan algoritma `BCrypt (rounds 12)`, menetapkan role `customer`, meregenerasi session ID, dan mengarahkan pengguna ke dashboard dengan pesan selamat datang.

- **AUTH-02: Otentikasi Multi-Role & Proteksi Brute Force**
  - *User Story*: Sebagai pengguna terdaftar, saya ingin login menggunakan email dan password dengan perlindungan session fixation dan rate-limiting agar akun saya aman dari serangan brute-force.
  - *Acceptance Criteria*:
    - **Given** pengguna berada di `/login`,
    - **When** pengguna memasukkan kredensial yang cocok,
    - **Then** sistem mencatat aktivitas pada `audit_logs`, meregenerasi session token, dan mengarahkan pengguna ke route dashboard sesuai role (`/admin`, `/joki/dashboard`, atau `/dashboard`).
    - **Given** pengguna salah memasukkan password 5 kali berturut-turut,
    - **Then** sistem mengunci percobaan login selama 60 detik (*throttling*) dan menampilkan pesan waktu tunggu.

### 3.2 Modul Katalog Game & Produk Top-Up Instan
- **CAT-01: Penjelajahan Game Dinamis & Pencarian Instan**
  - *User Story*: Sebagai pembeli, saya ingin mencari game berdasarkan nama dan memfilter berdasarkan kategori (MOBA, FPS, RPG, Battle Royale) agar saya dapat menemukan game yang ingin saya top-up secara cepat.
  - *Acceptance Criteria*:
    - **Given** pengunjung mengakses halaman utama `/` atau `/games`,
    - **When** pengunjung mengetikkan kata kunci game pada kolom pencarian atau memilih filter kategori,
    - **Then** daftar kartu game merespons seketika dengan menampilkan logo, nama publisher, dan badge status aktif.

- **CAT-02: Pemilihan Denominasi Nominal Produk & Kalkulasi Promo**
  - *User Story*: Sebagai pembeli, saya ingin memilih nominal diamond/koin game dan melihat potongan diskon dari kode voucher promo aktif agar saya mendapatkan harga terbaik.
  - *Acceptance Criteria*:
    - **Given** pembeli berada pada halaman top-up game tertentu,
    - **When** pembeli memilih denominasi nominal produk (misal: "86 Diamonds") dan memasukkan kode voucher promo "HEMATGAMING",
    - **Then** sistem memvalidasi masa berlaku promo di database, mengalkulasi potongan harga diskon, dan menampilkan ringkasan rincian biaya transparan sebelum checkout.

### 3.3 Modul Transaksi & Checkout Anti-Manipulasi
- **ORD-01: Kalkulasi Harga Sisi Server (*Server-Side Strict Pricing*)**
  - *User Story*: Sebagai pengelola sistem, saya ingin memastikan harga produk dan jasa joki dihitung mutlak pada sisi server, agar tidak dapat dimanipulasi melalui inspect element atau interception request HTTP.
  - *Acceptance Criteria*:
    - **Given** klien mengirimkan payload checkout dengan manipulasi nominal field harga,
    - **When** server menerima request checkout pada `CheckoutController::process`,
    - **Then** server mengabaikan nominal yang dikirimkan klien, mengambil harga master dari tabel `game_products` atau `joki_services` berdasarkan ID relasional, dan menyimpan harga resmi ke dalam tabel `orders` dan `order_items`.

- **ORD-02: Idempotensi Transaksi (*Double Submission Protection*)**
  - *User Story*: Sebagai pelanggan, saya ingin memastikan transaksi saya tidak terpotong dua kali ketika saya secara tidak sengaja menekan tombol "Bayar Sekarang" berkali-kali.
  - *Acceptance Criteria*:
    - **Given** sebuah request checkout menyertakan `idempotency_key` unik berbasis UUIDv4,
    - **When** request dengan `idempotency_key` yang identik dikirimkan kembali ke server dalam interval waktu singkat,
    - **Then** sistem mendeteksi keberadaan kunci tersebut pada database, menolak pembuatan order duplikat, dan mengembalikan data order yang sudah terbuat sebelumnya (*HTTP 200/409 idempotent response*).

### 3.4 Modul Integrasi Payment Gateway & Webhook
- **PAY-01: Pembayaran Multi-Channel & Mock Simulation Engine**
  - *User Story*: Sebagai pengguna lokal dalam lingkungan Laragon, saya ingin dapat menyelesaikan pembayaran baik melalui simulasi instan (*Mock Payment Sandbox*) maupun gerbang pembayaran resmi (*Midtrans Snap*), agar pengujian dan demonstrasi berjalan tanpa hambatan.
  - *Acceptance Criteria*:
    - **Given** pesanan berstatus `PENDING`,
    - **When** pengguna memilih metode pembayaran QRIS/Virtual Account pada mode mock atau gateway,
    - **Then** sistem menghasilkan link/token pembayaran dan mencatat entri pada tabel `payments` dengan referensi `payment_code` unik.

- **PAY-02: Verifikasi Tanda Tangan Webhook Idempoten (*HMAC SHA512*)**
  - *User Story*: Sebagai pengembang sistem, saya ingin webhook pembayaran diverifikasi keasliannya melalui checksum tanda tangan SHA512 dan kebal terhadap *replay attack*.
  - *Acceptance Criteria*:
    - **Given** server menerima notifikasi webhook pada `/payment/webhook`,
    - **When** payload webhook diverifikasi menggunakan secret key dan signature cocok,
    - **Then** status pembayaran beralih menjadi `PAID`, status order beralih menjadi `PROCESSING`, stok produk terpotong (jika ada), dan notifikasi diterbitkan secara otomatis.
    - **When** signature tidak valid atau nominal pembayaran tidak cocok dengan order di database,
    - **Then** sistem menolak payload dengan respon `HTTP 400 Bad Request` dan mencatat potensi anomali pada `audit_logs`.

### 3.5 Modul Joki Game Marketplace & Lifecycle Eksekusi
- **JOKI-01: Formulir Pemesanan Joki & Enkripsi Kredensial Akun Game**
  - *User Story*: Sebagai pelanggan yang sibuk, saya ingin memesan jasa joki rank dengan memilih rank awal dan rank target, memilih jenis login (Moonton, VK, Riot, Google), serta mengisikan akun game yang terenkripsi aman.
  - *Acceptance Criteria*:
    - **Given** pelanggan berada di form `/joki/{service_slug}`,
    - **When** pelanggan memilih rank awal, rank target, hero request, catatan booster, dan kredensial akun,
    - **Then** server mengalkulasi total biaya berdasarkan selisih bintang/tier rank, mengenkripsi data sensitif akun game, dan menghasilkan order joki dengan status `PENDING`.

- **JOKI-02: Penugasan Booster & Pembaruan Progres Pengerjaan**
  - *User Story*: Sebagai Pro Booster yang ditugaskan, saya ingin melihat detail spesifikasi order joki dan memperbarui status progres pengerjaan (misal: "Sedang push bintang 12 ke 20") beserta tangkapan layar bukti match.
  - *Acceptance Criteria*:
    - **Given** booster login dan membuka `/joki/dashboard`,
    - **When** booster memilih order yang ditugaskan kepadanya dan mengunggah catatan progres serta file gambar bukti (format JPG/PNG max 2MB),
    - **Then** sistem menyimpan file ke storage aman, memperbarui `progress_note`, mencatat timestamp, dan mengirimkan notifikasi pembaruan ke pelanggan.

### 3.6 Modul Administrator Operations & Audit Logging
- **ADM-01: Manajemen Master Data & Role RBAC**
  - *User Story*: Sebagai Administrator, saya ingin dapat menambah, mengedit, mengaktifkan/menonaktifkan game, produk, paket joki, serta mengatur role pengguna agar katalog selalu terbarui.
  - *Acceptance Criteria*:
    - **Given** admin mengakses menu `/admin/games` atau `/admin/users`,
    - **When** admin melakukan aksi update atau toggle status pengguna,
    - **Then** database memperbarui record seketika, dan sistem menuliskan log jejak audit mencakup `user_id`, `action`, `ip_address`, dan `user_agent`.

---

## BAB IV: SPESIFIKASI KEBUTUHAN NON-FUNGSIONAL

| ID | Dimensi Kebutuhan | Deskripsi Standar Kinerja & Pengujian |
| :--- | :--- | :--- |
| **NFR-01** | **Kinerja & Latensi (Performance)** | Waktu respons pemuatan halaman web rata-rata tidak melebihi 1,2 detik pada kondisi koneksi lokal Laragon. Respon asisten AI berbasis Fast-Path lokal diselesaikan dalam < 100 ms, dan pemanggilan upstream AI tidak melebihi batas *timeout* 15 detik dengan graceful fallback. |
| **NFR-02** | **Keamanan Data (Security & OWASP)** | 1) Seluruh form transaksi dilindungi token CSRF.<br>2) Enkripsi kata sandi menggunakan BCrypt hash factor 12.<br>3) Proteksi SQL Injection melalui PDO Prepared Statements bawaan Eloquent ORM.<br>4) Proteksi Cross-Site Scripting (XSS) melalui sanitasi output Blade `{{ }}`.<br>5) Strict Role Authorization melalui Middleware dan Gate/Policy Laravel. |
| **NFR-03** | **Keandalan & Transaksi (Reliability)** | Database menerapkan transaksi berantai (`DB::transaction`) pada setiap alur checkout dan webhook pembayaran untuk menjamin prinsip ACID (Atomicity, Consistency, Isolation, Durability). Jika terjadi kegagalan di tengah proses, seluruh state di-*rollback* utuh. |
| **NFR-04** | **Ketersediaan Layanan (Availability)** | Sistem dirancang dengan arsitektur *Dual-Engine Fallback*. Apabila koneksi internet mati atau kuota API Upstream LLM habis, fitur asisten AI tidak akan pernah *crash* (500 Error), melainkan secara otomatis beralih ke *Deterministic Context Grounded Engine*. |
| **NFR-05** | **Aksesibilitas & UI/UX (Usability)** | Antarmuka mematuhi standar rasio kontras WCAG 2.1 Level AA pada tema gelap (*Dark Futuristic Theme*). Navigasi dapat diakses menggunakan keyboard, tata letak sepenuhnya responsif (*mobile-friendly* dari resolusi 360px hingga 4K desktop). |

---

## BAB V: SPESIFIKASI FITUR KECERDASAN ARTIFISIAL (AI ENGINE)

### 5.1 Arsitektur AI Dual-Engine
Fitur asisten cerdas **Aethereal AI** diimplementasikan melalui kelas `AetherealAIService` yang mengadopsi pola arsitektur *Hybrid Fallback*:
1. **Tier 1: Upstream Generative Cloud LLM**
   - Mendukung integrasi terhadap penyedia upstream terkemuka: **NVIDIA NIM AI API** (`meta/llama-3.2-11b-vision-instruct`) dan **Google Gemini API** (`gemini-1.5-flash` / `gemini-2.0-flash`).
   - Dipersenjatai dengan *system prompt context injection* yang menyuplai data operasional terkini toko secara berkala sebelum payload dikirimkan.
2. **Tier 2: Intelligent Context-Grounded Fallback Engine**
   - Berfungsi mandiri tanpa koneksi internet atau ketika API key tidak disediakan.
   - Menggunakan algoritma pemindaian niat (*intent matching*), pemecahan token leksikal (*lexical token analysis*), dan *dynamic SQL query builder* untuk menggali data nyata dari database relasional (game, harga produk, promo aktif, profil booster, dan ulasan pelanggan).

### 5.2 Retrieval-Augmented Generation (RAG) Grounding Database
Kelas `DatabaseContextRetriever` bertindak sebagai agen RAG internal yang mengekstraksi data riil untuk memastikan jawaban AI 100% faktual:
- **Pencarian Promo**: Mengambil data promosi yang sedang aktif (`is_active = true` dan `valid_until >= now()`).
- **Pencarian Game & Produk**: Menghubungkan kata kunci game (misal: "Roblox", "Valorant", "Mobile Legends") dengan denominasi produk dan harga termurah yang tersedia.
- **Pelacakan Pesanan Terintegrasi**: Mengidentifikasi format kode order `ORD-XXXXXX` di dalam kalimat pengguna, memverifikasi kepemilikan akun jika pengguna sedang login, dan menyajikan status eksekusi pengerjaan terkini.
- **Statistik Terukur**: Menghitung secara presisi jumlah pelanggan aktif, total booster tersertifikasi, dan daftar admin tanpa mencampuradukkan data atau membocorkan data sensitif.

### 5.3 Sistem Pengamanan Prompt (*Strict Guardrails*)
1. **Guardrail Batasan Lingkup (*Web-Only Scope Enforcement*)**:
   - Asisten AI dibatasi khusus untuk ekosistem gaming, produk Aethereal Joki, dan konsultasi teknologi web (HTML, CSS, JS, PHP, Laravel).
   - Segala bentuk permintaan pembuatan kode Python (`isPythonCodingRequest`) secara tegas ditolak secara sopan (*Hard Guardrail*).
2. **Guardrail Identitas & Privasi Personal**:
   - Pertanyaan seputar identitas ("siapa saya", "siapa nama saya") disesuaikan dengan sesi pengguna yang sedang login (`$user->name`).
   - Proteksi nama pengembang (*Farrel Surya Pratama*) diterapkan agar AI tidak mengklaim nama pengguna lain secara keliru atau membocorkan identitas pengembang pada konteks pengguna umum.
3. **Guardrail Anti-Halusinasi Harga**:
   - Seluruh nominal harga diamond atau joki wajib diambil dari database atau ditolak jika produk belum terdaftar.

---

## BAB VI: KRITERIA PENERIMAAN & MATRIKS KETERLACAKAN

| Kode Kebutuhan | Nama Fitur | Komponen Pengujian Otomatis | Status Keterpenuhan |
| :--- | :--- | :--- | :--- |
| **REQ-AUTH-01** | Autentikasi & RBAC Multi-Role | `SecurityHardeningTest::test_user_registration_*` | **LULUS (100% Pass)** |
| **REQ-CAT-01** | Katalog & Navigasi Game | `TopUpFlowTest::test_games_listing_page_*` | **LULUS (100% Pass)** |
| **REQ-ORD-01** | Server-Side Strict Pricing | `TopUpFlowTest::test_price_manipulation_is_prevented` | **LULUS (100% Pass)** |
| **REQ-ORD-02** | Transaksi Idempoten | `SecurityHardeningTest::test_top_up_checkout_idempotency` | **LULUS (100% Pass)** |
| **REQ-PAY-01** | Webhook Pembayaran & Signature | `MidtransPaymentTest::test_midtrans_webhook_*` | **LULUS (100% Pass)** |
| **REQ-JOKI-01** | Marketplace & Lifecycle Joki | `JokiMarketplaceTest::test_*` & `JokiDashboardTest::*` | **LULUS (100% Pass)** |
| **REQ-AI-01** | AI RAG Database Grounding | `AiAssistantTest::test_ai_reads_game_products_*` | **LULUS (100% Pass)** |
| **REQ-AI-02** | AI Guardrail & Scope Restriction | `AiAssistantTest::test_python_coding_request_*` | **LULUS (100% Pass)** |
| **REQ-AI-03** | AI Personal Identity Inquiry | `AiAssistantTest::test_user_identity_inquiry_*` | **LULUS (100% Pass)** |
| **REQ-NOTIF-01**| Multi-Channel Notification Alerts | `TelegramNotificationTest::*` & `WhatsAppNotificationTest::*` | **LULUS (100% Pass)** |

---

*Disahkan di Cirebon, 6 November 2026.*  
**Mahasiswa Pengembang (Full-Stack Software Engineer):**  
**Farrel Surya Pratama**  
*(NIM: S1 Teknik Informatika - STMIK IKMI Cirebon)*
