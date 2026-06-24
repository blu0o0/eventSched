@extends('admin.layouts.app')

@section('title', 'Create Venue')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-building"></i> Create Venue</h1>
        <a href="{{ route('admin.venues.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.venues.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Name *</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location *</label>
                <input type="text" class="form-control" value="Santiago Campus" readonly style="cursor: default; background-color: #e9ecef; pointer-events: none;">
                <input type="hidden" name="location" value="Santiago Campus">
                @error('location')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="capacity" class="form-label">Max Occupancy *</label>
                <input type="number" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity') }}" min="1" required>
                @error('capacity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="map_coordinates" class="form-label">Map Coordinates</label>
                <div class="input-group mb-2">
                    <input type="text" class="form-control @error('map_coordinates') is-invalid @enderror" id="map_coordinates" name="map_coordinates" value="{{ old('map_coordinates') }}" placeholder="e.g., 16.72249174514112, 121.53739618722382" readonly>
                    <button type="button" class="btn btn-outline-secondary" id="clearCoordinates">
                        <i class="bi bi-x-circle"></i> Clear
                    </button>
                </div>
                <small class="form-text text-muted">Click on the map below to select coordinates</small>
                @error('map_coordinates')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div id="coordinatePickerMap" style="height: 400px; width: 100%; margin-top: 10px; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
            </div>
            <div class="mb-3">
                <label for="photo" class="form-label">Venue Photo</label>
                <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                <small class="form-text text-muted">Upload a photo of the venue (Max: 5MB, Formats: JPEG, PNG, JPG, GIF, WEBP)</small>
                @error('photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div id="photoPreview" class="mt-3" style="display: none;">
                    <img id="photoPreviewImg" src="" alt="Photo Preview" class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                </div>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status *</label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="available" {{ old('status', 'available') === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="damaged" {{ old('status') === 'damaged' ? 'selected' : '' }}>Damaged</option>
                    <option value="under_construction" {{ old('status') === 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3" id="unavailableUntilGroup" style="display: none;">
                <label for="unavailable_until" class="form-label">Unavailable Until *</label>
                <input type="date" class="form-control @error('unavailable_until') is-invalid @enderror" id="unavailable_until" name="unavailable_until" value="{{ old('unavailable_until') }}" min="{{ date('Y-m-d') }}">
                <small class="form-text text-muted">Select the date when the venue will be available again</small>
                @error('unavailable_until')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Create Venue
            </button>
        </form>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key', env('GOOGLE_MAPS_API_KEY')) }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photoPreview');
    const photoPreviewImg = document.getElementById('photoPreviewImg');

    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreviewImg.src = e.target.result;
                photoPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            photoPreview.style.display = 'none';
        }
    });

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
            unavailableUntilInput.value = '';
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
    
    // Initialize map
    const map = new google.maps.Map(mapDiv, {
        center: defaultCenter,
        zoom: 16,
        mapTypeId: 'satellite'
    });
    
    let marker = null;
    
    // Check if there's an existing coordinate value
    const existingCoords = coordinateInput.value.trim();
    if (existingCoords) {
        const coords = existingCoords.split(',');
        if (coords.length === 2) {
            const lat = parseFloat(coords[0].trim());
            const lng = parseFloat(coords[1].trim());
            if (!isNaN(lat) && !isNaN(lng)) {
                const position = { lat: lat, lng: lng };
                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    draggable: true,
                    title: 'Venue Location'
                });
                map.setCenter(position);
            }
        }
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
});
</script>
@endsection

