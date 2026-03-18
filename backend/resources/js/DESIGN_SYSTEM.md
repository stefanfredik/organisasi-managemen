# Design System - Organisasi Manajemen

## 📐 Standard Layout Guidelines

### Breakpoints
| Name | Size | Target |
|------|------|--------|
| xs | < 375px | Small phones (SE, Galaxy S mini) |
| sm | 640px | Large phones |
| md | 768px | Tablets |
| lg | 1024px | Desktop |
| xl | 1280px | Large desktop |

### Standard Page Template
```vue
<template>
    <Head title="Page Title" />
    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Page Title" description="Optional description">
                <template #actions>
                    <!-- Action buttons here -->
                </template>
            </PageHeader>
        </template>

        <PageContainer>
            <!-- Content here -->
        </PageContainer>
    </AuthenticatedLayout>
</template>
```

### Spacing Standards
| Context | Classes |
|---------|---------|
| Page padding | `px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 lg:py-8` |
| Card padding | `p-3 sm:p-4 md:p-6` |
| Card header | `px-3 sm:px-4 md:px-6 py-3 sm:py-4` |
| Section gap | `mb-6 sm:mb-8` |
| Grid gap | `gap-3 sm:gap-4 lg:gap-6` |
| Button gap | `gap-2 sm:gap-3` |

### Grid Patterns
```vue
<!-- Stats: 1→2→4 columns -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">

<!-- Cards: 1→2→3 columns -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">

<!-- Form: 1→2 columns -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 lg:gap-6">

<!-- Or use MobileGrid component -->
<MobileGrid :cols="4">...</MobileGrid>
```

### Components Available
| Component | Usage |
|-----------|-------|
| `PageContainer` | Standard page wrapper with max-width & padding |
| `PageSection` | Section with title, description, actions slot |
| `MobileGrid` | Responsive grid (auto column management) |
| `ResponsiveTable` | Table with horizontal scroll on mobile |
| `Card` | Card with responsive padding |
| `PageHeader` | Page title with actions |

### CSS Utility Classes
| Class | Purpose |
|-------|---------|
| `.page-container` | Standard content wrapper |
| `.page-padding-y` | Vertical page padding |
| `.card` / `.card-body` | Card styling |
| `.form-grid` | 1→2 col form grid |
| `.form-grid-3` | 1→2→3 col form grid |
| `.action-bar` | Form action buttons (stacks on mobile) |
| `.stat-grid` | 1→2→4 stat grid |
| `.btn-group-mobile` | Buttons stack on mobile |
| `.touch-target` | Min 44px touch area |
| `.safe-bottom` | Safe area bottom padding |

### Mobile Rules
1. **Min touch target**: 44px height for all interactive elements
2. **No horizontal overflow**: Use `overflow-x-auto` for wide content
3. **Stack on mobile**: Flex rows → columns on small screens
4. **Truncate text**: Use `truncate` for long text in tight spaces
5. **Font sizes**: Use `text-sm sm:text-base` pattern
6. **Safe areas**: Use `safe-bottom` for fixed bottom bars
