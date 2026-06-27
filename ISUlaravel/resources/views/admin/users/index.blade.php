@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-people"></i> Users</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add User
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="ADMINISTRATOR" {{ request('role') === 'ADMINISTRATOR' ? 'selected' : '' }}>Administrator</option>
                    <option value="SBO BSIT WMAD" {{ request('role') === 'SBO BSIT WMAD' ? 'selected' : '' }}>SBO BSIT WMAD</option>
                    <option value="SBO BSIT NETSEC" {{ request('role') === 'SBO BSIT NETSEC' ? 'selected' : '' }}>SBO BSIT NETSEC</option>
                    <option value="SBO BSA" {{ request('role') === 'SBO BSA' ? 'selected' : '' }}>SBO BSA</option>
                    <option value="SBL BSLEA" {{ request('role') === 'SBL BSLEA' ? 'selected' : '' }}>SBL BSLEA</option>
                    <option value="SSC OFFICER" {{ request('role') === 'SSC OFFICER' ? 'selected' : '' }}>SSC Officer</option>
                    <option value="FACULTY/STAFF" {{ request('role') === 'FACULTY/STAFF' ? 'selected' : '' }}>Faculty/Staff</option>
                    <option value="STUDENT" {{ request('role') === 'STUDENT' ? 'selected' : '' }}>Student</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'ADMINISTRATOR')
                                    <span class="badge bg-danger">Administrator</span>
                                @elseif($user->role === 'SBO BSIT WMAD')
                                    <span class="badge bg-primary">SBO BSIT WMAD</span>
                                @elseif($user->role === 'SBO BSIT NETSEC')
                                    <span class="badge bg-primary">SBO BSIT NETSEC</span>
                                @elseif($user->role === 'SBO BSA')
                                    <span class="badge bg-primary">SBO BSA</span>
                                @elseif($user->role === 'SBL BSLEA')
                                    <span class="badge bg-primary">SBL BSLEA</span>
                                @elseif($user->role === 'SSC OFFICER')
                                    <span class="badge bg-info">SSC Officer</span>
                                @elseif($user->role === 'FACULTY/STAFF')
                                    <span class="badge bg-secondary">Faculty/Staff</span>
                                @else
                                    <span class="badge bg-secondary">Student</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
                            <td colspan="5" class="text-center">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
