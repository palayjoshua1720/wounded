# Changelog

## [Unreleased]
- Add your changes here under your own name and date.
- Format:
  - ## [Your Name] - YYYY-MM-DD
  - ### Added / Updated / Fixed

---

## [Joshua] - 2025-08-26

### Modified
- **Clinic Dashboard**
  - Added Recent Activity section and made it functional

- **Clinic Management**
  - Updated UI
  - Removed pagination loader
  - Updated DB schema

- **Clinician Management**
  - Updated UI
  - Removed pagination loader
  - Added create user (clinician) functionality

### Added
- **Recent Activity Functionality**
  - Implemented `woundmed_audit_logs` integration
  - Displayed action type, action message, entity type, IP address, and timestamp
  - Added relative time format (e.g., "2 mins ago")

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
