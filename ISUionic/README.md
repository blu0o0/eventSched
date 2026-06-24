# Ionic Vue Venue Reservation App

A mobile application built with Ionic Vue for managing venue reservations and emergency reporting. This app connects to a Laravel backend API and provides a complete solution for venue browsing, reservation management, calendar views, and emergency reporting.

## Features

- **Authentication**: Secure login and registration with token-based authentication
- **Venue Management**: Browse venues, view details, and check availability
- **Reservation System**: Create, view, edit, and delete reservations
- **Calendar View**: Visual calendar with month, week, and day views
- **Emergency Reporting**: Submit and track emergency reports
- **User Profile**: View and manage user information

## Prerequisites

- Node.js (v16 or higher)
- npm or yarn
- Ionic CLI (optional, for development)
- Laravel backend API running

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd ISUionic
```

2. Install dependencies:
```bash
npm install
```

3. Configure environment variables:
   - Create a `.env` file in the root directory
   - Add your Laravel API base URL:
   ```
   VITE_API_BASE_URL=https://your-laravel-api.com/api
   ```

## Development

Run the development server:
```bash
npm run dev
```

The app will be available at `http://localhost:5173` (or the port shown in the terminal).

## Building for Production

Build the app for production:
```bash
npm run build
```

The built files will be in the `dist` directory.

## Building for Mobile

### Android

1. Add Android platform:
```bash
npx cap add android
```

2. Sync the project:
```bash
npx cap sync
```

3. Open in Android Studio:
```bash
npx cap open android
```

### iOS

1. Add iOS platform:
```bash
npx cap add ios
```

2. Sync the project:
```bash
npx cap sync
```

3. Open in Xcode:
```bash
npx cap open ios
```

## Project Structure

```
src/
├── api/              # API service files
│   ├── auth.ts
│   ├── venues.ts
│   ├── reservations.ts
│   ├── calendar.ts
│   ├── emergency.ts
│   └── client.ts
├── components/       # Reusable components
│   ├── VenueCard.vue
│   ├── ReservationCard.vue
│   ├── StatusBadge.vue
│   └── LoadingSpinner.vue
├── composables/     # Vue composables
│   ├── useAuth.ts
│   └── useApi.ts
├── config/          # Configuration files
│   └── env.ts
├── router/          # Vue Router configuration
│   └── index.ts
├── stores/          # Pinia stores
│   └── auth.ts
├── types/           # TypeScript type definitions
│   └── index.ts
├── utils/           # Utility functions
│   ├── storage.ts
│   └── validators.ts
└── views/           # Page components
    ├── Login.vue
    ├── Register.vue
    ├── Home.vue
    ├── Venues.vue
    ├── VenueDetail.vue
    ├── Reservations.vue
    ├── ReservationDetail.vue
    ├── CreateReservation.vue
    ├── Calendar.vue
    ├── EmergencyReports.vue
    ├── CreateEmergency.vue
    └── Profile.vue
```

## API Configuration

The app expects a Laravel backend API with the following endpoints:

### Authentication
- `POST /api/login` - User login
- `POST /api/register` - User registration
- `POST /api/logout` - User logout
- `GET /api/user` - Get current user

### Venues
- `GET /api/venues` - Get all venues
- `GET /api/venues/{id}` - Get venue details

### Reservations
- `GET /api/reservations` - Get all reservations (with optional status filter)
- `GET /api/reservations/{id}` - Get reservation details
- `POST /api/reservations` - Create reservation
- `PUT /api/reservations/{id}` - Update reservation
- `DELETE /api/reservations/{id}` - Delete reservation

### Calendar
- `GET /api/calendar/events` - Get calendar events (with optional start/end dates)

### Emergency Reports
- `GET /api/emergency/list` - Get all emergency reports
- `GET /api/emergency/{id}` - Get emergency report details
- `POST /api/emergency` - Create emergency report

## User Roles

- **administrator**: Full access (not available via mobile registration)
- **main_proponent**: Can create reservations
- **general_user**: Can create reservations and view own data

## Authentication

The app uses Laravel Sanctum token-based authentication. Tokens are stored securely using Capacitor Preferences and are automatically included in all API requests.

## Technologies Used

- **Ionic Vue**: UI framework for mobile apps
- **Vue 3**: Progressive JavaScript framework
- **TypeScript**: Type-safe JavaScript
- **Pinia**: State management
- **Vue Router**: Client-side routing
- **Axios**: HTTP client
- **FullCalendar**: Calendar component
- **Capacitor**: Native runtime

## Testing

Run unit tests:
```bash
npm run test:unit
```

Run end-to-end tests:
```bash
npm run test:e2e
```

## Linting

Check code quality:
```bash
npm run lint
```

## Troubleshooting

### API Connection Issues
- Verify the `VITE_API_BASE_URL` environment variable is set correctly
- Check that the Laravel backend is running and accessible
- Ensure CORS is properly configured on the backend

### Authentication Issues
- Clear app storage if experiencing token issues
- Verify the backend API is returning tokens in the correct format

### Build Issues
- Ensure all dependencies are installed: `npm install`
- Clear node_modules and reinstall if needed
- Check Node.js version compatibility

## License

[Your License Here]

## Support

For issues and questions, please contact [your contact information].

