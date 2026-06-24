@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Left Column - Donut Chart + Recent Reservations -->
    <div class="col-lg-9">
        <!-- Donut Chart Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Reservation Overview</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-5 text-center">
                        <canvas id="reservationChart" height="220" width="220" style="max-width: 240px; max-height: 240px; margin: 0 auto;"></canvas>
                    </div>
                    <div class="col-md-7">
                        <div class="chart-legend">
                            <div class="legend-item">
                                <span class="legend-dot" style="background: linear-gradient(135deg, #ffd54f 0%, #ffca28 100%); border: 2px solid #f9a825;"></span>
                                <span>Pending</span>
                                <span class="legend-value">{{ $stats['pending_reservations'] }}</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background: linear-gradient(135deg, #81c784 0%, #66bb6a 100%); border: 2px solid #43a047;"></span>
                                <span>Approved</span>
                                <span class="legend-value">{{ $stats['approved_reservations'] }}</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background: linear-gradient(135deg, #ef9a9a 0%, #e57373 100%); border: 2px solid #e53935;"></span>
                                <span>Rejected</span>
                                <span class="legend-value">{{ $stats['rejected_reservations'] }}</span>
                            </div>
                            <hr>
                            <div class="legend-item total">
                                <span class="legend-dot" style="background: linear-gradient(135deg, #90caf9 0%, #64b5f6 100%); border: 2px solid #1e88e5;"></span>
                                <span><strong>Total Reservations</strong></span>
                                <span class="legend-value"><strong>{{ $stats['total_reservations'] }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Reservations Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Recent Reservations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Venue</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_reservations as $reservation)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.reservations.show', $reservation) }}">
                                            {{ $reservation->title }}
                                        </a>
                                    </td>
                                    <td>{{ $reservation->venue->name }}</td>
                                    <td>{{ $reservation->date->format('M d, Y') }}</td>
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
                                    <td>{{ $reservation->user->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No reservations found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Small Stat Cards -->
    <div class="col-lg-3">
        <div class="compact-stat-card" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: 1px solid #90caf9; border-radius: 16px; padding: 20px; margin-bottom: 16px;">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #42a5f5 0%, #1e88e5 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <p style="margin: 0; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #1565c0;">Total Venues</p>
                    <h3 style="margin: 2px 0 0; font-size: 22px; font-weight: 800; color: #0d47a1; line-height: 1.2;">{{ $stats['total_venues'] }}</h3>
                </div>
            </div>
        </div>

        <div class="compact-stat-card" style="background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%); border: 1px solid #f48fb1; border-radius: 16px; padding: 20px; margin-bottom: 16px;">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #ef5350 0%, #e53935 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(239, 83, 80, 0.3);">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <p style="margin: 0; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #c62828;">Open Emergencies</p>
                    <h3 style="margin: 2px 0 0; font-size: 22px; font-weight: 800; color: #b71c1c; line-height: 1.2;">{{ $stats['open_emergencies'] }}</h3>
                </div>
            </div>
        </div>

        <div class="compact-stat-card" style="background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%); border: 1px solid #ce93d8; border-radius: 16px; padding: 20px; margin-bottom: 16px;">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #ab47bc 0%, #8e24aa 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(171, 71, 188, 0.3);">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <p style="margin: 0; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #7b1fa2;">Total Users</p>
                    <h3 style="margin: 2px 0 0; font-size: 22px; font-weight: 800; color: #4a148c; line-height: 1.2;">{{ $stats['total_users'] }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chart-legend {
    padding: 0 10px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    font-size: 14px;
    color: #374151;
}

.legend-item.total {
    padding-top: 12px;
}

.legend-dot {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    flex-shrink: 0;
}

.legend-value {
    margin-left: auto;
    font-weight: 700;
    font-size: 16px;
    color: #111827;
}

.compact-stat-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: default;
}

.compact-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('reservationChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved', 'Rejected'],
            datasets: [{
                data: [
                    {{ $stats['pending_reservations'] }},
                    {{ $stats['approved_reservations'] }},
                    {{ $stats['rejected_reservations'] }}
                ],
                backgroundColor: [
                    'rgba(255, 202, 40, 0.9)',
                    'rgba(102, 187, 106, 0.9)',
                    'rgba(229, 115, 115, 0.9)'
                ],
                borderColor: [
                    '#f9a825',
                    '#43a047',
                    '#e53935'
                ],
                borderWidth: 3,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#111827',
                    bodyColor: '#374151',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    boxPadding: 6,
                    callbacks: {
                        label: function(context) {
                            const total = {{ $stats['total_reservations'] }};
                            const value = context.parsed;
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        },
        plugins: [{
            id: 'centerText',
            beforeDraw: function(chart) {
                const width = chart.width;
                const height = chart.height;
                const ctx = chart.ctx;
                ctx.restore();
                const fontSize = (height / 7).toFixed(2);
                ctx.font = fontSize + 'px Inter, sans-serif';
                ctx.textBaseline = 'middle';
                
                const total = {{ $stats['total_reservations'] }};
                const text = total.toString();
                const textX = Math.round((width - ctx.measureText(text).width) / 2);
                const textY = height / 2 - 10;
                
                ctx.fillStyle = '#111827';
                ctx.font = 'bold ' + (height / 5).toFixed(2) + 'px Inter, sans-serif';
                ctx.fillText(text, textX, textY);
                
                ctx.fillStyle = '#6b7280';
                ctx.font = (height / 16).toFixed(2) + 'px Inter, sans-serif';
                const subtext = 'Total';
                const subtextX = Math.round((width - ctx.measureText(subtext).width) / 2);
                ctx.fillText(subtext, subtextX, textY + (height / 8).toFixed(2));
                
                ctx.save();
            }
        }]
    });
});
</script>
@endsection