@extends('layouts.app')

@section('title', 'Job Opportunities')

@section('content')
<style>
    .recruitments-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 4rem 0;
        margin-bottom: 3rem;
    }

    .recruitments-hero-content {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 1.5rem;
        text-align: center;
    }

    .recruitments-hero h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1rem;
        line-height: 1.1;
    }

    .recruitments-hero p {
        font-size: 1.1rem;
        opacity: 0.95;
        margin-bottom: 1.5rem;
    }

    .recruitments-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1.5rem 3rem;
    }

    .recruitments-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .recruitments-header-info h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
    }

    .recruitments-header-info p {
        margin: 0.5rem 0 0;
        color: #6b7280;
    }

    .recruitments-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .recruitment-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }

    .recruitment-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }

    .recruitment-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        padding: 1.5rem;
        color: white;
    }

    .recruitment-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .recruitment-type {
        display: inline-block;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .recruitment-content {
        padding: 1.5rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .recruitment-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-grow: 1;
    }

    .detail-item {
        background: #f9fafb;
        padding: 0.75rem;
        border-radius: 8px;
        border-left: 3px solid #f59e0b;
    }

    .detail-label {
        font-size: 0.8rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .detail-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1f2937;
    }

    .recruitment-description {
        font-size: 0.9rem;
        color: #6b7280;
        line-height: 1.6;
        padding: 1rem 0;
        border-top: 1px solid #e5e7eb;
        margin-bottom: 1rem;
        flex-grow: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .recruitment-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }

    .recruitment-status {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .recruitment-status.closed {
        background: #ef4444;
    }

    .recruitment-actions {
        display: flex;
        gap: 1rem;
    }

    .btn-view {
        background: #f3f4f6;
        color: #1f2937;
        padding: 0.6rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        text-align: center;
        border: 1px solid #e5e7eb;
    }

    .btn-view:hover {
        background: #e5e7eb;
        transform: translateY(-2px);
    }

    .btn-apply {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 0.6rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        text-align: center;
        border: none;
    }

    .btn-apply:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
    }

    .recruitments-empty {
        text-align: center;
        padding: 3rem 1.5rem;
        background: #f9fafb;
        border-radius: 12px;
        color: #6b7280;
    }

    .recruitments-empty-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .recruitments-pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .pagination .page-link {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        color: #f59e0b;
        transition: all 0.2s ease;
    }

    .pagination .page-link:hover {
        background: #f59e0b;
        color: white;
    }

    .pagination .page-item.active .page-link {
        background: #f59e0b;
        border-color: #f59e0b;
    }

    @media (max-width: 768px) {
        .recruitments-hero h1 {
            font-size: 2rem;
        }

        .recruitments-hero p {
            font-size: 1rem;
        }

        .recruitments-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .recruitment-card {
            border-radius: 15px;
        }

        .recruitments-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 480px) {
        .recruitments-hero {
            padding: 2.5rem 0;
            margin-bottom: 2rem;
        }

        .recruitments-hero h1 {
            font-size: 1.5rem;
        }

        .recruitments-grid {
            grid-template-columns: 1fr;
        }

        .recruitment-details {
            grid-template-columns: 1fr;
        }

        .recruitment-actions {
            flex-direction: column;
        }

        .btn-view,
        .btn-apply {
            width: 100%;
        }
    }
</style>

<div class="recruitments-hero">
    <div class="recruitments-hero-content">
        <h1>Discover Your Next Opportunity</h1>
        <p>Explore exciting job and partnership opportunities in the transportation industry</p>
    </div>
</div>

<div class="recruitments-container">
    <div class="recruitments-header">
        <div class="recruitments-header-info">
            <h2>Active Opportunities</h2>
            <p>{{ $recruitments->total() }} positions available</p>
        </div>
        @auth
            <a href="{{ route('recruitments.create') }}" class="btn btn-warning">
                <i class="fas fa-plus me-2"></i>Post Opportunity
            </a>
        @endauth
    </div>

    @if($recruitments->count() > 0)
        <div class="recruitments-grid">
            @forelse($recruitments as $recruitment)
                <div class="recruitment-card">
                    <div class="recruitment-header">
                        <h3 class="recruitment-title">{{ $recruitment->title }}</h3>
                        <span class="recruitment-type">
                            <i class="fas fa-briefcase me-1"></i>
                            {{ str_replace('_', ' ', ucfirst($recruitment->position_type)) }}
                        </span>
                    </div>

                    <div class="recruitment-content">
                        <div class="recruitment-details">
                            <div class="detail-item">
                                <div class="detail-label">Location</div>
                                <div class="detail-value">{{ $recruitment->location }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Salary Range</div>
                                <div class="detail-value">{{ $recruitment->salary_range ?? 'Negotiable' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Company</div>
                                <div class="detail-value">{{ $recruitment->user?->name ?? 'Various' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <span style="color: #10b981; font-weight: 600;">
                                        {{ ucfirst($recruitment->status ?? 'open') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p class="recruitment-description">
                            {{ $recruitment->description ?? 'No additional details available' }}
                        </p>

                        <div class="recruitment-footer">
                            <span class="recruitment-status">
                                <i class="fas fa-check-circle me-1"></i>
                                Open
                            </span>
                            @php
                                $applyRole = str_replace('_', '-', $recruitment->position_type);
                            @endphp
                            <div class="recruitment-actions">
                                <a href="{{ route('recruitments.show', $recruitment) }}" class="btn-view">
                                    <i class="fas fa-eye me-1"></i>Details
                                </a>
                                @auth
                                    <a href="{{ route('application.page', ['role' => $applyRole, 'recruitment' => $recruitment->id]) }}" class="btn-apply">
                                        <i class="fas fa-paper-plane me-1"></i>Apply Now
                                    </a>
                                @else
                                    <a href="{{ url('/guest/apply/' . $applyRole . '/' . $recruitment->id) }}" class="btn-apply">
                                        <i class="fas fa-paper-plane me-1"></i>Apply Now
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="recruitments-empty">
                    <div class="recruitments-empty-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <p>No open opportunities available at the moment. Please check back later.</p>
                </div>
            @endforelse
        </div>

        @if($recruitments->hasPages())
            <div class="recruitments-pagination">
                {{ $recruitments->links() }}
            </div>
        @endif
    @else
        <div class="recruitments-empty">
            <div class="recruitments-empty-icon">
                <i class="fas fa-briefcase"></i>
            </div>
            <p>No open opportunities available at the moment. Please check back later.</p>
        </div>
    @endif
</div>

@endsection
