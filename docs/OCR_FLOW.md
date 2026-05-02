# OCR_FLOW.md

## Main Principle

There is **no central admin approval** for uploaded documents.

After OCR/AI processing, the **branch user** reviews and confirms the extracted fields. Once confirmed, the document data is saved directly into monitoring.

The flow should feel like KTP scanning in a digital banking app.

## Upload Modes

## 1. Targeted Upload

Targeted upload starts from the vessel monitoring table.

Flow:

1. User cabang opens Monitoring Kapal.
2. The table already lists required document types for a selected vessel.
3. User clicks Upload on a specific document row.
4. The system already knows:
   - `vessel_id`
   - `document_type_id`
5. User uploads PDF/JPG/PNG.
6. System stores the file privately.
7. System creates a `vessel_documents` row:
   - `upload_mode = targeted`
   - `processing_status = pending`
   - `vessel_id`
   - `document_type_id`
   - `uploaded_by`
   - file metadata
8. Queue job runs OCR/AI.
9. Since document type is already known, classification is skipped.
10. OCR/AI extracts:
    - `letter_number`
    - `issued_at`
    - `expires_at`
    - `issuer`
    - `is_permanent`
11. System sets `processing_status = need_confirmation`.
12. User reviews/edit fields in OCR Confirmation page.
13. User clicks Save.
14. Confirmation modal appears.
15. User confirms.
16. System saves final values and sets `processing_status = confirmed`.

## 2. Smart Upload

Smart upload starts from an upload page.

Flow:

1. User selects a vessel.
2. User uploads document without choosing document type.
3. System stores file privately.
4. System creates a `vessel_documents` row:
   - `upload_mode = smart`
   - `document_type_id = null`
   - `processing_status = pending`
5. Queue job runs OCR/AI.
6. OCR extracts text.
7. Classifier detects document type using `document_types` table:
   - `name`
   - `aliases`
   - `keywords`
8. AI extracts:
   - `document_type_id`
   - `letter_number`
   - `issued_at`
   - `expires_at`
   - `issuer`
   - `is_permanent`
9. System sets `processing_status = need_confirmation`.
10. User reviews/edit fields in OCR Confirmation page.
11. User may correct detected document type.
12. User clicks Save.
13. Confirmation modal appears.
14. User confirms.
15. System saves final values and sets `processing_status = confirmed`.

## OCR Confirmation Page

Layout:

Left side:

- PDF preview if file is PDF
- image preview if file is image
- fallback download/open button

Right side:

- editable form
- document type selector
- letter number field
- issue date field
- expiry date field
- issuer field
- is permanent toggle
- OCR confidence
- classification confidence
- warnings
- collapsible OCR text

Save button opens confirmation modal.

Modal text:

> Apakah data sudah sesuai dengan dokumen yang diunggah? Data ini akan digunakan untuk monitoring pusat.

Buttons:

- Batal
- Ya, Simpan Data

After confirmation:

- save final values
- set `processing_status = confirmed`
- set `confirmed_by = current user`
- set `confirmed_at = now`
- calculate `validity_status`
- create audit log `document.confirmed`
- redirect to Monitoring Kapal

## Processing Status

- `pending`
- `processing`
- `need_confirmation`
- `confirmed`
- `failed`

## Validity Status

- `active`
- `expiring_soon`
- `expired`
- `permanent`
- `missing`
- `unknown`

## Extraction Rules

- Preserve document number exactly as written.
- Normalize all dates to `YYYY-MM-DD`.
- Support Indonesian dates:
  - `14 Februari 2026`
  - `14-Feb-26`
  - `14/02/2026`
  - `2026-02-14`
- Extract issuer/agency if available.
- Do not guess aggressively.
- Return `null` if uncertain.
- Store confidence scores.
- Store warnings.
- Store original OCR/AI extracted values separately from user-confirmed final values.

## Services to Create

- `OcrProviderInterface`
- `OcrResult` DTO
- `FakeOcrProvider`
- `AiExtractionProviderInterface`
- `AiExtractionResult` DTO
- `FakeAiExtractionProvider`
- `DocumentClassifierService`
- `DocumentFieldExtractionService`
- `DocumentProcessingService`
- `ProcessVesselDocumentJob`
- `IndonesianDateNormalizer`

## MVP Provider Rule

For the first version, use fake OCR/AI providers.

Do not require real OCR API yet.

The architecture should allow later replacement with:

- Google Document AI
- Azure Document Intelligence
- AWS Textract
- Tesseract
- OpenAI
- Gemini
- Claude
