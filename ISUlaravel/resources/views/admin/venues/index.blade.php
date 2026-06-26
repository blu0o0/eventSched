@extends('admin.layouts.app')

@section('title', 'Venues')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('admin.venues.create') }}" class="btn" style="background-color: #176d2d; color: white;">
                <i class="bi bi-plus-circle"></i> Add Venue
            </a>
        </div>
        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="managementTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="venues-tab" data-bs-toggle="tab" data-bs-target="#venues-content" type="button" role="tab">
                    <i class="bi bi-building"></i> Venues
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" href="{{ route('admin.areas.index') }}" role="tab">
                    <i class="bi bi-grid"></i> Areas
                </a>
            </li>
        </ul>
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.venues.index') }}" class="d-flex align-items-center gap-3">
                    <div class="flex-grow-1">
                        <label for="location" class="form-label mb-0">Filter by Location:</label>
                        <select name="location" id="location" class="form-select" onchange="this.form.submit()">
                            <option value="Santiago Campus" {{ ($location ?? 'Santiago Campus') === 'Santiago Campus' ? 'selected' : '' }}>Santiago Campus</option>
                            <option value="Main Campus" {{ ($location ?? '') === 'Main Campus' ? 'selected' : '' }}>Main Campus</option>
                            <option value="" {{ ($location ?? '') === '' ? 'selected' : '' }}>All Locations</option>
                        </select>
                    </div>
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
                        <th>Location</th>
                        <th>Max Occupancy</th>
                        <th>Status</th>
                        <th>Reservations</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($venues as $venue)
                        <tr>
                            <td>
                                @if($venue->photo_url)
                                    <img src="{{ $venue->photo_url }}" alt="{{ $venue->name }}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $venue->name }}</td>
                            <td>{{ $venue->location }}</td>
                            <td>{{ $venue->capacity }}</td>
                            <td>
                                @if($venue->status === 'available')
                                    <span class="badge bg-success">Available</span>
                                @elseif($venue->status === 'damaged')
                                    <span class="badge bg-danger">Damaged</span>
                                    @if($venue->unavailable_until)
                                        <br><small class="text-muted">Until: {{ $venue->unavailable_until->format('M d, Y') }}</small>
                                        <br><small class="text-muted">({{ $venue->days_until_available }} days)</small>
                                    @endif
                                @elseif($venue->status === 'under_construction')
                                    <span class="badge bg-warning text-dark">Under Construction</span>
                                    @if($venue->unavailable_until)
                                        <br><small class="text-muted">Until: {{ $venue->unavailable_until->format('M d, Y') }}</small>
                                        <br><small class="text-muted">({{ $venue->days_until_available }} days)</small>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $venue->reservations_count }}</td>
                            <td>
                                <a href="{{ route('admin.venues.show', $venue) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="{{ route('admin.venues.edit', $venue) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.venues.destroy', $venue) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
                            <td colspan="7" class="text-center">No venues found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $venues->links() }}
        </div>
    </div>
</div>
@endsection

