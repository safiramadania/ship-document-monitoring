# AGENTS.md — Codex Instructions

## Project

This project is **Ship Document Monitoring**, a Laravel + React web application for ASDP vessel permit/certification document monitoring.

The system replaces manual spreadsheet + Google Drive workflows where users manually read vessel documents, identify document type, record letter number, issue date, expiry date, issuer, upload to Drive, and paste document links into a spreadsheet.

## Tech Stack

Use:

- Laravel
- Inertia.js
- React
- TypeScript
- Tailwind CSS
- shadcn/ui
- PostgreSQL
- Laravel Queue
- Private file storage

Do **not** use Filament.

Local development uses **Laragon on Windows**.

## Required Reading

Before making changes, read these files:

- `docs/PROJECT_BRIEF.md`
- `docs/DATABASE_SCHEMA.md`
- `docs/ROLES_AND_PERMISSIONS.md`
- `docs/OCR_FLOW.md`
- `docs/UI_STYLE_GUIDE.md`
- `docs/CODEX_MILESTONES.md`

Seed/master data is stored in:

- `database/seed-data/ship_document_codex_instruction_pack.xlsx`

## Core Rules

1. Work one milestone at a time.
2. Do not implement future milestones unless explicitly asked.
3. Do not refactor unrelated files.
4. Do not hardcode master data in React components.
5. Use database seeders for branches, vessels, branch users, document types, and sample documents.
6. Uploaded files must be stored privately, not in public storage.
7. Document preview/download must go through protected routes with authorization checks.
8. Enforce branch access in backend policies/middleware, not only by hiding UI.
9. User cabang confirms OCR-filled fields; central admin does not approve every uploaded document.
10. After user cabang confirms OCR results, save directly to monitoring.
11. Dashboard admin must show recent data changes, timestamp, and who edited the data.
12. User management must show last login and last online.
13. After each milestone, run build/tests if available, summarize changed files, and stop for review.

## Roles

Use only:

- `super_admin`
- `admin`
- `user_cabang`

## Document Upload Modes

Support two modes:

1. **Targeted Upload**
   - User uploads from a specific document row in the vessel monitoring table.
   - `vessel_id` and `document_type_id` are already known.
   - OCR/AI extracts metadata only.

2. **Smart Upload**
   - User uploads a document after selecting a vessel.
   - The system classifies the document type automatically using document type names, aliases, and keywords.
   - User can correct the detected document type before saving.

## OCR Confirmation Flow

The OCR flow must feel like KTP scanning in a digital banking app:

Upload document → OCR/AI auto-fills form → user reviews/edits → user clicks Save → confirmation modal → data is saved directly into monitoring.

Confirmation modal text:

> Apakah data sudah sesuai dengan dokumen yang diunggah? Data ini akan digunakan untuk monitoring pusat.

## Stop Condition

After completing a requested milestone:

- summarize what changed
- list files added/modified
- mention commands run
- mention any remaining issues
- stop and wait for review
