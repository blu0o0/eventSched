# Isabela State University - Echague Campus
## Event Scheduling / Reservation System

A comprehensive Laravel-based event scheduling and reservation system with an admin panel and RESTful API for mobile app integration.

---

## 📌 Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Admin Panel**: Blade Templates, Bootstrap 5
- **API**: Laravel Sanctum
- **Database**: MariaDB/MySQL
- **Frontend (Mobile)**: Ionic + Vue + TypeScript (API consumer)

---

## 🚀 Features

### 1. User Roles System
- **Administrator**: Full access, approves/rejects reservations
- **Main Proponent**: Can request event reservations
- **General Users**: Can log in and view calendar/submit requests

### 2. Authentication
- **Web (Admin Panel)**: Session-based authentication
- **API (Mobile App)**: Token-based Sanctum authentication

### 3. Reservation Module
- Create, view, update, and delete reservations
- Automatic conflict detection (prevents double booking)
- Status workflow: Pending → Approved/Rejected
- Only administrators can approve/reject reservations

### 4. Calendar View
- FullCalendar.js integration
- Color-coded events:
  - 🟡 Yellow = Pending
  - 🟢 Green = Approved
  - 🔴 Red = Rejected
- Click events to view details and approve/reject

### 5. Venue Management
- Full CRUD for venues
- Fields: name, location, capacity, description, map_coordinates
- View venue availability by date

### 6. Venue Map View
- Visual campus venue map
- Clickable venues
- Highlights occupied/available venues based on selected date

### 7. Emergency / Disaster Notifications
- Users can submit emergency reports
- Administrators receive:
  - Dashboard notifications
  - Email notifications
  - Database notifications
- Track emergency status (open/closed)

### 8. RESTful API Endpoints
All endpoints require Sanctum authentication (Bearer token).

#### Authentication
- `POST /api/login` - Login and get token
- `POST /api/logout` - Logout
- `POST /api/register` - Register new user
- `GET /api/user` - Get current user

#### Reservations
- `GET /api/reservations` - List reservations
- `POST /api/reservations` - Create reservation
- `GET /api/reservations/{id}` - Get reservation details
- `PUT /api/reservations/{id}` - Update reservation
- `DELETE /api/reservations/{id}` - Delete reservation

#### Venues
- `GET /api/venues` - List all venues
- `GET /api/venues/{id}` - Get venue details

#### Calendar
- `GET /api/calendar/events` - Get calendar events (FullCalendar format)

#### Emergency
- `POST /api/emergency` - Submit emergency report
- `GET /api/emergency/list` - List emergency reports
- `GET /api/emergency/{id}` - Get emergency report details

---

## 📦 Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MariaDB/MySQL

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd ISUlaravel
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database**
   Edit `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=isu_event_system
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run Migrations and Seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Start Development Server**
   ```bash
   php artisan serve
   ```

8. **Build Frontend Assets (if needed)**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

---

## 🔐 Default Login Credentials

After running seeders, you can login with:

**Administrator:**
- Email: `admin@isu.edu.ph`
- Password: `password`

**Main Proponent:**
- Email: `proponent@isu.edu.ph`
- Password: `password`

**General User:**
- Email: `user@isu.edu.ph`
- Password: `password`

---

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/          # API Controllers
│   │   └── Admin/        # Admin Panel Controllers
│   ├── Middleware/       # Custom Middleware
│   ├── Requests/         # Form Request Validation
│   └── Resources/        # API Resources
├── Models/               # Eloquent Models
├── Policies/            # Authorization Policies
├── Services/             # Business Logic Services
└── Notifications/        # Notification Classes

database/
├── migrations/           # Database Migrations
└── seeders/             # Database Seeders

resources/
└── views/
    └── admin/           # Blade Templates for Admin Panel

routes/
├── api.php              # API Routes
└── web.php              # Web Routes
```

---

## 🛠️ Code Quality Features

- ✅ Repository/Service Pattern for business logic
- ✅ Form Requests for validation
- ✅ API Resources for JSON transformation
- ✅ Policy-based authorization
- ✅ Clean folder structure
- ✅ Type safety where possible
- ✅ No code duplication

---

## 📝 API Usage Example

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@isu.edu.ph",
    "password": "password"
  }'
```

### Create Reservation
```bash
curl -X POST http://localhost:8000/api/reservations \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Annual Meeting",
    "venue_id": 1,
    "date": "2024-12-25",
    "start_time": "09:00",
    "end_time": "12:00",
    "capacity": 100
  }'
```

---

## 🧪 Testing

Run the test suite:
```bash
php artisan test
```

---

## 📄 License

This project is proprietary software for Isabela State University - Echague Campus.

---

## 👥 Support

For issues or questions, please contact the development team.

---

## 🔄 Version History

- **v1.0.0** - Initial release with all core features

---

**Built with ❤️ for ISU Echague Campus**
