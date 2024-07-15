# Laravel Contact Management System

A full-featured CRM-style contact management system with companies, contacts, notes, and activities.

## Features

- ✅ Contact CRUD with search & filters
- ✅ Company management
- ✅ Contact groups/tags
- ✅ Notes & activity timeline
- ✅ Import/Export contacts (CSV)
- ✅ Contact sharing between users
- ✅ Custom fields
- ✅ Duplicate detection
- ✅ RESTful API + Web Interface

## Installation

```bash
# Clone the repo
git clone https://github.com/yourusername/laravel-contact-management.git
cd laravel-contact-management

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Storage link for profile photos
php artisan storage:link

# Run
php artisan serve
npm run dev
```

## Routes

### Web Routes
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/contacts` | List all contacts |
| GET | `/contacts/create` | Create contact form |
| POST | `/contacts` | Store contact |
| GET | `/contacts/{contact}` | View contact |
| GET | `/contacts/{contact}/edit` | Edit contact form |
| PUT | `/contacts/{contact}` | Update contact |
| DELETE | `/contacts/{contact}` | Delete contact |
| GET | `/companies` | List all companies |
| POST | `/contacts/import` | Import from CSV |
| GET | `/contacts/export` | Export to CSV |

### API Routes
All CRUD operations available at `/api/contacts` and `/api/companies`

## Database Schema

- **contacts**: id, first_name, last_name, email, phone, company_id, ...
- **companies**: id, name, website, industry, ...
- **groups**: id, name, color, ...
- **contact_group**: pivot table
- **notes**: id, contact_id, body, ...
- **activities**: id, contact_id, type, description, ...

## License

MIT
