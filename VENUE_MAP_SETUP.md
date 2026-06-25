# Venue Map Feature - Setup Guide

This guide explains how to set up and use the Venue Map feature that has been added to both Laravel and Ionic applications.

## Overview

The Venue Map feature displays an interactive Google Maps view of the Santiago Campus with venue markers showing real-time availability status. It's now available in both the Laravel admin panel and the Ionic mobile app.

## Features

- Interactive Google Maps with satellite view
- Color-coded markers (Green = Available, Red = Occupied)
- Date filter to check venue availability for specific dates
- Detailed venue information popups
- Campus boundary visualization
- Legend for easy reference
- Responsive design for both web and mobile

## Laravel Backend Setup

### 1. API Endpoint Created

**File Modified:** `ISUlaravel/routes/api.php`
- Added new route: `GET /venues/map/data`

**File Modified:** `ISUlaravel/app/Http/Controllers/Api/VenueController.php`
- Added `mapData()` method to provide venue data with availability information
- Returns venues, availability status, and reservations for selected date

**File Modified:** `ISUlaravel/app/Http/Resources/VenueResource.php`
- Updated to include proper photo URL generation using `asset('storage/' . $this->photo)`

### 2. How It Works

The API endpoint:
1. Accepts a `date` parameter (defaults to today)
2. Retrieves all Santiago Campus venues
3. Checks reservation status for each venue on the selected date
4. Determines if venues are currently occupied (for today's date)
5. Returns structured data with availability information

## Ionic Mobile App Setup

### 1. Files Created/Modified

**Created:** `ISUionic/src/views/VenueMap.vue`
- Complete venue map view with Google Maps integration
- Date picker for filtering
- Legend card
- Responsive design

**Modified:** `ISUionic/src/router/index.ts`
- Added route: `/venue-map` with name `VenueMap`

**Modified:** `ISUionic/src/components/AppMenu.vue`
- Added "Venue Map" menu item in the Management section
- Added map icon import

**Modified:** `ISUionic/src/config/env.ts`
- Added `GOOGLE_MAPS_API_KEY` configuration

**Modified:** `ISUionic/src/App.vue`
- Enhanced global UI styling to match Laravel design
- Improved cards, buttons, toolbars, and other components

### 2. Required Configuration

#### Step 1: Get Google Maps API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/google/maps-apis)
2. Create a new project or select existing one
3. Enable the following APIs:
   - Maps JavaScript API
   - Geocoding API (optional, for address lookup)
4. Create credentials (API Key)
5. Restrict the API key to your domains/IPs for security

#### Step 2: Configure Laravel `.env`

Add to your `ISUlaravel/.env` file:

```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
```

#### Step 3: Configure Ionic Environment

Add to your `ISUionic/.env` file:

```env
VITE_GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
```

**Note:** Use the same API key for both Laravel and Ionic, or create separate keys for each platform.

#### Step 4: Configure Laravel services.php

Add to `ISUlaravel/config/services.php`:

```php
'google_maps' => [
    'api_key' => env('GOOGLE_MAPS_API_KEY'),
],
```

## Design Consistency

Both Laravel and Ionic implementations now share:

### Color Scheme
- **Primary Green:** `#2d8659` (ISU Green)
- **Dark Green:** `#1e5d3f`
- **Success (Available):** `#28a745`
- **Danger (Occupied):** `#dc3545`
- **Dark Background:** `#001829` to `#002a45` gradient

### UI Elements
- Green gradient toolbar with shadow
- Card-based layout with subtle shadows
- Rounded corners (12-16px radius)
- Consistent typography and spacing
- Icon-based navigation
- Hover effects and transitions

### Sidebar Navigation
Both applications have matching sidebar designs:
- Dark gradient background
- ISU Logo and branding
- Section dividers (Main, Management, Account)
- Active state highlighting with green gradient
- User profile section at bottom

## Usage

### Laravel Admin Panel

1. Navigate to Admin Panel → Venue Map
2. Select a date using the date picker
3. View the interactive map with venue markers
4. Click on markers to see detailed venue information
5. Check the legend for color coding

### Ionic Mobile App

1. Open the Ionic app
2. Tap the menu icon (☰) to open sidebar
3. Navigate to Management → Venue Map
4. Select a date using the date picker
5. View the interactive map
6. Tap markers to see venue details
7. View legend at the bottom of the screen

## Venue Data Requirements

For venues to appear on the map, they must have:
- `location` set to "Santiago Campus"
- Valid `map_coordinates` in format: "latitude,longitude"
- Optional: `photo` uploaded (will be displayed in popup)

Example coordinates:
```
map_coordinates: "16.72249174514112,121.53739618722382"
```

## API Response Format

### Request
```
GET /api/venues/map/data?date=2026-06-26
```

### Response
```json
{
  "venues": [
    {
      "id": 1,
      "name": "Main Hall",
      "location": "Santiago Campus",
      "capacity": 500,
      "map_coordinates": "16.72249174514112,121.53739618722382",
      "photo_url": "http://localhost:8000/storage/venues/photo.jpg",
      "is_available": true,
      "description": "Main event hall..."
    }
  ],
  "venue_availability": {
    "1": {
      "is_available": true,
      "is_currently_occupied": false,
      "reservations": [],
      "reservation_count": 0
    }
  },
  "selected_date": "2026-06-26"
}
```

## Troubleshooting

### Map Not Loading

1. **Check API Key:**
   - Verify Google Maps API key is correctly configured in both Laravel `.env` and Ionic `.env`
   - Ensure the API key has Maps JavaScript API enabled
   - Check browser console for API key errors

2. **Check Network:**
   - Verify the API endpoint is accessible: `http://your-domain/api/venues/map/data`
   - Check CORS settings if accessing from different domain

3. **Check Venue Data:**
   - Ensure venues have `location = 'Santiago Campus'`
   - Verify `map_coordinates` are in correct format
   - Check that coordinates are valid (not NaN)

### Markers Not Appearing

1. Check browser console for coordinate parsing errors
2. Verify venues have valid map_coordinates in database
3. Ensure coordinates are within visible map bounds

### Styling Issues

1. Clear browser cache
2. Rebuild Ionic app: `npm run build`
3. Check that theme variables are properly loaded

## Testing

### Laravel Testing

```bash
# Test API endpoint
curl http://localhost:8000/api/venues/map/data?date=2026-06-26

# Should return JSON with venues and availability
```

### Ionic Testing

```bash
# Navigate to Ionic directory
cd ISUionic

# Install dependencies (if not already done)
npm install

# Run development server
npm run dev

# Navigate to http://localhost:5173/venue-map
```

## Browser Compatibility

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support
- Mobile browsers: Full support

## Performance Considerations

- Google Maps script is loaded dynamically
- Markers are cleaned up when component unmounts
- Map bounds are calculated to show all venues
- Zoom levels are limited (15-17) for optimal campus view
- Images are lazy-loaded in popups

## Security Notes

- API key should be restricted to specific domains/IPs
- Consider implementing rate limiting for the API endpoint
- Photo URLs are served through Laravel's storage system
- No sensitive data is exposed in the API response

## Future Enhancements

Possible improvements:
- Add venue categories/filters
- Implement venue search on map
- Add directions/navigation to venues
- Show real-time occupancy updates
- Add venue amenities information
- Implement offline map caching
- Add multiple campus support

## Support

For issues or questions:
1. Check this documentation
2. Review Laravel logs: `ISUlaravel/storage/logs/`
3. Check browser console for errors
4. Verify API responses in Network tab

## Summary

The Venue Map feature is now fully integrated into both Laravel and Ionic applications with:
- ✅ Backend API endpoint
- ✅ Ionic view component
- ✅ Sidebar navigation
- ✅ Matching UI/UX design
- ✅ Google Maps integration
- ✅ Date filtering
- ✅ Availability status
- ✅ Responsive design
- ✅ Documentation

The implementation maintains consistency between both platforms while respecting their respective design patterns and best practices.