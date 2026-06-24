@extends('admin.layouts.app')

@section('title', 'Emergency Report Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-exclamation-triangle"></i> Emergency Report Details</h1>
        <a href="{{ route('admin.emergency.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Type</h5>
                <p><strong>{{ $emergency->type }}</strong></p>

                <h5>Description</h5>
                <p>{{ $emergency->description }}</p>

                <h5>Status</h5>
                <p>
                    @if($emergency->status === 'open')
                        <span class="badge bg-danger">Open</span>
                    @else
                        <span class="badge bg-success">Closed</span>
                    @endif
                </p>
            </div>
            <div class="col-md-6">
                <h5>Reported By</h5>
                <p>{{ $emergency->reporter->name }} ({{ $emergency->reporter->email }})</p>

                <h5>Reported At</h5>
                <p>{{ $emergency->created_at->format('F d, Y H:i:s') }}</p>

                @if($emergency->resolved_by)
                    <h5>Resolved By</h5>
                    <p>{{ $emergency->resolver->name }} on {{ $emergency->resolved_at->format('F d, Y H:i:s') }}</p>
                @endif
            </div>
        </div>

        @if($emergency->status === 'open')
            <hr>
            <form action="{{ route('admin.emergency.resolve', $emergency) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Mark as Resolved
                </button>
            </form>
        @endif
    </div>
</div>
@endsection

