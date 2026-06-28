<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Generated Report</title>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family: Arial, Helvetica, sans-serif;
            padding:20px;
            color:#333;
            background:#fff;
        }

        .report-header{
            text-align:center;
            margin-bottom:20px;
        }

        .report-header h1{
            color:#0d6efd;
            margin-bottom:5px;
        }

        .report-header p{
            font-size:14px;
            color:#666;
        }

        .report-info{
            background:#f8f9fa;
            border-left:5px solid #0d6efd;
            padding:12px;
            margin-bottom:20px;
        }

        .report-info strong{
            color:#0d6efd;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-bottom:25px;
        }

        th,
        td{
            border:1px solid #dee2e6;
            padding:10px;
            font-size:13px;
        }

        th{
            text-align:center;
        }

        td{
            vertical-align:middle;
        }

        /* SUMMARY TABLE */
        .summary th{
            color:white;
            font-size:14px;
        }

        .summary td{
            text-align:center;
            font-weight:bold;
            font-size:24px;
            color:white;
            padding:15px;
        }

        .users-total{
            background:#0d6efd;
        }

        .reservations-total{
            background:#198754;
        }

        .emergency-total{
            background:#dc3545;
        }

        /* TITLES */
        .section-title{
            color:white;
            padding:10px;
            font-size:16px;
            margin-bottom:0;
        }

        .users-title{
            background:#0d6efd;
        }

        .reservation-title{
            background:#198754;
        }

        .emergency-title{
            background:#dc3545;
        }

        /* USERS TABLE */
        .users-table{
            border:2px solid #0d6efd;
        }

        .users-table thead{
            background:#0d6efd;
            color:white;
        }

        .users-table tbody tr:nth-child(even){
            background:#eaf2ff;
        }

        /* RESERVATIONS TABLE */
        .reservations-table{
            border:2px solid #198754;
        }

        .reservations-table thead{
            background:#198754;
            color:white;
        }

        .reservations-table tbody tr:nth-child(even){
            background:#eaf8ef;
        }

        /* EMERGENCY TABLE */
        .emergency-table{
            border:2px solid #dc3545;
        }

        .emergency-table thead{
            background:#dc3545;
            color:white;
        }

        .emergency-table tbody tr:nth-child(even){
            background:#fdecec;
        }

        .text-center{
            text-align:center;
        }

        .btn-print{
            background:#0d6efd;
            color:white;
            border:none;
            padding:10px 20px;
            border-radius:5px;
            cursor:pointer;
            font-size:14px;
        }

        @media print{
            .no-print{
                display:none;
            }

            body{
                padding:0;
            }
        }
    </style>
</head>

<body>

<div class="report-header">
    <h1>REPORT GENERATOR</h1>
    <p>Users, Reservations and Emergency Reports Summary</p>
</div>

<div class="report-info">
    <strong>Report Period:</strong>
    {{ \Carbon\Carbon::parse($start)->format('F d, Y') }}
    -
    {{ \Carbon\Carbon::parse($end)->format('F d, Y') }}
</div>

<!-- SUMMARY -->
<table class="summary">
    <tr>
        <th style="background:#0d6efd;">TOTAL USERS</th>
        <th style="background:#198754;">TOTAL RESERVATIONS</th>
        <th style="background:#dc3545;">TOTAL EMERGENCY REPORTS</th>
    </tr>

    <tr>
        <td class="users-total">
            {{ $users->count() }}
        </td>

        <td class="reservations-total">
            {{ $reservations->count() }}
        </td>

        <td class="emergency-total">
            {{ $emergencies->count() }}
        </td>
    </tr>
</table>

<!-- USERS -->
<h3 class="section-title users-title">👥 USERS LIST</h3>

<table class="users-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Date Registered</th>
        </tr>
    </thead>

    <tbody>
        @forelse($users as $index => $user)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->role ?? 'N/A' }}</td>
            <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">
                No users found for selected date range.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<!-- RESERVATIONS -->
<h3 class="section-title reservation-title">📅 RESERVATIONS LIST</h3>

<table class="reservations-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Reserved By</th>
            <th>Venue</th>
            <th>Status</th>
            <th>Date Reserved</th>
        </tr>
    </thead>

    <tbody>
        @forelse($reservations as $index => $reservation)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $reservation->user->name ?? 'N/A' }}</td>
            <td>{{ $reservation->venue->name ?? 'N/A' }}</td>
            <td>{{ ucfirst($reservation->status) }}</td>
            <td>{{ $reservation->created_at->format('Y-m-d H:i') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">
                No reservations found for selected date range.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<!-- EMERGENCY REPORTS -->
<h3 class="section-title emergency-title">🚨 EMERGENCY REPORTS</h3>

<table class="emergency-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Reporter</th>
            <th>Emergency Type</th>
            <th>Description</th>
            <th>Status</th>
            <th>Date Reported</th>
        </tr>
    </thead>

    <tbody>
        @forelse($emergencies as $index => $em)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $em->reporter->name ?? 'N/A' }}</td>
            <td>{{ $em->type ?? 'N/A' }}</td>
            <td>{{ $em->description }}</td>
            <td>{{ ucfirst($em->status) }}</td>
            <td>{{ $em->created_at->format('Y-m-d H:i') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">
                No emergency reports found for selected date range.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="no-print">
    <button type="button" class="btn-print" onclick="window.print();">
        🖨️ Print Report
    </button>
</div>

</body>
</html>

