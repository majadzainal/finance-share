# Finance Share

Finance Share adalah fondasi aplikasi finance ledger/accounting-lite menggunakan Native PHP 8+, MySQL 8+, dan PDO MySQL tanpa framework.

## Struktur

```text
public/              Front controller dan aset publik
app/config/          Konfigurasi aplikasi dan database
app/controllers/     Controller
app/core/            Router, Controller, dan Model dasar
app/models/          Model domain
app/views/           Template view
app/helpers/         Helper global
app/services/        Service layer
app/middleware/      Middleware aplikasi
database/            File SQL atau migrasi manual
storage/uploads/     Upload file
storage/logs/        Log aplikasi
```

## Menjalankan

Pastikan PHP 8+ sudah tersedia, lalu jalankan:

```bash
php -S localhost:8000 -t public
```

Buka `http://localhost:8000` di browser.

## Konfigurasi Database

Ubah konfigurasi koneksi di `app/config/database.php`.

Default:

- Host: `127.0.0.1`
- Port: `3306`
- Database: `finance_share`
- Username: `root`
- Password: kosong

Koneksi database menggunakan PDO dan disiapkan di `app/core/Model.php`.
