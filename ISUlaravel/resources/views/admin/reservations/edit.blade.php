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