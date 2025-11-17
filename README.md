# Contact Management System - Laravel CRM

A comprehensive Laravel-based Contact Management System implementing all CRM features with AJAX operations and contact merging capabilities.

## 🚀 Quick Start

1. **Install Dependencies**:
   ```bash
   composer install
   ```

2. **Setup Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

4. **Seed Sample Data**:
   ```bash
   php artisan db:seed --class=ContactSeeder
   ```

5. **Start Development Server**:
   ```bash
   php artisan serve
   ```

6. **Access Application**:
   ```
   http://127.0.0.1:8000
   ```

## ✅ Features Implemented

### Core CRM Features
- ✅ **Complete CRUD Operations** with Laravel resource controller
- ✅ **Required Fields**: Name, Email, Phone, Gender, Profile Image (PNG/JPG/JPEG), PDF File
- ✅ **Dynamic Custom Fields** with JSON storage (minimum 1 required)
- ✅ **Full AJAX Integration** for all operations without page refresh
- ✅ **Case-Insensitive Search** by Name, Email, Custom Fields with real-time updates
- ✅ **Advanced Filtering** by Gender and Custom Field values
- ✅ **Pagination** with customizable per-page options (5, 10, 25, 50)
- ✅ **File Upload Validation** with size limits and format restrictions
- ✅ **Image & File Preview** during upload process

### Contact Merging System
- ✅ **Intelligent Merge System** with master contact selection
- ✅ **Complete Data Preservation** - no data is ever lost during merge
- ✅ **Smart Custom Field Merging** with conflict resolution (" | " separator)
- ✅ **Merge Prevention** - already merged contacts cannot be merged again
- ✅ **Preserved Data Viewer** - view original secondary contact data
- ✅ **Master Contact Grouping** - merged contacts appear after their master
- ✅ **Comprehensive Merge UI** with confirmation workflows and user guide

### User Experience
- ✅ **Professional Bootstrap 5 Interface** with gradient styling
- ✅ **Compact One-Line Search** with icons for all filters
- ✅ **Color-Coded Contact Rows** (yellow=merged, blue=master, white=active)
- ✅ **Interactive Help System** with merge guide modal
- ✅ **Real-Time Validation** with red borders and field-specific error messages
- ✅ **Toast Notifications** for success/error feedback
- ✅ **Responsive Design** for all screen sizes

## 🏗️ Laravel Architecture

### Models
- `Contact.php` - Eloquent model with relationships and scopes

### Controllers
- `ContactController.php` - Resource controller with CRUD and merge operations

### Requests
- `ContactRequest.php` - Form validation with comprehensive rules

### Services
- `ContactService.php` - Business logic layer for all contact operations

### Views
- `contacts/index.blade.php` - Main interface with modals
- `contacts/partials/contact_list.blade.php` - AJAX-updated contact list

## 📊 Database Schema

```sql
contacts table:
- id (Primary Key)
- name (VARCHAR, required)
- email (VARCHAR, unique, required)
- phone (VARCHAR, required)
- gender (ENUM: male, female, other)
- profile_image (VARCHAR, nullable)
- additional_file (VARCHAR, nullable)
- custom_fields (JSON, nullable) - Dynamic custom fields
- is_merged (BOOLEAN, default: false)
- merged_into (Foreign Key, nullable) - Reference to master contact
- merged_data (JSON, nullable) - Original data preservation
- created_at, updated_at (Timestamps)
```

## 🎯 Key Features for Demo

### 1. CRUD Operations
- Add contacts with custom fields
- Edit existing contacts
- Delete contacts with confirmation
- Real-time validation and error handling

### 2. Advanced Search & Filter
- **One-line compact search** with 7 filter options
- **Case-insensitive search** by name, email, and custom field values
- **Gender filtering** with dropdown selection
- **Custom field search** across all field names and values
- **Pagination controls** with per-page selection
- **Real-time results** without page refresh
- **Search persistence** during pagination

### 3. Custom Fields Management
- Add unlimited custom fields per contact
- Dynamic UI for field management
- JSON storage for flexibility

### 4. Advanced Contact Merging
- **Two-step merge process**: Select target contact, then choose master
- **Master contact selection** with detailed contact information
- **Intelligent field merging** with " | " separator for conflicts
- **Complete data preservation** in `merged_data` JSON column
- **Merge prevention system** - no nested merging allowed
- **Preserved data viewer** - access original secondary contact data
- **Visual merge indicators** - color-coded rows and badges
- **Merge relationship tracking** with master/secondary links

### 5. Enhanced File Management
- **Profile image upload**: PNG, JPG, JPEG only (max 5MB)
- **PDF file upload**: PDF documents only (max 5MB)
- **File preview system**: Image thumbnails and file info display
- **Upload validation**: Format and size restrictions with helpful messages
- **Automatic directory creation** with proper permissions
- **File cleanup**: Automatic deletion when contacts are removed
- **Edit mode support**: Optional file replacement during updates

## 🔧 Technical Implementation

### AJAX Operations
- **Complete AJAX integration** for all CRUD operations
- **Real-time search and filtering** with debounced input
- **Dynamic pagination** without page refresh
- **Modal-based forms** with proper state management
- **Comprehensive error handling** with field-specific feedback

### Advanced Validation
- **Server-side validation** with ContactRequest and custom messages
- **Client-side visual feedback** with red borders and error text
- **File upload validation** with format, size, and preview checks
- **Custom field validation** ensuring minimum requirements
- **Merge validation** preventing invalid merge operations

### Database Design
- **Intelligent ordering** with complex SQL for merge grouping
- **JSON field utilization** for flexible custom fields
- **Case-insensitive search** using LOWER() and JSON functions
- **Relationship tracking** for merge audit trails
- **Data preservation** with complete original record storage

### Security & Performance
- **CSRF protection** on all forms and AJAX requests
- **File upload security** with type validation and sanitized names
- **Input sanitization** and SQL injection prevention
- **Efficient pagination** with configurable page sizes
- **Optimized queries** with proper indexing and selective loading

## 🎥 Demo Scenarios

### Basic Operations
1. **Add Contact**: Fill all required fields including image/PDF upload with preview
2. **Search & Filter**: Use one-line search with case-insensitive matching
3. **Edit Contact**: Modify existing contact with optional file replacement
4. **Delete Contact**: Remove contact with confirmation and file cleanup
5. **Pagination**: Navigate through contacts with customizable page sizes

### Advanced Merge Demonstration
1. **Create Similar Contacts**: 
   - Contact A: "John Smith" (position: "Manager", company: "ABC Corp")
   - Contact B: "J. Smith" (role: "Senior Manager", department: "Sales")
2. **Initiate Merge**: Click merge button on Contact A
3. **Select Target**: Choose Contact B from dropdown of all active contacts
4. **Choose Master**: Select which contact becomes the master record
5. **Confirm Merge**: Review merge details and confirm operation
6. **Verify Results**: 
   - Master shows combined fields: position: "Manager | Senior Manager"
   - Secondary marked as merged with yellow highlighting
   - Original data preserved and viewable via "Data" button

### User Guide Features
1. **Help System**: Click ❓ icon for comprehensive merge guide
2. **Visual Indicators**: Color-coded rows and badges for merge status
3. **Data Preservation**: View original secondary contact data
4. **Merge Prevention**: Attempt to merge already-merged contact (blocked)
5. **Search Testing**: Test case-insensitive search across all fields

## 🚀 Laravel Commands Used

```bash
# Project setup
composer create-project laravel/laravel contact_app
cd contact_app

# Generate components
php artisan make:migration create_contacts_table
php artisan make:model Contact
php artisan make:controller ContactController --resource
php artisan make:request ContactRequest
php artisan make:seeder ContactSeeder

# Database operations
php artisan migrate:fresh --seed  # Fresh start with seeded data
php artisan db:seed --class=ContactSeeder  # Seed data only

# File management
mkdir -p public/uploads/{profile_images,additional_files}
chmod -R 755 public/uploads

# Development
php artisan serve
php artisan tinker  # For testing
```

## 🔄 Refresh Database & Files

```bash
# Complete refresh (drops all tables and files)
rm -rf public/uploads/profile_images/* public/uploads/additional_files/*
php artisan migrate:fresh --seed

# Seed data only
php artisan db:seed --class=ContactSeeder
```

## 📁 Project Structure

```
contact_app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── ContactController.php     # Resource controller + merge endpoint
│   │   └── Requests/
│   │       └── ContactRequest.php        # Validation with custom messages
│   ├── Models/
│   │   └── Contact.php                   # Eloquent model with relationships
│   └── Services/
│       └── ContactService.php            # Business logic layer
├── database/
│   ├── migrations/
│   │   └── create_contacts_table.php     # Database schema with merge fields
│   └── seeders/
│       └── ContactSeeder.php             # Sample data generator
├── resources/views/contacts/
│   ├── index.blade.php                   # Main interface with Bootstrap 5
│   └── partials/
│       └── contact_list.blade.php        # AJAX-updated contact table
├── public/
│   ├── js/
│   │   └── contacts.js                   # AJAX operations & UI interactions
│   └── uploads/
│       ├── profile_images/               # User uploaded images
│       └── additional_files/             # User uploaded PDFs
├── routes/
│   └── web.php                          # Application routes
└── README.md                            # This documentation
```

## 🏆 Key Achievements

This implementation demonstrates:
- **Professional Laravel Development** with proper MVC architecture
- **Service Layer Pattern** for clean business logic separation
- **Advanced AJAX Integration** with comprehensive error handling
- **Intelligent Database Design** with merge tracking and data preservation
- **Modern UI/UX** with Bootstrap 5 and responsive design
- **Production-Ready Features** including validation, security, and file management
- **Comprehensive Documentation** with user guides and technical details

**Perfect for**: CRM systems, contact management, data merging scenarios, and Laravel learning projects.