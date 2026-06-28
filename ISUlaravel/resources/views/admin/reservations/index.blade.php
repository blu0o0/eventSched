@extends('admin.layouts.app')

@section('title', 'Reservations')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row g-3 align-items-center mb-3">
            <div class="col">
                <h6 class="mb-0">Total Reservations: <span class="badge bg-secondary" id="total-count">{{ $totalReservations ?? 0 }}</span></h6>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="input-group">
                    <button class="btn btn-outline-secondary" type="button" id="search-btn" title="Search">
                        <i class="bi bi-search"></i>
                    </button>
                    <input type="text" name="search" class="form-control" placeholder="Search reservations..." id="search-input" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="button" id="search-clear" title="Clear search" style="border-color: #cacacaa0; color: #6c757d;">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-2">
                <input type="date" name="date" class="form-control" id="date-filter" value="{{ request('date') }}" placeholder="Filter by date">
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
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle-select-btn" onclick="toggleSelectMode()">
                    <i class="bi bi-check-square"></i> Select
                </button>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>
    <div class="card-body">
        <!-- Sticky Bulk Actions Bar -->
        <div id="bulk-actions-sticky" style="display: none; position: fixed; top: 64px; right: 20px; z-index: 1050; background: #fff; padding: 8px 12px; border: 1px solid #dee2e6; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
            <div class="d-flex align-items-center gap-2 justify-content-end">
                <input type="checkbox" id="select-all-sticky" onchange="toggleSelectAll(this)">
                <span class="text-muted" id="selected-count-sticky" style="font-size: 0.8rem;">0 selected</span>
                <button type="button" class="btn btn-sm btn-danger py-0 px-2" onclick="executeBulkAction('reject')">
                    Reject
                </button>
                <button type="button" class="btn btn-sm btn-danger py-0 px-2" onclick="executeBulkAction('delete')">
                    Delete
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="executeBulkAction('reject_delete')">
                    Reject & Delete
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="toggleSelectMode()">
                    Cancel
                </button>
            </div>
        </div>
        <div class="table-responsive" id="table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Venue</th>
                        <th>Area</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>User</th>
                        <th>Status</th>
                        <th id="actions-th">Actions</th>
                        <th style="width: 40px;"></th>
                    </tr>
                </thead>
                <tbody id="reservations-table-body">
                    @include('admin.reservations.partials.table')
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #search-clear:hover {
        opacity: 0.3;
    }
    .select-mode .reservation-checkbox-col {
        display: table-cell !important;
    }
    .select-mode .reservation-checkbox {
        display: inline-block !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let searchTimeout;

    function fetchReservations() {
        const search = document.getElementById('search-input').value;
        const status = document.getElementById('status-select').value;
        const date = document.getElementById('date-filter').value;

        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (status) params.append('status', status);
        if (date) params.append('date', date);

        fetch('{{ route('admin.reservations.index') }}?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('reservations-table-body').innerHTML = data.html;
            if (data.total !== undefined) {
                document.getElementById('total-count').textContent = data.total;
            }
        })
        .catch(error => console.error('Error fetching reservations:', error));
    }

    document.getElementById('search-input').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(fetchReservations, 400);
    });

    document.getElementById('status-select').addEventListener('change', fetchReservations);
    document.getElementById('date-filter').addEventListener('change', fetchReservations);

    document.getElementById('search-btn').addEventListener('click', function() {
        fetchReservations();
    });

    document.getElementById('search-clear').addEventListener('click', function() {
        document.getElementById('search-input').value = '';
        fetchReservations();
    });

    let selectModeActive = false;

    function toggleSelectMode() {
        selectModeActive = !selectModeActive;
        const toggleBtn = document.getElementById('toggle-select-btn');
        const container = document.getElementById('table-container');
        const stickyBar = document.getElementById('bulk-actions-sticky');
        const checkboxes = document.querySelectorAll('.reservation-checkbox');
        const checkboxCols = document.querySelectorAll('.reservation-checkbox-col');

        if (selectModeActive) {
            toggleBtn.innerHTML = '<i class="bi bi-x"></i> Cancel';
            toggleBtn.classList.remove('btn-outline-secondary');
            toggleBtn.classList.add('btn-secondary');
            container.classList.add('select-mode');
            stickyBar.style.display = 'block';
            checkboxes.forEach(cb => cb.checked = false);
            updateSelectedCount();
        } else {
            toggleBtn.innerHTML = '<i class="bi bi-check-square"></i> Select';
            toggleBtn.classList.remove('btn-secondary');
            toggleBtn.classList.add('btn-outline-secondary');
            container.classList.remove('select-mode');
            stickyBar.style.display = 'none';
            checkboxes.forEach(cb => cb.checked = false);
        }
    }

    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('.reservation-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checked = document.querySelectorAll('.reservation-checkbox:checked');
        const count = checked.length;
        document.getElementById('selected-count-sticky').textContent = count + ' selected';

        let hasRejectable = false;

        checked.forEach(cb => {
            const status = cb.getAttribute('data-status');
            if (status === 'pending' || status === 'postponed') {
                hasRejectable = true;
            }
        });

        const buttons = document.querySelectorAll('#bulk-actions-sticky .btn');
        const rejectBtn = buttons[0];
        const deleteBtn = buttons[1];
        const rejectDeleteBtn = buttons[2];

        if (count > 0) {
            rejectBtn.style.display = hasRejectable ? 'inline-block' : 'none';
            deleteBtn.style.display = 'inline-block';
            rejectDeleteBtn.style.display = hasRejectable ? 'inline-block' : 'none';
        } else {
            rejectBtn.style.display = 'none';
            deleteBtn.style.display = 'none';
            rejectDeleteBtn.style.display = 'none';
        }
    }

    // Listen for checkbox changes (delegated since table body gets reloaded)
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('reservation-checkbox')) {
            updateSelectedCount();
        }
    });

    function executeBulkAction(action) {
        if (!selectModeActive) return;
        
        const checkboxes = document.querySelectorAll('.reservation-checkbox:checked');
        const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));
        
        if (ids.length === 0) {
            alert('Please select at least one reservation.');
            return;
        }

        const actionLabels = {
            reject: 'reject',
            delete: 'delete',
            reject_delete: 'reject and delete'
        };

        if (!confirm('Are you sure you want to ' + (actionLabels[action] || action) + ' ' + ids.length + ' reservation(s)?')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', action);
        ids.forEach(id => formData.append('ids[]', id));

        fetch('{{ route('admin.reservations.bulk-action') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Action completed successfully.');
            toggleSelectMode();
            fetchReservations();
        })
        .catch(error => {
            alert('An error occurred while processing the bulk action.');
            console.error('Bulk action error:', error);
        });
    }
</script>
@endpush