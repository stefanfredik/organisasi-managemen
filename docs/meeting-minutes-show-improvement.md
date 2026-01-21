# Perbaikan Layout Detail Notulensi - Fokus ke Hasil Notulensi

## Perubahan Layout

### Masalah

Tampilan sebelumnya memberikan bobot yang sama untuk semua informasi, sehingga hasil notulensi (konten utama) tidak menonjol.

### Solusi

Menggunakan **2-column layout** dengan fokus utama pada hasil notulensi.

---

## Layout Baru

### Desktop Layout (≥1024px)

```
┌─────────────────────────────────────────────────────────────┐
│                         HEADER                               │
│  [← Back] Notulensi Rapat                      [Edit Button] │
└─────────────────────────────────────────────────────────────┘

┌──────────────────────────────┬──────────────────────────────┐
│  MAIN CONTENT (2/3 width)    │  SIDEBAR (1/3 width)         │
│                              │                              │
│  ┌────────────────────────┐  │  ┌────────────────────────┐  │
│  │ AGENDA & TANGGAL       │  │  │ INFORMASI RAPAT        │  │
│  │ [Status Badge]         │  │  │ - Dibuat oleh          │  │
│  └────────────────────────┘  │  │ - Peserta (scrollable) │  │
│                              │  └────────────────────────┘  │
│  ┌────────────────────────┐  │                              │
│  │ HASIL NOTULENSI        │  │  ┌────────────────────────┐  │
│  │ ==================     │  │  │ LAMPIRAN               │  │
│  │                        │  │  │ [File 1] [Download]    │  │
│  │ (KONTEN UTAMA)         │  │  │ [File 2] [Download]    │  │
│  │ - Larger font          │  │  │ [File 3] [Download]    │  │
│  │ - More spacing         │  │  └────────────────────────┘  │
│  │ - Better readability   │  │                              │
│  │                        │  │  (Sticky sidebar)            │
│  └────────────────────────┘  │                              │
└──────────────────────────────┴──────────────────────────────┘
```

### Mobile Layout (<1024px)

```
┌─────────────────────────┐
│ HEADER                  │
└─────────────────────────┘
│ AGENDA & TANGGAL        │
│ [Status Badge]          │
├─────────────────────────┤
│ HASIL NOTULENSI         │
│ (Full width)            │
│                         │
│ (KONTEN UTAMA)          │
├─────────────────────────┤
│ INFORMASI RAPAT         │
│ - Dibuat oleh           │
│ - Peserta               │
├─────────────────────────┤
│ LAMPIRAN                │
│ [File 1] [Download]     │
│ [File 2] [Download]     │
└─────────────────────────┘
```

---

## Fitur Utama

### 1. **Main Content Area (2/3 width)** ✅

**Fokus: Hasil Notulensi**

- ✅ **Header Card**:
  - Agenda rapat (h1, 3xl, bold)
  - Tanggal dengan icon
  - Status badge "Selesai" (hijau)

- ✅ **Hasil Notulensi Card**:
  - Accent bar (indigo) di kiri
  - Heading "Hasil Notulensi Rapat" (2xl, bold)
  - Konten dengan `prose-lg` untuk readability
  - Font lebih besar (1.125rem)
  - Line height lebih lebar (1.75)
  - Spacing yang generous
  - Empty state dengan icon

### 2. **Sidebar (1/3 width)** ✅

**Informasi Pendukung**

#### A. **Info Rapat Card** (Sticky)

- ✅ Dibuat oleh (dengan icon user)
- ✅ Peserta rapat:
  - Badge jumlah peserta
  - List dengan checkmark icon
  - Scrollable (max-height: 12rem)
  - Custom scrollbar styling
  - Truncate nama panjang

#### B. **Lampiran Card**

- ✅ Badge jumlah file
- ✅ File cards yang clickable
- ✅ Hover effect (bg-gray-100)
- ✅ Icon berdasarkan tipe file
- ✅ Download icon di kanan
- ✅ Ukuran file
- ✅ Empty state dengan icon

---

## Improvements

### Visual Hierarchy

```
1. HASIL NOTULENSI (Paling Prominent)
   - 2/3 width
   - Larger font (prose-lg)
   - More spacing
   - Accent bar

2. Agenda & Tanggal (Secondary)
   - Header card
   - Large title (3xl)
   - Status badge

3. Info & Lampiran (Supporting)
   - Sidebar 1/3 width
   - Compact layout
   - Sticky positioning
```

### Typography

- **Agenda**: `text-3xl font-bold` (48px)
- **Section Heading**: `text-2xl font-bold` (32px)
- **Hasil Notulensi**: `prose-lg` (18px, line-height 1.75)
- **Sidebar Heading**: `text-lg font-semibold` (18px)
- **Body Text**: `text-sm` (14px)

### Spacing

- **Main Content**: `p-8` (2rem padding)
- **Sidebar**: `p-6` (1.5rem padding)
- **Gap between columns**: `gap-6` (1.5rem)
- **Section spacing**: `space-y-6` (1.5rem)

### Colors

- **Accent**: Indigo-600 (sidebar accent bar)
- **Status Badge**: Green-100/800 (success)
- **File Icons**: Type-specific colors
- **Hover**: Gray-100 (subtle)

---

## Responsive Behavior

### Desktop (≥1024px)

- 2-column layout (2/3 + 1/3)
- Sidebar sticky
- Full width content

### Tablet & Mobile (<1024px)

- Single column stack
- Sidebar below main content
- Full width for all sections

---

## User Experience

### Before ❌

- Semua informasi sama pentingnya
- Hasil notulensi tidak menonjol
- Banyak scrolling horizontal
- Sulit fokus ke konten utama

### After ✅

- **Hasil notulensi jadi fokus utama**
- Layout 2 kolom yang jelas
- Info pendukung di sidebar
- Sticky sidebar untuk akses mudah
- Better readability dengan prose-lg
- Lampiran mudah diakses
- Clean & organized

---

## Technical Details

### Sticky Sidebar

```vue
<div class="sticky top-6">
  <!-- Sidebar content -->
</div>
```

### Custom Scrollbar

```css
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}
```

### Prose Styling

```vue
<div class="prose prose-lg max-w-none">
  <!-- Rich text content -->
</div>
```

### Grid Layout

```vue
<div class="lg:grid lg:grid-cols-3 lg:gap-6">
  <div class="lg:col-span-2">Main</div>
  <div class="lg:col-span-1">Sidebar</div>
</div>
```

---

## Status: SELESAI ✅

Layout baru dengan fokus ke hasil notulensi sudah selesai! Konten utama sekarang lebih prominent dan mudah dibaca.
