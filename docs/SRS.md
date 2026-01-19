# Software Requirements Specification (SRS)
## Sistem Manajemen Organisasi

---

### Document Information

| Item | Detail |
|------|--------|
| **Project Name** | Sistem Manajemen Organisasi Berbasis Web |
| **Version** | 1.1 |
| **Date** | 19 Januari 2026 |
| **Status** | Draft - Updated for Laravel Breeze Vue |
| **Prepared By** | Tim Pengembang |

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Overall Description](#2-overall-description)
3. [System Features](#3-system-features)
4. [External Interface Requirements](#4-external-interface-requirements)
5. [Non-Functional Requirements](#5-non-functional-requirements)
6. [Other Requirements](#6-other-requirements)
7. [Appendices](#7-appendices)

---

## 1. Introduction

### 1.1 Purpose

Dokumen Software Requirements Specification (SRS) ini menjelaskan kebutuhan sistem untuk Sistem Manajemen Organisasi berbasis web. Dokumen ini ditujukan untuk:

- Tim pengembang sebagai acuan pembangunan sistem
- Project manager untuk perencanaan dan monitoring
- Quality assurance untuk pengujian sistem
- Stakeholder organisasi untuk pemahaman fitur sistem

### 1.2 Scope

Sistem Manajemen Organisasi adalah platform berbasis web yang dirancang untuk mengelola organisasi secara terpusat, terstruktur, dan transparan. Sistem ini mencakup:

**In Scope:**
- Manajemen keanggotaan organisasi
- Manajemen kegiatan dan event
- Manajemen keuangan (kas, iuran, donasi)
- Manajemen konten publik (visi/misi, struktur, galeri)
- Sistem role-based access control
- Dashboard dan reporting

**Out of Scope:**
- Integrasi payment gateway (fase 1)
- Notifikasi email/WhatsApp otomatis (fase 1)
- Mobile application native

### 1.3 Definitions, Acronyms, and Abbreviations

| Term | Definition |
|------|------------|
| **SRS** | Software Requirements Specification |
| **RBAC** | Role-Based Access Control |
| **CRUD** | Create, Read, Update, Delete |
| **UI** | User Interface |
| **UX** | User Experience |
| **API** | Application Programming Interface |
| **PDF** | Portable Document Format |
| **SPA** | Single Page Application |
| **SSR** | Server-Side Rendering |
| **Inertia.js** | Framework untuk membuat SPA klasik menggunakan routing dan controller server-side |

### 1.4 References

- IEEE Std 830-1998: IEEE Recommended Practice for Software Requirements Specifications
- Laravel 12 Documentation
- Laravel Breeze Documentation
- Vue.js 3 Documentation
- Inertia.js Documentation
- MySQL 8.0 Documentation
- Tailwind CSS Documentation

### 1.5 Overview

Dokumen ini terorganisir dalam beberapa bagian utama yang menjelaskan kebutuhan fungsional dan non-fungsional sistem, interface requirements, dan spesifikasi teknis lainnya.

---

## 2. Overall Description

### 2.1 Product Perspective

Sistem Manajemen Organisasi adalah sistem standalone yang dapat diakses melalui web browser. Sistem ini dibangun dengan arsitektur modern menggunakan Laravel Breeze Vue Starter Kit:

**Technology Stack:**
- **Backend Framework**: Laravel 12 (PHP 8.2+)
- **Frontend Framework**: Vue.js 3 dengan Composition API
- **Bridge Layer**: Inertia.js (menghubungkan Laravel dan Vue tanpa API)
- **Authentication**: Laravel Breeze (built-in authentication scaffolding)
- **CSS Framework**: Tailwind CSS 3.x
- **Build Tool**: Vite (bundler modern untuk Vue/JavaScript)
- **Database**: MySQL 8.0
- **Containerization**: Docker
- **Session Management**: Laravel Session (cookie-based)
- **Form Handling**: Inertia.js Form Helper

**Architecture Pattern:**
- Monolithic architecture dengan SPA experience
- Server-side routing menggunakan Laravel Routes
- Client-side rendering menggunakan Vue 3 components
- No REST API required (Inertia.js handles data passing)

### 2.2 Product Functions

Fungsi utama sistem meliputi:

1. **Manajemen Keanggotaan**: Pengelolaan data anggota, status keanggotaan, dan riwayat aktivitas
2. **Manajemen Kegiatan**: Penjadwalan, dokumentasi, dan monitoring event organisasi
3. **Manajemen Keuangan**: Pencatatan kas, iuran, donasi, dan pelaporan keuangan
4. **Manajemen Konten**: Pengelolaan visi/misi, struktur organisasi, dan galeri foto
5. **Administrasi**: Pengumuman, notulensi, dan arsip dokumen
6. **Portal Publik**: Informasi organisasi yang dapat diakses tanpa login

### 2.3 User Classes and Characteristics

| User Class | Karakteristik | Keahlian Teknis |
|------------|---------------|-----------------|
| **Admin** | Pengelola sistem dengan akses penuh | Tinggi |
| **Ketua** | Pimpinan organisasi, monitoring seluruh data | Sedang |
| **Bendahara** | Pengelola keuangan organisasi | Sedang |
| **Sekretaris** | Pengelola administrasi dan dokumentasi | Sedang |
| **Anggota** | Member organisasi dengan akses terbatas | Rendah-Sedang |
| **Publik** | Pengunjung tanpa login | Rendah |

### 2.4 Operating Environment

- **Client**: Web browser modern (Chrome, Firefox, Safari, Edge) versi terbaru dengan JavaScript enabled
- **Server**: Linux/Ubuntu Server dengan Docker support
- **Database**: MySQL 8.0 atau lebih tinggi
- **Minimum Server Specs**:
  - CPU: 2 cores
  - RAM: 4GB
  - Storage: 20GB SSD
  - Network: Stable internet connection

### 2.5 Design and Implementation Constraints

- Sistem harus menggunakan Laravel 12 sebagai backend framework
- Frontend harus menggunakan Vue.js 3 dengan Composition API
- Harus menggunakan Inertia.js sebagai bridge antara Laravel dan Vue
- Authentication harus menggunakan Laravel Breeze
- Styling harus menggunakan Tailwind CSS
- Build tool harus menggunakan Vite
- Harus mendukung deployment via Docker
- Harus responsive dan mobile-friendly
- Harus mengikuti prinsip RBAC untuk keamanan
- Tidak boleh membuat REST API (kecuali untuk kebutuhan khusus external integration)

### 2.6 Assumptions and Dependencies

**Assumptions:**
- User memiliki akses internet yang stabil
- User menggunakan browser modern yang mendukung JavaScript ES6+
- Server memiliki environment yang mendukung Docker
- Developer familiar dengan Laravel dan Vue.js

**Dependencies:**
- Laravel 12 framework
- Laravel Breeze package
- Inertia.js (Laravel Adapter & Vue Adapter)
- Vue.js 3
- Tailwind CSS
- Vite
- MySQL database server
- Docker container runtime
- Composer (PHP dependency manager)
- NPM/PNPM (JavaScript package manager)

---

## 3. System Features

### 3.1 User Authentication and Authorization

#### 3.1.1 Description

Sistem autentikasi dan autorisasi berbasis role (RBAC) untuk mengontrol akses user terhadap fitur sistem menggunakan Laravel Breeze.

#### 3.1.2 Functional Requirements

**FR-AUTH-001**: Sistem harus menyediakan halaman login menggunakan Laravel Breeze dengan validasi email dan password

**FR-AUTH-002**: Sistem harus mendukung 5 role user: Admin, Ketua, Bendahara, Sekretaris, dan Anggota

**FR-AUTH-003**: Sistem harus membatasi akses fitur berdasarkan role user menggunakan Laravel Policies dan Gates

**FR-AUTH-004**: Sistem harus menyimpan session user dengan aman menggunakan Laravel Session

**FR-AUTH-005**: Sistem harus menyediakan fitur logout

**FR-AUTH-006**: Sistem harus mencatat log aktivitas login/logout user

**FR-AUTH-007**: Sistem harus mendukung "Remember Me" functionality

**FR-AUTH-008**: Sistem harus menyediakan email verification (opsional, dapat diaktifkan)

**FR-AUTH-009**: Sistem harus menyediakan password reset via email menggunakan Laravel Breeze

### 3.2 Manajemen Data Anggota

#### 3.2.1 Description

Modul untuk mengelola data keanggotaan organisasi dengan lengkap.

#### 3.2.2 Functional Requirements

**FR-MEMBER-001**: Sistem harus dapat menambah data anggota baru dengan field:
- Nama lengkap
- Email
- Nomor telepon
- Alamat
- Tanggal bergabung
- Status keanggotaan (aktif/nonaktif)
- Foto profil (optional)

**FR-MEMBER-002**: Sistem harus dapat menampilkan daftar anggota dengan fitur pencarian dan filter

**FR-MEMBER-003**: Sistem harus dapat mengupdate data anggota

**FR-MEMBER-004**: Sistem harus dapat menghapus data anggota (soft delete)

**FR-MEMBER-005**: Sistem harus menampilkan riwayat iuran anggota

**FR-MEMBER-006**: Sistem harus menampilkan riwayat keikutsertaan kegiatan anggota

**FR-MEMBER-007**: Anggota harus dapat mengupdate profil pribadi (data terbatas)

### 3.3 Manajemen Event/Kegiatan

#### 3.3.1 Description

Modul untuk merencanakan, mengelola, dan mendokumentasikan kegiatan organisasi.

#### 3.3.2 Functional Requirements

**FR-EVENT-001**: Sistem harus dapat membuat event baru dengan field:
- Nama kegiatan
- Deskripsi
- Tanggal mulai dan selesai
- Lokasi
- Penanggung jawab
- Status (draft/published/completed)

**FR-EVENT-002**: Sistem harus dapat menampilkan kalender kegiatan

**FR-EVENT-003**: Sistem harus dapat mengupload dokumentasi kegiatan (foto/file)

**FR-EVENT-004**: Sistem harus dapat menampilkan list event yang akan datang di halaman publik

**FR-EVENT-005**: Sistem harus dapat mengelola peserta kegiatan

**FR-EVENT-006**: Sistem harus dapat mengupdate dan menghapus event

### 3.4 Manajemen Iuran

#### 3.4.1 Description

Modul untuk mengelola sistem iuran anggota organisasi.

#### 3.4.2 Functional Requirements

**FR-IURAN-001**: Sistem harus dapat membuat jenis iuran dengan field:
- Nama iuran
- Nominal
- Periode (bulanan/tahunan/sekali)
- Tanggal jatuh tempo

**FR-IURAN-002**: Sistem harus dapat mencatat pembayaran iuran per anggota

**FR-IURAN-003**: Sistem harus menampilkan status pembayaran iuran (lunas/belum lunas/terlambat)

**FR-IURAN-004**: Sistem harus dapat generate reminder iuran yang akan jatuh tempo

**FR-IURAN-005**: Sistem harus dapat menampilkan laporan iuran per periode

**FR-IURAN-006**: Anggota dapat melihat status iuran pribadi

### 3.5 Manajemen Donasi/Penggalangan Dana

#### 3.5.1 Description

Modul untuk mengelola program donasi dan penggalangan dana organisasi.

#### 3.5.2 Functional Requirements

**FR-DONASI-001**: Sistem harus dapat membuat program donasi dengan field:
- Nama program
- Deskripsi
- Target dana
- Periode donasi
- Status (aktif/selesai)

**FR-DONASI-002**: Sistem harus dapat mencatat donasi masuk

**FR-DONASI-003**: Sistem harus menampilkan progress donasi (dana terkumpul vs target)

**FR-DONASI-004**: Sistem harus dapat membuat laporan penggunaan dana

**FR-DONASI-005**: Sistem harus menampilkan program donasi aktif di halaman publik

### 3.6 Manajemen Keuangan

#### 3.6.1 Description

Modul komprehensif untuk mengelola seluruh aspek keuangan organisasi.

#### 3.6.2 Functional Requirements

**FR-FINANCE-001**: Sistem harus dapat mengelola multiple kas/dompet dengan field:
- Nama kas
- Saldo awal
- Saldo berjalan
- Deskripsi

**FR-FINANCE-002**: Sistem harus dapat mencatat uang masuk dengan kategori:
- Iuran anggota
- Donasi
- Sumber lainnya

**FR-FINANCE-003**: Sistem harus dapat mencatat uang keluar dengan kategori:
- Pengeluaran kegiatan
- Operasional organisasi
- Lain-lain

**FR-FINANCE-004**: Sistem harus dapat mengupload bukti transaksi (foto/file)

**FR-FINANCE-005**: Sistem harus menampilkan riwayat transaksi dengan filter tanggal dan kategori

**FR-FINANCE-006**: Sistem harus dapat generate laporan keuangan:
- Laporan kas harian
- Laporan bulanan
- Laporan tahunan
- Laporan custom periode

**FR-FINANCE-007**: Sistem harus menampilkan ringkasan keuangan di dashboard

### 3.7 Manajemen Foto/Album

#### 3.7.1 Description

Modul untuk mengelola dokumentasi visual organisasi dalam bentuk album foto.

#### 3.7.2 Functional Requirements

**FR-ALBUM-001**: Sistem harus dapat membuat album dengan field:
- Nama album
- Deskripsi
- Kategori (event/kegiatan rutin/dokumentasi resmi)
- Status (publik/privat)
- Cover album

**FR-ALBUM-002**: Sistem harus dapat upload multiple foto dalam satu album

**FR-ALBUM-003**: Sistem harus dapat menambahkan deskripsi untuk setiap foto

**FR-ALBUM-004**: Sistem harus dapat menghubungkan album dengan event tertentu

**FR-ALBUM-005**: Sistem harus menampilkan galeri foto di halaman publik (hanya yang berstatus publik)

**FR-ALBUM-006**: Sistem harus dapat mengelola (edit/delete) album dan foto

### 3.8 Manajemen Visi & Misi

#### 3.8.1 Description

Modul untuk mengelola konten visi dan misi organisasi.

#### 3.8.2 Functional Requirements

**FR-VISI-001**: Sistem harus dapat membuat/edit visi organisasi

**FR-VISI-002**: Sistem harus dapat membuat/edit multiple poin misi

**FR-VISI-003**: Sistem harus dapat mencatat periode kepengurusan (optional)

**FR-VISI-004**: Sistem harus menyimpan riwayat perubahan visi/misi (audit trail)

**FR-VISI-005**: Sistem harus dapat set status aktif/nonaktif untuk histori

**FR-VISI-006**: Sistem harus menampilkan visi/misi aktif di halaman publik

### 3.9 Manajemen Struktur Organisasi

#### 3.9.1 Description

Modul untuk mengelola hierarki dan struktur kepengurusan organisasi.

#### 3.9.2 Functional Requirements

**FR-STRUKTUR-001**: Sistem harus dapat membuat struktur organisasi dengan field:
- Jabatan
- Nama anggota (relasi ke data anggota)
- Periode jabatan
- Urutan hierarki
- Foto pengurus
- Status aktif

**FR-STRUKTUR-002**: Sistem harus dapat menampilkan struktur organisasi dalam bentuk hierarki/bagan

**FR-STRUKTUR-003**: Sistem harus membedakan antara role sistem dan jabatan organisasi

**FR-STRUKTUR-004**: Sistem harus menampilkan struktur organisasi aktif di halaman publik

**FR-STRUKTUR-005**: Sistem harus dapat menyimpan histori struktur organisasi periode sebelumnya

### 3.10 Administrasi

#### 3.10.1 Description

Modul untuk mengelola administrasi organisasi seperti pengumuman, notulensi, dan arsip.

#### 3.10.2 Functional Requirements

**FR-ADMIN-001**: Sistem harus dapat membuat pengumuman dengan field:
- Judul
- Konten
- Tanggal publish
- Status (draft/published)
- Target audience (all/specific role)

**FR-ADMIN-002**: Sistem harus dapat membuat notulensi rapat dengan field:
- Tanggal rapat
- Agenda
- Peserta
- Hasil rapat
- File attachment

**FR-ADMIN-003**: Sistem harus dapat mengupload dan mengelola dokumen arsip

**FR-ADMIN-004**: Sistem harus dapat mengkategorikan dokumen

**FR-ADMIN-005**: Sistem harus menyediakan fitur pencarian dokumen

### 3.11 Dashboard

#### 3.11.1 Description

Halaman utama setelah login yang menampilkan ringkasan informasi penting.

#### 3.11.2 Functional Requirements

**FR-DASH-001**: Sistem harus menampilkan ringkasan jumlah anggota (total, aktif, nonaktif)

**FR-DASH-002**: Sistem harus menampilkan ringkasan keuangan (saldo kas, total uang masuk/keluar bulan ini)

**FR-DASH-003**: Sistem harus menampilkan event terdekat (upcoming events)

**FR-DASH-004**: Sistem harus menampilkan notifikasi penting

**FR-DASH-005**: Sistem harus menampilkan widget sesuai role user

**FR-DASH-006**: Sistem harus menampilkan statistik dalam bentuk chart/grafik

### 3.12 Halaman Publik

#### 3.12.1 Description

Portal publik yang dapat diakses tanpa login untuk menampilkan informasi organisasi.

#### 3.12.2 Functional Requirements

**FR-PUBLIC-001**: Sistem harus menyediakan halaman Beranda dengan informasi umum organisasi

**FR-PUBLIC-002**: Sistem harus menyediakan halaman Tentang Kami

**FR-PUBLIC-003**: Sistem harus menampilkan Visi & Misi organisasi

**FR-PUBLIC-004**: Sistem harus menampilkan Struktur Organisasi

**FR-PUBLIC-005**: Sistem harus menampilkan daftar Event/Kegiatan

**FR-PUBLIC-006**: Sistem harus menampilkan Galeri Foto (album publik)

**FR-PUBLIC-007**: Sistem harus menyediakan halaman Kontak dengan informasi kontak organisasi

**FR-PUBLIC-008**: Sistem harus responsive dan mobile-friendly

### 3.13 Manajemen User & Role

#### 3.13.1 Description

Modul khusus Admin untuk mengelola user dan permission sistem.

#### 3.13.2 Functional Requirements

**FR-USER-001**: Admin harus dapat membuat user baru dan assign role

**FR-USER-002**: Admin harus dapat mengelola (edit/delete) user

**FR-USER-003**: Admin harus dapat reset password user

**FR-USER-004**: Admin harus dapat mengubah role user

**FR-USER-005**: Admin harus dapat menonaktifkan user tanpa menghapus

**FR-USER-006**: Admin harus dapat mengelola permission per role

**FR-USER-007**: Sistem harus mencatat log aktivitas user

---

## 4. External Interface Requirements

### 4.1 User Interfaces

#### 4.1.1 General UI Requirements

- **UI-001**: Interface harus menggunakan Tailwind CSS untuk styling
- **UI-002**: Interface harus responsive untuk desktop, tablet, dan mobile
- **UI-003**: Interface harus mengikuti prinsip UX yang baik (intuitif, konsisten, accessible)
- **UI-004**: Sistem harus menyediakan feedback visual untuk setiap aksi user (loading, success, error)
- **UI-005**: Menggunakan Vue 3 components untuk reusable UI elements
- **UI-006**: Form validation menggunakan Inertia.js Form Helper dengan real-time feedback

#### 4.1.2 Laravel Breeze Vue Components

Sistem memanfaatkan komponen bawaan Laravel Breeze Vue:

**Authentication Pages:**
- Login.vue
- Register.vue (dapat dinonaktifkan untuk production)
- ForgotPassword.vue
- ResetPassword.vue
- VerifyEmail.vue (optional)

**Layout Components:**
- AuthenticatedLayout.vue (untuk halaman yang memerlukan login)
- GuestLayout.vue (untuk halaman publik dan auth)

**Shared Components:**
- ApplicationLogo.vue
- Dropdown.vue
- NavLink.vue
- ResponsiveNavLink.vue
- TextInput.vue
- InputError.vue
- InputLabel.vue
- PrimaryButton.vue
- SecondaryButton.vue
- DangerButton.vue
- Checkbox.vue
- Modal.vue

#### 4.1.3 Custom Components to Build

**Dashboard Components:**
- StatCard.vue (untuk menampilkan statistik)
- ChartWidget.vue (untuk grafik)
- NotificationPanel.vue
- UpcomingEvents.vue

**Data Table Components:**
- DataTable.vue (sortable, searchable, filterable)
- Pagination.vue
- SearchBar.vue
- FilterDropdown.vue

**Form Components:**
- ImageUpload.vue
- MultipleImageUpload.vue
- DatePicker.vue
- RichTextEditor.vue
- FileUpload.vue

**Display Components:**
- EventCard.vue
- MemberCard.vue
- AlbumGallery.vue
- StructureChart.vue

#### 4.1.4 Specific Interface Elements

- **Login Page**: Menggunakan Login.vue dari Laravel Breeze
- **Dashboard**: Grid layout dengan cards untuk statistik menggunakan Vue components
- **Data Tables**: Custom DataTable.vue component dengan sorting, searching, filtering
- **Forms**: Menggunakan Inertia Form Helper dengan validasi real-time
- **Navigation**: Sidebar menu menggunakan AuthenticatedLayout.vue
- **Modal/Dialog**: Menggunakan Modal.vue component dari Breeze untuk confirmation dan form

### 4.2 Hardware Interfaces

Tidak ada hardware interface khusus yang dibutuhkan. Sistem diakses melalui standard web browser.

### 4.3 Software Interfaces

#### 4.3.1 Backend Framework
- **Laravel 12** (PHP 8.2+)
- Laravel Breeze untuk authentication scaffolding
- Eloquent ORM untuk database operations
- Laravel Policies dan Gates untuk authorization
- Laravel File Storage untuk file management
- Laravel Queue untuk background jobs (optional)

#### 4.3.2 Frontend Framework
- **Vue.js 3** dengan Composition API
- Inertia.js Vue Adapter
- Vue Router tidak diperlukan (handled by Inertia.js)
- Pinia dapat digunakan untuk state management kompleks (optional)

#### 4.3.3 Bridge Layer
- **Inertia.js**
- Server-side rendering support (optional via Inertia SSR)
- Automatic CSRF protection
- Form helper dengan error handling
- Shared data untuk passing data ke semua pages

#### 4.3.4 Build Tools
- **Vite** untuk asset bundling
- PostCSS untuk Tailwind CSS processing
- Autoprefixer untuk CSS compatibility

#### 4.3.5 Database
- **MySQL 8.0+**
- InnoDB storage engine
- UTF-8mb4 encoding
- Laravel Migrations untuk schema management

#### 4.3.6 Containerization
- **Docker** untuk deployment
- Docker Compose untuk orchestration

### 4.4 Communication Interfaces

- **HTTP/HTTPS**: Protokol komunikasi antara client dan server
- **Inertia Protocol**: Format komunikasi data antara Laravel dan Vue (JSON-based)
- **Session-based Authentication**: Menggunakan Laravel Session dengan cookies
- **CSRF Protection**: Automatic via Laravel dan Inertia.js

---

## 5. Non-Functional Requirements

### 5.1 Performance Requirements

**NFR-PERF-001**: Halaman harus load dalam waktu maksimal 3 detik dengan koneksi internet normal

**NFR-PERF-002**: Sistem harus dapat menangani minimal 100 concurrent users

**NFR-PERF-003**: Query database untuk list data harus dioptimasi dengan pagination (max 50 items per page)

**NFR-PERF-004**: Upload file/foto harus memiliki progress indicator

**NFR-PERF-005**: Response time server harus < 200ms untuk 95% requests

**NFR-PERF-006**: Vite build harus menghasilkan optimized bundle dengan code splitting

**NFR-PERF-007**: Inertia.js partial reloads harus digunakan untuk update data tanpa full page reload

### 5.2 Security Requirements

**NFR-SEC-001**: Password harus di-hash menggunakan bcrypt (Laravel default)

**NFR-SEC-002**: Sistem harus menggunakan HTTPS untuk production

**NFR-SEC-003**: Sistem harus immune terhadap SQL Injection (handled by Eloquent ORM)

**NFR-SEC-004**: Sistem harus immune terhadap XSS (Cross-Site Scripting) via Vue.js escaping

**NFR-SEC-005**: Sistem harus immune terhadap CSRF (handled by Laravel dan Inertia.js)

**NFR-SEC-006**: Session timeout setelah 30 menit inaktivitas

**NFR-SEC-007**: Sistem harus melakukan validasi input di sisi server menggunakan Laravel Form Requests

**NFR-SEC-008**: File upload harus dibatasi tipe dan ukuran file

**NFR-SEC-009**: Sistem harus mencatat log aktivitas penting (audit trail)

**NFR-SEC-010**: Authorization checks menggunakan Laravel Policies dan Gates

**NFR-SEC-011**: Rate limiting untuk login attempts menggunakan Laravel Throttle

### 5.3 Reliability Requirements

**NFR-REL-001**: Sistem harus memiliki uptime minimal 99% per bulan

**NFR-REL-002**: Sistem harus memiliki mekanisme backup otomatis database (daily)

**NFR-REL-003**: Sistem harus dapat recovery dari failure dalam waktu maksimal 1 jam

**NFR-REL-004**: Sistem harus melakukan soft delete untuk data penting

### 5.4 Maintainability Requirements

**NFR-MAINT-001**: Code harus mengikuti PSR standard untuk PHP

**NFR-MAINT-002**: Code harus ter-dokumentasi dengan baik (comments, docblocks)

**NFR-MAINT-003**: Sistem harus modular dan mudah dikembangkan

**NFR-MAINT-004**: Database schema harus ter-dokumentasi

**NFR-MAINT-005**: Sistem harus menggunakan version control (Git)

**NFR-MAINT-006**: Vue components harus mengikuti Vue.js Style Guide

**NFR-MAINT-007**: Laravel code harus mengikuti Laravel best practices

### 5.5 Usability Requirements

**NFR-USE-001**: User dengan technical skill minimal harus dapat mengoperasikan sistem dalam waktu < 1 jam training

**NFR-USE-002**: Error messages harus jelas dan memberikan solusi

**NFR-USE-003**: Interface harus konsisten di seluruh sistem

**NFR-USE-004**: Sistem harus menyediakan tooltips/help text untuk field yang kompleks

**NFR-USE-005**: Sistem harus mobile-friendly dengan touch-optimized controls

**NFR-USE-006**: Form validation errors harus ditampilkan secara real-time menggunakan Inertia Form Helper

### 5.6 Scalability Requirements

**NFR-SCALE-001**: Database schema harus dirancang untuk mendukung pertumbuhan data

**NFR-SCALE-002**: Sistem harus dapat di-scale horizontal (multiple servers)

**NFR-SCALE-003**: Arsitektur harus mendukung caching (Redis/Memcached)

**NFR-SCALE-004**: Asset files harus dapat di-serve via CDN

### 5.7 Compatibility Requirements

**NFR-COMPAT-001**: Sistem harus kompatibel dengan browser: Chrome 100+, Firefox 100+, Safari 15+, Edge 100+

**NFR-COMPAT-002**: Sistem harus kompatibel dengan mobile browsers (iOS Safari, Chrome Mobile)

**NFR-COMPAT-003**: Responsive design harus mendukung screen size 320px - 2560px

**NFR-COMPAT-004**: JavaScript ES6+ compatibility via Vite transpilation

---

## 6. Other Requirements

### 6.1 Database Requirements

#### 6.1.1 Core Tables

Sistem memerlukan minimal tabel-tabel berikut (managed via Laravel Migrations):

**users** (bawaan Laravel Breeze)
```php
id: bigIncrements
name: string
email: string (unique)
email_verified_at: timestamp (nullable)
password: string
role: enum('admin','ketua','bendahara','sekretaris','anggota')
status: enum('active','inactive') default 'active'
remember_token: string (nullable)
created_at: timestamp
updated_at: timestamp
```

**password_reset_tokens** (bawaan Laravel Breeze)
```php
email: string (primary)
token: string
created_at: timestamp
```

**sessions** (bawaan Laravel Breeze)
```php
id: string (primary)
user_id: bigInteger (nullable)
ip_address: string (nullable)
user_agent: text (nullable)
payload: longText
last_activity: integer
```

**members**
```php
id: bigIncrements
user_id: unsignedBigInteger (nullable, foreign → users.id)
member_code: string (unique)
full_name: string
email: string (nullable)
phone: string (nullable)
address: text (nullable)
date_of_birth: date (nullable)
join_date: date
status: enum('active','inactive') default 'active'
photo: string (nullable)
notes: text (nullable)
created_at: timestamp
updated_at: timestamp
deleted_at: timestamp (nullable - soft delete)
```

**events**
```php
id: bigIncrements
name: string
slug: string (unique)
description: text (nullable)
start_date: dateTime
end_date: dateTime (nullable)
location: string (nullable)
pic_id: unsignedBigInteger (nullable, foreign → members.id)
max_participants: integer (nullable)
status: enum('draft','published','ongoing','completed','cancelled')
is_public: boolean default true
created_by: unsignedBigInteger (foreign → users.id)
created_at: timestamp
updated_at: timestamp
deleted_at: timestamp (nullable)
```

**event_participants**
```php
id: bigIncrements
event_id: unsignedBigInteger (foreign → events.id)
member_id: unsignedBigInteger (foreign → members.id)
registration_date: timestamp
attendance_status: enum('registered','attended','absent') default 'registered'
notes: text (nullable)
created_at: timestamp
updated_at: timestamp
unique(event_id, member_id)
```

**contribution_types**
```php
id: bigIncrements
name: string
description: text (nullable)
amount: decimal(15,2)
period: enum('once','monthly','quarterly','yearly')
is_active: boolean default true
created_at: timestamp
updated_at: timestamp
```

**contributions**
```php
id: bigIncrements
member_id: unsignedBigInteger (foreign → members.id)
contribution_type_id: unsignedBigInteger (foreign → contribution_types.id)
amount: decimal(15,2)
payment_date: date
due_date: date (nullable)
status: enum('pending','paid','overdue','cancelled') default 'pending'
payment_method: string (nullable)
notes: text (nullable)
recorded_by: unsignedBigInteger (foreign → users.id)
created_at: timestamp
updated_at: timestamp
```

**donations**
```php
id: bigIncrements
program_name: string
slug: string (unique)
description: text (nullable)
target_amount: decimal(15,2)
collected_amount: decimal(15,2) default 0
start_date: date
end_date: date (nullable)
status: enum('active','completed','cancelled') default 'active'
is_public: boolean default true
created_by: unsignedBigInteger (foreign → users.id)
created_at: timestamp
updated_at: timestamp
```

**donation_transactions**
```php
id: bigIncrements
donation_id: unsignedBigInteger (foreign → donations.id)
donor_name: string (nullable)
donor_email: string (nullable)
donor_phone: string (nullable)
amount: decimal(15,2)
donation_date: date
is_anonymous: boolean default false
notes: text (nullable)
created_at: timestamp
updated_at: timestamp
```

**wallets**
```php
id: bigIncrements
name: string
description: text (nullable)
initial_balance: decimal(15,2) default 0
current_balance: decimal(15,2) default 0
is_active: boolean default true
created_at: timestamp
updated_at: timestamp
```

**finances**
```php
id: bigIncrements
wallet_id: unsignedBigInteger (foreign → wallets.id)
type: enum('in','out')
category: string
amount: decimal(15,2)
description: text
transaction_date: date
receipt: string (nullable)
reference_type: string (nullable)
reference_id: unsignedBigInteger (nullable)
recorded_by: unsignedBigInteger (foreign → users.id)
created_at: timestamp
updated_at: timestamp
deleted_at: timestamp (nullable)
```

**albums**
```php
id: bigIncrements
name: string
slug: string (unique)
description: text (nullable)
category: enum('event','routine','official','other')
cover_image: string (nullable)
event_id: unsignedBigInteger (nullable, foreign → events.id)
status: enum('public','private') default 'public'
created_by: unsignedBigInteger (foreign → users.id)
created_at: timestamp
updated_at: timestamp
deleted_at: timestamp (nullable)
```

**photos**
```php
id: bigIncrements
album_id: unsignedBigInteger (foreign → albums.id, onDelete cascade)
filename: string
original_name: string
file_path: string
file_size: integer (nullable)
description: text (nullable)
order: integer default 0
uploaded_by: unsignedBigInteger (foreign → users.id)
created_at: timestamp
updated_at: timestamp
```

**vision_missions**
```php
id: bigIncrements
vision: text
missions: json
period_start: year (nullable)
period_end: year (nullable)
status: enum('active','inactive') default 'active'
created_by: unsignedBigInteger (foreign → users.id)
created_at: timestamp
updated_at: timestamp
```

**organization_structures**
```php
id: bigIncrements
position: string
member_id: unsignedBigInteger (foreign → members.id)
period_start: year
period_end: year (nullable)
hierarchy_order: integer default 0
parent_position_id: unsignedBigInteger (nullable, foreign → organization_structures.id)
photo: string (nullable)
status: enum('active','inactive') default 'active'
created_at: timestamp
updated_at: timestamp
```

**announcements**
```php
id: bigIncrements
title: string
content: text
publish_date: dateTime
status: enum('draft','published','archived') default 'draft'
target_audience: enum('all','admin','ketua','bendahara','sekretaris','anggota')
priority: enum('low','normal','high','urgent') default 'normal'
created_by: unsignedBigInteger (foreign → users.id)
created_at: timestamp
updated_at: timestamp
```

**meeting_minutes**
```php
id: bigIncrements
meeting_date: dateTime
agenda: text
participants: json (nullable)
results: text
attachments: json (nullable)
created_by: unsignedBigInteger (foreign → users.id)
created_at: timestamp
updated_at: timestamp
```

**documents**
```php
id: bigIncrements
name: string
category: string (nullable)
file_path: string
file_size: integer (nullable)
file_type: string (nullable)
description: text (nullable)
uploaded_by: unsignedBigInteger (foreign → users.id)
created_at: timestamp
updated_at: timestamp
deleted_at: timestamp (nullable)
```

**activity_logs**
```php
id: bigIncrements
user_id: unsignedBigInteger (nullable, foreign → users.id)
action: string
model: string (nullable)
model_id: unsignedBigInteger (nullable)
description: text (nullable)
ip_address: string (nullable)
user_agent: text (nullable)
created_at: timestamp
```

### 6.2 Laravel Breeze Vue Routes Structure

#### 6.2.1 Authentication Routes (Bawaan Breeze)

```php
// routes/auth.php
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
        ->name('verification.notice');
    
    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    
    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');
    
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
```

#### 6.2.2 Application Routes

```php
// routes/web.php

use Inertia\Inertia;

// Public Routes
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/vision-mission', [PublicController::class, 'visionMission'])->name('vision-mission');
Route::get('/structure', [PublicController::class, 'structure'])->name('structure');
Route::get('/events', [PublicController::class, 'events'])->name('events.public');
Route::get('/events/{slug}', [PublicController::class, 'eventDetail'])->name('events.public.detail');
Route::get('/gallery', [PublicController::class, 'gallery'])->name('gallery');
Route::get('/gallery/{slug}', [PublicController::class, 'albumDetail'])->name('gallery.detail');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::get('/donations', [PublicController::class, 'donations'])->name('donations.public');

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    
    // Members
    Route::middleware('can:viewAny,App\Models\Member')->group(function () {
        Route::resource('members', MemberController::class);
        Route::get('members/{member}/contributions', [MemberController::class, 'contributions'])
            ->name('members.contributions');
        Route::get('members/{member}/events', [MemberController::class, 'events'])
            ->name('members.events');
        Route::post('members/{member}/photo', [MemberController::class, 'uploadPhoto'])
            ->name('members.photo');
    });
    
    // Events
    Route::middleware('can:viewAny,App\Models\Event')->group(function () {
        Route::resource('events', EventController::class);
        Route::get('events/calendar/view', [EventController::class, 'calendar'])
            ->name('events.calendar');
        Route::post('events/{event}/participants', [EventParticipantController::class, 'store'])
            ->name('events.participants.store');
        Route::delete('events/{event}/participants/{member}', [EventParticipantController::class, 'destroy'])
            ->name('events.participants.destroy');
    });
    
    // Contributions
    Route::middleware('can:viewAny,App\Models\Contribution')->group(function () {
        Route::resource('contribution-types', ContributionTypeController::class);
        Route::resource('contributions', ContributionController::class);
        Route::get('contributions/overdue/list', [ContributionController::class, 'overdue'])
            ->name('contributions.overdue');
        Route::get('my-contributions', [ContributionController::class, 'myContributions'])
            ->name('contributions.my');
    });
    
    // Donations
    Route::middleware('can:viewAny,App\Models\Donation')->group(function () {
        Route::resource('donations', DonationController::class);
        Route::post('donations/{donation}/transactions', [DonationTransactionController::class, 'store'])
            ->name('donations.transactions.store');
        Route::get('donations/{donation}/report', [DonationController::class, 'report'])
            ->name('donations.report');
    });
    
    // Finance & Wallets
    Route::middleware('can:viewAny,App\Models\Finance')->group(function () {
        Route::resource('wallets', WalletController::class);
        Route::get('wallets/{wallet}/transactions', [WalletController::class, 'transactions'])
            ->name('wallets.transactions');
        
        Route::resource('finances', FinanceController::class);
        Route::post('finances/{finance}/receipt', [FinanceController::class, 'uploadReceipt'])
            ->name('finances.receipt');
        
        Route::get('reports/finance', [ReportController::class, 'finance'])
            ->name('reports.finance');
        Route::get('reports/cash-flow', [ReportController::class, 'cashFlow'])
            ->name('reports.cash-flow');
        Route::get('reports/balance-sheet', [ReportController::class, 'balanceSheet'])
            ->name('reports.balance-sheet');
        Route::post('reports/export', [ReportController::class, 'export'])
            ->name('reports.export');
    });
    
    // Albums & Photos
    Route::middleware('can:viewAny,App\Models\Album')->group(function () {
        Route::resource('albums', AlbumController::class);
        Route::post('albums/{album}/photos', [PhotoController::class, 'store'])
            ->name('albums.photos.store');
        Route::delete('albums/{album}/photos/{photo}', [PhotoController::class, 'destroy'])
            ->name('albums.photos.destroy');
        Route::patch('albums/{album}/photos/{photo}', [PhotoController::class, 'update'])
            ->name('albums.photos.update');
    });
    
    // Vision & Mission
    Route::middleware('can:manage,App\Models\VisionMission')->group(function () {
        Route::resource('vision-missions', VisionMissionController::class)
            ->except(['show']);
    });
    
    // Organization Structure
    Route::middleware('can:manage,App\Models\OrganizationStructure')->group(function () {
        Route::resource('structures', OrganizationStructureController::class);
        Route::post('structures/{structure}/photo', [OrganizationStructureController::class, 'uploadPhoto'])
            ->name('structures.photo');
    });
    
    // Announcements
    Route::middleware('can:viewAny,App\Models\Announcement')->group(function () {
        Route::resource('announcements', AnnouncementController::class);
    });
    
    // Meeting Minutes
    Route::middleware('can:viewAny,App\Models\MeetingMinute')->group(function () {
        Route::resource('meeting-minutes', MeetingMinuteController::class);
        Route::post('meeting-minutes/{minute}/attachments', [MeetingMinuteController::class, 'uploadAttachment'])
            ->name('meeting-minutes.attachments');
    });
    
    // Documents
    Route::middleware('can:viewAny,App\Models\Document')->group(function () {
        Route::resource('documents', DocumentController::class);
        Route::get('documents/search/query', [DocumentController::class, 'search'])
            ->name('documents.search');
    });
    
    // User Management (Admin Only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('users.reset-password');
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');
        
        // Activity Logs
        Route::get('activity-logs', [ActivityLogController::class, 'index'])
            ->name('activity-logs.index');
        Route::get('activity-logs/{log}', [ActivityLogController::class, 'show'])
            ->name('activity-logs.show');
        
        // System Settings
        Route::get('settings', [SettingController::class, 'index'])
            ->name('settings.index');
        Route::patch('settings', [SettingController::class, 'update'])
            ->name('settings.update');
        
        // Backup & Restore
        Route::get('backup', [BackupController::class, 'index'])
            ->name('backup.index');
        Route::post('backup/create', [BackupController::class, 'create'])
            ->name('backup.create');
        Route::get('backup/{file}/download', [BackupController::class, 'download'])
            ->name('backup.download');
        Route::post('backup/restore', [BackupController::class, 'restore'])
            ->name('backup.restore');
    });
});

require __DIR__.'/auth.php';
```

### 6.3 Inertia.js Page Components Structure

#### 6.3.1 Directory Structure

```
resources/js/
├── app.js                          # Main entry point
├── bootstrap.js                    # Bootstrap code
├── Components/                     # Reusable components
│   ├── ApplicationLogo.vue
│   ├── Checkbox.vue
│   ├── DangerButton.vue
│   ├── Dropdown.vue
│   ├── DropdownLink.vue
│   ├── InputError.vue
│   ├── InputLabel.vue
│   ├── Modal.vue
│   ├── NavLink.vue
│   ├── PrimaryButton.vue
│   ├── ResponsiveNavLink.vue
│   ├── SecondaryButton.vue
│   ├── TextInput.vue
│   ├── Dashboard/                  # Dashboard specific
│   │   ├── StatCard.vue
│   │   ├── ChartWidget.vue
│   │   ├── NotificationPanel.vue
│   │   └── UpcomingEvents.vue
│   ├── DataTable/                  # Data table components
│   │   ├── DataTable.vue
│   │   ├── Pagination.vue
│   │   ├── SearchBar.vue
│   │   └── FilterDropdown.vue
│   ├── Form/                       # Form components
│   │   ├── ImageUpload.vue
│   │   ├── MultipleImageUpload.vue
│   │   ├── DatePicker.vue
│   │   ├── RichTextEditor.vue
│   │   └── FileUpload.vue
│   └── Display/                    # Display components
│       ├── EventCard.vue
│       ├── MemberCard.vue
│       ├── AlbumGallery.vue
│       └── StructureChart.vue
├── Layouts/                        # Layout components
│   ├── AuthenticatedLayout.vue
│   ├── GuestLayout.vue
│   └── PublicLayout.vue
└── Pages/                          # Page components
    ├── Auth/                       # Authentication pages
    │   ├── ConfirmPassword.vue
    │   ├── ForgotPassword.vue
    │   ├── Login.vue
    │   ├── Register.vue
    │   ├── ResetPassword.vue
    │   └── VerifyEmail.vue
    ├── Profile/                    # Profile pages
    │   ├── Edit.vue
    │   └── Partials/
    │       ├── DeleteUserForm.vue
    │       ├── UpdatePasswordForm.vue
    │       ├── UpdateProfileInformationForm.vue
    │       └── UpdateProfilePhotoForm.vue
    ├── Dashboard.vue               # Main dashboard
    ├── Members/                    # Member management
    │   ├── Index.vue
    │   ├── Create.vue
    │   ├── Edit.vue
    │   ├── Show.vue
    │   └── Contributions.vue
    ├── Events/                     # Event management
    │   ├── Index.vue
    │   ├── Create.vue
    │   ├── Edit.vue
    │   ├── Show.vue
    │   └── Calendar.vue
    ├── Contributions/              # Contribution management
    │   ├── Types/
    │   │   ├── Index.vue
    │   │   ├── Create.vue
    │   │   └── Edit.vue
    │   ├── Index.vue
    │   ├── Create.vue
    │   ├── Edit.vue
    │   ├── Overdue.vue
    │   └── MyContributions.vue
    ├── Donations/                  # Donation management
    │   ├── Index.vue
    │   ├── Create.vue
    │   ├── Edit.vue
    │   ├── Show.vue
    │   └── Report.vue
    ├── Finance/                    # Finance management
    │   ├── Wallets/
    │   │   ├── Index.vue
    │   │   ├── Create.vue
    │   │   ├── Edit.vue
    │   │   └── Transactions.vue
    │   ├── Index.vue
    │   ├── Create.vue
    │   ├── Edit.vue
    │   └── Show.vue
    ├── Reports/                    # Reports
    │   ├── Finance.vue
    │   ├── CashFlow.vue
    │   └── BalanceSheet.vue
    ├── Albums/                     # Album & Photo management
    │   ├── Index.vue
    │   ├── Create.vue
    │   ├── Edit.vue
    │   └── Show.vue
    ├── VisionMissions/             # Vision & Mission
    │   ├── Index.vue
    │   ├── Create.vue
    │   └── Edit.vue
    ├── Structures/                 # Organization structure
    │   ├── Index.vue
    │   ├── Create.vue
    │   └── Edit.vue
    ├── Announcements/              # Announcements
    │   ├── Index.vue
    │   ├── Create.vue
    │   ├── Edit.vue
    │   └── Show.vue
    ├── MeetingMinutes/             # Meeting minutes
    │   ├── Index.vue
    │   ├── Create.vue
    │   ├── Edit.vue
    │   └── Show.vue
    ├── Documents/                  # Documents
    │   ├── Index.vue
    │   ├── Create.vue
    │   └── Search.vue
    ├── Users/                      # User management (Admin)
    │   ├── Index.vue
    │   ├── Create.vue
    │   └── Edit.vue
    ├── ActivityLogs/               # Activity logs (Admin)
    │   ├── Index.vue
    │   └── Show.vue
    ├── Settings/                   # System settings (Admin)
    │   └── Index.vue
    ├── Backup/                     # Backup & restore (Admin)
    │   └── Index.vue
    └── Public/                     # Public pages
        ├── Home.vue
        ├── About.vue
        ├── VisionMission.vue
        ├── Structure.vue
        ├── Events.vue
        ├── EventDetail.vue
        ├── Gallery.vue
        ├── AlbumDetail.vue
        ├── Contact.vue
        └── Donations.vue
```

### 6.4 Laravel Backend Structure

#### 6.4.1 Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                   # Authentication controllers (Breeze)
│   │   │   ├── AuthenticatedSessionController.php
│   │   │   ├── ConfirmablePasswordController.php
│   │   │   ├── EmailVerificationNotificationController.php
│   │   │   ├── EmailVerificationPromptController.php
│   │   │   ├── NewPasswordController.php
│   │   │   ├── PasswordController.php
│   │   │   ├── PasswordResetLinkController.php
│   │   │   ├── RegisteredUserController.php
│   │   │   └── VerifyEmailController.php
│   │   ├── ActivityLogController.php
│   │   ├── AlbumController.php
│   │   ├── AnnouncementController.php
│   │   ├── BackupController.php
│   │   ├── ContributionController.php
│   │   ├── ContributionTypeController.php
│   │   ├── DashboardController.php
│   │   ├── DocumentController.php
│   │   ├── DonationController.php
│   │   ├── DonationTransactionController.php
│   │   ├── EventController.php
│   │   ├── EventParticipantController.php
│   │   ├── FinanceController.php
│   │   ├── MeetingMinuteController.php
│   │   ├── MemberController.php
│   │   ├── OrganizationStructureController.php
│   │   ├── PhotoController.php
│   │   ├── ProfileController.php
│   │   ├── PublicController.php
│   │   ├── ReportController.php
│   │   ├── SettingController.php
│   │   ├── UserController.php
│   │   ├── VisionMissionController.php
│   │   └── WalletController.php
│   ├── Middleware/
│   │   ├── HandleInertiaRequests.php
│   │   ├── RoleMiddleware.php
│   │   └── LogActivity.php
│   ├── Requests/
│   │   ├── Auth/                   # Auth requests (Breeze)
│   │   │   └── LoginRequest.php
│   │   ├── AlbumRequest.php
│   │   ├── AnnouncementRequest.php
│   │   ├── ContributionRequest.php
│   │   ├── ContributionTypeRequest.php
│   │   ├── DonationRequest.php
│   │   ├── EventRequest.php
│   │   ├── FinanceRequest.php
│   │   ├── MeetingMinuteRequest.php
│   │   ├── MemberRequest.php
│   │   ├── OrganizationStructureRequest.php
│   │   ├── ProfileUpdateRequest.php
│   │   ├── UserRequest.php
│   │   ├── VisionMissionRequest.php
│   │   └── WalletRequest.php
│   └── Resources/
│       ├── AlbumResource.php
│       ├── AnnouncementResource.php
│       ├── ContributionResource.php
│       ├── DonationResource.php
│       ├── EventResource.php
│       ├── FinanceResource.php
│       ├── MemberResource.php
│       └── UserResource.php
├── Models/
│   ├── User.php
│   ├── Member.php
│   ├── Event.php
│   ├── EventParticipant.php
│   ├── ContributionType.php
│   ├── Contribution.php
│   ├── Donation.php
│   ├── DonationTransaction.php
│   ├── Wallet.php
│   ├── Finance.php
│   ├── Album.php
│   ├── Photo.php
│   ├── VisionMission.php
│   ├── OrganizationStructure.php
│   ├── Announcement.php
│   ├── MeetingMinute.php
│   ├── Document.php
│   └── ActivityLog.php
├── Policies/
│   ├── AlbumPolicy.php
│   ├── AnnouncementPolicy.php
│   ├── ContributionPolicy.php
│   ├── DonationPolicy.php
│   ├── DocumentPolicy.php
│   ├── EventPolicy.php
│   ├── FinancePolicy.php
│   ├── MemberPolicy.php
│   └── UserPolicy.php
├── Services/
│   ├── ActivityLogService.php
│   ├── BackupService.php
│   ├── FinanceService.php
│   ├── ReportService.php
│   └── NotificationService.php
├── Traits/
│   ├── HasRole.php
│   ├── LogsActivity.php
│   └── Searchable.php
└── Enums/
    ├── UserRole.php
    ├── UserStatus.php
    ├── EventStatus.php
    ├── ContributionStatus.php
    ├── DonationStatus.php
    ├── AnnouncementStatus.php
    └── Priority.php
```

### 6.5 Backup and Recovery

**REQ-BACKUP-001**: Sistem harus menyediakan fitur manual backup database

**REQ-BACKUP-002**: Sistem harus menyediakan fitur restore database

**REQ-BACKUP-003**: Backup file harus dapat didownload oleh Admin

**REQ-BACKUP-004**: Sistem harus melakukan auto-backup daily menggunakan Laravel Scheduler

**REQ-BACKUP-005**: Backup harus mencakup database dan uploaded files

**REQ-BACKUP-006**: Sistem harus menyimpan minimal 7 backup terakhir

### 6.6 Reporting Requirements

**REQ-REPORT-001**: Sistem harus dapat generate laporan keuangan dalam format PDF menggunakan library seperti DomPDF atau Snappy

**REQ-REPORT-002**: Sistem harus dapat export data dalam format Excel menggunakan Laravel Excel

**REQ-REPORT-003**: Laporan harus mencakup:
- Laporan keuangan (kas, iuran, donasi)
- Laporan keanggotaan
- Laporan kegiatan
- Activity log

**REQ-REPORT-004**: Laporan harus dapat difilter berdasarkan periode waktu

**REQ-REPORT-005**: Laporan harus dapat dicetak langsung dari browser

### 6.7 File Storage Requirements

**REQ-STORAGE-001**: File upload harus menggunakan Laravel Storage dengan disk configuration

**REQ-STORAGE-002**: File types yang diizinkan:
- Images: jpg, jpeg, png, gif (max 5MB)
- Documents: pdf, doc, docx, xls, xlsx (max 10MB)

**REQ-STORAGE-003**: Uploaded files harus disimpan dengan struktur folder yang organized:
```
storage/app/public/
├── members/
│   └── photos/
├── events/
│   └── documentation/
├── finances/
│   └── receipts/
├── albums/
│   ├── covers/
│   └── photos/
├── structures/
│   └── photos/
├── documents/
└── meeting-minutes/
    └── attachments/
```

**REQ-STORAGE-004**: File names harus di-sanitize dan menggunakan unique identifier

**REQ-STORAGE-005**: Sistem harus membuat thumbnail untuk images

### 6.8 Validation Requirements

**REQ-VALID-001**: Semua input harus divalidasi menggunakan Laravel Form Requests

**REQ-VALID-002**: Validation rules harus comprehensive:
- Required fields
- Data types
- Min/max length
- Format validation (email, phone, date)
- Unique constraints

**REQ-VALID-003**: Error messages harus user-friendly dalam Bahasa Indonesia

**REQ-VALID-004**: Client-side validation menggunakan Inertia Form Helper untuk better UX

**REQ-VALID-005**: Custom validation rules untuk business logic:
- Member code format
- Date range validation
- Balance validation (finance)
- File type and size validation

### 6.9 Future Enhancements (Phase 2)

Fitur-fitur yang direncanakan untuk pengembangan selanjutnya:

1. **Integrasi Payment Gateway**
   - Midtrans, GoPay, OVO, DANA
   - Auto-update status pembayaran iuran
   - Payment confirmation via webhook

2. **Notification System**
   - Email notifications menggunakan Laravel Mail
   - WhatsApp notifications via API (Fonnte, Wablas)
   - In-app notifications menggunakan Laravel Broadcasting
   - Push notifications untuk mobile

3. **Advanced Analytics**
   - Dashboard dengan chart interaktif (Chart.js, ApexCharts)
   - Statistik keanggotaan
   - Analisis keuangan
   - Trend analysis
   - Export to various formats

4. **Mobile Application**
   - Native iOS dan Android app atau PWA
   - Push notifications
   - Offline mode support
   - Mobile-optimized UI

5. **Advanced Features**
   - QR Code untuk absensi event menggunakan SimpleSoftwareIO/simple-qrcode
   - Digital certificate generator untuk peserta event
   - Online voting system dengan security
   - Ticketing system untuk event berbayar
   - Discussion forum atau chat feature
   - Calendar integration (Google Calendar, Outlook)

6. **Performance Optimization**
   - Redis caching untuk frequently accessed data
   - Laravel Horizon untuk queue monitoring
   - Database query optimization
   - CDN integration untuk static assets
   - Image optimization dan lazy loading

7. **Security Enhancements**
   - Two-factor authentication (2FA)
   - Activity monitoring dan anomaly detection
   - IP whitelisting untuk admin access
   - Automated security scanning
   - GDPR compliance features

---

## 7. Appendices

### 7.1 User Role Permission Matrix

| Fitur/Module | Admin | Ketua | Bendahara | Sekretaris | Anggota | Publik |
|--------------|-------|-------|-----------|------------|---------|--------|
| Dashboard | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| Manage Users | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Manage Members (CRUD) | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| View Members | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ |
| Manage Events (CRUD) | ✓ | ✓ | ✗ | ✓ | ✗ | ✗ |
| View Events | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Manage Finance (CRUD) | ✓ | ✗ | ✓ | ✗ | ✗ | ✗ |
| View Finance Reports | ✓ | ✓ (RO) | ✓ | ✗ | ✗ | ✗ |
| Manage Contributions | ✓ | ✗ | ✓ | ✗ | ✗ | ✗ |
| View Own Contributions | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| Manage Donations | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ |
| View Donations | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Manage Albums (CRUD) | ✓ | ✓ | ✗ | ✓ | ✗ | ✗ |
| View Gallery | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Manage Vision/Mission | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| View Vision/Mission | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Manage Structure | ✓ | ✓ | ✗ | ✓* | ✗ | ✗ |
| View Structure | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Manage Announcements | ✓ | ✓ | ✗ | ✓ | ✗ | ✗ |
| View Announcements | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| Manage Meeting Minutes | ✓ | ✓ | ✗ | ✓ | ✗ | ✗ |
| Manage Documents | ✓ | ✓ | ✗ | ✓ | ✗ | ✗ |
| System Settings | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Backup & Restore | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| View Activity Logs | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| Update Own Profile | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |

*Note: ✓ = Full Access, ✓ (RO) = Read Only, ✓* = Conditional Access, ✗ = No Access*

### 7.2 System Workflow Diagram

#### 7.2.1 User Registration & Onboarding Flow

```
Admin Creates User Account
    ↓
Assign Role (Admin/Ketua/Bendahara/Sekretaris/Anggota)
    ↓
User Receives Login Credentials
    ↓
First Login → Force Password Change (via Breeze)
    ↓
User Access Dashboard (Role-Based via Inertia)
```

#### 7.2.2 Member Management Flow

```
Ketua/Admin: Add New Member (Inertia Form)
    ↓
Enter Member Details
    ↓
Create Associated User Account (Optional)
    ↓
Member Active in System
    ↓
Track: Contributions + Event Participation
```

#### 7.2.3 Financial Transaction Flow

```
Bendahara: Select Wallet/Kas (Inertia Page)
    ↓
Choose Transaction Type (In/Out)
    ↓
Enter Amount + Category + Description
    ↓
Upload Receipt (Optional - Laravel Storage)
    ↓
Transaction Recorded (Laravel Controller)
    ↓
Wallet Balance Auto-Updated (Model Event)
    ↓
Reflected in Financial Reports (Vue Component)
```

#### 7.2.4 Event Management Flow

```
Sekretaris: Create Event (Inertia Form)
    ↓
Enter Event Details
    ↓
Publish Event (Status Change)
    ↓
Event Appears on Public Page (PublicController → Vue)
    ↓
Track Event Participants (EventParticipant Model)
    ↓
Upload Event Documentation (Laravel Storage)
    ↓
Link to Photo Album (Relationship)
    ↓
Event Completed
```

#### 7.2.5 Contribution Payment Flow

```
Bendahara: Record Contribution Payment (Inertia Form)
    ↓
Select Member + Contribution Type
    ↓
Enter Amount + Payment Date
    ↓
Mark as Paid (Status Update)
    ↓
Update Member Contribution History (Database)
    ↓
Update Finance (Uang Masuk - Auto create Finance record)
    ↓
Member Can View Own Payment Status (MyContributions Page)
```

### 7.3 Inertia.js Data Flow

#### 7.3.1 How Inertia.js Works

```
User Action (Vue Component)
    ↓
Inertia Request (via Inertia.visit() or Inertia Form)
    ↓
Laravel Route → Controller
    ↓
Business Logic + Data Processing
    ↓
Return Inertia Response (Inertia::render())
    ↓
JSON Response with Component Name + Props
    ↓
Inertia.js Updates Vue Component
    ↓
No Full Page Reload (SPA Experience)
```

#### 7.3.2 Example: Creating a Member

**Vue Component (Members/Create.vue)**
```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    full_name: '',
    email: '',
    phone: '',
    address: '',
    join_date: '',
    status: 'active'
})

const submit = () => {
    form.post(route('members.store'), {
        onSuccess: () => {
            // Redirect to members index handled by Inertia
        }
    })
}
</script>
```

**Laravel Controller**
```php
public function store(MemberRequest $request)
{
    $member = Member::create($request->validated());
    
    return redirect()
        ->route('members.index')
        ->with('success', 'Member created successfully');
}
```

### 7.4 Laravel Policy Examples

#### 7.4.1 MemberPolicy

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Member;

class MemberPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'ketua', 'bendahara', 'sekretaris']);
    }
    
    public function view(User $user, Member $member): bool
    {
        return in_array($user->role, ['admin', 'ketua', 'bendahara', 'sekretaris']);
    }
    
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'ketua']);
    }
    
    public function update(User $user, Member $member): bool
    {
        return in_array($user->role, ['admin', 'ketua']);
    }
    
    public function delete(User $user, Member $member): bool
    {
        return $user->role === 'admin';
    }
}
```

#### 7.4.2 FinancePolicy

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Finance;

class FinancePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'ketua', 'bendahara']);
    }
    
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'bendahara']);
    }
    
    public function update(User $user, Finance $finance): bool
    {
        return in_array($user->role, ['admin', 'bendahara']);
    }
    
    public function delete(User $user, Finance $finance): bool
    {
        return $user->role === 'admin';
    }
}
```

### 7.5 Middleware Configuration

#### 7.5.1 HandleInertiaRequests Middleware

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'permissions' => [
                'can_manage_users' => $request->user()?->role === 'admin',
                'can_manage_finance' => in_array($request->user()?->role, ['admin', 'bendahara']),
                'can_manage_members' => in_array($request->user()?->role, ['admin', 'ketua']),
                // Add more permissions as needed
            ],
        ]);
    }
}
```

### 7.6 Testing Requirements

#### 7.6.1 Unit Testing

**TEST-UNIT-001**: All model methods harus memiliki unit test coverage minimal 80%

**TEST-UNIT-002**: All service classes harus ditest secara isolated menggunakan PHPUnit

**TEST-UNIT-003**: Validation rules harus ditest dengan valid dan invalid inputs

**TEST-UNIT-004**: Policy authorization methods harus ditest untuk semua roles

**Example Unit Test:**
```php
public function test_admin_can_create_member()
{
    $admin = User::factory()->create(['role' => 'admin']);
    
    $this->actingAs($admin)
        ->assertTrue($admin->can('create', Member::class));
}
```

#### 7.6.2 Feature Testing

**TEST-FEAT-001**: Inertia responses harus ditest untuk correct component dan props

**TEST-FEAT-002**: CRUD operations harus ditest end-to-end

**TEST-FEAT-003**: File upload functionality harus ditest

**TEST-FEAT-004**: Form validation harus ditest

**Example Feature Test:**
```php
public function test_user_can_view_members_index()
{
    $user = User::factory()->create(['role' => 'ketua']);
    
    $response = $this->actingAs($user)
        ->get(route('members.index'));
    
    $response->assertInertia(fn ($page) => 
        $page->component('Members/Index')
             ->has('members.data')
    );
}
```

#### 7.6.3 Browser Testing (Dusk)

**TEST-DUSK-001**: User authentication flow menggunakan Laravel Dusk

**TEST-DUSK-002**: Critical user workflows (create member, record payment, etc.)

**TEST-DUSK-003**: Responsive design testing pada berbagai viewport sizes

**Example Dusk Test:**
```php
public function test_user_can_login()
{
    $user = User::factory()->create();
    
    $this->browse(function ($browser) use ($user) {
        $browser->visit('/login')
            ->type('email', $user->email)
            ->type('password', 'password')
            ->press('Log in')
            ->assertPathIs('/dashboard');
    });
}
```

#### 7.6.4 Performance Testing

**TEST-PERF-001**: Load testing dengan 100 concurrent users menggunakan tools seperti Apache JMeter

**TEST-PERF-002**: Database query performance testing dengan Laravel Telescope

**TEST-PERF-003**: File upload performance dengan various file sizes

**TEST-PERF-004**: Page load time testing untuk semua major pages (target < 3s)

#### 7.6.5 Security Testing

**TEST-SEC-001**: CSRF protection testing

**TEST-SEC-002**: SQL injection testing pada semua input fields

**TEST-SEC-003**: XSS testing pada user-generated content

**TEST-SEC-004**: Authentication bypass testing

**TEST-SEC-005**: Authorization testing untuk setiap role

### 7.7 Deployment Requirements

#### 7.7.1 Development Environment Setup

```bash
# Requirements
PHP: 8.2+
Composer: 2.x
Node.js: 20.x LTS
NPM/PNPM: 10.x
MySQL: 8.0+
Docker: 24.x (optional)
Docker Compose: 2.x (optional)

# Installation Steps
1. Clone repository
2. composer install
3. npm install
4. cp .env.example .env
5. php artisan key:generate
6. Configure database in .env
7. php artisan migrate
8. php artisan db:seed
9. npm run dev
10. php artisan serve
```

#### 7.7.2 Production Environment Specifications

**Server Requirements:**
- OS: Ubuntu 22.04 LTS atau yang lebih baru
- CPU: 4 cores minimum
- RAM: 8GB minimum (16GB recommended)
- Storage: 100GB SSD minimum
- Network: 100Mbps bandwidth minimum

**Software Stack:**
- Nginx/Apache Web Server with PHP-FPM
- PHP 8.2+ with extensions:
  - pdo_mysql
  - mbstring
  - xml
  - bcmath
  - gd
  - curl
  - zip
- MySQL 8.0+
- Redis 6+ (for cache and sessions)
- Supervisor (for queue workers)
- Certbot (for SSL/TLS certificates)

#### 7.7.3 Docker Deployment

**docker-compose.yml Example:**

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: orgmanage_app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
      - ./storage:/var/www/storage
    networks:
      - orgmanage
    environment:
      - APP_ENV=${APP_ENV}
      - APP_DEBUG=${APP_DEBUG}

  nginx:
    image: nginx:alpine
    container_name: orgmanage_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./docker/nginx:/etc/nginx/conf.d
      - ./storage/logs/nginx:/var/log/nginx
    networks:
      - orgmanage
    depends_on:
      - app

  mysql:
    image: mysql:8.0
    container_name: orgmanage_mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf
    ports:
      - "3306:3306"
    networks:
      - orgmanage

  redis:
    image: redis:alpine
    container_name: orgmanage_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - orgmanage

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: orgmanage_phpmyadmin
    restart: unless-stopped
    environment:
      PMA_HOST: mysql
      PMA_PORT: 3306
    ports:
      - "8080:80"
    networks:
      - orgmanage
    depends_on:
      - mysql

networks:
  orgmanage:
    driver: bridge

volumes:
  mysql_data:
    driver: local
```

#### 7.7.4 Environment Variables

```env
# Application
APP_NAME="Sistem Manajemen Organisasi"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=orgmanage_db
DB_USERNAME=orgmanage_user
DB_PASSWORD=secure_password_here

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (for password reset, etc.)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Filesystem
FILESYSTEM_DISK=public

# Inertia SSR (Optional)
INERTIA_SSR_ENABLED=false
INERTIA_SSR_URL=http://127.0.0.1:13714

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error

# Broadcasting (for future real-time features)
BROADCAST_DRIVER=log
```

#### 7.7.5 Build and Deployment Process

```bash
# Production Build Steps

1. Git pull latest code
   git pull origin main

2. Install/Update dependencies
   composer install --no-dev --optimize-autoloader
   npm ci

3. Build assets
   npm run build

4. Run migrations
   php artisan migrate --force

5. Clear and cache configs
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache

6. Optimize
   php artisan optimize

7. Set correct permissions
   chmod -R 755 storage
   chmod -R 755 bootstrap/cache
   chown -R www-data:www-data storage
   chown -R www-data:www-data bootstrap/cache

8. Restart services
   sudo systemctl restart php8.2-fpm
   sudo systemctl restart nginx
   sudo supervisorctl restart all

9. Clear application cache (if needed)
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
```

### 7.8 Maintenance and Support

#### 7.8.1 Maintenance Schedule

**Daily:**
- Automated database backup (Laravel Scheduler)
- Log rotation
- System health check
- Queue monitoring

**Weekly:**
- Review error logs (Laravel Telescope/Log Viewer)
- Performance monitoring review
- Security scan
- Test backup restore

**Monthly:**
- Full system backup
- Security patch updates (composer update, npm update)
- Performance optimization review
- Database optimization (analyze tables, index review)

**Quarterly:**
- Major dependency updates (Laravel framework)
- Security audit
- User feedback review and implementation
- Performance benchmarking

#### 7.8.2 Support Levels

**Level 1 - Critical (Response: 1 hour)**
- System completely down
- Data loss or corruption
- Security breach
- Payment system failure

**Level 2 - High (Response: 4 hours)**
- Major feature not working
- Performance severely degraded
- Authentication issues
- Database connection issues

**Level 3 - Medium (Response: 1 business day)**
- Minor feature not working
- UI/UX issues
- Report generation issues
- Non-critical bugs

**Level 4 - Low (Response: 3 business days)**
- Enhancement requests
- Cosmetic issues
- Documentation updates
- Feature requests

#### 7.8.3 Monitoring and Logging

**Tools to implement:**
- Laravel Telescope (development/staging)
- Laravel Horizon (queue monitoring)
- Laravel Log Viewer
- Server monitoring (CPU, RAM, Disk usage)
- Application Performance Monitoring (APM)
- Error tracking (Sentry, Bugsnag, Flare)

### 7.9 Success Criteria

Sistem dianggap berhasil jika memenuhi kriteria berikut:

**SC-001**: All functional requirements (FR-XXX-XXX) terimplement 100%

**SC-002**: All critical non-functional requirements terpenuhi:
- Performance: Page load < 3s
- Security: Pass security audit
- Uptime: > 99% per bulan
- Test coverage: > 80%

**SC-003**: User acceptance testing (UAT) mencapai tingkat kepuasan minimal 85%

**SC-004**: Zero critical bugs di production setelah 1 bulan deployment

**SC-005**: Training documentation lengkap dan user dapat mengoperasikan sistem dengan minimal training

**SC-006**: Backup dan restore berfungsi dengan baik (tested monthly)

**SC-007**: System dapat handle minimal 100 concurrent users tanpa degradasi performance

**SC-008**: Inertia.js integration works seamlessly (no API needed)

**SC-009**: All Laravel Breeze authentication features working properly

**SC-010**: Mobile responsive design berfungsi pada semua major devices

### 7.10 Glossary

| Term | Definition |
|------|------------|
| **SRS** | Software Requirements Specification |
| **RBAC** | Role-Based Access Control - sistem keamanan yang membatasi akses berdasarkan role user |
| **CRUD** | Create, Read, Update, Delete |
| **UI** | User Interface |
| **UX** | User Experience |
| **API** | Application Programming Interface (tidak diperlukan dengan Inertia.js) |
| **PDF** | Portable Document Format |
| **SPA** | Single Page Application - aplikasi web yang tidak reload full page |
| **SSR** | Server-Side Rendering - rendering initial HTML di server |
| **Inertia.js** | Framework untuk membuat SPA klasik menggunakan routing dan controller server-side |
| **Laravel Breeze** | Authentication scaffolding starter kit untuk Laravel |
| **Vite** | Modern build tool dan bundler untuk frontend assets |
| **Soft Delete** | Penghapusan data yang hanya menandai record sebagai deleted tanpa menghapus fisik dari database |
| **Audit Trail** | Catatan kronologis aktivitas sistem untuk keperluan tracking dan security |
| **Session** | Periode waktu interaksi user dengan sistem dari login hingga logout |
| **Artifact** | File atau dokumen yang dihasilkan sistem (laporan, dokumen, dll) |
| **Pagination** | Pembagian data dalam beberapa halaman untuk performa lebih baik |
| **Concurrent Users** | Jumlah user yang mengakses sistem secara bersamaan |
| **Environment** | Konfigurasi deployment (development/staging/production) |
| **Migration** | Script untuk mengubah struktur database secara terkelola |
| **Seeder** | Script untuk mengisi database dengan data initial/dummy |
| **Factory** | Class untuk generate dummy data untuk testing |
| **Policy** | Laravel class untuk define authorization logic |
| **Middleware** | Layer yang dijalankan sebelum request mencapai controller |
| **Resource** | Laravel class untuk transform data sebelum dikirim ke frontend |
| **Form Request** | Laravel class untuk handle validation logic |
| **Eloquent** | Laravel ORM (Object-Relational Mapping) |
| **Queue** | System untuk menjalankan tasks secara asynchronous |
| **Job** | Class yang berisi logic untuk dijalankan via queue |
| **Event** | Laravel event system untuk decouple code |
| **Listener** | Class yang meresponse Laravel events |

### 7.11 Technology Stack Summary

#### 7.11.1 Core Technologies

**Backend:**
- Framework: Laravel 12
- Language: PHP 8.2+
- Authentication: Laravel Breeze
- ORM: Eloquent
- Package Manager: Composer

**Frontend:**
- Framework: Vue.js 3
- Language: JavaScript (ES6+)
- Bridge: Inertia.js
- CSS: Tailwind CSS 3.x
- Build Tool: Vite
- Package Manager: NPM/PNPM

**Database:**
- Primary: MySQL 8.0+
- Cache: Redis 6+

**DevOps:**
- Containerization: Docker
- Orchestration: Docker Compose
- Web Server: Nginx/Apache
- Process Manager: Supervisor

#### 7.11.2 Key Packages

**Backend Packages:**
```json
{
    "laravel/framework": "^12.0",
    "laravel/breeze": "^2.0",
    "inertiajs/inertia-laravel": "^1.0",
    "tightenco/ziggy": "^2.0",
    "barryvdh/laravel-dompdf": "^2.0",
    "maatwebsite/excel": "^3.1",
    "spatie/laravel-permission": "^6.0",
    "intervention/image": "^3.0"
}
```

**Frontend Packages:**
```json
{
    "@inertiajs/vue3": "^1.0",
    "vue": "^3.4",
    "tailwindcss": "^3.4",
    "@tailwindcss/forms": "^0.5",
    "@headlessui/vue": "^1.7",
    "axios": "^1.6",
    "vite": "^5.0"
}
```

### 7.12 Document Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 19 Jan 2026 | Tim Pengembang | Initial SRS document creation |
| 1.1 | 19 Jan 2026 | Tim Pengembang | Updated to use Laravel Breeze Vue Starter Kit |

---

## Approval Signatures

| Role | Name | Signature | Date |
|------|------|-----------|------|
| **Project Sponsor** | | | |
| **Project Manager** | | | |
| **Technical Lead** | | | |
| **QA Lead** | | | |
| **Stakeholder Representative** | | | |

---

**END OF DOCUMENT**

---

*This Software Requirements Specification document is confidential and proprietary. It is intended for use by authorized personnel only.*

*Laravel Breeze Vue Edition - Optimized for modern web development with Inertia.js*
