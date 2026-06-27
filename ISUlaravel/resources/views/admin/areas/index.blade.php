@extends('admin.layouts.app')

@section('title', 'Areas')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('admin.areas.create') }}" class="btn" style="background-color: #176d2d; color: white;">
                <i class="bi bi-plus-circle"></i> Add Area
            </a>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="managementTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="{{ route('admin.venues.index') }}" role="tab">
                    <i class="bi bi-building"></i> Venues
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="areas-tab" data-bs-toggle="tab" data-bs-target="#areas-content" type="button" role="tab">
                    <i class="bi bi-grid"></i> Areas
                </button>
            </li>
        </ul>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.areas.index') }}" class="d-flex align-items-end gap-3 mb-3">
                    <button type="submit" class="btn" style="background-color: #176d2d; color: white; margin-bottom: 8px;">
                        <i class="bi bi-search"></i>
                    </button>
                    <div class="flex-grow-1">
                        <label for="search" class="form-label mb-0">Search by Name:</label>
                        <input type="text" class="form-control" name="search" id="search" value="{{ $search ?? '' }}">
                    </div>
                    <div class="flex-grow-1">
                        <label for="venue_id" class="form-label mb-0">Filter by Venue:</label>
                        <select name="venue_id" id="venue_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Venues</option>
                            @foreach($venues as $venue)
                                <option value="{{ $venue->id }}" {{ ($venueId ?? '') == $venue->id ? 'selected' : '' }}>
                                    {{ $venue->name }} ({{ $venue->location }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-grow-1">
                        <label for="status" class="form-label mb-0">Filter by Status:</label>
                        <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="available" {{ (request('status') ?? '') === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="occupied" {{ (request('status') ?? '') === 'occupied' ? 'selected' : '' }}>Occupied</option>
                            <option value="not_available" {{ (request('status') ?? '') === 'not_available' ? 'selected' : '' }}>Not Available</option>
                        </select>
                    </div>
                    @if(($search ?? '') || ($venueId ?? '') || (request('status') ?? ''))
                        <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary" style="margin-bottom: 8px;">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Venue</th>
                    <th>Status</th>
                    <th>Reservations</th>
                    <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($areas as $area)
                        <tr>
                            <td>
                                @if($area->photo_url)
                                    <img src="{{ $area->photo_url }}" alt="{{ $area->name }}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $area->name }}</td>
                            <td>
                                @if($area->venue)
                                    {{ $area->venue->name }} <small class="text-muted">({{ $area->venue->location }})</small>
                                @else
                                    <span class="text-muted">No venue assigned</span>
                                @endif
                            </td>
                            <td>
                                @if($area->status === 'available')
                                    <span class="badge bg-success">Available</span>
                                @elseif($area->status === 'occupied')
                                    <span class="badge bg-warning text-dark">Occupied</span>
                                @elseif($area->status === 'not_available')
                                    <span class="badge bg-danger">Not Available</span>
                                @endif
                            </td>
                            <td>
                                {{ $area->reservations->count() }}
                            </td>
                            <td>
                                <a href="{{ route('admin.areas.show', $area) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="{{ route('admin.areas.edit', $area) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No areas found</td>
                            </tr>
                        @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $areas->links() }}
        </div>
    </div>
</div>
@endsection