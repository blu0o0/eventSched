@extends('admin.layouts.app')

@section('title', 'Generate Reports')

@section('content')
<div class="container">

    <h2>Reports Generator</h2>


    <form method="POST" action="{{ route('admin.reports.generate') }}">
        @csrf

        <div class="row">
            <div class="col-md-4">
                <label>Start Date</label>
                <input type="date"
                       name="start_date"
                       class="form-control"
                       required>
            </div>

            <div class="col-md-4">
                <label>End Date</label>
                <input type="date"
                       name="end_date"
                       class="form-control"
                       required>
            </div>

            <div class="col-md-4 mt-4">
                <button type="submit" class="btn btn-primary">
                    Generate Report
                </button>
            </div>
        </div>
    </form>

    <hr>

    @isset($users)

    <h4>
    Report Result
    ({{ \Carbon\Carbon::parse($start)->format('Y-m-d') }}
    →
    {{ \Carbon\Carbon::parse($end)->format('Y-m-d') }})
</h4>

    <div class="row mt-3">

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
            <td colspan="4" class="text-center">
                No users found.
            </td>
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
            <td colspan="5" class="text-center">
                No reservations found.
            </td>
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
            <td colspan="5" class="text-center">
                No emergency reports found.
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>

    <a href="{{ route('admin.reports.print', [
    'start_date' => $start->format('Y-m-d'),
    'end_date' => $end->format('Y-m-d')
]) }}"
target="_blank"
class="btn btn-success">
    🖨️ Print Report
</a>

    @endisset

</div>
@endsection