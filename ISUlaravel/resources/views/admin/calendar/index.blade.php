
@extends('admin.layouts.app')

@section('title', 'Calendar View')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-calendar3"></i> Calendar View</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reservation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetails">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div id="eventActions">
                    <!-- Action buttons will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: '{{ route("admin.calendar.events") }}',
        eventClick: function(info) {
            const event = info.event;
            const reservationId = event.id;

            // Fetch reservation details
            fetch(`/admin/reservations/${reservationId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Handle both API response format and direct data
                const reservation = data.data || data;
                document.getElementById('eventDetails').innerHTML = `
                    <h6>${reservation.title}</h6>
                    <p><strong>Venue:</strong> ${reservation.venue.name}</p>
                    <p><strong>Date:</strong> ${reservation.date}</p>
                    <p><strong>Time:</strong> ${reservation.start_time} - ${reservation.end_time}</p>
                    <p><strong>Max Occupancy:</strong> ${reservation.capacity}</p>
                    <p><strong>Status:</strong> <span class="badge bg-${reservation.status === 'approved' ? 'success' : reservation.status === 'pending' ? 'warning' : 'danger'}">${reservation.status}</span></p>
                    <p><strong>Requested By:</strong> ${reservation.user.name}</p>
                    ${reservation.description ? `<p><strong>Description:</strong> ${reservation.description}</p>` : ''}
                `;

                let actionsHtml = '';
                if (reservation.status === 'pending') {
                    actionsHtml = `
                        <form action="/admin/reservations/${reservationId}/approve" method="POST" class="d-inline">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="btn btn-success">Approve</button>
                        </form>
                        <a href="/admin/reservations/${reservationId}" class="btn btn-primary">View Details</a>
                    `;
                } else {
                    actionsHtml = `<a href="/admin/reservations/${reservationId}" class="btn btn-primary">View Details</a>`;
                }
                document.getElementById('eventActions').innerHTML = actionsHtml;

                const modal = new bootstrap.Modal(document.getElementById('eventModal'));
                modal.show();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load reservation details');
            });
        }
    });
    calendar.render();
});
</script>
@endpush
