# CODEX_MILESTONES.md

## Rule

Implement one milestone at a time. Do not implement future milestones unless explicitly asked.

After every milestone:

1. run migrations if needed
2. run tests/build if available
3. summarize changed files
4. mention commands run
5. stop for review

## Milestone 0 — Context Check

Goal: Understand the project before coding.

Prompt:

```text
Read AGENTS.md, all docs in /docs, and database/seed-data/ship_document_codex_instruction_pack.xlsx.

Do not implement features yet.

Summarize:
1. project goal
2. roles
3. upload modes
4. OCR confirmation flow
5. database entities
6. seed data available
7. milestone implementation order

Stop after summarizing.
```

## Milestone 1 — Base Laravel React Project

Goal: Create the app foundation.

Requirements:

- Laravel
- Inertia.js
- React
- TypeScript
- Tailwind CSS
- shadcn/ui
- PostgreSQL
- no Filament
- base layout with sidebar/topbar
- placeholder pages

Pages:

- Dashboard
- Monitoring Kapal
- Upload Dokumen
- Smart Upload
- OCR Confirmation
- User Approval
- Users
- Master Data
- Email Logs
- Audit Logs

## Milestone 2 — Database Schema, Models, Seed Data

Goal: Build core database.

Create:

- branches
- vessels
- document_types
- vessel_documents
- document_extractions
- audit_logs
- email_notifications

Use:

`database/seed-data/ship_document_codex_instruction_pack.xlsx`

If needed, convert sheets into:

- branches.csv
- branch_users.csv
- vessels.csv
- document_types.csv
- existing_documents.csv

Seed:

- branches
- branch user emails
- vessels
- document types
- sample existing documents

## Milestone 3 — Auth, Roles, Approval, Branch Access

Goal: Implement security and access flow.

Implement:

- public registration
- email verification
- user statuses: pending, active, rejected, suspended
- roles: super_admin, admin, user_cabang
- pending approval page
- rejected account page
- super_admin user approval page
- approve/reject user
- assign branch and role
- backend branch-based authorization
- last_login_at
- throttled last_seen_at

Seed one default super_admin account.

## Milestone 4 — Role-Aware Dashboards

Goal: Create dashboards for each role.

Super admin:

- total branches
- total vessels
- total documents
- active users
- pending users
- recent edits
- recent audit logs

Admin:

- all branch monitoring
- active documents
- expiring soon
- expired
- missing
- need confirmation
- recent uploads
- recent edits with timestamp/editor identity

User cabang:

- assigned branch only
- vessels in branch
- documents needing upload
- documents needing confirmation
- expired
- expiring soon
- quick actions

Also create user list page with:

- name
- email
- role
- branch
- status
- last_login_at
- last_seen_at

## Milestone 5 — Monitoring Kapal

Goal: Build the core monitoring table.

Requirements:

- branch filter for admin/super_admin
- fixed branch for user_cabang
- vessel filter
- compliance summary
- progress bar
- required document checklist per vessel
- status calculation
- targeted upload button per row
- smart upload button at top

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

## Milestone 6 — Targeted Upload and Private Storage

Goal: Upload from a known document row.

Flow:

- user clicks Upload on document row
- system knows vessel_id and document_type_id
- validate PDF/JPG/PNG max 20MB
- store privately
- create vessel_documents row
- upload_mode = targeted
- processing_status = pending
- protected preview/download routes
- branch authorization enforced

Do not implement OCR yet.

## Milestone 7 — Fake OCR and OCR Confirmation

Goal: Implement KTP-like confirmation flow.

Create:

- OcrProviderInterface
- OcrResult DTO
- FakeOcrProvider
- AiExtractionProviderInterface
- AiExtractionResult DTO
- FakeAiExtractionProvider
- DocumentProcessingService
- ProcessVesselDocumentJob
- IndonesianDateNormalizer

Flow:

- targeted upload dispatches job
- processing_status = processing
- fake OCR/AI extracts metadata
- save ocr_text, extracted_values, confidence, warnings
- processing_status = need_confirmation
- OCR Confirmation page
- editable auto-filled form
- confirmation modal
- save final values
- processing_status = confirmed
- confirmed_by and confirmed_at
- validity_status calculated
- audit log document.confirmed
- redirect to Monitoring Kapal

No central admin approval.

## Milestone 8 — Smart Upload and Fake Classification

Goal: Upload without known document type.

Flow:

- user selects vessel
- uploads file
- upload_mode = smart
- document_type_id = null initially
- fake classifier detects document type using document_types name/aliases/keywords
- OCR Confirmation page allows document type correction
- same confirmation flow as targeted upload

## Milestone 9 — Audit Log and Recent Changes

Goal: Track changes and editor identity.

Log:

- user.registered
- user.approved
- user.rejected
- user.login
- document.uploaded
- document.ocr_processed
- document.confirmed
- document.updated
- reminder.sent
- reminder.failed

Create pages:

- Audit Logs
- Recent Document Changes

Dashboard additions:

- Recent Activity card
- Recent Document Edits table
- timestamp
- user name
- role
- branch
- action
- entity
- before/after summary

## Milestone 10 — Email Reminder System

Goal: Send reminder email for expiring documents.

Rules:

- daily scheduled job
- confirmed documents only
- thresholds:
  - expired
  - expires today
  - expires in 7 days
  - expires in 14 days
  - expires in 30 days
  - expires in 60 days
  - expires in 90 days
- group by branch
- send to active user_cabang accounts assigned to branch
- optionally CC admins
- store in email_notifications
- avoid duplicates using vessel_document_id + threshold_days + sent_date
- use log mail driver or Mailtrap for dev

## Milestone 11 — UI Polish

Goal: Make app ready for mentor/demo/skripsi.

Polish:

- Dashboard
- Monitoring Kapal
- OCR Confirmation
- Smart Upload
- User Approval
- User Management
- Email Logs
- Audit Logs

Add:

- empty states
- loading states
- responsive layout
- status badges
- document preview polish
- confirmation modal polish
- recent activity cards

Do not change business logic unless necessary.
