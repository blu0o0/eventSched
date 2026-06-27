@extends('admin.layouts.app')

@section('title', 'Create Area')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-grid"></i> Create Area</h1>
        <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.areas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Area Name *</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="venue_id" class="form-label">Venue *</label>
                <select class="form-select @error('venue_id') is-invalid @enderror" id="venue_id" name="venue_id" required>
                    <option value="">Select a venue</option>
                    @foreach($venues as $venue)
                        <option value="{{ $venue->id }}" {{ old('venue_id') == $venue->id ? 'selected' : '' }}>
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
                <select class="form-select" id="status" name="status" required>
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="not_available">Not Available</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="photo" class="form-label">Area Photo</label>
                <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                <small class="form-text text-muted">Upload a photo of the area (Max: 5MB, Formats: JPEG, PNG, JPG, GIF, WEBP)</small>
                @error('photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div id="photoPreview" class="mt-3" style="display: none;">
                    <img id="photoPreviewImg" src="" alt="Photo Preview" class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Create Area
            </button>
        </form>
    </div>
</div>

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
});
</script>
@endsection