@extends('admin.layouts.app')

@section('title', 'Venue Map')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-map"></i> Venue Map</h1>
        <form method="GET" class="d-inline">
            <div class="input-group mb-3" style="max-width: 300px;">
                <label class="input-group-text" for="date">Select Date:</label>
                <input type="date" name="date" id="date" value="{{ $selectedDate }}" class="form-control" onchange="this.form.submit()">
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div id="venueMap" style="height: 600px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Legend</h5>
            </div>
            <div class="card-body">
                <div class="d-flex gap-3">
                    <div>
                        <span class="badge bg-success" style="width: 20px; height: 20px; display: inline-block;"></span>
                        <span class="ms-2">Available</span>
                    </div>
                    <div>
                        <span class="badge bg-danger" style="width: 20px; height: 20px; display: inline-block;"></span>
                        <span class="ms-2">Occupied</span>
                    </div>
                    <div>
                        <span class="badge bg-secondary" style="width: 20px; height: 20px; display: inline-block;"></span>
                        <span class="ms-2">No Coordinates</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Google Maps API -->
<!-- 
    IMPORTANT: Add your Google Maps API Key to your .env file:
    GOOGLE_MAPS_API_KEY=your_api_key_here
    
    Then add it to config/services.php:
    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],
    
    Get your API key from: https://console.cloud.google.com/google/maps-apis
    Make sure to enable "Maps JavaScript API" in your Google Cloud Console
-->
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key', env('GOOGLE_MAPS_API_KEY')) }}&libraries=geometry"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Google Map (default center - Santiago Campus)
    var map = new google.maps.Map(document.getElementById('venueMap'), {
        center: { lat: 16.72249174514112, lng: 121.53739618722382 },
        zoom: 16,
        mapTypeId: 'satellite'
    });

    // Draw campus boundary polygon
    var campusBoundary = new google.maps.Polygon({
        paths: [
            { lat: 16.72287, lng: 121.53544 },
            { lat: 16.72213, lng: 121.53542 },
            { lat: 16.72214, lng: 121.53735 },
            { lat: 16.72280, lng: 121.53742 }
        ],
        strokeColor: '#FF0000',
        strokeOpacity: 0.8,
        strokeWeight: 3,
        fillColor: '#FF0000',
        fillOpacity: 0.15
    });
    campusBoundary.setMap(map);

    // Venue data from Laravel
    var venues = @json($venues);
    var venueAvailability = @json($venueAvailability);
    var selectedDate = @json($selectedDate);
    var isAdministrator = {{ Auth::user()->isAdministrator() ? 'true' : 'false' }};
    var isOsas = {{ Auth::user()->isOsas() ? 'true' : 'false' }};

    // Array to store all markers for bounds calculation
    var markers = [];
    
    // Create bounds that include campus boundary
    var bounds = new google.maps.LatLngBounds();
    campusBoundary.getPath().forEach(function(latLng) {
        bounds.extend(latLng);
    });

    // Process each venue
    venues.forEach(function(venue) {
        var availability = venueAvailability[venue.id];
        var isAvailable = availability.is_available;
        var reservations = availability.reservations;

        // Parse coordinates
        var coordinates = null;
        if (venue.map_coordinates) {
            var coords = venue.map_coordinates.split(',');
            if (coords.length === 2) {
                coordinates = {
                    lat: parseFloat(coords[0].trim()),
                    lng: parseFloat(coords[1].trim())
                };
            }
        }

        // Skip venues without coordinates
        if (!coordinates || isNaN(coordinates.lat) || isNaN(coordinates.lng)) {
            console.warn('Venue ' + venue.name + ' has invalid coordinates: ' + venue.map_coordinates);
            return;
        }

        // Determine marker color based on availability
        var iconColor = isAvailable ? '#28a745' : '#dc3545';

        // Build popup content (InfoWindow content)
        var statusText = isAvailable ? 'Available' : 'Occupied';
        var statusBadge = '<span class="badge bg-' + (isAvailable ? 'success' : 'danger') + '">' + statusText + '</span>';
        
        if (availability.is_currently_occupied) {
            statusBadge += ' <span class="badge bg-warning text-dark">Currently In Use</span>';
        }
        
        if (!isAvailable && availability.reservation_count > 0) {
            statusBadge += ' <span class="badge bg-info">' + availability.reservation_count + ' Reservation(s)</span>';
        }

        var popupContent = '<div style="min-width: 250px; max-width: 350px; padding: 5px;">' +
            '<h6 style="margin: 0 0 10px 0; font-weight: bold; color: #333;">' + venue.name + '</h6>';
        
        // Add venue photo if available
        if (venue.photo_url) {
            popupContent += '<div style="margin-bottom: 10px;">' +
                '<img src="' + venue.photo_url + '" alt="' + venue.name + '" style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6;">' +
                '</div>';
        }
        
        popupContent += '<p style="margin: 5px 0;"><strong>Status:</strong> ' + statusBadge + '</p>' +
            '<p style="margin: 5px 0;"><strong>Location:</strong> ' + venue.location + '</p>' +
            '<p style="margin: 5px 0;"><strong>Max Occupancy:</strong> ' + venue.capacity + ' people</p>' +
            '<p style="margin: 5px 0; color: #666; font-size: 0.9em;"><strong>Date:</strong> ' + selectedDate + '</p>';

        if (venue.description) {
            popupContent += '<p style="margin: 5px 0; color: #666; font-size: 0.9em;">' + 
                (venue.description.length > 100 ? venue.description.substring(0, 100) + '...' : venue.description) + 
                '</p>';
        }

        if (!isAvailable && reservations.length > 0) {
            popupContent += '<div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #dee2e6;">' +
                '<strong style="font-size: 0.95em;">Reservations for this date:</strong>' +
                '<ul style="margin: 8px 0 0 0; padding-left: 20px; font-size: 0.9em;">';
            reservations.forEach(function(reservation) {
                if (isAdministrator || isOsas) {
                popupContent += '<li style="margin: 5px 0;">' +
                    '<a href="/admin/reservations/' + reservation.id + '" target="_blank" style="text-decoration: none;">' +
                    '<strong>' + reservation.title + '</strong></a><br>' +
                    '<small style="color: #666;">' + reservation.start_time + ' - ' + reservation.end_time + '</small>' +
                    '</li>';
                } else {
                    popupContent += '<li style="margin: 5px 0;">' +
                        '<strong>' + reservation.title + '</strong><br>' +
                        '<small style="color: #666;">' + reservation.start_time + ' - ' + reservation.end_time + '</small>' +
                        '</li>';
                }
            });
            popupContent += '</ul></div>';
        }

        if (isAdministrator) {
        popupContent += '<div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #dee2e6;">' +
            '<a href="/admin/venues/' + venue.id + '" class="btn btn-sm btn-primary" target="_blank" style="text-decoration: none; display: inline-block; padding: 5px 10px;">View Details</a>' +
            '</div></div>';
        } else {
            popupContent += '</div>';
        }

        // Create custom marker icon
        var markerIcon = {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 15,
            fillColor: iconColor,
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 3,
            anchor: new google.maps.Point(0, 0)
        };

        // Create marker
        var marker = new google.maps.Marker({
            position: coordinates,
            map: map,
            icon: markerIcon,
            title: venue.name
        });

        // Create InfoWindow
        var infoWindow = new google.maps.InfoWindow({
            content: popupContent
        });

        // Add click listener to marker
        marker.addListener('click', function() {
            infoWindow.open(map, marker);
    });

        markers.push(marker);
        bounds.extend(coordinates);
    });

    // Fit map to show all markers and campus boundary
    // Bounds already include campus boundary, now includes markers too
    map.fitBounds(bounds);
    
    // Limit maximum zoom to show campus area (not too zoomed in)
    google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
        if (map.getZoom() > 17) {
            map.setZoom(17);
        }
        // Ensure minimum zoom to show campus context
        if (map.getZoom() < 15) {
            map.setZoom(15);
    }
    });
});
</script>

<style>
#venueMap {
    border-radius: 8px;
    border: 1px solid #dee2e6;
}
</style>
@endsection

