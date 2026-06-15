# AI Assessment & Case Journaling Platform — Journey Map

## Main Components

### 1. API Backend (Laravel 13 / PHP 8.3)
- **REST API**: Versioned endpoints under `/api/v1/` and `/api/v2/` for mobile clients.
- **Authentication**: Laravel Sanctum for API (token-based), session guard for admin panel.
        R[Routes (api_v1.php, api_v2.php)] --> A[Controllers (api/v1, v2)]
        A --> F[FormRequests]
        A --> B[Services]
        B --> C[Models]
        B --> D[File Storage (S3)]
        B --> E[Notifications (SNS, Twilio)]
        C --> G[config/tables.php]

### 2. Admin Panel
        RH[Routes (web.php)] --> I[Controllers (admin)]
        I --> B
        I --> H[Blade Views]
- **Management**: CRUD for users, plans, AI assessments, and case journals.

### 3. AI & Journaling Features
- **AI Assessments**: Models, migrations, and endpoints for AI-driven assessments.
- **Case Journals**: Models, migrations, and endpoints for clinical/learning journaling.
- **File Attachments**: S3-backed file uploads for both features.

### 4. Configuration & Conventions
- **Table Names**: Centralized in `config/tables.php` for DB prefixing.
- **Constants**: App-wide constants in `config/constants.php`.
- **Routes**: API routes in `routes/api_v1.php` and `routes/api_v2.php`; admin in `routes/web.php`.
- **Traits**: Common helpers (file upload, OTP, etc.) in `app/Traits/Common_trait.php`.

## General Architecture

```mermaid
graph TD
    subgraph API Backend
        A[Controllers (api/v1, v2)] --> B[Services]
        B --> C[Models]
        B --> D[File Storage (S3)]
        B --> E[Notifications (SNS, Twilio)]
        A --> F[FormRequests]
        C --> G[config/tables.php]
    end
    subgraph Admin Panel
        H[Blade Views] --> I[Controllers (admin)]
        I --> B
    end
    subgraph Features
        J[AI Assessments] --> B
        K[Case Journals] --> B
        J --> L[S3 Files]
        K --> L
    subgraph Auth
        M[Sanctum (API)]
        N[Session (Admin)]
    end
    M --> A
    N --> I
```

## Key Principles
- **Strict Types**: All PHP files use `declare(strict_types=1)`.
- **No Business Logic in Controllers**: Use Service classes.
- **SoftDeletes**: On all user-facing models.
- **API Response Shape**: Unified via `BaseApiController`.
- **Versioned API**: `/api/v1/`, `/api/v2/` mapped to controller subdirs.
- **Testing**: Database refresh, AWS/Twilio mocking, both happy/error paths.

---

For more details, see:
- `.github/copilot-instructions.md`
- `docs/architecture.md`
- `docs/features.md`
- `docs/guidelines.md`
- `config/tables.php`, `config/constants.php`
