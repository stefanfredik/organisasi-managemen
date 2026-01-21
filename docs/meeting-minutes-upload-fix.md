# Perbaikan Form Meeting Minutes - Upload Lampiran

## Perubahan yang Dilakukan

### Masalah

Form **Tambah Notulensi Rapat** tidak memiliki fitur upload lampiran, sedangkan form **Edit** sudah memiliki fitur tersebut. Ini menyebabkan inkonsistensi UX.

### Solusi

Menambahkan fitur upload lampiran di form **Create** (Tambah) agar konsisten dengan form **Edit**.

---

## File yang Dimodifikasi

### 1. **Create.vue** - Frontend

**File**: `resources/js/Pages/MeetingMinutes/Create.vue`

**Perubahan**:

- ✅ Menambahkan `files: []` ke form data
- ✅ Menambahkan `selectedFiles` ref untuk tracking file yang dipilih
- ✅ Menambahkan fungsi `handleFileChange()` untuk menangani pemilihan file
- ✅ Menambahkan fungsi `removeFile()` untuk menghapus file dari list
- ✅ Menambahkan fungsi `formatFileSize()` untuk format ukuran file
- ✅ Menambahkan UI section "Lampiran (Opsional)" dengan:
  - File input dengan multiple selection
  - Preview list file yang akan diunggah
  - Tombol hapus untuk setiap file
  - Informasi ukuran file
  - Format yang didukung

### 2. **MeetingMinuteController.php** - Backend

**File**: `app/Http/Controllers/MeetingMinuteController.php`

**Perubahan di method `store()`**:

```php
// Handle file uploads
if ($request->hasFile('files')) {
    foreach ($request->file('files') as $file) {
        $path = $file->store('meeting-minutes/attachments', 'public');
        MeetingMinuteAttachment::create([
            'meeting_minute_id' => $minute->id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }
}
```

### 3. **StoreMeetingMinuteRequest.php** - Validation

**File**: `app/Http/Requests/StoreMeetingMinuteRequest.php`

**Perubahan**:

```php
'files' => ['nullable', 'array'],
'files.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif', 'max:10240'], // 10MB
```

---

## Fitur yang Ditambahkan

### 1. **Multiple File Upload**

- User dapat memilih multiple file sekaligus
- Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF
- Maksimal ukuran per file: 10MB

### 2. **File Preview**

- Menampilkan list file yang akan diunggah
- Menampilkan nama file dan ukuran
- Icon file untuk visual feedback

### 3. **Remove File**

- User dapat menghapus file dari list sebelum submit
- Tombol X untuk setiap file

### 4. **Validation**

- Validasi tipe file di backend
- Validasi ukuran file (max 10MB)
- Error message jika validasi gagal

---

## Konsistensi dengan Form Edit

Sekarang kedua form (Create dan Edit) memiliki fitur yang sama:

| Fitur           | Create | Edit |
| --------------- | ------ | ---- |
| Upload Lampiran | ✅     | ✅   |
| Multiple Files  | ✅     | ✅   |
| File Preview    | ✅     | ✅   |
| Remove File     | ✅     | ✅   |
| Format Support  | ✅     | ✅   |
| Size Limit      | ✅     | ✅   |

---

## Testing

Untuk menguji fitur ini:

1. ✅ Buka form **Tambah Notulensi Rapat**
2. ✅ Isi form (Tanggal, Agenda, Peserta, Hasil Rapat)
3. ✅ Upload satu atau lebih file lampiran
4. ✅ Verifikasi file muncul di preview list
5. ✅ Coba hapus file dari list
6. ✅ Submit form
7. ✅ Verifikasi file tersimpan di database dan storage
8. ✅ Buka detail/edit notulensi untuk melihat lampiran

---

## Status: SELESAI ✅

Form Create dan Edit sekarang konsisten dan memiliki fitur upload lampiran yang sama!
