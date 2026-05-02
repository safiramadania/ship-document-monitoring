# UI_STYLE_GUIDE.md

## UI Stack

Use:

- React
- TypeScript
- Tailwind CSS
- shadcn/ui
- Lucide icons where useful

Do not use Filament.

## Visual Direction

The application should look like a modern soft enterprise dashboard, not a generic CRUD admin panel.

Visual style:

- soft cyan
- pastel blue
- slate
- white
- subtle shadows
- rounded cards
- clean whitespace
- calm enterprise feel
- readable tables
- clear status badges
- operational monitoring focus

Avoid:

- over-colorful landing page style
- default admin CRUD look
- excessive animations
- cluttered layouts

## Layout

Use a consistent authenticated app layout:

### Left Sidebar

Contains:

- app logo/name
- Dashboard
- Monitoring Kapal
- Upload Dokumen
- Smart Upload
- Dokumen Kapal
- Master Data
- User Approval
- Users
- Email Logs
- Audit Logs
- Settings/Profile

Navigation must be role-aware.

### Topbar

Contains:

- page title
- optional search input
- notification icon
- user avatar/profile dropdown

### Main Content

Use:

- cards
- tables
- filters
- status badges
- progress bars
- clean form layouts

## Main Pages

## Dashboard

Admin and super admin dashboard should show:

- total branches
- total vessels
- total documents
- active documents
- expiring soon documents
- expired documents
- missing documents
- documents needing confirmation
- recent uploads
- recent document edits
- who edited data
- timestamp of edit
- before/after summary if possible

User cabang dashboard should show:

- assigned branch only
- vessels in branch
- documents needing upload
- documents needing confirmation
- expired documents
- expiring soon documents
- quick actions:
  - Monitoring Kapal
  - Upload Dokumen
  - Smart Upload

## Monitoring Kapal

This is the core page.

Requirements:

- branch filter for admin/super_admin
- fixed branch for user_cabang
- vessel filter
- compliance summary cards
- progress bar
- table like a modern version of the spreadsheet

Table columns:

- No
- Jenis Sertifikat / Dokumen
- No Surat
- Terbit
- Sampai Dengan
- Instansi Penerbit
- Status
- Link Dokumen
- Action

Actions:

- Upload button per document row for targeted upload
- Smart Upload button at top

## OCR Confirmation

Design like a verification form.

Layout:

- two-column desktop layout
- left: document preview/download
- right: auto-filled editable form

Fields:

- document type
- letter number
- issue date
- expiry date
- issuer
- is permanent

Also show:

- OCR confidence
- classification confidence
- warnings
- collapsible OCR text

Save button opens confirmation modal.

## Status Badges

Use consistent colors:

- `active`: green
- `expiring_soon`: amber
- `expired`: red
- `permanent`: blue
- `missing`: gray/red
- `unknown`: gray
- `need_confirmation`: purple
- `processing`: blue
- `failed`: red

## Components

Recommended components:

- `AppLayout`
- `Sidebar`
- `Topbar`
- `StatCard`
- `StatusBadge`
- `ComplianceProgress`
- `DataTable`
- `FilterBar`
- `DocumentUploadDropzone`
- `DocumentPreview`
- `OcrConfirmationForm`
- `ConfirmationModal`
- `EmptyState`
- `RecentActivityList`
- `RecentEditsTable`
- `UserLastSeenBadge`

## UX Rules

- Show loading states.
- Show empty states.
- Confirm destructive actions.
- Keep form labels clear.
- Use Indonesian labels for user-facing operational pages.
- Keep status language easy for ASDP users.
- Prioritize readability over visual decoration.
