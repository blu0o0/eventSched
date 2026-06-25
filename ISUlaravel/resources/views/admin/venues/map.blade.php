@extends('admin.layouts.app')

@section('title', 'Venue Map')

@section('content')
<div class="venue-map-container" style="padding: 0; height: calc(100vh - 60px);">
    <div class="row" style="height: 100%; margin: 0;">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-3" style="padding: 0.5; background: #f8f9fa; border-right: 1px solid #dee2e6; box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);">
            <div style="background: #367c48; color: white; padding: 16px;">
                <h5 style="margin: 0; font-size: 18px; font-weight: 600; color: #ffffff;">Venues</h5>
                <p style="margin: 4px 0 0 0; font-size: 12px; opacity: 0.9; color: #86efac;">{{ count($venues) }} venue(s) found</p>
            </div>
            
            <div style="padding: 20px; overflow-y: auto; height: calc(100% - 80px);">
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
                            <div class="status-indicator {{ $statusClass }}" style="width: 100%; height: 100%; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);"></div>
                        </div>
                        <div class="venue-info" style="flex: 1; min-width: 0;">
                            <h6 class="venue-name" style="font-size: 16px; font-weight: 600; color: #1f2937; margin: 0 0 4px 0;">{{ $venue->name }}</h6>
                            <p class="venue-location" style="font-size: 13px; color: #6b7280; margin: 0 0 8px 0;">
                                <i class="bi bi-geo-alt"></i> {{ $venue->location }}
                            </p>
                            <div class="venue-details" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                                <span class="venue-capacity" style="font-size: 12px; color: #4b5563;">
                                    <i class="bi bi-people"></i> {{ $venue->capacity }} people
                                </span>
                                <span class="venue-status {{ $statusClass }}" style="font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    {{ $statusText }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                @if(count($venues) == 0)
                    <div class="no-venues" style="text-align: center; padding: 32px 16px; color: #6b7280;">
                        <p>No venues available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Map -->
        <div class="col-md-9 col-lg-9" style="padding: 0; position: relative;">
            <div id="venueMap" style="height: 100%; width: 100%; background: #f5f5f5;"></div>
        </div>
    </div>
</div>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key', env('GOOGLE_MAPS_API_KEY')) }}&libraries=geometry"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectedVenueId = {{ $selectedVenueId ?? 'null' }};
    
    // Initialize Google Map
    var map = new google.maps.Map(document.getElementById('venueMap'), {
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
    var venues = @json($venues);
    var venueAvailability = @json($venueAvailability);
    var selectedDate = @json($selectedDate);
    var isAdministrator = {{ Auth::user()->isAdministrator() ? 'true' : 'false' }};
    var isOsas = {{ Auth::user()->isOsas() ? 'true' : 'false' }};

    var markers = [];
    var bounds = new google.maps.LatLngBounds();
    
    campusBoundary.getPath().forEach(function(latLng) {
        bounds.extend(latLng);
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

        var popupContent = '<div style="min-width: 250px; max-width: 350px; padding: 5px;">' +
            '<h6 style="margin: 0 0 10px 0; font-weight: bold; color: #333;">' + venue.name + '</h6>';
        
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
    border-left: 4px solid #0dfd45;
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

@media (max-width: 768px) {
    .venue-map-container {
        padding: 10px;
    }
    
    .row {
        height: auto;
    }
    
    div[style*="border-right"] {
        border-right: none !important;
        border-bottom: 1px solid #dee2e6;
    }
}
</style>
@endsection