@extends('admin.layouts.app')

@section('title', 'Area Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-grid"></i> {{ $area->name }}</h1>
        <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('admin.areas.edit', $area) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            @if($area->photo_url)
                <img src="{{ $area->photo_url }}" class="card-img-top" alt="{{ $area->name }}" style="max-height: 400px; object-fit: cover;">
            @else
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                    <div class="text-center text-muted">
                        <i class="bi bi-image" style="font-size: 4rem;"></i>
                        <p class="mt-2">No photo available</p>
                    </div>
                </div>
            @endif
        </div>
        <div class="card">
            <div class="card-header">
                <h5>Area Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> {{ $area->name }}</p>
                <p><strong>Venue:</strong> 
                    @if($area->venue)
                        {{ $area->venue->name }} <small class="text-muted">({{ $area->venue->location }})</small>
                    @else
                        <span class="text-muted">No venue assigned</span>
                    @endif
                </p>
                <p><strong>Status:</strong> 
                    @if($area->status === 'available')
                        <span class="badge bg-success">Available</span>
                    @elseif($area->status === 'occupied')
                        <span class="badge bg-warning text-dark">Occupied</span>
                    @elseif($area->status === 'not_available')
                        <span class="badge bg-danger">Not Available</span>
                    @endif
                </p>
                <p><strong>Created:</strong> {{ $area->created_at->format('M d, Y h:i A') }}</p>
                <p><strong>Last Updated:</strong> {{ $area->updated_at->format('M d, Y h:i A') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Reservations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reservations as $reservation)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.reservations.show', $reservation) }}">
                                            {{ $reservation->title }}
                                        </a>
                                    </td>
                                    <td>{{ $reservation->date->format('M d, Y') }}</td>
                                    <td>
                                        @if($reservation->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($reservation->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($reservation->status === 'postponed')
                                            <span class="badge bg-info">Postponed</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No reservations</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
