@extends('admin.layouts.app')

@section('title', 'Area Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><i class="bi bi-grid"></i> Area Details</h1>
            <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                @if($area->photo_url)
                    <img src="{{ $area->photo_url }}" alt="{{ $area->name }}" class="img-fluid rounded" style="width: 100%; max-height: 400px; object-fit: cover;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 400px;">
                        <div class="text-center">
                            <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-2">No photo available</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ $area->name }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Venue:</strong>
                    </div>
                    <div class="col-sm-8">
                        @if($area->venue)
                            {{ $area->venue->name }} <small class="text-muted">({{ $area->venue->location }})</small>
                        @else
                            <span class="text-muted">No venue assigned</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Status:</strong>
                    </div>
                    <div class="col-sm-8">
                        @if($area->status === 'available')
                            <span class="badge bg-success">Available</span>
                        @elseif($area->status === 'occupied')
                            <span class="badge bg-warning text-dark">Occupied</span>
                        @elseif($area->status === 'not_available')
                            <span class="badge bg-danger">Not Available</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Created:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $area->created_at->format('M d, Y h:i A') }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Last Updated:</strong>
                    </div>
                    <div class="col-sm-8">
                        {{ $area->updated_at->format('M d, Y h:i A') }}
                    </div>
                </div>
                @if($area->venue)
                <div class="row">
                    <div class="col-12">
                        <a href="{{ route('admin.venues.show', $area->venue) }}" class="btn btn-info">
                            <i class="bi bi-building"></i> View Venue Details
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($area->reservations && $area->reservations->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Reservations in this Area</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Reserved By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($area->reservations as $reservation)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.reservations.show', $reservation) }}">
                                                {{ $reservation->title }}
                                            </a>
                                        </td>
                                        <td>{{ $reservation->date->format('M d, Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('g:i A') }}</td>
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
                                        <td>{{ $reservation->user->name }}</td>
                                        <td>
                                            <a href="{{ route('admin.reservations.show', $reservation) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection
