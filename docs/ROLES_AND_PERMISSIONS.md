# ROLES_AND_PERMISSIONS.md

## Roles

Use only three roles:

1. `super_admin`
2. `admin`
3. `user_cabang`

## User Statuses

Use:

- `pending`
- `active`
- `rejected`
- `suspended`

Only `active` users can access the dashboard.

## Super Admin

Super admin has full system control.

Permissions:

- access all pages
- approve/reject user registrations
- assign roles
- assign branch to users
- manage users
- manage branches
- manage vessels
- manage document types
- view all branches
- view all vessels
- view all documents
- view audit logs
- view email notification history
- view recent changes
- access dashboard statistics for all data

## Admin

Admin represents ASDP central operations.

Permissions:

- view all branches
- view all vessels
- view all documents
- monitor expired, expiring soon, missing, active, and permanent documents
- view recent document edits
- see who edited data and when
- view audit logs
- view email notification history
- trigger or monitor email reminders

Limitations:

- does not need full system settings access
- does not need to manage all master data unless explicitly allowed
- does not approve every uploaded document

## User Cabang

User cabang represents branch users.

Permissions:

- access only assigned branch
- view only vessels from assigned branch
- view only documents from assigned branch
- upload documents for vessels in assigned branch
- use targeted upload
- use smart upload
- review/correct OCR-filled fields
- confirm and save document data
- view branch dashboard

Limitations:

- cannot access other branches
- cannot manage users
- cannot approve/reject user registrations
- cannot access central admin pages
- cannot see all-branch dashboards
- cannot bypass branch restriction by changing URLs

## Registration Flow

1. User opens public registration page.
2. User fills:
   - name
   - email
   - password
   - branch
   - job title
3. User verifies email.
4. Account status remains `pending`.
5. Pending user sees waiting approval page.
6. Super admin approves or rejects the user.
7. If approved:
   - status becomes `active`
   - role is assigned
   - branch is assigned
   - `approved_by` is stored
   - `approved_at` is stored
8. If rejected:
   - status becomes `rejected`
   - `rejected_reason` is stored

## Branch Access Rule

Branch access must be enforced in backend policies/middleware, not only frontend UI.

For `user_cabang`:

- all branch/vessel/document queries must be scoped to `auth()->user()->branch_id`
- preview/download routes must check branch ownership
- upload actions must check selected vessel belongs to user's branch

For `admin` and `super_admin`:

- may access all branches unless a page intentionally restricts them

## Last Online

Track:

- `last_login_at`: updated when user successfully logs in
- `last_seen_at`: updated when authenticated user accesses the app

Throttle `last_seen_at` updates, for example only update once every 5 minutes per user to avoid database writes on every request.
