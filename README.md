# KasirKel (Sistem Kasir / POS)

Repositori aplikasi KasirKel dengan struktur terpisah antara Backend (API) dan Frontend (SPA).

## Struktur Repositori

```
kasirkel/
├── backend/    # REST API berbasis Laravel 13 & PHP
└── frontend/   # Client SPA berbasis Vue 3, Vite, Tailwind CSS & TypeScript
```

## Cara Menjalankan

### 1. Backend (Laravel API)
```bash
cd backend
composer install
cp .env.example .env   # sesuaikan koneksi database (MySQL pos_sekolah)
php artisan key:generate
php artisan serve
```
Server berjalan di: `http://127.0.0.1:8000`

### 2. Frontend (Vue 3 SPA)
```bash
cd frontend
npm install
npm run dev
```
Aplikasi berjalan di: `http://localhost:5173`
