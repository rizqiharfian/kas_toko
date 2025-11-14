Panduan singkat menjalankan aplikasi (Laragon):

1) Tempatkan folder 'kasapp' di C:\laragon\www\ (sudah berada di /mnt/data for download).
2) Buka Laragon -> Terminal atau gunakan File Explorer.
3) Buat database:
   - Buka http://localhost/phpmyadmin atau mysql CLI via Laragon.
   - Jalankan file create_db.sql atau copy & paste isinya.
4) Jalankan create_admin.php sekali untuk membuat admin default (username: admin, password: admin123).
   - Setelah membuat admin, hapus atau amankan create_admin.php.
5) Akses aplikasi:
   - http://kasapp.test  (Laragon biasanya mengaktifkan domain .test)
   - atau http://localhost/kasapp
6) Login: admin / admin123 (ubah password segera).

Catatan:
- Edit koneksi database di koneksi.php jika perlu.
- File export_csv.php dan print.php sudah tersedia.
