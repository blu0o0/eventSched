@extends('admin.layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-people"></i> Edit User</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Name *</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password (leave blank to keep current)</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" style="padding-right: 50px;">
                    <button type="button" class="password-toggle" id="passwordToggle">
                        <i class="bi bi-eye-fill" id="passwordIcon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" style="padding-right: 50px;">
                    <button type="button" class="password-toggle" id="confirmPasswordToggle">
                        <i class="bi bi-eye-fill" id="confirmPasswordIcon"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role *</label>
                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="administrator" {{ old('role', $user->role) === 'administrator' ? 'selected' : '' }}>Administrator</option>
                    <option value="osas" {{ old('role', $user->role) === 'osas' ? 'selected' : '' }}>OSAS</option>
                    <option value="main_proponent" {{ old('role', $user->role) === 'main_proponent' ? 'selected' : '' }}>Main Proponent</option>
                    <option value="general_user" {{ old('role', $user->role) === 'general_user' ? 'selected' : '' }}>General User</option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Update User
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Password visibility toggles
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');
    
    const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    const confirmPasswordIcon = document.getElementById('confirmPasswordIcon');

    if (passwordToggle && passwordInput && passwordIcon) {
        passwordToggle.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('bi-eye-fill');
                passwordIcon.classList.add('bi-eye-slash-fill');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('bi-eye-slash-fill');
                passwordIcon.classList.add('bi-eye-fill');
            }
        });
    }

    if (confirmPasswordToggle && confirmPasswordInput && confirmPasswordIcon) {
        confirmPasswordToggle.addEventListener('click', function() {
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                confirmPasswordIcon.classList.remove('bi-eye-fill');
                confirmPasswordIcon.classList.add('bi-eye-slash-fill');
            } else {
                confirmPasswordInput.type = 'password';
                confirmPasswordIcon.classList.remove('bi-eye-slash-fill');
                confirmPasswordIcon.classList.add('bi-eye-fill');
            }
        });
    }

    // Real-time password validation
    const passwordField = document.getElementById('password');
    if (passwordField) {
        passwordField.addEventListener('input', function() {
            const password = this.value;
            let errorMessage = '';
            
            if (password.length > 0) {
                if (password.length < 8) {
                    errorMessage = 'Password must be at least 8 characters';
                } else if (!/[A-Za-z]/.test(password)) {
                    errorMessage = 'Password must contain at least one letter (a-z, A-Z)';
                } else if (!/\d/.test(password)) {
                    errorMessage = 'Password must contain at least one number (0-9)';
                } else if (!/[@$!%*?&.,]/.test(password)) {
                    errorMessage = 'Password must contain at least one symbol (@$!%*?&.,)';
                }
            }
            
            // Update error display
            const errorDiv = this.closest('.mb-3').querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.textContent = errorMessage;
                if (errorMessage) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            }
        });
    }
</script>
@endpush

