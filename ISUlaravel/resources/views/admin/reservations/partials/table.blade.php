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
        <td>
            {{ $reservation->date->format('M d, Y') }}
            @if($reservation->end_date)
                - {{ $reservation->end_date instanceof \Carbon\Carbon ? $reservation->end_date->format('M d, Y') : \Carbon\Carbon::parse($reservation->end_date)->format('M d, Y') }}
            @endif
        </td>
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
        <td class="action-column">
            <a href="{{ route('admin.reservations.show', $reservation) }}" class="btn btn-sm btn-info" title="View">
                <i class="bi bi-eye"></i>
            </a>
            @if($reservation->status === 'pending' || $reservation->status === 'postponed')
                <form action="{{ route('admin.reservations.approve', $reservation) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success" title="Approve">
                        <i class="bi bi-check"></i>
                    </button>
                </form>
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $reservation->id }}" title="Reject">
                    <i class="bi bi-x"></i>
                </button>
            @endif
            @if($reservation->status === 'approved')
                <a href="{{ route('admin.reservations.edit', $reservation) }}" class="btn btn-sm btn-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('admin.reservations.destroy', $reservation) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this approved reservation?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            @endif
            @if($reservation->status === 'rejected')
                <form action="{{ route('admin.reservations.destroy', $reservation) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this rejected reservation?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            @endif
        </td>
        <td class="reservation-checkbox-col" style="display: none;">
            <input type="checkbox" class="reservation-checkbox" value="{{ $reservation->id }}" data-status="{{ $reservation->status }}" style="display: none;">
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
        <td colspan="9" class="text-center">No reservations found</td>
    </tr>
@endforelse