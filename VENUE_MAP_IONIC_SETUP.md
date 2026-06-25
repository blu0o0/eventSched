# Venue Map - Ionic Implementation Setup Guide

This guide explains how to set up and use the Google Maps venue map feature in the Ionic mobile application.

## Overview

The venue map feature displays all Santiago Campus venues on an interactive Google Map with:
- Real-time availability status (Available/Occupied/Reserved)
- Interactive sidebar listing all venues
- Date filtering to check venue availability
- Campus boundary visualization
- Detailed venue information popups
- Responsive design for mobile and desktop

## Prerequisites

1. **Laravel Backend** must be running on `http://localhost:8000`
2. **Google Maps API Key** must be configured in both Laravel and Ionic
3. **Ionic Development Server** must be running on `http://localhost:8100`

## Configuration

### 1. Laravel Configuration (Already Done)

The Laravel backend has been configured with:

**File: `ISUlaravel/.env`**
```env
GOOGLE_MAPS_API_KEY=AIzaSyBy73Zngwr-1WrVhIKn2A2gVQGMOv0WMxM
```

**File: `ISUlaravel/routes/api.php`**
- Added route: `GET /venues/map/data`

**File: `ISUlaravel/app/Http/Controllers/Api/VenueController.php`**
- Added `mapData()` method to return venues with availability information

### 2. Ionic Configuration

**File: `ISUionic/src/config/env.ts`**
```typescript
export const GOOGLE_MAPS_API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY || 'AIzaSyBy73Zngwr-1WrVhIKn2A2gVQGMOv0WMxM';
```

**File: `ISUionic/.env.example`**
```env
VITE_GOOGLE_MAPS_API_KEY=AIzaSyBy73Zngwr-1WrVhIKn2A2gVQGMOv0WMxM
```

**To use your own API key:**
1. Copy `.env.example` to `.env` in the Ionic project root
2. Replace the API key with your own from [Google Cloud Console](https://console.cloud.google.com/google/maps-apis)
3. Make sure "Maps JavaScript API" is enabled in your Google Cloud project

## Components

### 1. VenueSidebar Component

**File: `ISUionic/src/components/VenueSidebar.vue`**

A sidebar component that displays:
- List of all venues
- Color-coded availability status (Green=Available, Red=Occupied, Yellow=Reserved)
- Venue details (name, location, capacity)
- Click to select and center map on venue

**Props:**
- `venues: Venue[]` - Array of venue objects
- `venueAvailability: Record<number, any>` - Availability data keyed by venue ID
- `selectedVenueId?: number` - Currently selected venue ID

**Events:**
- `venue-select` - Emitted when a venue is clicked, passes the venue object

### 2. VenueMap View

**File: `ISUionic/src/views/VenueMap.vue`**

Main view that integrates:
- Google Maps with satellite view
- Campus boundary polygon (red outline)
- Color-coded markers for each venue
- Interactive sidebar
- Date filter
- Legend
- Venue information popups

**Features:**
- Date selection to view availability for specific dates
- Automatic map centering on all venues
- Click sidebar items to focus on specific venues
- Responsive layout (sidebar on left for desktop, top for mobile)

## API Endpoint

### GET `/api/venues/map/data`

**Query Parameters:**
- `date` (optional) - Date in YYYY-MM-DD format (defaults to today)

**Response:**
```json
{
  "venues": [
    {
      "id": 1,
      "name": "Venue Name",
      "location": "Santiago Campus",
      "capacity": 50,
      "description": "Venue description",
      "map_coordinates": "16.72287,121.53544",
      "photo_url": "https://example.com/photo.jpg",
      "status": "available",
      "is_available": true,
      "is_unavailable": false
    }
  ],
  "venue_availability": {
    "1": {
      "is_available": true,
      "is_currently_occupied": false,
      "reservation_count": 0,
      "reservations": []
    }
  },
  "selected_date": "2026-06-26"
}
```

## Running the Application

### 1. Start Laravel Backend

```bash
cd ISUlaravel
php artisan serve
```

The API should be available at `http://localhost:8000`

### 2. Start Ionic Development Server

```bash
cd ISUionic
npm install  # If not already done
npm run dev
```

The app should be available at `http://localhost:8100`

### 3. Access the Venue Map

Navigate to the Venue Map page in the Ionic app. The route is typically:
- `/venues/map` or similar (check your router configuration)

## Troubleshooting

### Google Maps Not Loading

1. **Check API Key**: Ensure the API key is correctly set in both Laravel `.env` and Ionic `.env`
2. **Enable API**: Make sure "Maps JavaScript API" is enabled in Google Cloud Console
3. **Check Console**: Open browser DevTools (F12) and check for errors in the Console tab
4. **Network Tab**: Check if the Google Maps script is loading in the Network tab

### No Venues Showing

1. **Check Laravel Server**: Ensure Laravel is running on `http://localhost:8000`
2. **Check API Response**: Open browser DevTools > Network tab, find the request to `/api/venues/map/data` and check the response
3. **Check Database**: Ensure venues exist in the database with `location = 'Santiago Campus'`
4. **Check Coordinates**: Ensure venues have valid `map_coordinates` in format "lat,lng"

### Markers Not Appearing

1. **Check Coordinates**: Verify that venues have valid coordinates (not null or invalid)
2. **Check Console**: Look for warnings about invalid coordinates in the browser console
3. **Check Map Initialization**: Ensure the map is properly initialized before markers are added

### Sidebar Not Showing

1. **Check Component Import**: Ensure `VenueSidebar` is properly imported in `VenueMap.vue`
2. **Check Props**: Verify that `venues` and `venueAvailability` data is being passed correctly
3. **Check Styles**: Ensure the sidebar container has proper width and is not hidden

## File Structure

```
ISUionic/
├── src/
│   ├── components/
│   │   └── VenueSidebar.vue          # Sidebar component
│   ├── views/
│   │   └── VenueMap.vue              # Main map view
│   ├── config/
│   │   └── env.ts                    # Environment configuration
│   └── types/
│       └── index.ts                  # TypeScript interfaces
├── .env.example                      # Environment variables template
└── VENUE_MAP_IONIC_SETUP.md         # This file

ISUlaravel/
├── app/
│   └── Http/
│       └── Controllers/
│           └── Api/
│               └── VenueController.php  # Added mapData() method
├── routes/
│   └── api.php                       # Added /venues/map/data route
└── .env                              # Contains GOOGLE_MAPS_API_KEY
```

## Customization

### Changing Campus Location

To use a different campus location, update the `campusBoundary` array in `VenueMap.vue`:

```typescript
const campusBoundary = [
  { lat: 16.72287, lng: 121.53544 },
  { lat: 16.72213, lng: 121.53542 },
  { lat: 16.72214, lng: 121.53735 },
  { lat: 16.72280, lng: 121.53742 }
];
```

### Changing Map Center

Update the center coordinates in the map initialization:

```typescript
map.value = new window.google.maps.Map(document.getElementById('venueMap'), {
  center: { lat: 16.72249174514112, lng: 121.53739618722382 },
  zoom: 16,
  mapTypeId: 'satellite'
});
```

### Changing Colors

Update the color values in the CSS sections of both `VenueMap.vue` and `VenueSidebar.vue`:
- Available: `#28a745` (green)
- Occupied: `#dc3545` (red)
- Reserved: `#ffc107` (yellow)

## Testing Checklist

- [ ] Laravel backend is running
- [ ] Ionic dev server is running
- [ ] Google Maps loads correctly
- [ ] Venues appear on the map with markers
- [ ] Sidebar displays all venues
- [ ] Clicking sidebar item centers map on that venue
- [ ] Date filter works and updates availability
- [ ] Legend shows correct color codes
- [ ] Info windows display venue details
- [ ] Responsive design works on mobile and desktop
- [ ] Campus boundary polygon is visible

## Notes

- The map uses **satellite view** by default (can be changed to roadmap in the map initialization)
- Venues are filtered to show only **Santiago Campus** locations
- The sidebar is **350px wide** on desktop and becomes a **top panel** on mobile devices
- Markers are **color-coded** based on real-time availability
- The map automatically **fits all markers** in view on load

## Support

For issues related to:
- **Laravel API**: Check `ISUlaravel/routes/api.php` and `VenueController.php`
- **Ionic Components**: Check `ISUionic/src/components/VenueSidebar.vue`
- **Map View**: Check `ISUionic/src/views/VenueMap.vue`
- **Configuration**: Check `ISUionic/src/config/env.ts`