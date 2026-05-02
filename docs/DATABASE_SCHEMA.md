# DATABASE_SCHEMA.md

## Notes

Use PostgreSQL. Create migrations normally. Do not hardcode master data in migrations or React components.

Seed data should come from:

`database/seed-data/ship_document_codex_instruction_pack.xlsx`

If reading XLSX directly is inconvenient, convert the relevant sheets into CSV files first:

- `branches.csv`
- `branch_users.csv`
- `vessels.csv`
- `document_types.csv`
- `existing_documents.csv`

## Tables

## users

Stores all application users.

Columns:

- `id`
- `name`
- `email`
- `email_verified_at`
- `password`
- `role` enum/string: `super_admin`, `admin`, `user_cabang`
- `status` enum/string: `pending`, `active`, `rejected`, `suspended`
- `branch_id` nullable foreign key to `branches.id`
- `job_title` nullable string
- `approved_by` nullable foreign key to `users.id`
- `approved_at` nullable timestamp
- `rejected_reason` nullable text
- `last_login_at` nullable timestamp
- `last_seen_at` nullable timestamp
- `remember_token`
- `created_at`
- `updated_at`

Rules:

- `user_cabang` must have a branch.
- `admin` and `super_admin` may have null branch.
- Pending/rejected/suspended users cannot access the dashboard.

## branches

Stores ASDP branches.

Columns:

- `id`
- `code` unique string
- `name` string
- `regional` nullable string
- `email` nullable string
- `created_at`
- `updated_at`

Relationships:

- has many vessels
- has many users

## vessels

Stores vessels by branch.

Columns:

- `id`
- `branch_id` foreign key
- `code` nullable string
- `name` string
- `operator` nullable string
- `status` string default `active`
- `created_at`
- `updated_at`

Relationships:

- belongs to branch
- has many vessel_documents

## document_types

Stores required document/certificate types.

Columns:

- `id`
- `code` unique string, for example `S01`
- `name` string
- `agency` nullable string
- `category` nullable string
- `required` boolean default true
- `permanent_allowed` boolean default false
- `validity_months` nullable integer
- `sort_order` integer default 0
- `aliases` json nullable
- `keywords` json nullable
- `created_at`
- `updated_at`

Rules:

- The monitoring table should display required document types for each vessel.
- Smart upload classification uses `name`, `aliases`, and `keywords`.

## vessel_documents

Stores uploaded vessel documents and confirmed metadata.

Columns:

- `id`
- `vessel_id` foreign key
- `document_type_id` nullable foreign key
- `uploaded_by` foreign key to users
- `confirmed_by` nullable foreign key to users
- `confirmed_at` nullable timestamp
- `upload_mode` string: `targeted`, `smart`
- `letter_number` nullable string
- `issued_at` nullable date
- `expires_at` nullable date
- `issuer` nullable string
- `is_permanent` boolean default false
- `validity_status` string: `active`, `expiring_soon`, `expired`, `permanent`, `missing`, `unknown`
- `processing_status` string: `pending`, `processing`, `need_confirmation`, `confirmed`, `failed`
- `file_path` string
- `original_filename` string
- `mime_type` nullable string
- `file_size` nullable integer
- `ocr_text` long text nullable
- `classification_confidence` decimal nullable
- `extraction_confidence` decimal nullable
- `extracted_values` json nullable
- `final_values` json nullable
- `warnings` json nullable
- `processing_error` text nullable
- `created_at`
- `updated_at`

Rules:

- For targeted upload, `document_type_id` is known.
- For smart upload, `document_type_id` can be null initially and filled after classification/user confirmation.
- Confirmed documents are used in monitoring.
- Store OCR extracted values separately from final user-confirmed values.

## document_extractions

Stores raw OCR/AI processing results.

Columns:

- `id`
- `vessel_document_id` foreign key
- `provider` string
- `raw_ocr_response` json nullable
- `classification_result` json nullable
- `extracted_result` json nullable
- `warnings` json nullable
- `created_at`
- `updated_at`

## audit_logs

Stores important activity and data changes.

Columns:

- `id`
- `user_id` nullable foreign key
- `action` string indexed
- `entity_type` nullable string
- `entity_id` nullable unsigned bigint
- `old_values` json nullable
- `new_values` json nullable
- `ip_address` nullable string
- `user_agent` nullable text
- `created_at`

Actions to log:

- `user.registered`
- `user.approved`
- `user.rejected`
- `user.login`
- `document.uploaded`
- `document.ocr_processed`
- `document.confirmed`
- `document.updated`
- `reminder.sent`
- `reminder.failed`

## email_notifications

Stores email reminder history.

Columns:

- `id`
- `branch_id` nullable foreign key
- `vessel_document_id` nullable foreign key
- `recipients` json
- `cc` json nullable
- `subject` string
- `body` text
- `threshold_days` nullable integer
- `sent_date` nullable date
- `sent_at` nullable timestamp
- `status` string: `sent`, `failed`, `skipped`
- `error` text nullable
- `created_at`
- `updated_at`

Deduplication:

Use a database-safe unique key:

- `vessel_document_id`
- `threshold_days`
- `sent_date`

Do not use database-specific expression indexes like `DATE(sent_at)`.

## Status Logic

### processing_status

- `pending`: file uploaded, processing not started
- `processing`: OCR/AI is running
- `need_confirmation`: OCR/AI finished, waiting for branch user confirmation
- `confirmed`: branch user confirmed and saved the data
- `failed`: OCR/AI failed

### validity_status

- `permanent`: `is_permanent = true`
- `expired`: `expires_at` is before today
- `expiring_soon`: `expires_at` is within 60 days
- `active`: `expires_at` is more than 60 days away
- `unknown`: no expiry date and not permanent
- `missing`: required document type has no confirmed document for the vessel
