<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report - {{ \Carbon\Carbon::parse($start)->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($end)->format('Y-m-d') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body { padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <h2 class="text-center mb-4">Event Scheduling System Report</h2>
        <h5 class="text-center mb-4">
            Period: {{ \Carbon\Carbon::parse($start)->format('F d, Y') }} - {{ \Carbon\Carbon::parse($end)->format('F d, Y') }}
        </h5>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body text-center">
                        <h5>Total Users</h5>
                        <h2>{{ $users->count() }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <h5>Total Reservations</h5>
                        <h2>{{ $reservations->count() }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-white bg-danger">
                    <div class="card-body text-center">
                        <h5>Emergency Reports</h5>
                        <h2>{{ $emergencies->count() }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <h4>Users List</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Date Registered</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <h4>Reservations List</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Venue</th>
                    <th>Status</th>
                    <th>Date Reserved</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $reservation)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $reservation->user->name ?? 'N/A' }}</td>
                    <td>{{ $reservation->venue->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($reservation->status) }}</td>
                    <td>{{ $reservation->created_at }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No reservations found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <h4>Emergency Reports</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reporter</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Date Reported</th>
                </tr>
            </thead>
            <tbody>
                @forelse($emergencies as $em)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $em->reporter->name ?? 'N/A' }}</td>
                    <td>{{ $em->description }}</td>
                    <td>{{ ucfirst($em->status) }}</td>
                    <td>{{ $em->created_at }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No emergency reports found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="no-print mt-4">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>