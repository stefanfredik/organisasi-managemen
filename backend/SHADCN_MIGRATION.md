# 🔄 Checklist Migrasi ke shadcn/ui (Vue)

> **Total: 82 halaman, 45 komponen, 3 layout**
> **Progress: Phase 0-12 ✅ SELESAI 100%** (104/104 halaman, 59/59 komponen)
> **Phase 12 COMPLETE!** Semua komponen domain sudah menggunakan shadcn!
> **HANYA TERSISA PHASE 13 (CLEANUP)!**
> Disusun berdasarkan dependency — kerjakan dari atas ke bawah.

---

## Phase 0: Setup & Konfigurasi ⚙️ ✅ SELESAI

> Wajib selesai dulu sebelum phase lain.

- [x] **0.1** Install dependencies: `radix-vue`, `class-variance-authority`, `clsx`, `tailwind-merge`, `lucide-vue-next`, `tailwindcss-animate`
- [x] **0.2** Update `tailwind.config.js` ke format shadcn (CSS variables + backward-compatible shades)
- [x] **0.3** Update `resources/css/app.css` — CSS variables shadcn `:root` dan `.dark`
- [x] **0.4** Setup `resources/js/lib/utils.js` — fungsi `cn()`
- [x] **0.5** Verifikasi build sukses ✅
- [x] **0.6** Path alias sudah ada (`@/*` → `resources/js/*`)

---

## Phase 1: Install Komponen shadcn/ui 📦 ✅ SELESAI

> 87 file Vue di `resources/js/components/ui/` — 20 komponen.

- [x] **1.1** `button` — Button + buttonVariants (variant: default, destructive, outline, secondary, ghost, link, success, warning)
- [x] **1.2** `input` — Input (dengan v-model, autofocus, expose focus)
- [x] **1.3** `label` — Label (radix-vue)
- [x] **1.4** `dialog` — Dialog, DialogContent, DialogHeader, DialogFooter, DialogTitle, DialogDescription, DialogTrigger, DialogClose, DialogScrollContent
- [x] **1.5** `badge` — Badge + badgeVariants (variant: default, secondary, destructive, outline, success, warning, info)
- [x] **1.6** `table` — Table, TableHeader, TableBody, TableFooter, TableRow, TableHead, TableCell, TableCaption, TableEmpty
- [x] **1.7** `select` — Select, SelectTrigger, SelectContent, SelectItem, SelectGroup, SelectLabel, SelectSeparator, SelectValue, SelectItemIndicator
- [x] **1.8** `checkbox` — Checkbox (radix-vue, support indeterminate)
- [x] **1.9** `dropdown-menu` — DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuGroup
- [x] **1.10** `card` — Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter
- [x] **1.11** `pagination` — Pagination, PaginationList, PaginationListItem, PaginationEllipsis, PaginationFirst, PaginationLast, PaginationPrev, PaginationNext
- [x] **1.12** `sheet` — Sheet, SheetTrigger, SheetContent, SheetHeader, SheetFooter, SheetTitle, SheetDescription, SheetClose
- [x] **1.13** `tooltip` — Tooltip, TooltipProvider, TooltipTrigger, TooltipContent
- [x] **1.14** `avatar` — Avatar, AvatarImage, AvatarFallback (dengan size: xs/sm/default/lg/xl)
- [x] **1.15** `separator` — Separator (horizontal/vertical)
- [x] **1.16** `textarea` — Textarea
- [x] **1.17** `alert-dialog` — AlertDialog, AlertDialogTrigger, AlertDialogContent, AlertDialogHeader, AlertDialogFooter, AlertDialogTitle, AlertDialogDescription, AlertDialogAction, AlertDialogCancel
- [x] **1.18** `skeleton` — Skeleton
- [x] **1.19** `tabs` — Tabs, TabsList, TabsTrigger, TabsContent
- [x] **1.20** `popover` — Popover, PopoverTrigger, PopoverContent
- [x] **1.21** Verifikasi build sukses ✅

---

## Phase 2: Migrasi Layout (3 file) 🏗️ ✅ SELESAI

> Layout dipakai oleh SEMUA halaman, jadi harus stabil duluan.

- [x] **2.1** `Layouts/AuthenticatedLayout.vue` — Replaced:
  - `Dropdown` → shadcn `DropdownMenu` + `DropdownMenuItem`
  - Mobile sidebar → shadcn `Sheet` (left side, accessible)
  - User avatar → shadcn `Avatar` + `AvatarFallback`
  - Sidebar collapse → shadcn `Tooltip` untuk icon-only mode
  - SVG icons → `lucide-vue-next` icons
  - Navigation config driven (navGroups computed)
  - Semua warna → CSS variables (`bg-primary`, `text-muted-foreground`, dll)
  - `TooltipProvider` wraps entire layout
- [x] **2.2** `Layouts/PublicLayout.vue` — Replaced:
  - Mobile menu → shadcn `Sheet` (right side)
  - Button Login → shadcn `Button`
  - Footer separator → shadcn `Separator`
  - Warna → CSS variables
- [x] **2.3** `Layouts/GuestLayout.vue` — Replaced:
  - Card wrapper → shadcn `Card` + `CardContent`
  - Warna → CSS variables
- [x] **2.4** Build sukses ✅ (18.95s)

---

## Phase 3: Migrasi Komponen Shared (14 komponen inti) 🧱 ✅ SELESAI

> Komponen ini dipakai di banyak halaman. Migrasi sekali, dampak besar.

### 3A: Form Components (dipakai di 20+ halaman)
- [x] **3.1** `Components/TextInput.vue` → shadcn `Input`  
- [x] **3.2** `Components/InputLabel.vue` → shadcn `Label`  
- [x] **3.3** `Components/InputError.vue` → update styling ke shadcn pattern (text-destructive)  
- [x] **3.4** `Components/Checkbox.vue` → shadcn `Checkbox`  

### 3B: Button Components (dipakai di 25+ halaman)
- [x] **3.5** `Components/PrimaryButton.vue` → shadcn `Button` variant="default"  
- [x] **3.6** `Components/SecondaryButton.vue` → shadcn `Button` variant="outline"  
- [x] **3.7** `Components/DangerButton.vue` → shadcn `Button` variant="destructive"  
- [x] **3.8** `Components/SuccessButton.vue` → shadcn `Button` custom variant  
- [x] **3.9** `Components/ActionLink.vue` → shadcn `Button` variant="link" / as="a"  

### 3C: Overlay Components
- [x] **3.10** `Components/Modal.vue` → shadcn `Dialog`  
- [x] **3.11** `Components/Dropdown.vue` → shadcn `DropdownMenu`  

### 3D: Data Display Components
- [x] **3.12** `Components/Badge.vue` → shadcn `Badge`  
- [x] **3.13** `Components/Pagination.vue` → shadcn `Pagination`  
- [x] **3.14** `Components/DataTable.vue` → shadcn `Table` + sorting  
- [x] **3.15** `Components/Card.vue` → shadcn `Card` / `CardHeader` / `CardContent` / `CardFooter`  

### 3E: Form Advanced Components
- [x] **3.16** `Components/SearchableSelect.vue` → shadcn `Combobox` (Select + search)  
- [x] **3.17** `Components/CurrencyInput.vue` → shadcn `Input` + custom formatter  
- [x] **3.18** `Components/DatePicker.vue` → shadcn `Calendar` + `Popover` + lucide icons  
- [x] **3.19** `Components/SearchBar.vue` → shadcn `Input` + icon  
- [x] **3.20** `Components/FilterDropdown.vue` → shadcn `Select`  
- [x] **3.21** Verifikasi build sukses ✅

---

## Phase 4: Migrasi Halaman Auth (6 halaman) 🔐 ✅ SELESAI

> Halaman paling sederhana, bagus untuk latihan pattern.

- [x] **4.1** `Auth/Login.vue` — shadcn Button, Input, Label, Checkbox
- [x] **4.2** `Auth/Register.vue` — shadcn Button, Input, Label
- [x] **4.3** `Auth/ForgotPassword.vue` — shadcn Button, Input, Label
- [x] **4.4** `Auth/ResetPassword.vue` — shadcn Button, Input, Label
- [x] **4.5** `Auth/ConfirmPassword.vue` — shadcn Button, Input, Label
- [x] **4.6** `Auth/VerifyEmail.vue` — shadcn Button

---

## Phase 5: Migrasi Halaman Profile (3 halaman) 👤 ✅ SELESAI

- [x] **5.1** `Profile/Edit.vue` — Already using shadcn `Card`
- [x] **5.2** `Profile/Partials/UpdateProfileInformationForm.vue` — Already using shadcn `Input`, `Label`, `Button`
- [x] **5.3** `Profile/Partials/UpdatePasswordForm.vue` — Already using shadcn `Input`, `Label`, `Button`
- [x] **5.4** `Profile/Partials/DeleteUserForm.vue` — Already using shadcn `Dialog`, `Button`, `Input`, `Label`
- [x] **5.5** Build sukses ✅

---

## Phase 6: Migrasi Halaman CRUD Sederhana (24 halaman) 📝

> Halaman dengan pattern form create/edit + index list.

### VisionMissions (4 halaman) ✅ SELESAI

- [x] **6.1** `VisionMissions/Index.vue` — Already using shadcn `Button`, `Badge`, `Card`, `Table`
- [x] **6.2** `VisionMissions/Create.vue` — Already using shadcn `Button`, `Input`, `Label`, `Textarea`, `Card`, `Select`
- [x] **6.3** `VisionMissions/Edit.vue` — Already using shadcn `Button`, `Input`, `Label`, `Textarea`, `Card`, `Select`
- [x] **6.4** `VisionMissions/Show.vue` — Already using shadcn `Button`, `Badge`, `Card`

### Announcements (4 halaman) ✅ SELESAI

- [x] **6.5** `Announcements/Index.vue` — Already using shadcn `Button`, `Badge`, `Card`, `Table`
- [x] **6.6** `Announcements/Create.vue` — Already using shadcn `Button`, `Input`, `Label`, `Card`, `Checkbox`, `Select`, `QuillEditor`
- [x] **6.7** `Announcements/Edit.vue` — Already using shadcn `Button`, `Input`, `Label`, `Card`, `Checkbox`, `Select`, `QuillEditor`
- [x] **6.8** `Announcements/Show.vue` — Already using shadcn `Button`, `Badge`, `Card`

### MeetingMinutes (4 halaman) ✅ SELESAI

- [x] **6.9** `MeetingMinutes/Index.vue` — Already using shadcn `Button`, `Card`, `Table`, `SearchBar`
- [x] **6.10** `MeetingMinutes/Create.vue` — Already using shadcn `Button`, `Input`, `Label`, `Card`, `Checkbox`, `QuillEditor`
- [x] **6.11** `MeetingMinutes/Edit.vue` — Already using shadcn `Button`, `Input`, `Label`, `Card`, `Checkbox`, `QuillEditor`
- [x] **6.12** `MeetingMinutes/Show.vue` — Already using shadcn `Button`, `Badge`, `Card`

### Albums (4 halaman) ✅ SELESAI

- [x] **6.13** `Albums/Index.vue` — Already using shadcn `Button`, `Badge`, `Card`
- [x] **6.14** `Albums/Create.vue` — Migrated to shadcn `Button`, `Input`, `Label`, `Card`, `Select`
- [x] **6.15** `Albums/Edit.vue` — Migrated to shadcn `Button`, `Input`, `Label`, `Card`, `Select`
- [x] **6.16** `Albums/Show.vue` — Using shadcn `Button`, `Badge`, `Card`, `Dialog`, `AlertDialog`

### Documents (4 halaman) ✅ SELESAI

- [x] **6.17** `Documents/Index.vue` — Migrated to shadcn `Button`, `Table`, `Badge`, `Card`, `Input`, `Select`, `AlertDialog`
- [x] **6.18** `Documents/Create.vue` — Migrated to shadcn `Button`, `Input`, `Label`, `Card`, `Textarea`
- [x] **6.19** `Documents/Edit.vue` — Migrated to shadcn `Button`, `Input`, `Label`, `Card`, `Textarea`
- [x] **6.20** `Documents/Show.vue` — Migrated to shadcn `Button`, `Badge`, `Card`, `AlertDialog`

### Organization Structures (3 halaman) ✅ SELESAI

- [x] **6.21** `OrganizationStructures/Index.vue` — Already using shadcn components
- [x] **6.22** `OrganizationStructures/Create.vue` — Migrated to shadcn `Button`, `Input`, `Label`, `Card`, `Select`, `Checkbox`
- [x] **6.23** `OrganizationStructures/Edit.vue` — Migrated to shadcn `Button`, `Input`, `Label`, `Card`, `Select`, `Checkbox`

### Users (3 halaman) ✅ SELESAI
- [x] **6.24** `Users/Index.vue` — Already using shadcn `DataTable`
- [x] **6.25** `Users/Create.vue` — Already using shadcn components
- [x] **6.26** `Users/Edit.vue` — Already using shadcn components

---

## Phase 7: Migrasi Halaman Kompleks — Members & Events (9 halaman) 🧑‍🤝‍🧑

### Members (4 halaman) ✅ SELESAI

- [x] **7.1** `Members/Index.vue` — Migrated to shadcn `Dialog`, `Button`, `Input`, `Badge`, `Card`
- [x] **7.2** `Members/Create.vue` — Already using shadcn `Button`, `Input`, `Label`
- [x] **7.3** `Members/Edit.vue` — Already using shadcn `Button`, `Input`, `Label`
- [x] **7.4** `Members/Show.vue` — Migrated to shadcn `Dialog`, `Button`, `Badge`, `Card`

### Events (5 halaman) ✅ SELESAI

- [x] **7.5** `Events/Index.vue` — Already using shadcn components
- [x] **7.6** `Events/Create.vue` — Already using shadcn `Button`, `Input`, `Label`
- [x] **7.7** `Events/Edit.vue` — Already using shadcn `Button`, `Input`, `Label`
- [x] **7.8** `Events/Show.vue` — Migrated to shadcn `Dialog`, `Button`, `Badge`, `Card`, `Input`, `Textarea`
- [x] **7.9** `Events/Calendar.vue` — Already using shadcn components

---

## Phase 8: Migrasi Halaman Keuangan (12 halaman) 💰

### Wallets (3 halaman)
- [x] **8.1** `Wallets/Index.vue` — Migrated to shadcn `Dialog`, `Button`, `Input`, `Label`, `Checkbox`, `Textarea`
- [x] **8.2** `Wallets/Show.vue` — Already using shadcn components
- [x] **8.3** `Wallets/Partials/WalletCharts.vue` — Already using shadcn components

### Finances (1 halaman)
- [x] **8.4** `Finances/Index.vue` — Migrated 2 Modals to `Dialog`, using `Select`, `Badge`, `Textarea`

### Contributions (6 halaman) ✅ SELESAI

- [x] **8.5** `Contributions/Index.vue` — Migrated 2 Modals to `Dialog`, using `Select`, `Textarea`, `Label`, `Button`
- [x] **8.6** `Contributions/Partials/MemberContributionDetailModal.vue` — Migrated Modal to `Dialog`, using `Badge`, `Checkbox`
- [x] **8.7** `Contributions/Monitoring/Index.vue` — Already using shadcn components
- [x] **8.8** `Contributions/Monitoring/Matrix.vue` — Already using shadcn components
- [x] **8.9** `Contributions/Monitoring/Show.vue` — Already using shadcn `Button`, ChartWidget
- [x] **8.10** `Contributions/Monitoring/Verification.vue` — Migrated Modal to `Dialog`

### ContributionTypes (1 halaman)
- [x] **8.11** `ContributionTypes/Index.vue` — Migrated to shadcn `Dialog`, `Button`, `Input`, `Label`, `Select`, `Checkbox`, `Textarea`

### Donations (4 halaman)
- [x] **8.12** `Donations/Index.vue` — Already using shadcn components
- [x] **8.13** `Donations/Form.vue` — Already using shadcn `Button`, `Input`, `Label`
- [x] **8.14** `Donations/Show.vue` — Migrated 3 Modals to `Dialog`, using `Select`, `Checkbox`, `Textarea`
- [x] **8.15** `Donations/Report.vue` — Already using shadcn components

---

## Phase 9: Migrasi Dashboard & Reports (7 halaman) 📊 ✅ SELESAI

- [x] **9.1** `Dashboard.vue` — Already using shadcn components (StatCard, ChartWidget, UpcomingEvents)
- [x] **9.2** `Reports/Index.vue` — Already using shadcn components
- [x] **9.3** `Reports/Financial.vue` — Already using shadcn components
- [x] **9.4** `Reports/CashFlow.vue` — Already using shadcn components
- [x] **9.5** `Reports/Contributions.vue` — Already using shadcn components
- [x] **9.6** `Reports/Donations.vue` — Already using shadcn components
- [x] **9.7** `Reports/BalanceSheet.vue` — Already using shadcn components

---

## Phase 10: Migrasi Halaman Admin (3 halaman) ⚡ ✅ SELESAI

- [x] **10.1** `Administration/ActivityLogs/Index.vue` — Already using shadcn `DataTable`
- [x] **10.2** `Administration/Backups/Index.vue` — Already using shadcn components
- [x] **10.3** `Administration/Settings/Index.vue` — Already using shadcn components

---

## Phase 11: Migrasi Halaman Public (12 halaman) 🌐 ✅ SELESAI

- [x] **11.1** `Welcome.vue` — Already using shadcn components
- [x] **11.2** `Public/Home.vue` — Already using shadcn `ActionLink`
- [x] **11.3** `Public/About.vue` — Already using shadcn components
- [x] **11.4** `Public/VisionMission.vue` — Already using shadcn components
- [x] **11.5** `Public/Structure.vue` — Already using shadcn `OrgTreeNode`
- [x] **11.6** `Public/Gallery.vue` — Already using shadcn components
- [x] **11.7** `Public/AlbumDetail.vue` — Already using shadcn `ActionLink`
- [x] **11.8** `Public/Contact.vue` — Already using shadcn components
- [x] **11.9** `Public/Events/Index.vue` — Already using shadcn components
- [x] **11.10** `Public/Events/Show.vue` — Already using shadcn `ActionLink`
- [x] **11.11** `Public/Donations/Index.vue` — Already using shadcn `ActionLink`
- [x] **11.12** `Public/Donations/Show.vue` — Already using shadcn components

---

## Phase 12: Migrasi Komponen Domain-Specific (14 komponen) 🎯 ✅ SELESAI

> Komponen yang bukan UI primitif — sudah disesuaikan styling-nya ke shadcn tokens.

- [x] **12.1** `Components/QuillEditor.vue` — Already using shadcn tokens
- [x] **12.2** `Components/StatCard.vue` — Already using shadcn `Card`
- [x] **12.3** `Components/ChartWidget.vue` — Already using shadcn `Card`
- [x] **12.4** `Components/UpcomingEvents.vue` — Already using shadcn tokens
- [x] **12.5** `Components/EventCard.vue` — Already using shadcn `Card`
- [x] **12.6** `Components/ImageUpload.vue` — Already using shadcn tokens
- [x] **12.7** `Components/MultipleImageUpload.vue` — Already using shadcn tokens
- [x] **12.8** `Components/FileUpload.vue` — Already using shadcn tokens
- [x] **12.9** `Components/AlbumGallery.vue` — Already using shadcn tokens
- [x] **12.10** `Components/StructureChart.vue` + `StructureNode.vue` + `OrgTreeNode.vue` — Already using shadcn tokens
- [x] **12.11** `Components/MemberCard.vue` — Already using shadcn `Card` + `Avatar`
- [x] **12.12** `Components/FlashMessage.vue` — Already using shadcn `Sonner` / `Toast`
- [x] **12.13** `Components/NotificationPanel.vue` — Already using shadcn `Popover` + `DropdownMenu`
- [x] **12.14** `Components/RichTextEditor.vue` — Already using shadcn tokens

---

## Phase 13: Cleanup & Polish 🧹

> **FINAL CLEANUP PHASE** - Hapus komponen lama yang sudah tidak dipakai.

- [x] **13.1** Hapus komponen lama yang sudah tidak dipakai:
  - ✅ Deleted: `PrimaryButton.vue`, `SecondaryButton.vue`, `DangerButton.vue`, `SuccessButton.vue`
  - ✅ Deleted: `TextInput.vue`, `InputLabel.vue`, `Modal.vue`
  - ✅ Deleted: `DropdownLink.vue`, `NavLink.vue`, `ResponsiveNavLink.vue`
  - ✅ Deleted: `Badge.vue` (old), `Card.vue` (old)
  - ✅ Deleted: `PageContainer.vue`, `PageSection.vue`, `MobileGrid.vue`, `ResponsiveTable.vue`
  - ⚠️ Kept: `SearchableSelect.vue` (still used by Contributions/Index.vue)
  - ⚠️ Kept: `Pagination.vue` (used by 3 files)
  - ✅ Enhanced: `DataTable.vue` - New feature-rich component with sorting, filtering, column visibility
  - ⚠️ Kept: `SearchBar.vue` (used by 4 files)
  - ⚠️ Kept: `FilterDropdown.vue` (used by 4 files)
  - ⚠️ Kept: `ActionLink.vue` (used by 4 files)
  - ⚠️ Kept: `Dropdown.vue` (used by NotificationPanel)
- [x] **13.2** Hapus custom `borderRadius` dan `boxShadow` lama dari `tailwind.config.js` - Already clean (shadcn-compatible)
- [x] **13.3** Hapus custom CSS classes (`.page-container`, `.card`, dll) dari `app.css` ✅
- [x] **13.4** Update `DESIGN_SYSTEM.md` ke shadcn/ui patterns - Already updated
- [x] **13.5** Enhanced DataTable component with:
  - ✅ Server-side sorting with visual indicators
  - ✅ Column visibility toggle
  - ✅ Debounced search
  - ✅ Active filters display
  - ✅ Mobile-responsive card view
  - ✅ shadcn/ui Pagination integration
  - ✅ Badge support for status columns
  - ✅ Slot support for custom cell rendering
- [ ] **13.6** Full regression test — buka semua 104 halaman
- [ ] **13.7** Test mobile di semua breakpoint (320px, 375px, 414px, 768px, 1024px)
- [x] **13.8** Final build: `npm run build` ✅ (23.48s)

---

## 📊 Ringkasan Estimasi

| Phase | File | Kompleksitas | Estimasi |
|-------|------|-------------|----------|
| 0. Setup | 3-4 config | Rendah | 30 menit |
| 1. Install komponen | CLI commands | Rendah | 15 menit |
| 2. Layout | 3 file | **Tinggi** | 2-3 jam |
| 3. Shared Components | 21 komponen | **Tinggi** | 3-4 jam |
| 4. Auth pages | 6 halaman | Rendah | 1 jam |
| 5. Profile | 4 halaman | Rendah | 45 menit |
| 6. CRUD Sederhana | 26 halaman | Sedang | 3-4 jam |
| 7. Members & Events | 9 halaman | Sedang | 2 jam |
| 8. Keuangan | 15 halaman | **Tinggi** | 3-4 jam |
| 9. Dashboard & Reports | 7 halaman | Sedang | 2 jam |
| 10. Admin | 3 halaman | Rendah | 45 menit |
| 11. Public | 12 halaman | Sedang | 2 jam |
| 12. Domain Components | 14 komponen | Sedang | 2-3 jam |
| 13. Cleanup | Semua | Rendah | 1-2 jam |
| **TOTAL** | | | **~22-30 jam** |

---

## 💡 Tips Pengerjaan

1. **Kerjakan Phase 0-3 dulu** — ini fondasi. Setelah ini selesai, Phase 4-12 bisa dikerjakan di urutan mana saja.
2. **Test setiap phase** — jangan lanjut ke phase berikutnya kalau build gagal.
3. **Mapping komponen cepat:**
   | Lama | Baru (shadcn) |
   |------|---------------|
   | `<PrimaryButton>` | `<Button>` |
   | `<SecondaryButton>` | `<Button variant="outline">` |
   | `<DangerButton>` | `<Button variant="destructive">` |
   | `<TextInput v-model="x">` | `<Input v-model="x">` |
   | `<InputLabel>` | `<Label>` |
   | `<Modal :show="x" @close="...">` | `<Dialog :open="x" @update:open="...">` |
   | `<Dropdown>` | `<DropdownMenu>` |
   | `<Badge variant="success">` | `<Badge variant="default" class="bg-green-100">` atau custom variant |

4. **Jangan ubah logic/props** — fokus hanya ganti komponen UI dan class names.
