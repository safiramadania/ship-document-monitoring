# PROJECT_BRIEF.md

## Project Name

**Ship Document Monitoring**

## Background

This system is intended for ASDP vessel permit and certification document monitoring.

The user is part of the operational division handling vessel permits. Before a vessel can operate, it must have multiple required permit/certification documents. Currently, the monitoring workflow is manual using spreadsheets and Google Drive links.

The manual workflow usually requires users to:

1. Open/read each vessel document manually.
2. Identify the document type.
3. Read and record the letter/document number.
4. Read and record the issue date.
5. Read and record the expiry date.
6. Read and record the issuer/agency.
7. Upload the document to Google Drive.
8. Paste the document link into a spreadsheet.
9. Manually check whether the document is active, expired, or missing.

This is slow and error-prone because one vessel may have around 30 required documents, while ASDP has more than 150 vessels. The total number of monitored records can reach thousands.

## Goal

Build a modern web application that replaces the spreadsheet workflow.

The system should allow:

- ASDP central operations to monitor all branch/vessel document compliance.
- Branch users to upload vessel permit/certification documents.
- OCR/AI to auto-fill document metadata after upload.
- Users to confirm or correct OCR-filled fields before saving.
- Smart document classification for free uploads.
- Automatic validity status calculation.
- Email reminders for expired or soon-to-expire documents.
- Role-based and branch-based access control.
- Audit logs and recent change tracking.

## Main Users

### Super Admin

Controls the whole system, approves users, manages master data, sees all branches and all data.

### Admin

Central operations staff. Monitors all branches, vessels, expired documents, expiring documents, missing documents, recent edits, audit logs, and email notification history.

### User Cabang

Branch user. Uploads and confirms documents only for their assigned branch.

## Master Data Source

Use this file as seed/master data source:

`database/seed-data/ship_document_codex_instruction_pack.xlsx`

The spreadsheet includes:

- `README`
- `Overview`
- `Branches`
- `BranchUsers`
- `Vessels`
- `DocumentTypes`
- `ExistingDocuments`
- `SchemaMap`
- `CodexMilestones`
- `CodexPromptNotes`

Use it as source of truth for development seed data.

Do not hardcode master data in React components.

## Core Features

1. Authentication and registration.
2. Email verification.
3. User approval by super admin.
4. Role-based access.
5. Branch-based access.
6. Branch, vessel, and document type master data.
7. Monitoring table per vessel.
8. Targeted upload from monitoring rows.
9. Smart upload with document classification.
10. OCR/AI autofill.
11. User confirmation of OCR-filled fields.
12. Automatic document validity status calculation.
13. Recent edit tracking.
14. Last login / last online tracking.
15. Email reminders.
16. Audit logs.
17. Protected private document storage.

## Important Product Rule

There is **no central admin approval** for uploaded documents.

After OCR/AI processing, the branch user reviews and confirms the extracted fields. Once the branch user confirms, the data is saved directly into monitoring.

Central admin only monitors, audits, and can see changes.
