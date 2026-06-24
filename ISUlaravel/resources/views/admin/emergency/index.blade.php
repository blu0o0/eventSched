
@extends('admin.layouts.app')

@section('title', 'Emergency Reports')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h1 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Emergency Reports</h1>
            <a href="{{ route('admin.emergency.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Emergency
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.emergency.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Reporter</th>
                        <th>Status</th>
                        <th>Reported At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($emergencies as $emergency)
                        <tr>
                            <td><strong>{{ $emergency->type }}</strong></td>
                            <td>{{ Str::limit($emergency->description, 50) }}</td>
                            <td>{{ $emergency->reporter->name }}</td>
                            <td>
                                @if($emergency->status === 'open')
                                    <span class="badge bg-danger">Open</span>
                                @else
                                    <span class="badge bg-success">Closed</span>
                                @endif
                            </td>
                            <td>{{ $emergency->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.emergency.show', $emergency) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                @if($emergency->status === 'open')
                                    <form action="{{ route('admin.emergency.resolve', $emergency) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check-circle"></i> Resolve
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No emergency reports found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $emergencies->links() }}
        </div>
    </div>
</div>
@endsection
