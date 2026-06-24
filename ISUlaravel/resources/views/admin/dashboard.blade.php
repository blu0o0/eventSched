@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
        <p class="text-muted">Welcome, {{ Auth::user()->name }}!</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-calendar-check"></i> Total Reservations</h5>
                <h2>{{ $stats['total_reservations'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-clock-history"></i> Pending</h5>
                <h2>{{ $stats['pending_reservations'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-check-circle"></i> Approved</h5>
                <h2>{{ $stats['approved_reservations'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-x-circle"></i> Rejected</h5>
                <h2>{{ $stats['rejected_reservations'] }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-building"></i> Total Venues</h5>
                <h2>{{ $stats['total_venues'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-exclamation-triangle text-danger"></i> Open Emergencies</h5>
                <h2>{{ $stats['open_emergencies'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-people"></i> Total Users</h5>
                <h2>{{ $stats['total_users'] }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-calendar-check"></i> Recent Reservations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Venue</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_reservations as $reservation)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.reservations.show', $reservation) }}">
                                            {{ $reservation->title }}
                                        </a>
                                    </td>
                                    <td>{{ $reservation->venue->name }}</td>
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
                                    <td>{{ $reservation->user->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No reservations found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5><i class="bi bi-exclamation-triangle"></i> Open Emergencies</h5>
            </div>
            <div class="card-body">
                @forelse($recent_emergencies as $emergency)
                    <div class="mb-3 p-2 border rounded">
                        <strong>{{ $emergency->type }}</strong><br>
                        <small class="text-muted">{{ $emergency->reporter->name }}</small><br>
                        <a href="{{ route('admin.emergency.show', $emergency) }}" class="btn btn-sm btn-danger mt-2">
                            View Details
                        </a>
                    </div>
                @empty
                    <p class="text-muted">No open emergencies</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

