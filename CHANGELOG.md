# Changelog

## [Unreleased]
- Add your changes here under your own name and date.
- Format:
  - ## [Your Name] - YYYY-MM-DD
  - ### Added / Updated / Fixed

---

## [Joshua] - 2025-08-22

### Added
- **Changelog**
  - Created initial `CHANGELOG.md` file

- **Audit Logs**
  - Added `woundmed_audit_logs` table
  - Records login success/failure
  - Records 2FA success/failure
  - Tracks unauthorized access attempts for non-existent users

- **Force Password Change**
  - Added `force_password_change` table
  - Generates random password after creating a Clinician
  - Forces user to change their password on first login

- **Last Login Tracking**
  - Added `last_logged_in` table
  - Records datetime of every successful login

- **Clinic Dashboard**
  - Fetch clinic and clinician counts dynamically from DB

- **Clinic Management**
  - Dynamically fetch clinic data from DB

- **Clinician Management**
  - Dynamically fetch clinician data from DB
  - Added create user (clinician) functionality

- **UI Components**
  - Added pagination component
  - Added global page loader composable (for redirections)
  - Added table loader component (table-specific)
  - Added content loader component (generic)

- **Dependencies**
  - Integrated `lucide-vue-next` (Lucide icon package)

- **Login**
  - Modified login UI
  - Implemented working login functionality
  - Added 2FA validation logic
  - Updated UI to support 2FA flow
