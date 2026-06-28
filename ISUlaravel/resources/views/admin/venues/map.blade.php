@extends('admin.layouts.app')

@section('title', 'Venue Map')

@section('content')
<div style="display: flex; height: calc(100vh - 20px); width: 100%;">
    <!-- Sidebar -->
    <div style="width: 350px; min-width: 350px; height: 100%; overflow-y: auto; background: #f8f9fa; border-right: 1px solid #dee2e6; box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);">
        <div style="background: #23754c; color: white; padding: 16px;">
            <h5 style="margin: 0; font-size: 18px; font-weight: 600; color: #ffffff;">Venues</h5>
            <p style="margin: 4px 0 0 0; font-size: 12px; opacity: 0.9; color: #86efac;">{{ count($venues) }} venue(s) found</p>
        </div>
        
        <div style="padding: 20px;">
            @foreach($venues as $venue)
                @php
                    $availability = $venueAvailability[$venue->id] ?? null;
                    $statusClass = 'no-coords';
                    $statusText = 'No Data';
                    
                    if ($availability) {
                        if ($availability['is_available']) {
                            $statusClass = 'available';
                            $statusText = 'Available';
                        } elseif ($availability['is_currently_occupied']) {
                            $statusClass = 'occupied';
                            $statusText = 'In Use';
                        } else {
                            $statusClass = 'reserved';
                            $statusText = 'Reserved';
                        }
                    }
                @endphp
                
                <div class="venue-item {{ $selectedVenueId == $venue->id ? 'venue-selected' : '' }}" 
                     onclick="selectVenue({{ $venue->id }})"
                     style="background: white; padding: 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <div class="venue-avatar" style="width: 48px; height: 48px; flex-shrink: 0;">
                        @if($venue->photo_url)
                            <img src="{{ $venue->photo_url }}" alt="{{ $venue->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);" />
                        @else
                            <div class="status-indicator {{ $statusClass }}" style="width: 100%; height: 100%; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);"></div>
                        @endif
                    </div>
                    <div class="venue-info" style="flex: 1; min-width: 0;">
                        <h6 class="venue-name" style="font-size: 16px; font-weight: 600; color: #1f2937; margin: 0 0 4px 0;">{{ $venue->name }}</h6>
                        <div class="venue-details" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                            <span class="venue-reservations" style="font-size: 12px; color: #4b5563;">
                                <i class="bi bi-people"></i> {{ $availability['total_reservations'] ?? 0 }} reservations
                            </span>
                            <span class="venue-status {{ $statusClass }}" style="font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right chevron-icon" style="color: #6b7280; font-size: 20px;"></i>
                </div>
            @endforeach
            
            @if(count($venues) == 0)
                <div class="no-venues" style="text-align: center; padding: 32px 16px; color: #6b7280;">
                    <p>No venues available</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 1; display: flex; flex-direction: column; padding: 1rem; gap: 1rem; overflow-y: auto; height: 100%; position: relative;">
        <!-- Loading State -->
        @if(false)
        <div style="display: flex; align-items: center; justify-content: center; height: 100%;">
            <div>Loading venue map...</div>
        </div>
        @endif

        <!-- Map Container -->
        <div style="flex: 1; display: flex; flex-direction: column; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15); border: 1px solid #dee2e6; position: relative;">
            <div id="venueMap" style="height: 100%; min-height: 500px; width: 100%; background: #f5f5f5; border-radius: 12px;"></div>
        </div>
    </div>
</div>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key', env('GOOGLE_MAPS_API_KEY')) }}&libraries=geometry"></script>

<script>
var map = null;
var markers = [];
var venues = [];

document.addEventListener('DOMContentLoaded', function() {
    var selectedVenueId = {{ $selectedVenueId ?? 'null' }};
    
    // Initialize Google Map
    map = new google.maps.Map(document.getElementById('venueMap'), {
        center: { lat: 16.72249174514112, lng: 121.53739618722382 },
        zoom: 16,
        mapTypeId: 'satellite',
        zoomControl: true,
        zoomControlOptions: {
            position: google.maps.ControlPosition.RIGHT_CENTER
        }
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
    var venueAvailability = @json($venueAvailability);
    var selectedDate = @json($selectedDate);
    var isAdministrator = {{ Auth::user()->isAdministrator() ? 'true' : 'false' }};
    var isOsas = {{ Auth::user()->isOsas() ? 'true' : 'false' }};

    var bounds = new google.maps.LatLngBounds();
    
    campusBoundary.getPath().forEach(function(latLng) {
        bounds.extend(latLng);
    });

    venues = @json($venues);
    areas = @json($areas);
    reservations = @json($reservations);
    
    // Draw areas on map
    areas.forEach(function(area) {
        if (!area.map_coordinates) return;
        
        var coords = area.map_coordinates.split(',');
        if (coords.length !== 2) return;
        
        var position = {
            lat: parseFloat(coords[0].trim()),
            lng: parseFloat(coords[1].trim())
        };
        
        if (isNaN(position.lat) || isNaN(position.lng)) return;
        
        bounds.extend(position);
        
        // Create area marker (square shape)
        var areaMarkerIcon = {
            path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
            scale: 8,
            fillColor: '#9c27b0',
            fillOpacity: 0.9,
            strokeColor: '#ffffff',
            strokeWeight: 2,
            rotation: 45,
            anchor: new google.maps.Point(0, 0)
        };
        
        var areaMarker = new google.maps.Marker({
            position: position,
            map: map,
            icon: areaMarkerIcon,
            title: area.name + ' (' + (area.venue ? area.venue.name : 'No Venue') + ')',
            zIndex: 1000
        });
        
        // Area popup content
        var areaPopupContent = '<div class="map-popup">' +
            '<h6 class="popup-title">' + area.name + '</h6>' +
            '<p class="popup-info"><strong>Venue:</strong> ' + (area.venue ? area.venue.name : 'No venue assigned') + '</p>' +
            '<p class="popup-info"><strong>Status:</strong> ' + area.status.replace('_', ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); }) + '</p>';
            
        if (area.photo_url) {
            areaPopupContent += '<div class="popup-photo">' +
                '<img src="' + area.photo_url + '" alt="' + area.name + '" class="popup-image" />' +
                '</div>';
        }
        
        areaPopupContent += '</div>';
        
        var areaInfoWindow = new google.maps.InfoWindow({
            content: areaPopupContent
        });
        
        areaMarker.addListener('click', function() {
            areaInfoWindow.open(map, areaMarker);
        });
    });
    
    // Draw reservation markers on map
    reservations.forEach(function(reservation) {
        if (!reservation.area || !reservation.area.map_coordinates) return;
        
        var coords = reservation.area.map_coordinates.split(',');
        if (coords.length !== 2) return;
        
        var position = {
            lat: parseFloat(coords[0].trim()),
            lng: parseFloat(coords[1].trim())
        };
        
        if (isNaN(position.lat) || isNaN(position.lng)) return;
        
        bounds.extend(position);
        
        // Create reservation marker (diamond shape)
        var resMarkerIcon = {
            path: google.maps.SymbolPath.DIAMOND,
            scale: 10,
            fillColor: '#ff9800',
            fillOpacity: 0.9,
            strokeColor: '#ffffff',
            strokeWeight: 2,
            anchor: new google.maps.Point(0, 0)
        };
        
        var resMarker = new google.maps.Marker({
            position: position,
            map: map,
            icon: resMarkerIcon,
            title: reservation.title,
            zIndex: 2000
        });
        
        // Reservation popup content
        var resPopupContent = '<div class="map-popup">' +
            '<h6 class="popup-title">' + reservation.title + '</h6>' +
            '<p class="popup-info"><strong>Area:</strong> ' + (reservation.area ? reservation.area.name : 'N/A') + '</p>' +
            '<p class="popup-info"><strong>Venue:</strong> ' + (reservation.venue ? reservation.venue.name : 'N/A') + '</p>' +
            '<p class="popup-info"><strong>Date:</strong> ' + reservation.date + '</p>' +
            '<p class="popup-info"><strong>Time:</strong> ' + reservation.start_time + ' - ' + reservation.end_time + '</p>' +
            '<p class="popup-info"><strong>Status:</strong> <span class="badge bg-' + 
            (reservation.status === 'approved' ? 'success' : 'warning') + '">' + 
            reservation.status.charAt(0).toUpperCase() + reservation.status.slice(1) + '</span></p>' +
            '<p class="popup-info"><strong>User:</strong> ' + (reservation.user ? reservation.user.name : 'N/A') + '</p>' +
            '</div>';
        
        var resInfoWindow = new google.maps.InfoWindow({
            content: resPopupContent
        });
        
        resMarker.addListener('click', function() {
            resInfoWindow.open(map, resMarker);
        });
    });
    
    venues.forEach(function(venue) {
        var availability = venueAvailability[venue.id];
        var isAvailable = availability.is_available;
        var reservations = availability.reservations;

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

        if (!coordinates || isNaN(coordinates.lat) || isNaN(coordinates.lng)) {
            console.warn('Venue ' + venue.name + ' has invalid coordinates: ' + venue.map_coordinates);
            return;
        }

        var iconColor = isAvailable ? '#28a745' : '#dc3545';

        var statusText = isAvailable ? 'Available' : 'Occupied';
        var statusBadge = '<span class="badge bg-' + (isAvailable ? 'success' : 'danger') + '">' + statusText + '</span>';
        
        if (availability.is_currently_occupied) {
            statusBadge += ' <span class="badge bg-warning text-dark">Currently In Use</span>';
        }
        
        if (!isAvailable && availability.reservation_count > 0) {
            statusBadge += ' <span class="badge bg-info">' + availability.reservation_count + ' Reservation(s)</span>';
        }

        var popupContent = '<div class="map-popup">' +
            '<h6 class="popup-title">' + venue.name + '</h6>';
        
        if (venue.photo_url) {
            popupContent += '<div class="popup-photo">' +
                '<img src="' + venue.photo_url + '" alt="' + venue.name + '" class="popup-image" />' +
                '</div>';
        }
        
        popupContent += '<p class="popup-status"><strong>Status:</strong> ' + statusBadge + '</p>' +
            '<p class="popup-info"><strong>Location:</strong> ' + venue.location + '</p>' +
            '<p class="popup-info"><strong>Max Occupancy:</strong> ' + venue.capacity + ' people</p>' +
            '<p class="popup-date"><strong>Date:</strong> ' + selectedDate + '</p>';

        if (venue.description) {
            popupContent += '<p class="popup-description">' + 
                (venue.description.length > 100 ? venue.description.substring(0, 100) + '...' : venue.description) + 
                '</p>';
        }

        if (!isAvailable && reservations.length > 0) {
            popupContent += '<div class="popup-reservations">' +
                '<strong>Reservations for this date:</strong>' +
                '<ul class="reservation-list">';
            reservations.forEach(function(reservation) {
                if (isAdministrator || isOsas) {
                popupContent += '<li class="reservation-item">' +
                    '<a href="/admin/reservations/' + reservation.id + '" target="_blank" style="text-decoration: none;">' +
                    '<strong>' + reservation.title + '</strong></a><br>' +
                    '<small>' + reservation.start_time + ' - ' + reservation.end_time + '</small>' +
                    '</li>';
                } else {
                    popupContent += '<li class="reservation-item">' +
                        '<strong>' + reservation.title + '</strong><br>' +
                        '<small>' + reservation.start_time + ' - ' + reservation.end_time + '</small>' +
                        '</li>';
                }
            });
            popupContent += '</ul></div>';
        }

        if (isAdministrator) {
        popupContent += '<div class="popup-reservations">' +
            '<a href="/admin/venues/' + venue.id + '" class="btn btn-sm btn-primary" target="_blank" style="text-decoration: none; display: inline-block; padding: 5px 10px;">View Details</a>' +
            '</div></div>';
        } else {
            popupContent += '</div>';
        }

        var markerIcon = {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 15,
            fillColor: iconColor,
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 3,
            anchor: new google.maps.Point(0, 0)
        };

        var marker = new google.maps.Marker({
            position: coordinates,
            map: map,
            icon: markerIcon,
            title: venue.name
        });

        var infoWindow = new google.maps.InfoWindow({
            content: popupContent
        });

        marker.addListener('click', function() {
            infoWindow.open(map, marker);
        });

        markers.push(marker);
        bounds.extend(coordinates);
    });

    map.fitBounds(bounds);
    
    google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
        if (map.getZoom() > 17) {
            map.setZoom(17);
        }
        if (map.getZoom() < 15) {
            map.setZoom(15);
        }
    });
});

function selectVenue(venueId) {
    var venue = venues.find(v => v.id === venueId);
    if (venue && venue.map_coordinates) {
        var coords = venue.map_coordinates.split(',');
        if (coords.length === 2) {
            var lat = parseFloat(coords[0].trim());
            var lng = parseFloat(coords[1].trim());
            
            if (!isNaN(lat) && !isNaN(lng)) {
                var position = { lat: lat, lng: lng };
                map.panTo(position);
                map.setZoom(17);
                
                var marker = markers.find(m => {
                    var markerPos = m.getPosition();
                    return markerPos.lat() === lat && markerPos.lng() === lng;
                });
                
                if (marker) {
                    google.maps.event.trigger(marker, 'click');
                }
            }
        }
    }
}
</script>

<style>
.venue-item:hover {
    background: #f8f9fa !important;
    transform: translateX(4px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.venue-selected {
    background: #e3f2fd !important;
    border-left: 4px solid #0d6efd;
}

.status-indicator.available {
    background-color: #28a745;
}

.status-indicator.occupied {
    background-color: #dc3545;
}

.status-indicator.reserved {
    background-color: #ffc107;
}

.status-indicator.no-coords {
    background-color: #6c757d;
}

.venue-status.available {
    background-color: #d4edda;
    color: #155724;
}

.venue-status.occupied {
    background-color: #f8d7da;
    color: #721c24;
}

.venue-status.reserved {
    background-color: #fff3cd;
    color: #856404;
}

.chevron-icon {
    color: #6b7280;
    font-size: 20px;
}

/* Map Popup Styles */
.map-popup {
    padding: 8px;
    min-width: 250px;
    max-width: 350px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.popup-title {
    margin: 0 0 10px 0;
    font-weight: 700;
    color: #1f2937;
    font-size: 16px;
}

.popup-photo {
    margin-bottom: 10px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.popup-image {
    width: 100%;
    max-height: 200px;
    object-fit: cover;
    display: block;
}

.popup-status,
.popup-info,
.popup-date {
    margin: 6px 0;
    font-size: 14px;
    line-height: 1.5;
}

.popup-status strong,
.popup-info strong,
.popup-date strong {
    font-weight: 600;
    color: #374151;
}

.popup-description {
    margin: 8px 0;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.5;
}

.popup-reservations {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
    font-size: 14px;
}

.reservation-list {
    margin: 8px 0 0 0;
    padding-left: 20px;
    list-style-type: disc;
}

.reservation-item {
    margin: 6px 0;
    line-height: 1.5;
}

.reservation-item strong {
    font-weight: 600;
    color: #1f2937;
}

.reservation-item small {
    color: #6b7280;
    font-size: 12px;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.badge.bg-success {
    background-color: #10b981;
    color: white;
}

.badge.bg-danger {
    background-color: #ef4444;
    color: white;
}

@media (max-width: 768px) {
    div[style*="display: flex"] {
        flex-direction: column;
    }
    
    div[style*="width: 350px"] {
        width: 100% !important;
        min-width: 100% !important;
        height: auto;
        max-height: 40vh;
        border-right: none !important;
        border-bottom: 1px solid #dee2e6;
    }
    
    div[style*="min-height: 500px"] {
        min-height: 400px;
    }
}

@media (max-width: 480px) {
    div[style*="width: 350px"] {
        max-height: 35vh;
    }
    
    div[style*="min-height: 500px"] {
        min-height: 350px;
    }
}
</style>
@endsection