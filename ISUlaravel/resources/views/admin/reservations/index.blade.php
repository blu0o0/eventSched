@extends('admin.layouts.app')

@section('title', 'Reservations')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search reservations..." id="search-input" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select" id="status-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="postponed" {{ request('status') === 'postponed' ? 'selected' : '' }}>Postponed</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Venue</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="reservations-table-body">
                    @include('admin.reservations.partials.table')
                </tbody>
            </table>
        </div>
        <div class="mt-3" id="reservations-pagination">
            {{ $reservations->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let searchTimeout;

    function fetchReservations() {
        const search = document.getElementById('search-input').value;
        const status = document.getElementById('status-select').value;

        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (status) params.append('status', status);

        fetch('{{ route('admin.reservations.index') }}?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('reservations-table-body').innerHTML = data.html;
            document.getElementById('reservations-pagination').innerHTML = data.pagination;
        })
        .catch(error => console.error('Error fetching reservations:', error));
    }

    document.getElementById('search-input').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(fetchReservations, 400);
    });

    document.getElementById('status-select').addEventListener('change', fetchReservations);
</script>
@endpush

