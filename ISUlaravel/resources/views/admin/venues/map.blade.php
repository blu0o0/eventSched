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
            
            <!-- Map Controls -->
            <div style="position: absolute; top: 16px; right: 16px; z-index: 1000; display: flex; flex-direction: column; gap: 8px;">
                <button onclick="getMyLocation()" title="My Location" style="background: white; border: none; border-radius: 8px; padding: 10px 14px; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: #1f2937; transition: all 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#23754c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/></svg>
                    My Location
                </button>
                <button onclick="toggleDirections()" title="Directions" style="background: white; border: none; border-radius: 8px; padding: 10px 14px; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: #1f2937; transition: all 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='white'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#23754c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                    Directions
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key', env('GOOGLE_MAPS_API_KEY')) }}&libraries=geometry"></script>

<script>
var map = null;
var markers = [];
var infoWindows = [];
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
        
        popupContent += '<p class="popup-info"><strong>Location:</strong> ' + venue.location + '</p>';

        // Add reservations table if there are reservations
        var allReservations = availability.all_reservations || [];
        if (allReservations.length > 0) {
            popupContent += '<div class="reservations-table-container">' +
                '<table class="reservations-table">' +
                '<thead>' +
                '<tr>' +
                '<th>Name</th>' +
                '<th>Status</th>' +
                '<th>Date</th>' +
                '<th>Area</th>' +
                '</tr>' +
                '</thead>' +
                '<tbody>';
            
            allReservations.forEach(function(reservation) {
                var statusClass = reservation.status === 'approved' ? 'status-approved' : 
                                 reservation.status === 'pending' ? 'status-pending' : 'status-rejected';
                // Format date to "Jun 3" format
                var dateObj = new Date(reservation.date);
                var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                var formattedDate = months[dateObj.getMonth()] + ' ' + dateObj.getDate();
                
                var areaName = reservation.area ? reservation.area.name : 'N/A';
                var areaPhotoUrl = reservation.area ? reservation.area.photo_url : null;
                var areaCell = areaName;
                if (areaPhotoUrl) {
                    areaCell = '<a href="javascript:void(0)" onclick="event.stopPropagation(); showAreaPhoto(\'' + areaName.replace(/'/g, "\\'") + '\', \'' + areaPhotoUrl.replace(/'/g, "\\'") + '\')" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">' + areaName + '</a>';
                }
                
                popupContent += '<tr>' +
                    '<td><a href="/admin/reservations/' + reservation.id + '" target="_blank" style="color: #0d6efd; text-decoration: none; font-weight: 500;">' + reservation.title + '</a></td>' +
                    '<td><span class="status-badge ' + statusClass + '">' + reservation.status + '</span></td>' +
                    '<td>' + formattedDate + '</td>' +
                    '<td>' + areaCell + '</td>' +
                    '</tr>';
            });
            
            popupContent += '</tbody>' +
                '</table>' +
                '</div>';
        }

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
            // Close all other info windows
            infoWindows.forEach(iw => iw.close());
            // Open this info window
            infoWindow.open(map, marker);
        });

        markers.push(marker);
        infoWindows.push(infoWindow);
        bounds.extend(coordinates);
    });
    
    // Add click listener to map to close info windows
    map.addListener('click', function() {
        infoWindows.forEach(iw => iw.close());
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

// User location tracking
var userMarker = null;
var directionsRenderer = null;
var currentDirections = null;
var currentTravelMode = 'WALKING';
var directionsActive = false;
var selectedVenueCoords = null;

function getMyLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            
            // Remove existing user marker
            if (userMarker) {
                userMarker.setMap(null);
            }
            
            // Add user marker
            userMarker = new google.maps.Marker({
                position: pos,
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8,
                    fillColor: '#4285F4',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 3
                },
                title: 'Your Location'
            });
            
            map.panTo(pos);
            map.setZoom(17);
            
            // Show info window
            var infoWindow = new google.maps.InfoWindow({
                content: '<div class="map-popup"><h6 class="popup-title">Your Location</h6><p class="popup-info">Lat: ' + pos.lat.toFixed(6) + ', Lng: ' + pos.lng.toFixed(6) + '</p></div>'
            });
            infoWindow.open(map, userMarker);
            
        }, function() {
            alert('Error: The Geolocation service failed. Please enable location services.');
        });
    } else {
        alert('Error: Your browser doesn\'t support geolocation.');
    }
}

function toggleDirections() {
    if (directionsActive) {
        // Turn off directions
        if (directionsRenderer) {
            directionsRenderer.setMap(null);
            directionsRenderer = null;
        }
        currentDirections = null;
        directionsActive = false;
        updateDirectionsButton();
        return;
    }
    getDirections();
}

function getDirections(mode) {
    var travelMode = mode || currentTravelMode;
    var venueId = selectedVenueId || (venues.length > 0 ? venues[0].id : null);
    if (!venueId) {
        alert('Please select a venue first.');
        return;
    }
    
    var venue = venues.find(v => v.id === venueId);
    if (!venue || !venue.map_coordinates) {
        alert('Selected venue has no coordinates.');
        return;
    }
    
    var coords = venue.map_coordinates.split(',');
    if (coords.length !== 2) {
        alert('Invalid venue coordinates.');
        return;
    }
    
    var dest = {
        lat: parseFloat(coords[0].trim()),
        lng: parseFloat(coords[1].trim())
    };
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var origin = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            
            // Remove existing directions
            if (directionsRenderer) {
                directionsRenderer.setMap(null);
            }
            
            // Close all info windows
            infoWindows.forEach(iw => iw.close());
            
            // Create directions service and renderer
            var directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                map: map,
                suppressMarkers: false,
                preserveViewport: false
            });
            
            var request = {
                origin: origin,
                destination: dest,
                travelMode: travelMode
            };
            
            directionsService.route(request, function(result, status) {
                if (status === 'OK') {
                    directionsRenderer.setDirections(result);
                    currentDirections = result;
                    currentTravelMode = travelMode;
                    directionsActive = true;
                    updateDirectionsButton();
                    
                    // Show distance and duration
                    var route = result.routes[0];
                    if (route && route.legs && route.legs[0]) {
                        var leg = route.legs[0];
                        var modeNames = {
                            'WALKING': 'Walking',
                            'DRIVING': 'Driving',
                            'BICYCLING': 'Bicycling',
                            'TRANSIT': 'Transit'
                        };
                        var durationText = '';
                        if (leg.duration_in_traffic) {
                            durationText = leg.duration_in_traffic.text + ' (with traffic)';
                        } else {
                            durationText = leg.duration.text;
                        }
                        var modeName = modeNames[travelMode] || travelMode;
                        
                        // Create info window content with mode selector
                        var infoContent = '<div class="map-popup"><h6 class="popup-title">Directions to ' + venue.name + '</h6>';
                        infoContent += '<div class="dir-mode-selector" style="display: flex; gap: 4px; margin-bottom: 10px; flex-wrap: wrap;">';
                        var modes = {'WALKING': 'Walk', 'DRIVING': 'Drive', 'BICYCLING': 'Bike', 'TRANSIT': 'Bus'};
                        for (var m in modes) {
                            var active = m === travelMode ? ' style="background: #23754c; color: white; border-color: #23754c;"' : '';
                            infoContent += '<div onclick="event.stopPropagation(); getDirections(\'' + m + '\')" ' + active + ' style="cursor: pointer; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; border: 1px solid #d1d5db; transition: all 0.2s;">' + modes[m] + '</div>';
                        }
                        infoContent += '</div>';
                        infoContent += '<p class="popup-info"><strong>Distance:</strong> ' + leg.distance.text + '</p>';
                        infoContent += '<p class="popup-info"><strong>' + modeName + ' Time:</strong> ' + durationText + '</p>';
                        infoContent += '</div>';
                        
                        var infoWindow = new google.maps.InfoWindow({
                            content: infoContent
                        });
                        // Show info window at destination
                        var destMarker = new google.maps.Marker({
                            position: dest,
                            map: map,
                            visible: false
                        });
                        infoWindow.open(map, destMarker);
                    }
                } else {
                    alert('Directions request failed: ' + status + '. Please try a different mode of transportation.');
                }
            });
        }, function() {
            alert('Error: The Geolocation service failed. Please enable location services.');
        });
    } else {
        alert('Error: Your browser doesn\'t support geolocation.');
    }
}

function updateDirectionsButton() {
    var btn = document.querySelector('[onclick*="toggleDirections"]');
    if (btn) {
        if (directionsActive) {
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg> Hide Directions';
            btn.style.background = '#fef2f2';
            btn.title = 'Hide Directions';
        } else {
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#23754c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg> Directions';
            btn.style.background = 'white';
            btn.title = 'Show Directions';
        }
    }
}

// Show area photo modal
function showAreaPhoto(areaName, photoUrl) {
    var modal = document.createElement('div');
    modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 99999; display: flex; align-items: center; justify-content: center; cursor: pointer;';
    modal.onclick = function() { document.body.removeChild(modal); };
    
    var content = document.createElement('div');
    content.style.cssText = 'max-width: 90%; max-height: 90%; text-align: center;';
    
    var img = document.createElement('img');
    img.src = photoUrl;
    img.alt = areaName;
    img.style.cssText = 'max-width: 100%; max-height: 80vh; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.5);';
    
    var title = document.createElement('p');
    title.textContent = areaName;
    title.style.cssText = 'color: white; margin-top: 12px; font-size: 16px; font-weight: 600;';
    
    content.appendChild(img);
    content.appendChild(title);
    modal.appendChild(content);
    document.body.appendChild(modal);
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

/* Reservations Table Styles */
.reservations-table-container {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
    max-height: 300px;
    overflow-y: auto;
}

.reservations-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.reservations-table thead {
    background-color: #f8f9fa;
    position: sticky;
    top: 0;
}

.reservations-table th {
    padding: 8px;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #dee2e6;
}

.reservations-table td {
    padding: 6px 8px;
    border-bottom: 1px solid #e5e7eb;
}

.reservations-table tbody tr:hover {
    background-color: #f8f9fa;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
    text-transform: capitalize;
}

.status-approved {
    background-color: #d4edda;
    color: #155724;
}

.status-pending {
    background-color: #fff3cd;
    color: #856404;
}

.status-rejected {
    background-color: #f8d7da;
    color: #721c24;
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