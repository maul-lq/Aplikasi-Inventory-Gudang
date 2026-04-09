# Aplikasi Inventory Gudang

Repo ini adalah aplikasi inventory gudang berbasis CodeIgniter 4.

## Status Perbaikan

Repo hasil clone ini sudah disesuaikan agar bisa jalan di:

- PHP 8.2
- CodeIgniter 4.7
- MySQL 8 pada `localhost:3306`

Perbaikan utama yang sudah diterapkan:

- upgrade dependency Composer yang kompatibel dengan PHP 8.2
- sinkronisasi file bootstrap dan config lama ke struktur CI4 modern
- sinkronisasi setup database lokal ke MySQL Server aktif
- validasi login awal dan akses dashboard

## Requirement

- PHP 8.2+
- Composer 2
- MySQL Server aktif di port default `3306`
- Extension PHP yang aktif:
  - `mysqli`
  - `intl`
  - `zip`

## Setup Lokal

1. Install dependency:

```bash
composer install --no-interaction --prefer-dist
```

2. Pastikan file `.env` berisi konfigurasi berikut:

```dotenv
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = 127.0.0.1
database.default.database = dbgudang
database.default.username = root
database.default.password = admin
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306

database.tests.hostname = 127.0.0.1
database.tests.database = dbgudang_tests
database.tests.username = root
database.tests.password = admin
database.tests.DBDriver = MySQLi
database.tests.DBPrefix = db_
database.tests.port = 3306
```

3. Buat database aplikasi dan database test:

```powershell
& 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe' -uroot -padmin -e "CREATE DATABASE IF NOT EXISTS dbgudang CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
& 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe' -uroot -padmin -e "CREATE DATABASE IF NOT EXISTS dbgudang_tests CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
```

4. Jika `dbgudang` belum terisi, import dump:

```powershell
& 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe' -uroot -padmin dbgudang -e "SOURCE C:/Users/Rizlrad Fz/Koding/Codeigneter 4/Aplikasi-Inventory-Gudang/dbgudang.sql"
```

5. Jalankan server development:

```bash
php spark serve --host=127.0.0.1 --port=8080
```

6. Buka:

```text
http://localhost:8080/
```

## Login Awal

- Username: `admin`
- Password: `admin`

User seed lain dari dump database:

- `gudang`
- `kasir`
- `kasir2` (nonaktif)

## Verifikasi Dasar

Perintah yang sudah tervalidasi:

```bash
php spark
php spark routes
vendor/bin/phpunit
```

Smoke test yang sudah lolos:

- `GET /` mengembalikan `200`
- login AJAX `admin/admin` sukses
- `GET /main/index` setelah login mengembalikan `200`

## Catatan

- Binary `C:\xampp\mysql\bin\mysql.exe` pada mesin ini gagal terhadap service MySQL aktif karena masalah plugin auth `caching_sha2_password`, jadi gunakan binary MySQL milik service yang aktif.
- File `dbgudang.sql` sudah berisi tabel dan seed data aplikasi.
