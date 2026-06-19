# 📋 Kerangka Kerja — Sistem JOSIS
## Sistem Input Data Pemilih Kabupaten Magetan, Jawa Timur

---

## 🎯 Gambaran Umum Sistem

Sistem JOSIS adalah aplikasi web untuk **pendataan pemilih** di 4 kecamatan wilayah Kabupaten Magetan, Jawa Timur. Sistem ini memiliki hierarki akun yang ketat — hanya akun tingkat desa/kelurahan yang dapat menginput data, sementara akun kecamatan dan admin hanya dapat memantau.

---

## 🗺️ Wilayah yang Dicakup

Sumber data wilayah diambil dari **API Resmi wilayah.id** (https://wilayah.id/)

- Kabupaten Magetan → kode: **35.20**
- Endpoint kecamatan: `GET https://wilayah.id/api/districts/35.20.json`
- Endpoint desa: `GET https://wilayah.id/api/villages/{kode_kecamatan}.json`

| Kecamatan | Kode |
|-----------|------|
| Barat | 35.20.12 |
| Karangrejo | 35.20.13 |
| Karas | 35.20.14 |
| Kartoharjo | 35.20.15 |

> Desa/kelurahan per kecamatan diambil otomatis dari API saat seeder dijalankan.

---

## 👥 Hierarki Peran (Role)

```
┌─────────────────────────────────┐
│            ADMIN                │  ← Memantau SEMUA data
│   (1 akun, super administrator) │
└────────────────┬────────────────┘
                 │
    ┌────────────┴────────────┐
    ▼                         ▼
┌──────────┐           ┌──────────┐
│ KECAMATAN│    ...    │ KECAMATAN│  ← 4 akun kecamatan
│Karangrejo│           │Kartoharjo│    (hanya pantau)
└────┬─────┘           └─────┬────┘
     │                       │
  ┌──┴──┐                 ┌──┴──┐
  ▼     ▼                 ▼     ▼
[Desa][Desa]           [Desa][Desa]  ← Akun per desa/kel
                                        (INPUT data pemilih)
```

### Detail Peran:

| Role | Bisa Apa |
|------|----------|
| **Admin** | Lihat semua data pemilih seluruh kecamatan & desa, kelola akun kecamatan & desa, export data |
| **Kecamatan** | Lihat semua data pemilih di kecamatannya saja, lihat rekap per desa |
| **Desa/Kelurahan** | Input, edit, hapus data pemilih di desanya saja |

---

## 📊 Data Pemilih

Setiap data pemilih terdiri dari:

| Field | Tipe | Enkripsi | Keterangan |
|-------|------|----------|------------|
| NIK | String (16 digit) | 🔐 AES-256-GCM | Nomor Induk Kependudukan, unik |
| Nama | String | 🔐 AES-256-GCM | Nama lengkap pemilih |
| Jenis Kelamin | Enum | 🔐 AES-256-GCM | Laki-laki / Perempuan |
| Alamat | Text | 🔐 AES-256-GCM | Alamat lengkap |
| RT | String | 🔐 AES-256-GCM | Nomor RT |
| RW | String | 🔐 AES-256-GCM | Nomor RW |
| Desa/Kelurahan | Relasi | — | Otomatis sesuai akun yang login |
| Kecamatan | Relasi | — | Otomatis dari desa |
| Diinput oleh | Relasi | — | Akun desa yang menginput |
| Waktu Input | Timestamp | — | Otomatis |

---

## 🔄 Alur Kerja Lengkap

### 1️⃣ Setup Awal (oleh Developer/Admin)
```
[Developer]
    │
    ├── Seed data wilayah dari API (Kecamatan + Desa)
    ├── Buat akun Admin (1 akun)
    ├── Buat akun Kecamatan (4 akun: Karangrejo, Barat, Karas, Kartoharjo)
    └── Buat akun Desa (sesuai jumlah desa di 4 kecamatan)
```

### 2️⃣ Login & Autentikasi
```
[User buka /login]
    │
    ├── Input Email + Password
    │
    ├── Jika ADMIN      → redirect ke /admin/dashboard
    ├── Jika KECAMATAN  → redirect ke /kecamatan/dashboard
    └── Jika DESA       → redirect ke /desa/dashboard
```

### 3️⃣ Alur Admin
```
[Admin Login]
    │
    ├── Dashboard
    │     ├── Total pemilih seluruh wilayah
    │     ├── Breakdown per kecamatan (chart/tabel)
    │     └── Breakdown per desa
    │
    ├── Kelola Akun
    │     ├── Lihat daftar akun kecamatan
    │     ├── Lihat daftar akun desa
    │     └── Reset password akun
    │
    ├── Data Pemilih
    │     ├── Lihat semua data (filter by kecamatan/desa)
    │     └── Export ke Excel/PDF
    │
    └── Logout
```

### 4️⃣ Alur Kecamatan
```
[Akun Kecamatan Login]
    │
    ├── Dashboard
    │     ├── Total pemilih di kecamatannya
    │     └── Breakdown per desa dalam kecamatannya
    │
    ├── Data Pemilih
    │     ├── Lihat semua data di kecamatannya (filter by desa)
    │     └── Export ke Excel/PDF (scope kecamatannya)
    │
    └── Logout
```

### 5️⃣ Alur Desa/Kelurahan (Utama — yang bisa INPUT)
```
[Akun Desa Login]
    │
    ├── Dashboard
    │     └── Total pemilih yang sudah diinput di desanya
    │
    ├── Data Pemilih
    │     ├── Lihat daftar pemilih desanya
    │     ├── [TAMBAH] Input pemilih baru
    │     │     ├── Isi NIK (validasi 16 digit, unik)
    │     │     ├── Isi Nama
    │     │     ├── Pilih Jenis Kelamin
    │     │     ├── Isi Alamat
    │     │     ├── Isi RT & RW
    │     │     └── Submit → tersimpan otomatis ke desa akun ini
    │     │
    │     ├── [EDIT] Edit data pemilih
    │     └── [HAPUS] Hapus data pemilih
    │
    └── Logout
```

---

## 🗃️ Struktur Database

### Tabel `kecamatans`
```
- id
- kode        (kode dari API wilayah)
- nama
- timestamps
```

### Tabel `desas`
```
- id
- kode        (kode dari API wilayah)
- nama
- kecamatan_id (FK → kecamatans)
- timestamps
```

### Tabel `users`
```
- id
- name          🔐 AES-256-GCM
- email         🔐 AES-256-GCM
- email_hash    (SHA-256 dari email asli, untuk keperluan lookup/login)
- password      🔑 Argon2id (hashing, bukan enkripsi)
- role          (enum: admin | kecamatan | desa)
- kecamatan_id  (FK nullable → kecamatans)
- desa_id       (FK nullable → desas)
- timestamps
```

### Tabel `pemilihs`
```
- id
- nik           🔐 AES-256-GCM
- nik_hash      (SHA-256 dari NIK asli, untuk keperluan cek duplikat)
- nama          🔐 AES-256-GCM
- jenis_kelamin 🔐 AES-256-GCM
- alamat        🔐 AES-256-GCM
- rt            🔐 AES-256-GCM
- rw            🔐 AES-256-GCM
- desa_id       (FK → desas)
- kecamatan_id  (FK → kecamatans)
- user_id       (FK → users, siapa yang menginput)
- timestamps
```

> **Catatan `*_hash`:** karena field yang dienkripsi tidak bisa di-search/dicek duplikat langsung di DB, kita simpan hash-nya (SHA-256, one-way) untuk keperluan pencarian dan validasi unique NIK.

---

## 🔒 Aturan Akses (Authorization)

| Aksi | Admin | Kecamatan | Desa |
|------|:-----:|:---------:|:----:|
| Lihat semua data pemilih | ✅ | ❌ | ❌ |
| Lihat data se-kecamatan | ✅ | ✅ | ❌ |
| Lihat data desanya sendiri | ✅ | ✅ | ✅ |
| Input pemilih baru | ❌ | ❌ | ✅ |
| Edit data pemilih | ❌ | ❌ | ✅ |
| Hapus data pemilih | ❌ | ❌ | ✅ |
| Export semua data | ✅ | ❌ | ❌ |
| Export data kecamatan | ✅ | ✅ | ❌ |
| Export data desa | ✅ | ✅ | ✅ |
| Kelola akun user | ✅ | ❌ | ❌ |

---

## 🔐 Strategi Enkripsi

### Algoritma yang Digunakan

| Target | Algoritma | Alasan |
|--------|-----------|--------|
| **Data pemilih** (NIK, Nama, Alamat, dll) | **AES-256-GCM** | AEAD — enkripsi + verifikasi integritas, tamper-proof |
| **Data akun** (name, email) | **AES-256-GCM** | Sama seperti di atas |
| **Password akun** | **Argon2id** | Hashing (bukan enkripsi), standar OWASP 2024, tahan brute-force & GPU attack |
| **Cek duplikat NIK/Email** | **SHA-256 (HMAC)** | Hash deterministik untuk lookup tanpa membuka enkripsi |

### Mengapa AES-256-GCM bukan CBC?

```
AES-256-CBC  →  Hanya enkripsi (confidentiality)
                Rentan padding oracle attack
                Tidak bisa deteksi data yang dimanipulasi

AES-256-GCM  →  Enkripsi + Authentication Tag (AEAD)
                Immune padding oracle attack
                Jika data dimanipulasi di DB → sistem langsung tolak/error
                Ini yang dipakai WhatsApp, Google, TLS 1.3
```

### Mengapa Argon2id untuk password?

```
bcrypt       →  Bagus, tapi desain lama (1999)
                Tidak tahan serangan GPU modern

Argon2id     →  Pemenang Password Hashing Competition (PHC) 2015
                Tahan GPU attack & side-channel attack
                Direkomendasikan OWASP, NIST, dan RFC 9106
                Sudah built-in di PHP 7.2+ dan Laravel
```

### Key Management

```
┌─────────────────────────────────────────┐
│           Enkripsi Key Hierarchy        │
├─────────────────────────────────────────┤
│  APP_KEY (.env)                         │
│   └── Master key Laravel (AES-256)      │
│                                         │
│  ENCRYPT_KEY (.env) — tambahan khusus   │
│   └── Key khusus enkripsi data pemilih  │
│       (dipisah dari APP_KEY)            │
└─────────────────────────────────────────┘
```

- **APP_KEY** : key bawaan Laravel (untuk session, cookie, dll)
- **ENCRYPT_KEY** : key khusus tambahan untuk enkripsi data sensitif pemilih & akun
- Kedua key **tidak boleh sama** dan **wajib di-backup** secara terpisah
- Jika key hilang → data **tidak bisa didekripsi** (ini fitur, bukan bug)

### Implementasi di Laravel

```php
// Di Model Pemilih — gunakan Laravel Casts + Custom Encryption
protected $casts = [
    'nik'           => EncryptedCast::class,  // AES-256-GCM
    'nama'          => EncryptedCast::class,
    'jenis_kelamin' => EncryptedCast::class,
    'alamat'        => EncryptedCast::class,
    'rt'            => EncryptedCast::class,
    'rw'            => EncryptedCast::class,
];

// Di Model User
protected $casts = [
    'name'     => EncryptedCast::class,
    'email'    => EncryptedCast::class,
    'password' => 'hashed',  // Argon2id via config/hashing.php
];
```

> Laravel memiliki built-in `encrypted` cast yang menggunakan AES-256-CBC secara default. Kita akan **override** cast ini agar menggunakan **AES-256-GCM** yang lebih aman.

---

## 🛠️ Teknologi

| Layer | Teknologi |
|-------|----------|
| Backend | Laravel 13 |
| Frontend | Vue 3 + Inertia.js |
| Database | MySQL |
| Auth | Laravel Fortify |
| Styling | Tailwind CSS + shadcn/vue |
| API Wilayah | https://wilayah.id/ (Resmi, gratis, selalu update) |
| Export | maatwebsite/excel |

---

## 📅 Urutan Pengerjaan

```
[x] Step 1  — Migration: tabel kecamatans, desas, users (ULID, role, email_hash), pemilihs
              → 4 migration file, semua pakai ULID primary key
              → users: role (admin|kecamatan|desa), kecamatan_id, desa_id, email_hash

[x] Step 2  — Seeder wilayah dari API wilayah.id (35.20 = Magetan)
              → 4 kecamatan: Barat, Karangrejo, Karas, Kartoharjo
              → 50 desa/kelurahan (14 + 13 + 11 + 12)

[x] Step 3  — Seeder akun user (Argon2id password, email_hash SHA-256)
              → 1 Admin    : admin@josis.magetan.id
              → 4 Kecamatan: kec.{nama}@josis.magetan.id
              → 50 Desa    : desa.{nama}.{kecamatan}@josis.magetan.id
              → Total: 55 akun

[ ] Step 4  — Setup enkripsi: custom AES-256-GCM cast + re-seed dengan data terenkripsi
[ ] Step 5  — Role system: middleware & policy per role
[ ] Step 6  — Auth redirect: setelah login arahkan ke dashboard sesuai role
[ ] Step 7  — Dashboard Admin (statistik semua wilayah)
[ ] Step 8  — Dashboard Kecamatan (statistik kecamatannya)
[ ] Step 9  — Dashboard Desa (statistik + CRUD pemilih)
[ ] Step 10 — Fitur Export Excel
[ ] Step 11 — Polish UI & Testing
```

---

## 🌐 Struktur Route

```
/login

# Admin
/admin/dashboard
/admin/pemilih              → semua data (bisa filter)
/admin/akun                 → kelola akun

# Kecamatan
/kecamatan/dashboard
/kecamatan/pemilih          → data se-kecamatan

# Desa
/desa/dashboard
/desa/pemilih               → daftar pemilih desa ini
/desa/pemilih/create        → form input pemilih baru
/desa/pemilih/{id}/edit     → form edit pemilih
```

---

> ✅ **Setelah kerangka ini disetujui, langsung mulai dari Step 1: Migration Database.**

---

## 📝 Daftar Akun (Hasil Seeder)

> ⚠️ **Password default wajib diganti setelah pertama kali login!**

### 👑 Admin

| Email | Password |
|-------|----------|
| `admin@josis.magetan.id` | `Admin@Josis2026!` |

---

### 📍 Kecamatan (Password semua: `Kecamatan@2026!`)

| Kecamatan | Email |
|-----------|-------|
| Barat | `kec.barat@josis.magetan.id` |
| Karangrejo | `kec.karangrejo@josis.magetan.id` |
| Karas | `kec.karas@josis.magetan.id` |
| Kartoharjo | `kec.kartoharjo@josis.magetan.id` |

---

### 🏘️ Desa/Kelurahan (Password semua: `Desa@2026!`)

**Kecamatan Barat** (14 desa)

| Desa | Email |
|------|-------|
| Mangge | `desa.mangge.barat@josis.magetan.id` |
| Tebon | `desa.tebon.barat@josis.magetan.id` |
| Bogorejo | `desa.bogorejo.barat@josis.magetan.id` |
| Banjarejo | `desa.banjarejo.barat@josis.magetan.id` |
| Karangsono | `desa.karangsono.barat@josis.magetan.id` |
| Purwodadi | `desa.purwodadi.barat@josis.magetan.id` |
| Manjung | `desa.manjung.barat@josis.magetan.id` |
| Ngumpul | `desa.ngumpul.barat@josis.magetan.id` |
| Panggung | `desa.panggung.barat@josis.magetan.id` |
| Blaran | `desa.blaran.barat@josis.magetan.id` |
| Jonggrang | `desa.jonggrang.barat@josis.magetan.id` |
| Rejomulyo | `desa.rejomulyo.barat@josis.magetan.id` |
| Bangunasri | `desa.bangunasri.barat@josis.magetan.id` |
| Klagen | `desa.klagen.barat@josis.magetan.id` |

**Kecamatan Karangrejo** (13 desa)

| Desa | Email |
|------|-------|
| Karangrejo | `desa.karangrejo.karangrejo@josis.magetan.id` |
| Manisrejo | `desa.manisrejo.karangrejo@josis.magetan.id` |
| Mantren | `desa.mantren.karangrejo@josis.magetan.id` |
| Gondang | `desa.gondang.karangrejo@josis.magetan.id` |
| Sambirembe | `desa.sambirembe.karangrejo@josis.magetan.id` |
| Patihan | `desa.patihan.karangrejo@josis.magetan.id` |
| Pelem | `desa.pelem.karangrejo@josis.magetan.id` |
| Baluk | `desa.baluk.karangrejo@josis.magetan.id` |
| Gebyog | `desa.gebyog.karangrejo@josis.magetan.id` |
| Maron | `desa.maron.karangrejo@josis.magetan.id` |
| Prampelan | `desa.prampelan.karangrejo@josis.magetan.id` |
| Grabahan | `desa.grabahan.karangrejo@josis.magetan.id` |
| Kauman | `desa.kauman.karangrejo@josis.magetan.id` |

**Kecamatan Karas** (11 desa)

| Desa | Email |
|------|-------|
| Karas | `desa.karas.karas@josis.magetan.id` |
| Sobontoro | `desa.sobontoro.karas@josis.magetan.id` |
| Sumursongo | `desa.sumursongo.karas@josis.magetan.id` |
| Taji | `desa.taji.karas@josis.magetan.id` |
| Kuwon | `desa.kuwon.karas@josis.magetan.id` |
| Jungke | `desa.jungke.karas@josis.magetan.id` |
| Temboro | `desa.temboro.karas@josis.magetan.id` |
| Temenggungan | `desa.temenggungan.karas@josis.magetan.id` |
| Geplak | `desa.geplak.karas@josis.magetan.id` |
| Botok | `desa.botok.karas@josis.magetan.id` |
| Ginuk | `desa.ginuk.karas@josis.magetan.id` |

**Kecamatan Kartoharjo** (12 desa)

| Desa | Email |
|------|-------|
| Kartoharjo | `desa.kartoharjo.kartoharjo@josis.magetan.id` |
| Mrahu | `desa.mrahu.kartoharjo@josis.magetan.id` |
| Klurahan | `desa.klurahan.kartoharjo@josis.magetan.id` |
| Pencol | `desa.pencol.kartoharjo@josis.magetan.id` |
| Sukowidi | `desa.sukowidi.kartoharjo@josis.magetan.id` |
| Ngelang | `desa.ngelang.kartoharjo@josis.magetan.id` |
| Jajar | `desa.jajar.kartoharjo@josis.magetan.id` |
| Gunungan | `desa.gunungan.kartoharjo@josis.magetan.id` |
| Jeruk | `desa.jeruk.kartoharjo@josis.magetan.id` |
| Bayem Wetan | `desa.bayemwetan.kartoharjo@josis.magetan.id` |
| Bayem Taman | `desa.bayemtaman.kartoharjo@josis.magetan.id` |
| Karangmojo | `desa.karangmojo.kartoharjo@josis.magetan.id` |
