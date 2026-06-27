
@extends('admin.layouts.app')

@section('title', 'Emergency Reports')

@section('content')
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
                                @else
                                    <form action="{{ route('admin.emergency.destroy', $emergency) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this closed emergency?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Delete
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
