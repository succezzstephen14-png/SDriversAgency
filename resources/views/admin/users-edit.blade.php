@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<style>
    .admin-wrapper {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        min-height: calc(100vh - 80px);
        padding: 2rem 0;
        display: flex;
    }

    .admin-sidebar {
        width: 260px;
        background: white;
        padding: 2rem 1rem;
        border-radius: 24px 0 0 24px;
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
        margin-right: 1rem;
    }

    .admin-sidebar h5 {
        margin-bottom: 1.5rem;
        font-weight: 700;
        color: #1f2937;
    }

    .admin-sidebar ul {
        list-style: none;
        padding: 0;
    }

    .admin-sidebar li {
        margin-bottom: 0.5rem;
    }

    .admin-sidebar a {
        display: block;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        text-decoration: none;
        color: #4b5563;
        transition: all 0.2s ease;
        font-weight: 500;
        font-size: 0.95rem;
    }

    .admin-sidebar a:hover, .admin-sidebar a.active {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .admin-main-content {
        flex: 1;
        background: white;
        border-radius: 24px;
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
        padding: 2rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .admin-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 800;
    }

    .panel {
        background: white;
        border-radius: 24px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.06);
        padding: 1.75rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #1d4ed8;
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background: #1d4ed8;
        color: white;
    }

    .btn-primary:hover {
        background: #1e40af;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: #e5e7eb;
        color: #1f2937;
    }

    .btn-secondary:hover {
        background: #d1d5db;
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }

    .review-values {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }

    .review-value {
        padding: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow-wrap: anywhere;
    }

    .review-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .reset-link-field {
        overflow-wrap: anywhere;
    }

    @media (max-width: 1280px) {
        .admin-wrapper {
            flex-direction: column;
        }

        .admin-sidebar {
            border-radius: 24px;
            margin-right: 0;
            margin-bottom: 1rem;
        }

        .admin-main-content {
            border-radius: 24px;
        }
    }
</style>

<div class="admin-wrapper">
    <section class="admin-main-content">
        <div class="admin-header">
            <div>
                <p style="color: #475569; margin: 0 0 0.5rem 0;">Platform management</p>
                <h2><i class="fas fa-user-edit"></i> Edit User: {{ $user->name }}</h2>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Validation errors:</strong>
                <ul style="margin: 0.5rem 0 0 1.5rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        @if($user->pending_profile_changes)
            <div class="panel">
                <h3><i class="fas fa-user-clock"></i> Profile Changes Awaiting Review</h3>
                <p>These values are not active yet. Approving applies them to the account; rejecting leaves the current approved details unchanged.</p>
                <div class="review-values">
                    @foreach(['name' => 'Name', 'phone' => 'Phone', 'address' => 'Address'] as $field => $label)
                        @if(array_key_exists($field, $user->pending_profile_changes))
                            <div class="review-value">
                                <strong>{{ $label }}</strong>
                                <div><span class="text-muted">Current:</span> {{ $user->{$field} ?: 'Not provided' }}</div>
                                <div><span class="text-muted">Requested:</span> {{ $user->pending_profile_changes[$field] ?: 'Not provided' }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="review-actions">
                    <form action="{{ route('admin.users.profile-changes.approve', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">Approve Profile Changes</button>
                    </form>
                    <form action="{{ route('admin.users.profile-changes.reject', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Reject Changes</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="panel">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}">
                </div>

                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('admin.users') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <div class="panel">
            <h3><i class="fas fa-key"></i> Reset User Password</h3>
            <p>Generate a secure, single-use reset link for {{ $user->name }}. Share it with the user through a private channel.</p>
            <form action="{{ route('admin.users.password-reset-link', $user) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">Generate Password Reset Link</button>
            </form>
            @if(session('user_password_reset_link'))
                <div class="form-group mt-3">
                    <label for="user-password-reset-link">One-time reset link</label>
                    <input class="reset-link-field" id="user-password-reset-link" type="text" readonly value="{{ session('user_password_reset_link') }}" onclick="this.select()">
                    <small>Copy this link and send it only to the intended user. It expires and can be used once.</small>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
