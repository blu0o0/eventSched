@forelse($reservations as $reservation)
    <tr>
        <td>{{ $reservation->title }}</td>
        <td>{{ $reservation->venue->name }}</td>
        <td>
            @if($reservation->area)
                {{ $reservation->area->name }}
            @elseif($reservation->area_name)
                {{ $reservation->area_name }}
            @else
                N/A
            @endif
        </td>
        <td>{{ $reservation->date->format('M d, Y') }}</td>
        <td>{{ \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('g:i A') }}</td>
        <td>{{ $reservation->user->name }}</td>
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
        <td>
            <a href="{{ route('admin.reservations.show', $reservation) }}" class="btn btn-sm btn-info">
                <i class="bi bi-eye"></i> View
            </a>
            @if($reservation->status === 'pending' || $reservation->status === 'postponed')
                <form action="{{ route('admin.reservations.approve', $reservation) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="bi bi-check"></i> Approve
                    </button>
                </form>
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $reservation->id }}">
                    <i class="bi bi-x"></i> Reject
                </button>
            @endif
            @if($reservation->status === 'approved')
                <a href="{{ route('admin.reservations.edit', $reservation) }}" class="btn btn-sm btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <form action="{{ route('admin.reservations.destroy', $reservation) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this approved reservation?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
            @endif
            @if($reservation->status === 'rejected')
                <form action="{{ route('admin.reservations.destroy', $reservation) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this rejected reservation?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
            @endif
        </td>
    </tr>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal{{ $reservation->id }}" tabindex="-1">
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
@empty
    <tr>
        <td colspan="7" class="text-center">No reservations found</td>
    </tr>
@endforelse