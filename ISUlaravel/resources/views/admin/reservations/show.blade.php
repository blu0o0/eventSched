@extends('admin.layouts.app')

@section('title', 'Reservation Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-calendar-check"></i> Reservation Details</h1>
        <a href="{{ route('admin.reservations.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Title</h5>
                <p>{{ $reservation->title }}</p>

                <h5>Description</h5>
                <p>{{ $reservation->description ?? 'N/A' }}</p>

                <h5>Venue</h5>
                <p>{{ optional($reservation->venue)->name ?? 'N/A' }} ({{ optional($reservation->venue)->location ?? 'N/A' }})</p>

                <h5>Date & Time</h5>
                <p>
                    @if($reservation->date)
                        {{ $reservation->date instanceof \Carbon\Carbon ? $reservation->date->format('F d, Y') : \Carbon\Carbon::parse($reservation->date)->format('F d, Y') }}
                    @else
                        N/A
                    @endif
                    <br>
                    @if($reservation->start_time && $reservation->end_time)
                        {{ \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('g:i A') }}
                    @else
                        N/A
                    @endif
                </p>
            </div>
            <div class="col-md-6">
                <h5>Max Occupancy</h5>
                <p>{{ $reservation->capacity }} attendees</p>

                <h5>Status</h5>
                <p>
                    @if($reservation->status === 'pending')
                        <span class="badge bg-warning">Pending</span>
                    @elseif($reservation->status === 'approved')
                        <span class="badge bg-success">Approved</span>
                    @elseif($reservation->status === 'postponed')
                        <span class="badge bg-info">Postponed</span>
                    @else
                        <span class="badge bg-danger">Rejected</span>
                    @endif
                </p>

                <h5>Requested By</h5>
                <p>{{ optional($reservation->user)->name ?? 'N/A' }} ({{ optional($reservation->user)->email ?? 'N/A' }})</p>

                @if($reservation->approved_by && $reservation->approver)
                    <h5>Approved/Rejected By</h5>
                    <p>{{ $reservation->approver->name }} on {{ optional($reservation->approved_at)->format('M d, Y H:i') ?? 'N/A' }}</p>
                @endif

                @if($reservation->rejection_reason)
                    <h5>Rejection Reason</h5>
                    <p>{{ $reservation->rejection_reason }}</p>
                @endif

                @if($reservation->postponement_reason)
                    <h5>Postponement Reason</h5>
                    <p>{{ $reservation->postponement_reason }}</p>
                @endif
            </div>
        </div>

        @if($reservation->status === 'pending' || $reservation->status === 'postponed')
            <hr>
            <div class="d-flex gap-2">
                <form action="{{ route('admin.reservations.approve', $reservation) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check"></i> Approve
                    </button>
                </form>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x"></i> Reject
                </button>
            </div>

            <!-- Reject Modal -->
            <div class="modal fade" id="rejectModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('admin.reservations.reject', $reservation) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Reject Reservation</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="rejection_reason" class="form-label">Rejection Reason (Optional)</label>
                                    <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

