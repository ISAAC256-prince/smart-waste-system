<?php

namespace App\Http\Controllers;

use App\Models\Bin;
use App\Models\Report;
use App\Models\Truck;
use App\Models\Collection;
use App\Services\CollectionRoutePlanner;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, CollectionRoutePlanner $routePlanner)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard($routePlanner);
        }

        if ($user->isCollector()) {
            $collections = Collection::with('bin', 'truck')
                ->where('collector_id', $user->id)
                ->where('status', '!=', 'completed')
                ->latest()
                ->take(8)
                ->get();

            $stats = [
                'active_collections' => Collection::where('collector_id', $user->id)
                    ->where('status', '!=', 'completed')
                    ->count(),
                'completed_collections' => Collection::where('collector_id', $user->id)
                    ->where('status', 'completed')
                    ->count(),
            ];

            return view('dashboard', compact('collections', 'stats') + ['role' => 'collector']);
        }

        abort_unless($user->role === 'resident', 403);

        $recentReports = $user->reports()->with('bin', 'comments')->latest()->take(8)->get();
        $stats = [
            'my_reports' => $user->reports()->count(),
            'pending_reports' => $user->reports()->where('status', 'pending')->count(),
            'resolved_reports' => $user->reports()->where('status', 'resolved')->count(),
        ];
        $scheduledCollections = Collection::with('bin', 'truck')
            ->where('status', '!=', 'completed')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->take(5)
            ->get();

        return view('dashboard', compact('recentReports', 'scheduledCollections', 'stats') + ['role' => 'resident']);
    }

    private function adminDashboard(CollectionRoutePlanner $routePlanner)
    {
        $stats = [
            'total_bins' => Bin::active()->count(),
            'full_bins' => Bin::active()->full()->count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'available_trucks' => Truck::where('status', 'available')->count(),
            'today_collections' => Collection::whereDate('created_at', today())->count(),
        ];
        $routePlan = $routePlanner->plan(
            Bin::active()->full()
                ->whereDoesntHave('collections', fn ($query) => $query->where('status', '!=', 'completed'))
                ->get(),
            0.3136,
            32.5811,
        );
        $fullBins = Bin::active()->full()->latest()->take(5)->get();
        $recentReports = Report::with('user', 'bin')->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'fullBins', 'recentReports', 'routePlan') + ['role' => 'admin']);
    }

    public function map()
    {
        $bins = Bin::active()->get();
        $reports = Report::where('status', '!=', 'resolved')->get();
        $trucks = Truck::query()
            ->select('id', 'plate_number', 'latitude', 'longitude', 'location_updated_at')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
        return view('map', compact('bins', 'reports', 'trucks'));
    }
}