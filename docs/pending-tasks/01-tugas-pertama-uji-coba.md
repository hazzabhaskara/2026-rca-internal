# Tugas 01: Pembuatan fitur Export Laporan Aktivitas Karyawan ke PDF

**Deskripsi Tugas:**
Buat fitur yang memungkinkan pengguna untuk mengekspor Laporan Aktivitas Karyawan ke dalam format PDF. Fitur ini dapat diakses melalui antarmuka web, di mana sistem akan menarik log aktivitas karyawan dan mengonversinya menjadi dokumen yang rapi.

**Kriteria Penerimaan (Acceptance Criteria):**
1. Terdapat tombol/endpoint untuk menghasilkan ekspor PDF Aktivitas Karyawan.
2. Memanfaatkan library PDF yang terintegrasi di Laravel (misal: barryvdh/laravel-dompdf atau snappy).
3. Tabel pada PDF harus dapat dibaca dengan jelas, menyertakan Nama Karyawan, Tanggal, Jenis Aktivitas, dan Detail.
4. Setiap operasi *Export* harus memicu pencatatan aktivitas CRUD "Export PDF" melalui model `ActivityLog`.
5. Implementasikan validasi *Request* yang sesuai jika ada parameter filter (misal: filter rentang tanggal).

**Aturan Eksekusi:**
- Pastikan logika pembuatan PDF dipisahkan dalam *Service Pattern* jika melebihi batas kompleksitas.
- Lakukan pengecekan dependensi library PDF pada `composer.json` terlebih dahulu.
