@extends('layouts.dashboard')

@section('title', Auth::user()->isResident() ? 'My service request' : 'Report Details')

@section('content')

<div class="max-w-3xl">
    <div class="card">

        <div class="flex justify-between items-start mb-6 pb-4 border-b">
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    {{ ucfirst(str_replace('_', ' ', $report->type)) }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Reported {{ $report->created_at->format('F j, Y \a\t g:i A') }}
                </p>
            </div>
            @php
                $badgeClass = match($report->status) {
                    'pending' => 'bg-yellow-100 text-yellow-700',
                    'in_progress' => 'bg-blue-100 text-blue-700',
                    'resolved' => 'bg-green-100 text-green-700',
                };
            @endphp
            <span class="badge {{ $badgeClass }}">{{ $report->status }}</span>
        </div>

        <div class="space-y-4">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ Auth::user()->isResident() ? 'Submitted by you' : 'Reported by' }}</p>
                <p class="text-gray-800">{{ $report->user->name ?? 'Unknown' }}</p>
                <p class="text-sm text-gray-500">{{ $report->user->phone ?? '' }}</p>
            </div>

            @if($report->bin)
                <div>
                    <p class="text-sm font-medium text-gray-500">Collection point</p>
                    <p class="text-gray-800">{{ $report->bin->bin_code }} - {{ $report->bin->location_name }}</p>
                </div>
            @endif

            @if($report->description)
                <div>
                    <p class="text-sm font-medium text-gray-500">Description</p>
                    <p class="text-gray-800">{{ $report->description }}</p>
                </div>
            @endif

            @if($report->latitude)
                <div>
                    <p class="text-sm font-medium text-gray-500">GPS Location</p>
                    <p class="text-gray-800">{{ $report->latitude }}, {{ $report->longitude }}</p>
                </div>
            @endif
        </div>

        @if(Auth::user()->isAdmin())
        <div class="mt-6 pt-6 border-t">
            <form action="{{ route('reports.updateStatus', $report) }}" method="POST" class="flex gap-3">
                @csrf
                @method('PATCH')

                <select name="status" class="input-field flex-1">
                    @foreach(['pending', 'in_progress', 'resolved'] as $status)
                        <option value="{{ $status }}" {{ $report->status === $status ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-check mr-2"></i> Update Status
                </button>
            </form>
        </div>
        @endif

        <div class="mt-6 border-t pt-6">
            <h3 class="mb-1 text-sm font-bold uppercase tracking-wide text-gray-600">{{ Auth::user()->isResident() ? 'Messages from the collection team' : 'Resident conversation' }}</h3>
            <p class="mb-4 text-sm text-gray-500">{{ Auth::user()->isResident() ? 'Follow up here and we’ll keep the request conversation together.' : 'Reply to the resident with a service update.' }}</p>
            <div class="space-y-3">
                @forelse($report->comments as $comment)
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="text-sm font-semibold text-gray-800">{{ $comment->user->name }}</span>
                            <time class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</time>
                        </div>
                        <p class="text-sm text-gray-600">{{ $comment->message }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No messages yet. Add a note to keep the conversation moving.</p>
                @endforelse
            </div>

            <form action="{{ route('reports.comment', $report) }}" method="POST" class="mt-4 flex flex-col gap-3 sm:flex-row">
                @csrf
                <input type="text" name="message" required maxlength="1000" class="input-field flex-1" placeholder="{{ Auth::user()->isResident() ? 'Ask a question or add a follow-up...' : 'Write a service update for the resident...' }}">
                <button type="submit" class="btn-primary shrink-0"><i class="fas fa-paper-plane mr-2" aria-hidden="true"></i>Send message</button>
            </form>
        </div>

        <div class="mt-4">
            <a href="{{ route('reports.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>
</div>

@endsection