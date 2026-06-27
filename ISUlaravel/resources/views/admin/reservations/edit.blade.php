@extends('admin.layouts.app')

@section('title', 'Edit Reservation')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-pencil"></i> Edit Reservation</h1>
        <a href="{{ route('admin.reservations.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Reservations
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.reservations.update', $reservation) }}">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" 
                           value="{{ old('title', $reservation->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="venue_id" class="form-label">Venue <span class="text-danger">*</span></label>
                    <select id="venue_id" name="venue_id" class="form-select @error('venue_id') is-invalid @enderror" required>
                        <option value="">Select Venue</option>
                        @foreach($venues as $venue)
                            <option value="{{ $venue->id }}" {{ old('venue_id', $reservation->venue_id) == $venue->id ? 'selected' : '' }}>
                                {{ $venue->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('venue_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="area_id" class="form-label">Area <span class="text-danger">*</span></label>
                    <select id="area_id" name="area_id" class="form-select @error('area_id') is-invalid @enderror">
                        <option value="">None</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" 
                                    data-venue-id="{{ $area->venue_id }}"
                                    {{ old('area_id', $reservation->area_id) == $area->id ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                        <option value="others" {{ old('area_id', $reservation->area_id) === null && $reservation->area_name && !$reservation->area ? 'selected' : '' }}>Others:</option>
                    </select>
                    @error('area_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text" id="area-helper-text">
                        @if(old('venue_id', $reservation->venue_id))
                            Select an area for this venue
                        @else
                            Please select a venue first
                        @endif
                    </div>
                </div>

                <!-- Custom Area Input (shown when "Others:" is selected) -->
                <div class="col-md-6 mb-3" id="custom-area-container" style="display: {{ old('area_id', $reservation->area_id) === null && $reservation->area_name && !$reservation->area ? 'block' : 'none' }};">
                    <label for="area_name" class="form-label">Enter Custom Area Name</label>
                    <input type="text" id="area_name" name="area_name" class="form-control @error('area_name') is-invalid @enderror" 
                           value="{{ old('area_name', $reservation->area_name) }}" 
                           placeholder="e.g., Room 101, Lab 2">
                    @error('area_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" id="date" name="date" class="form-control @error('date') is-invalid @enderror" 
                           value="{{ old('date', $reservation->date->format('Y-m-d')) }}" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                    <input type="time" id="start_time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                           value="{{ old('start_time', $reservation->start_time) }}" required>
                    @error('start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                    <input type="time" id="end_time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                           value="{{ old('end_time', $reservation->end_time) }}" required>
                    @error('end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="capacity" class="form-label">Expected Capacity <span class="text-danger">*</span></label>
                    <input type="number" id="capacity" name="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                           value="{{ old('capacity', $reservation->capacity) }}" min="1" required>
                    @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $reservation->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Reservation
                </button>
                <a href="{{ route('admin.reservations.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const venueSelect = document.getElementById('venue_id');
    const areaSelect = document.getElementById('area_id');
    const areaHelperText = document.getElementById('area-helper-text');
    const customAreaContainer = document.getElementById('custom-area-container');
    const areaNameInput = document.getElementById('area_name');

    // Store current reservation's area info for initial state
    const currentAreaId = '{{ $reservation->area_id }}';
    const currentAreaName = '{{ $reservation->area_name ? addslashes($reservation->area_name) : '' }}';
    const hasLinkedArea = currentAreaId !== '' && currentAreaId !== null;

    /**
     * Filter areas based on selected venue
     */
    function filterAreasByVenue() {
        const venueId = venueSelect.value;
        
        if (!venueId) {
            // No venue selected - disable area select
            areaSelect.disabled = true;
            areaSelect.value = '';
            areaHelperText.textContent = 'Please select a venue first';
            hideCustomArea();
            return;
        }

        areaSelect.disabled = false;
        areaHelperText.textContent = 'Select an area for this venue';

        // Get all area options (excluding "None" and "Others:")
        const areaOptions = areaSelect.querySelectorAll('option[data-venue-id]');
        
        let visibleAreaCount = 0;
        let currentValueMatch = false;

        areaOptions.forEach(function(option) {
            if (option.getAttribute('data-venue-id') === venueId) {
                option.style.display = '';
                visibleAreaCount++;
                // Check if current selected value is among visible options
                if (option.value === areaSelect.value) {
                    currentValueMatch = true;
                }
            } else {
                option.style.display = 'none';
            }
        });

        if (visibleAreaCount === 0) {
            areaHelperText.textContent = 'No areas available for this venue';
        }

        // If current selection is no longer valid (hidden), reset to "None"
        if (areaSelect.value && areaSelect.value !== 'others' && !currentValueMatch) {
            areaSelect.value = '';
            hideCustomArea();
        }

        // Toggle "Others:" visibility based on whether venue has areas
        const othersOption = areaSelect.querySelector('option[value="others"]');
        if (othersOption) {
            othersOption.style.display = visibleAreaCount > 0 ? '' : 'none';
        }
    }

    /**
     * Show/hide custom area input
     */
    function toggleCustomArea() {
        if (areaSelect.value === 'others') {
            customAreaContainer.style.display = 'block';
            areaNameInput.required = false; // Not strictly required but encouraged
            areaNameInput.focus();
        } else {
            hideCustomArea();
        }
    }

    function hideCustomArea() {
        customAreaContainer.style.display = 'none';
        areaNameInput.value = '';
        areaNameInput.required = false;
    }

    // Initial filter on page load
    filterAreasByVenue();

    // If the reservation has a custom area name (not linked to an area table record)
    // and "Others:" was the saved selection, ensure custom input is shown
    if (areaSelect.value === 'others') {
        customAreaContainer.style.display = 'block';
    }

    // Event listeners
    venueSelect.addEventListener('change', function() {
        filterAreasByVenue();
        // Reset area selection when venue changes (like Ionic does)
        if (!hasLinkedArea || venueSelect.value !== '{{ $reservation->venue_id }}') {
            areaSelect.value = '';
            hideCustomArea();
        }
    });

    areaSelect.addEventListener('change', toggleCustomArea);
});
</script>
@endpush