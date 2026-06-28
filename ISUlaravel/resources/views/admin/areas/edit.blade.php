@extends('admin.layouts.app')

@section('title', 'Edit Area')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.areas.update', $area) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Area Name *</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $area->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="venue_id" class="form-label">Venue *</label>
                <select class="form-select @error('venue_id') is-invalid @enderror" id="venue_id" name="venue_id" required>
                    <option value="">Select a venue</option>
                    @foreach($venues as $venue)
                        <option value="{{ $venue->id }}" {{ old('venue_id', $area->venue_id) == $venue->id ? 'selected' : '' }}>
                            {{ $venue->name }}
                        </option>
                    @endforeach
                </select>
                @error('venue_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="available" {{ old('status', $area->status) == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="occupied" {{ old('status', $area->status) == 'occupied' ? 'selected' : '' }}>Occupied</option>
                    <option value="not_available" {{ old('status', $area->status) == 'not_available' ? 'selected' : '' }}>Not Available</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label>Area Photo</label>
                <div class="card mb-3" style="max-width: 400px;">
                    @if($area->photo_url)
                        <img src="{{ $area->photo_url }}" alt="{{ $area->name }}" class="card-img-top" id="currentPhoto" style="aspect-ratio: 1/1; object-fit: cover;">
                    @endif
                    <div id="photoPreview" class="card-body" style="display: none; padding: 0;">
                        <img id="photoPreviewImg" src="" alt="New Photo Preview" class="card-img-top" style="aspect-ratio: 1/1; object-fit: cover;">
                    </div>
                    <div id="photoPlaceholder" class="card-body bg-light d-flex align-items-center justify-content-center" style="aspect-ratio: 1/1;">
                        @if(!$area->photo_url)
                            <div class="text-center text-muted">
                                <i class="bi bi-image" style="font-size: 4rem;"></i>
                                <p class="mt-2">No photo selected</p>
                            </div>
                        @endif
                    </div>
                </div>
                @if($area->photo_url)
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="clear_photo" name="clear_photo" value="1">
                            <label class="form-check-label" for="clear_photo">
                                Remove current photo
                            </label>
                        </div>
                    </div>
                @endif
                <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                <small class="form-text text-muted">Upload a photo of the area (Max: 5MB, Formats: JPEG, PNG, JPG, GIF, WEBP)</small>
                @error('photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Update Area
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photoPreview');
    const photoPreviewImg = document.getElementById('photoPreviewImg');
    const photoPlaceholder = document.getElementById('photoPlaceholder');
    const currentPhoto = document.getElementById('currentPhoto');

    const clearPhotoCheckbox = document.getElementById('clear_photo');
    
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Hide current photo, show preview
                if (currentPhoto) currentPhoto.style.display = 'none';
                photoPreviewImg.src = e.target.result;
                photoPreview.style.display = 'block';
                photoPlaceholder.style.display = 'none';
                // Uncheck clear photo checkbox when new photo is selected
                if (clearPhotoCheckbox) clearPhotoCheckbox.checked = false;
            };
            reader.readAsDataURL(file);
        } else {
            // If no file selected, show current photo or placeholder
            if (currentPhoto && currentPhoto.src && !clearPhotoCheckbox?.checked) {
                currentPhoto.style.display = 'block';
                photoPreview.style.display = 'none';
            } else {
                currentPhoto.style.display = 'none';
                photoPreview.style.display = 'none';
                photoPlaceholder.style.display = 'flex';
            }
        }
    });
    
    // Handle clear photo checkbox
    if (clearPhotoCheckbox) {
        clearPhotoCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Hide current photo and preview
                if (currentPhoto) currentPhoto.style.display = 'none';
                photoPreview.style.display = 'none';
                photoPlaceholder.style.display = 'flex';
                // Clear the file input
                photoInput.value = '';
            } else {
                // Show current photo again
                if (currentPhoto && currentPhoto.src) {
                    currentPhoto.style.display = 'block';
                    photoPlaceholder.style.display = 'none';
                }
            }
        });
    }
});
</script>
@endsection