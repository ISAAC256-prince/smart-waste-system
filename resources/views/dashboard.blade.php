@extends('layouts.dashboard')

@section('title', $role === 'resident' ? 'My waste service' : 'Dashboard')

@section('content')

<div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
    <div>
        <h2 class="text-2xl font-bold text-gray-950 sm:text-3xl">{{ $role === 'admin' ? 'Kampala operations' : ($role === 'collector' ? 'Assigned pickups' : 'Your neighbourhood service') }}</h2>
        <p class="mt-1 text-sm text-gray-500">{{ $role === 'resident' ? 'Hello, '.Auth::user()->name.'. Collection updates and service requests, all in one place.' : Auth::user()->name.' · '.now()->format('M j, Y') }}</p>
    </div>
    @if ($role === 'resident')
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reports.create') }}" class="btn-primary shrink-0"><i class="fas fa-message mr-2" aria-hidden="true"></i>Report a waste issue</a>
            <a href="{{ route('map') }}" class="btn-secondary shrink-0"><i class="fas fa-map-location-dot mr-2" aria-hidden="true"></i>View collection map</a>
        </div>
    @elseif ($role === 'admin')
        <a href="{{ route('collections.create') }}" class="btn-primary shrink-0"><i class="fas fa-route mr-2" aria-hidden="true"></i>Assign a pickup</a>
    @endif
</div>

@if ($role === 'admin')
    <div class="mb-7 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="stat-card border-l-4 border-l-primary-500">
            <div class="flex items-center justify-between text-sm text-gray-500"><span>Active bins</span><i class="fas fa-dumpster text-primary-700" aria-hidden="true"></i></div>
            <p class="mt-3 text-3xl font-bold text-gray-950">{{ $stats['total_bins'] }}</p>
        </div>
        <div class="stat-card border-l-4 border-l-[#e36b55]">
            <div class="flex items-center justify-between text-sm text-gray-500"><span>Need collection</span><i class="fas fa-triangle-exclamation text-[#c94e3b]" aria-hidden="true"></i></div>
            <p class="mt-3 text-3xl font-bold text-gray-950">{{ $stats['full_bins'] }}</p>
        </div>
        <div class="stat-card border-l-4 border-l-[#e4ad37]">
            <div class="flex items-center justify-between text-sm text-gray-500"><span>Pending reports</span><i class="fas fa-flag text-[#b98215]" aria-hidden="true"></i></div>
            <p class="mt-3 text-3xl font-bold text-gray-950">{{ $stats['pending_reports'] }}</p>
        </div>
        <div class="stat-card border-l-4 border-l-[#547d9a]">
            <div class="flex items-center justify-between text-sm text-gray-500"><span>Available trucks</span><i class="fas fa-truck text-[#547d9a]" aria-hidden="true"></i></div>
            <p class="mt-3 text-3xl font-bold text-gray-950">{{ $stats['available_trucks'] }}</p>
        </div>
        <div class="stat-card border-l-4 border-l-[#c2643f]">
            <div class="flex items-center justify-between text-sm text-gray-500"><span>Pickups today</span><i class="fas fa-calendar-check text-[#c2643f]" aria-hidden="true"></i></div>
            <p class="mt-3 text-3xl font-bold text-gray-950">{{ $stats['today_collections'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <section class="card xl:col-span-1">
            <div class="mb-4 flex items-start justify-between gap-3 border-b border-gray-100 pb-4">
                <div>
                    <h3 class="font-bold text-gray-950">Priority route</h3>
                    <p class="mt-1 text-xs text-gray-500">Nearest-neighbour sequence from the city depot</p>
                </div>
                <span class="rounded-lg bg-primary-50 px-2.5 py-1 text-xs font-bold text-primary-800">{{ count($routePlan['stops']) }} stops</span>
            </div>
            <ol class="space-y-3">
                @forelse ($routePlan['stops'] as $index => $stop)
                    <li class="flex items-start gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-800">{{ $index + 1 }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-gray-800">{{ $stop['bin']->location_name }}</span>
                            <span class="mt-0.5 block text-xs text-gray-500">{{ $stop['bin']->bin_code }} · {{ $stop['bin']->fill_level }}% full</span>
                        </span>
                        <span class="shrink-0 text-xs font-semibold text-gray-500">{{ number_format($stop['distance_km'], 1) }} km</span>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-gray-400">No bins have crossed the 80% collection threshold.</li>
                @endforelse
            </ol>
            @if (count($routePlan['stops']) > 0)
                <p class="mt-4 border-t border-gray-100 pt-3 text-xs text-gray-500">Approx. {{ number_format($routePlan['total_distance_km'], 1) }} km by straight-line distance. Actual road distance may differ.</p>
            @endif
            <a href="{{ route('map') }}" class="mt-4 inline-flex text-sm font-semibold text-primary-700 hover:text-primary-900">View operations map <i class="fas fa-arrow-right ml-2 mt-0.5" aria-hidden="true"></i></a>
        </section>

        <section class="card">
            <div class="mb-4 flex items-center justify-between gap-3 border-b border-gray-100 pb-4">
                <h3 class="font-bold text-gray-950">Bins needing collection</h3>
                <a href="{{ route('bins.index') }}" class="text-sm font-semibold text-primary-700 hover:text-primary-900">All bins</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($fullBins as $bin)
                    <a href="{{ route('bins.show', $bin) }}" class="flex items-center justify-between gap-4 py-3 hover:bg-gray-50">
                        <span class="min-w-0"><span class="block font-semibold text-gray-800">{{ $bin->bin_code }}</span><span class="mt-0.5 block truncate text-xs text-gray-500">{{ $bin->location_name }}</span></span>
                        <span class="badge-danger shrink-0">{{ $bin->fill_level }}%</span>
                    </a>
                @empty
                    <p class="py-8 text-center text-sm text-gray-400">No full bins right now.</p>
                @endforelse
            </div>
        </section>

        <section class="card xl:col-span-1">
            <div class="mb-4 flex items-center justify-between gap-3 border-b border-gray-100 pb-4">
                <h3 class="font-bold text-gray-950">Recent reports</h3>
                <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-primary-700 hover:text-primary-900">All reports</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($recentReports as $report)
                    <a href="{{ route('reports.show', $report) }}" class="flex items-center justify-between gap-4 py-3 hover:bg-gray-50">
                        <span class="min-w-0"><span class="block font-semibold text-gray-800">{{ ucfirst(str_replace('_', ' ', $report->type)) }}</span><span class="mt-0.5 block truncate text-xs text-gray-500">{{ $report->bin->location_name ?? 'GPS report' }} · {{ $report->created_at->diffForHumans() }}</span></span>
                        <span class="badge {{ $report->status === 'pending' ? 'badge-warning' : ($report->status === 'resolved' ? 'badge-success' : 'badge-info') }}">{{ str_replace('_', ' ', $report->status) }}</span>
                    </a>
                @empty
                    <p class="py-8 text-center text-sm text-gray-400">No reports have been submitted.</p>
                @endforelse
            </div>
        </section>
    </div>
@elseif ($role === 'collector')
    <div class="mb-7 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="stat-card border-l-4 border-l-[#e4ad37]"><p class="text-sm text-gray-500">Active pickups</p><p class="mt-2 text-3xl font-bold text-gray-950">{{ $stats['active_collections'] }}</p></div>
        <div class="stat-card border-l-4 border-l-primary-500"><p class="text-sm text-gray-500">Completed pickups</p><p class="mt-2 text-3xl font-bold text-gray-950">{{ $stats['completed_collections'] }}</p></div>
    </div>
    <section class="card">
        <div class="mb-4 border-b border-gray-100 pb-4"><h3 class="font-bold text-gray-950">My assigned pickups</h3></div>
        <div class="divide-y divide-gray-100">
            @forelse ($collections as $collection)
                <div class="flex flex-wrap items-center justify-between gap-4 py-4">
                    <div class="min-w-0"><p class="font-semibold text-gray-900">{{ $collection->bin->bin_code ?? 'Bin unavailable' }} <span class="font-normal text-gray-400">/</span> {{ $collection->bin->location_name ?? '' }}</p><p class="mt-1 text-sm text-gray-500">{{ $collection->truck->plate_number ?? 'No truck' }} <span class="px-1">·</span> Assigned {{ $collection->created_at->format('M j') }}@if($collection->scheduled_at) <span class="px-1">·</span> Scheduled {{ $collection->scheduled_at->format('M j, g:i A') }}@endif</p></div>
                    <div class="flex items-center gap-2">
                        <span class="badge {{ $collection->status === 'completed' ? 'badge-success' : 'badge-warning' }}">{{ str_replace('_', ' ', $collection->status) }}</span>
                        @if ($collection->truck && $collection->status !== 'completed')
                            <button type="button" class="btn-secondary px-3 py-2 text-sm" data-location-url="{{ route('trucks.location', $collection->truck) }}">
                                <i class="fas fa-location-crosshairs mr-1" aria-hidden="true"></i>Share location
                            </button>
                        @endif
                        <a href="{{ route('collections.show', $collection) }}" class="btn-secondary px-3 py-2 text-sm">Open</a>
                        @if ($collection->status !== 'completed')
                            <form action="{{ route('collections.complete', $collection) }}" method="POST">@csrf @method('PATCH')<button type="submit" class="btn-success px-3 py-2 text-sm">Complete</button></form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-12 text-center"><i class="fas fa-circle-check mb-3 text-2xl text-primary-500" aria-hidden="true"></i><p class="font-semibold text-gray-700">No active pickups assigned</p><p class="mt-1 text-sm text-gray-500">New assignments will appear here.</p></div>
            @endforelse
        </div>
        <p id="locationUpdateMessage" class="mt-3 text-sm text-gray-500" role="status" aria-live="polite"></p>
        <a href="{{ route('collections.index') }}" class="mt-4 inline-flex text-sm font-semibold text-primary-700 hover:text-primary-900">View pickup history <i class="fas fa-arrow-right ml-2 mt-0.5" aria-hidden="true"></i></a>
    </section>
@else
    <section id="collection-notifications" class="mb-6 overflow-hidden rounded-2xl bg-[#20283a] text-white shadow-lg">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-white/10 px-5 py-5 sm:px-7">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#f0aa82]">Neighbourhood collection alerts</p>
                <h3 class="mt-2 text-xl font-bold">Collection times for Kampala service points</h3>
                <p class="mt-1 max-w-xl text-sm text-white/65">Upcoming pickup notices are listed by area, with the scheduled time and collection point.</p>
            </div>
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-[#f0aa82]" aria-hidden="true"><i class="fas fa-bell text-lg"></i></span>
        </div>
        <div class="divide-y divide-white/10 px-5 sm:px-7">
            @forelse ($scheduledCollections as $collection)
                <div class="flex flex-wrap items-center justify-between gap-4 py-4">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10 text-[#f0aa82]"><i class="fas fa-truck" aria-hidden="true"></i></span>
                        <span class="min-w-0">
                            <span class="block font-semibold">{{ $collection->bin->location_name ?? 'Collection point' }}</span>
                            <span class="mt-1 block text-sm text-white/60">{{ $collection->notes ?: 'Scheduled neighbourhood pickup' }}@if($collection->truck) · Collection vehicle {{ $collection->truck->plate_number }}@endif</span>
                        </span>
                    </div>
                    <span class="rounded-xl bg-[#f0aa82]/15 px-3 py-2 text-sm font-semibold text-[#ffd0b6]"><i class="far fa-clock mr-2" aria-hidden="true"></i>{{ $collection->scheduled_at->format('D, M j · g:i A') }}</span>
                </div>
            @empty
                <div class="py-6">
                    <p class="font-semibold">No collection visits are scheduled yet.</p>
                    <p class="mt-1 text-sm text-white/60">New area alerts will appear here as soon as a pickup is planned.</p>
                </div>
            @endforelse
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 sm:px-7">
            <a href="{{ route('map') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#ffd0b6] hover:text-white">See collection points <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <a href="{{ route('reports.create') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-white/75 hover:text-white">Need a pickup? Tell us <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    </section>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="stat-card border-l-4 border-l-primary-500"><p class="text-sm text-gray-500">My service requests</p><p class="mt-2 text-3xl font-bold text-gray-950">{{ $stats['my_reports'] }}</p></div>
        <div class="stat-card border-l-4 border-l-[#e4ad37]"><p class="text-sm text-gray-500">Awaiting a response</p><p class="mt-2 text-3xl font-bold text-gray-950">{{ $stats['pending_reports'] }}</p></div>
        <div class="stat-card border-l-4 border-l-[#547d9a]"><p class="text-sm text-gray-500">Resolved</p><p class="mt-2 text-3xl font-bold text-gray-950">{{ $stats['resolved_reports'] }}</p></div>
    </div>
    <section class="card">
        <div class="mb-4 flex items-center justify-between gap-3 border-b border-gray-100 pb-4">
            <div>
                <h3 class="font-bold text-gray-950">Your service requests</h3>
                <p class="mt-1 text-xs text-gray-500">Follow each request and read replies from the collection team.</p>
            </div>
            <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-primary-700 hover:text-primary-900">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse ($recentReports as $report)
                <a href="{{ route('reports.show', $report) }}" class="flex flex-wrap items-center justify-between gap-3 py-4 hover:bg-gray-50">
                    <span>
                        <span class="block font-semibold text-gray-800">{{ ucfirst(str_replace('_', ' ', $report->type)) }}</span>
                        <span class="mt-1 block text-xs text-gray-500">{{ $report->bin->location_name ?? 'Location supplied' }} · {{ $report->created_at->format('M j, Y') }} · {{ $report->comments->count() }} {{ \Illuminate\Support\Str::plural('message', $report->comments->count()) }}</span>
                    </span>
                    <span class="badge {{ $report->status === 'pending' ? 'badge-warning' : ($report->status === 'resolved' ? 'badge-success' : 'badge-info') }}">{{ str_replace('_', ' ', $report->status) }}</span>
                </a>
            @empty
                <div class="py-10 text-center">
                    <p class="font-semibold text-gray-700">Nothing to follow up yet</p>
                    <p class="mt-1 text-sm text-gray-500">Report missed collections, overflowing bins, or dumping in your area.</p>
                    <a href="{{ route('reports.create') }}" class="btn-primary mt-4">Create your first request</a>
                </div>
            @endforelse
        </div>
    </section>
@endif

@endsection

@if ($role === 'collector')
    @push('scripts')
    <script>
        document.querySelectorAll('[data-location-url]').forEach((button) => {
            button.addEventListener('click', async () => {
                const message = document.getElementById('locationUpdateMessage');
                if (!navigator.geolocation) {
                    message.textContent = 'This device does not provide location services.';
                    return;
                }

                button.disabled = true;
                message.textContent = 'Getting your current location...';
                try {
                    const position = await new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(resolve, reject, {
                            enableHighAccuracy: true,
                            timeout: 15000,
                        });
                    });
                    const response = await fetch(button.dataset.locationUrl, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                        }),
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Location could not be saved.');
                    message.textContent = `Fleet location updated at ${new Date(result.truck.location_updated_at).toLocaleTimeString()}.`;
                } catch (error) {
                    message.textContent = error.message || 'Location permission was denied or unavailable.';
                } finally {
                    button.disabled = false;
                }
            });
        });
    </script>
    @endpush
@endif
