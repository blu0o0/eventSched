@extends('admin.layouts.app')

@section('title', 'Venue Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-building"></i> {{ $venue->name }}</h1>
        <a href="{{ route('admin.venues.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="{{ route('admin.venues.edit', $venue) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card mb-3">
            @if($venue->photo_url)
                <img src="{{ $venue->photo_url }}" class="card-img-top" alt="{{ $venue->name }}" style="max-height: 400px; object-fit: cover;">
            @else
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                    <div class="text-center text-muted">
                        <i class="bi bi-image" style="font-size: 4rem;"></i>
                        <p class="mt-2">No photo available</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header">
                <h5>Venue Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> {{ $venue->name }}</p>
                <p><strong>Location:</strong> {{ $venue->location }}</p>
                <p><strong>Max Occupancy:</strong> {{ $venue->capacity }}</p>
                <p><strong>Status:</strong> 
                    @if($venue->status === 'available')
                        <span class="badge bg-success">Available</span>
                    @elseif($venue->status === 'damaged')
                        <span class="badge bg-danger">Damaged</span>
                    @elseif($venue->status === 'under_construction')
                        <span class="badge bg-warning text-dark">Under Construction</span>
                    @endif
                </p>
                @if($venue->isUnavailable() && $venue->unavailable_until)
                    <p><strong>Unavailable Until:</strong> {{ $venue->unavailable_until->format('F d, Y') }}</p>
                    <p><strong>Days Until Available:</strong> {{ $venue->days_until_available }} day(s)</p>
                @endif
                <p><strong>Description:</strong> {{ $venue->description ?? 'N/A' }}</p>
                @if($venue->map_coordinates)
                    <p><strong>Map Coordinates:</strong> {{ $venue->map_coordinates }}</p>
                @endif
            </div>
        </div>
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
                            @forelse($venue->reservations as $reservation)
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

