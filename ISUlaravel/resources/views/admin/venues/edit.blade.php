@extends('admin.layouts.app')

@section('title', 'Edit Venue')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('admin.venues.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.venues.update', $venue) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Name *</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $venue->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="capacity" class="form-label">Max Occupancy *</label>
                <input type="number" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity', $venue->capacity) }}" min="1" required>
                @error('capacity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $venue->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="location" class="form-label">Location *</label>
                    <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location', $venue->location) }}" required>
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="map_coordinates" class="form-label">Map Coordinates</label>
                    <div class="input-group">
                        <input type="text" class="form-control @error('map_coordinates') is-invalid @enderror" id="map_coordinates" name="map_coordinates" value="{{ old('map_coordinates', $venue->map_coordinates) }}" placeholder="e.g., 16.72249174514112, 121.53739618722382" readonly>
                        <button type="button" class="btn btn-outline-secondary" id="clearCoordinates">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                    <small class="form-text text-muted">Click on the map below to select coordinates</small>
                    @error('map_coordinates')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="mb-3">
                <div id="coordinatePickerMap" style="height: 400px; width: 100%; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
            </div>
            <div class="mb-3">
                <label>Location Preview</label>
                <div id="staticMapPreviewDiv" style="display: none; margin-top: 10px;">
                    <img id="staticMapPreview" src="" alt="Location Preview" class="img-thumbnail" style="max-width: 100%; height: 300px; object-fit: cover;">
                    <input type="hidden" name="photo" id="static_map_url" value="{{ $venue->photo ?? '' }}">
                </div>
                <small class="form-text text-muted">Click on the map to generate a satellite preview of the location</small>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status *</label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="available" {{ old('status', $venue->status) === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="damaged" {{ old('status', $venue->status) === 'damaged' ? 'selected' : '' }}>Damaged</option>
                    <option value="under_construction" {{ old('status', $venue->status) === 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3" id="unavailableUntilGroup" style="display: none;">
                <label for="unavailable_until" class="form-label">Unavailable Until *</label>
                <input type="date" class="form-control @error('unavailable_until') is-invalid @enderror" id="unavailable_until" name="unavailable_until" value="{{ old('unavailable_until', $venue->unavailable_until ? $venue->unavailable_until->format('Y-m-d') : '') }}" min="{{ date('Y-m-d') }}">
                <small class="form-text text-muted">Select the date when the venue will be available again</small>
                @error('unavailable_until')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Update Venue
            </button>
        </form>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key', env('GOOGLE_MAPS_API_KEY')) }}&libraries=places"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Google Places Autocomplete for location field
    const locationInput = document.getElementById('location');
    if (locationInput) {
        const autocomplete = new google.maps.places.Autocomplete(locationInput, {
            types: ['establishment', 'geocode'],
            componentRestrictions: { country: 'PH' }
        });
        autocomplete.setFields(['formatted_address', 'geometry', 'name', 'photos']);
        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (place.geometry && place.geometry.location) {
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();
                const coordinates = lat + ', ' + lng;
                
                // Update map coordinates
                const coordinateInput = document.getElementById('map_coordinates');
                if (coordinateInput) {
                    coordinateInput.value = coordinates;
                }
                
                // Pan map to the selected location
                map.panTo(place.geometry.location);
                map.setZoom(18);
                
                // Update or create marker at the selected location
                const position = place.geometry.location;
                if (marker) {
                    marker.setPosition(position);
                } else {
                    marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        draggable: true,
                        title: 'Venue Location'
                    });
                    attachMarkerDragListener(marker);
                }

                // Fetch and display place photo
                if (place.photos && place.photos.length > 0) {
                    const photoUrl = place.photos[0].getUrl({ maxWidth: 800, maxHeight: 600 });
                    const staticMapInput = document.getElementById('static_map_url');
                    if (staticMapInput) {
                        staticMapInput.value = photoUrl;
                    }
                    const previewImg = document.getElementById('staticMapPreview');
                    const previewDiv = document.getElementById('staticMapPreviewDiv');
                    if (previewImg && previewDiv) {
                        previewImg.src = photoUrl;
                        previewDiv.style.display = 'block';
                    }
                }
            }
        });
    }

    // Handle status change to show/hide unavailable_until field
    const statusSelect = document.getElementById('status');
    const unavailableUntilGroup = document.getElementById('unavailableUntilGroup');
    const unavailableUntilInput = document.getElementById('unavailable_until');

    function toggleUnavailableUntil() {
        const status = statusSelect.value;
        if (status === 'damaged' || status === 'under_construction') {
            unavailableUntilGroup.style.display = 'block';
            unavailableUntilInput.setAttribute('required', 'required');
        } else {
            unavailableUntilGroup.style.display = 'none';
            unavailableUntilInput.removeAttribute('required');
        }
    }

    statusSelect.addEventListener('change', toggleUnavailableUntil);
    toggleUnavailableUntil(); // Initialize on page load

    // Initialize Google Maps coordinate picker
    const coordinateInput = document.getElementById('map_coordinates');
    const clearBtn = document.getElementById('clearCoordinates');
    const mapDiv = document.getElementById('coordinatePickerMap');
    
    // Default center: Santiago Campus
    const defaultCenter = { lat: 16.72249174514112, lng: 121.53739618722382 };
    
    // Get existing coordinates from venue or old input
    const existingCoords = coordinateInput.value.trim();
    let initialCenter = defaultCenter;
    let marker = null;
    
    if (existingCoords) {
        const coords = existingCoords.split(',');
        if (coords.length === 2) {
            const lat = parseFloat(coords[0].trim());
            const lng = parseFloat(coords[1].trim());
            if (!isNaN(lat) && !isNaN(lng)) {
                initialCenter = { lat: lat, lng: lng };
            }
        }
    }
    
    // Initialize map
    const map = new google.maps.Map(mapDiv, {
        center: initialCenter,
        zoom: 16,
        mapTypeId: 'satellite'
    });
    
    // Create marker if coordinates exist
    if (existingCoords && initialCenter !== defaultCenter) {
        marker = new google.maps.Marker({
            position: initialCenter,
            map: map,
            draggable: true,
            title: 'Venue Location'
        });
    }
    
    // Function to update marker drag listener
    function attachMarkerDragListener(marker) {
        marker.addListener('dragend', function(event) {
            const lat = event.latLng.lat();
            const lng = event.latLng.lng();
            coordinateInput.value = lat + ', ' + lng;
        });
    }
    
    // Add click listener to map
    map.addListener('click', function(event) {
        const lat = event.latLng.lat();
        const lng = event.latLng.lng();
        const coordinates = lat + ', ' + lng;
        
        coordinateInput.value = coordinates;
        
        // Update or create marker
        if (marker) {
            marker.setPosition(event.latLng);
        } else {
            marker = new google.maps.Marker({
                position: event.latLng,
                map: map,
                draggable: true,
                title: 'Venue Location'
            });
            attachMarkerDragListener(marker);
        }
    });
    
    // Update coordinates when marker is dragged (if marker exists on load)
    if (marker) {
        attachMarkerDragListener(marker);
    }
    
    // Clear coordinates button
    clearBtn.addEventListener('click', function() {
        coordinateInput.value = '';
        if (marker) {
            marker.setMap(null);
            marker = null;
        }
        map.setCenter(defaultCenter);
    });

    // Update static map when marker is placed or moved (fallback if no place photo)
    map.addListener('click', function(event) {
        const lat = event.latLng.lat();
        const lng = event.latLng.lng();
        const coordinates = lat + ', ' + lng;
        coordinateInput.value = coordinates;
        
        // Update or create marker
        if (marker) {
            marker.setPosition(event.latLng);
        } else {
            marker = new google.maps.Marker({
                position: event.latLng,
                map: map,
                draggable: true,
                title: 'Venue Location'
            });
            attachMarkerDragListener(marker);
        }
    });

    if (marker) {
        const pos = marker.getPosition();
        coordinateInput.value = pos.lat() + ', ' + pos.lng();
    }
});
</script>
@endsection

