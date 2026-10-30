# School Daily Test Marks Management System — Context & Architecture

**Project**: School Daily Test Marks Management System  
**Institution**: Government High School (GHS) Maira  
**Framework**: Laravel 13.39.0 (PHP 8.3+)  
**Database**: SQLite (`database/database.sqlite`)  
**Frontend**: Blade + Tailwind CSS v4 (`@tailwindcss/vite` plugin with custom Pakistani school green theme)  
**Reporting Engine**: `barryvdh/laravel-dompdf` for PDF generation + native stream for CSV export  
**Test Suite**: Pest v5 (31 tests, 87 assertions, 100% passing)

---

## 1. System Overview & Roles

### 1.1 Administrator (`role = 'admin'`)
- Full administrative authority across the school.
- Creates, edits, deactivates, and resets teacher passwords.
- Creates and configures school classes and sections dynamically (e.g., KG, Prep, 1st to 10th A/B).
- Assigns or reassigns class incharge teachers to class sections.
- Manages global reusable subject catalogue (e.g., Urdu, English, Mathematics, Science, etc.).
- Enrolls, edits, and manages students across all classes.
- Views test results, student histories, and generates downloadable reports.
- Accesses the administrator dashboard with school statistics and recent activities.

### 1.2 Teacher (`role = 'teacher'`)
- Receives account credentials from Admin; no public registration.
- Views school classes, sections, students, and subjects.
- Class Incharge status is a **relationship** (`class_sections.incharge_id`), **not** a separate global user role.
- **As Class Incharge**:
  - Enrolls new students in their designated class section (with B-Form number & required photograph).
  - Assigns subjects from the catalogue to their class section.
  - Assigns teaching faculty to subjects in their class section.
  - Enters and edits test marks for their class.
- **As Teaching Faculty**:
  - Enters daily test marks for students in their assigned class-subject combinations.
  - Edits previously entered marks for tests they are authorized to manage.
- **School-wide Visibility**:
  - Can view the complete test history of any selected student across all classes attended.
  - Can preview printable test reports and download PDF/CSV test reports.

---

## 2. Database Schema & Relational Design

1. **`users`**
   - `id`, `name`, `email` (unique), `password`, `role` (admin/teacher), `is_active` (boolean), `timestamps`.
2. **`school_classes`**
   - `id`, `name` (e.g. "6th", "KG"), `display_order`, `is_active`, `timestamps`.
3. **`class_sections`**
   - `id`, `school_class_id` (FK), `name` (e.g. "A", "B", "Default"), `incharge_id` (FK -> users, nullable), `is_active`, `timestamps`.
   - Unique: `[school_class_id, name]`.
4. **`students`**
   - `id`, `name`, `b_form_number` (unique), `photo_path` (required student photograph), `is_active`, `timestamps`.
5. **`student_enrollments`** (Class History Preservation)
   - `id`, `student_id` (FK), `class_section_id` (FK), `enrolled_at`, `left_at` (nullable), `is_current` (boolean), `timestamps`.
   - When a student changes class, previous enrollment record is set to `is_current = false` with `left_at = today`, preserving historical test context.
6. **`subjects`**
   - `id`, `name` (unique catalogue), `is_active`, `timestamps`.
7. **`class_section_subject`** (Pivot)
   - `id`, `class_section_id` (FK), `subject_id` (FK), `timestamps`.
   - Unique: `[class_section_id, subject_id]`.
8. **`teacher_assignments`**
   - `id`, `user_id` (FK -> teacher), `class_section_subject_id` (FK), `timestamps`.
   - Unique: `[user_id, class_section_subject_id]`.
9. **`tests`**
   - `id`, `class_section_subject_id` (FK), `title`, `test_date`, `max_marks` (unsigned int > 0), `created_by` (FK -> users), `timestamps`.
10. **`test_results`**
    - `id`, `test_id` (FK), `student_id` (FK), `obtained_marks` (decimal 5,1 >= 0), `timestamps`.
    - Unique: `[test_id, student_id]`.

---

## 3. Implemented Routes & Endpoints

| Method | URI | Controller Action | Access / Middleware |
|---|---|---|---|
| `GET` | `/login` | `Auth\LoginController@showLoginForm` | Guest |
| `POST` | `/login` | `Auth\LoginController@login` | Guest |
| `POST` | `/logout` | `Auth\LoginController@logout` | Authenticated |
| `GET` | `/dashboard` | `DashboardController@index` | Auth + Active (Role-aware) |
| `GET` | `/teachers` | `TeacherController@index` | Admin only |
| `POST` | `/teachers` | `TeacherController@store` | Admin only |
| `PUT` | `/teachers/{teacher}` | `TeacherController@update` | Admin only |
| `PATCH`| `/teachers/{teacher}/toggle` | `TeacherController@toggleStatus` | Admin only |
| `GET` | `/classes` | `SchoolClassController@index` | Admin only |
| `POST` | `/classes` | `SchoolClassController@store` | Admin only |
| `GET` | `/classes/{class}` | `SchoolClassController@show` | All Teachers & Admin |
| `PUT` | `/classes/{class}` | `SchoolClassController@update` | Admin only |
| `GET` | `/sections/{section}` | `ClassSectionController@show` | All Teachers & Admin |
| `PATCH`| `/sections/{section}` | `ClassSectionController@update` | Admin only |
| `POST` | `/sections/{section}/subjects` | `ClassSectionController@assignSubject` | Incharge or Admin |
| `DELETE`| `/sections/{section}/subjects/{id}` | `ClassSectionController@removeSubject` | Incharge or Admin |
| `POST` | `/teacher-assignments` | `TeacherAssignmentController@store` | Incharge or Admin |
| `DELETE`| `/teacher-assignments/{id}` | `TeacherAssignmentController@destroy` | Incharge or Admin |
| `GET` | `/students` | `StudentController@index` | All Teachers & Admin |
| `GET` | `/students/create` | `StudentController@create` | Incharge or Admin |
| `POST` | `/students` | `StudentController@store` | Incharge or Admin |
| `GET` | `/students/{student}/edit` | `StudentController@edit` | Incharge or Admin |
| `PUT` | `/students/{student}` | `StudentController@update` | Incharge or Admin |
| `DELETE`| `/students/{student}` | `StudentController@destroy` | Admin only (safeguarded) |
| `GET` | `/students/{student}/history` | `StudentHistoryController@show` | All Teachers & Admin |
| `GET` | `/subjects` | `SubjectController@index` | Admin only |
| `POST` | `/subjects` | `SubjectController@store` | Admin only |
| `GET` | `/tests` | `TestController@index` | All Teachers & Admin |
| `GET` | `/tests/create` | `TestController@create` | Authorized Teacher or Admin |
| `POST` | `/tests` | `TestController@store` | Authorized Teacher or Admin |
| `GET` | `/tests/{test}` | `TestController@show` | All Teachers & Admin |
| `GET` | `/tests/{test}/edit` | `TestController@edit` | Authorized Teacher or Admin |
| `PUT` | `/tests/{test}` | `TestController@update` | Authorized Teacher or Admin |
| `GET` | `/reports/test/{test}` | `ReportController@show` | All Teachers & Admin (Preview) |
| `GET` | `/reports/test/{test}/pdf` | `ReportController@downloadPdf` | All Teachers & Admin (PDF) |
| `GET` | `/reports/test/{test}/csv` | `ReportController@downloadCsv` | All Teachers & Admin (CSV) |

---

## 4. Setup, Run & Administration Commands

### 4.1 Running the Application
```bash
# Start backend server
php artisan serve

# Or run via Herd (pre-configured)
# Start Vite asset watcher (during development if modifying CSS/JS)
npm run build
```

### 4.2 Creating / Resetting Initial Administrator
Use the custom Artisan command:
```bash
# Interactive prompt:
php artisan app:make-admin

# Or with direct options:
php artisan app:make-admin --name="Headmaster" --email="admin@ghsmaira.edu.pk" --password="Password123!"
```
Default seed account (from `php artisan db:seed`):
- **Email**: `admin@ghsmaira.edu.pk`
- **Password**: `Admin123!`

### 4.3 Creating Teacher Accounts
1. Log in as an Administrator (`/login`).
2. Navigate to **Teacher Accounts** (`/teachers`).
3. Click **Register New Teacher** (`/teachers/create`).
4. Enter Teacher Full Name, Email Address, and Initial Password (minimum 8 characters).
5. The teacher can immediately log in at `/login` with these credentials.
6. Teachers can be assigned as Class Incharge in **Classes & Sections** (`/classes`), and to specific subjects in **Class Section View** (`/sections/{id}`).

### 4.4 Automated Testing
Run the complete Pest test suite:
```bash
php artisan test
```
Result: **31 tests passed, 87 assertions** covering authentication, role middleware, class and section CRUD, student enrollment with photos, subject cataloguing, class incharge permissions, bulk daily test marks entry, validation, historical student test history, PDF generation, and CSV exports.
