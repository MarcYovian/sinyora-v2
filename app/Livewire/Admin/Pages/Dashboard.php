<?php

namespace App\Livewire\Admin\Pages;

use App\Enums\BorrowingStatus;
use App\Enums\DocumentStatus;
use App\Enums\EventApprovalStatus;
use App\Models\Article;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\Document;
use App\Models\Event;
use App\Models\EventRecurrence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    use AuthorizesRequests;

    #[Layout('layouts.app')]

    // Statistics
    public int $totalEvents = 0;
    public int $pendingEvents = 0;
    public int $totalArticles = 0;
    public int $draftArticles = 0;
    public int $totalBorrowings = 0;
    public int $pendingBorrowings = 0;
    public int $totalDocuments = 0;
    public int $pendingDocuments = 0;
    public int $totalUsers = 0;
    public int $totalAssets = 0;

    // Data collections
    public $upcomingEvents = [];
    public $recentArticles = [];
    public $pendingEventsList = [];
    public $pendingBorrowingsList = [];

    // UI state
    public string $greeting = '';
    public string $lastUpdated = '';

    private const STATS_CACHE_TTL = 300; // 5 minutes

    public function mount()
    {
        $this->authorize('access', 'admin.dashboard.index');

        $user = Auth::user();

        $this->greeting = $this->getGreeting();
        $this->loadStatistics($user);
        $this->loadUpcomingEvents($user);
        $this->loadRecentArticles($user);
        $this->loadPendingApprovals($user);
        $this->lastUpdated = now()->translatedFormat('d M Y, H:i');
    }

    protected function getGreeting(): string
    {
        $hour = (int) now()->format('H');

        return match (true) {
            $hour >= 5 && $hour < 12 => 'Selamat Pagi',
            $hour >= 12 && $hour < 15 => 'Selamat Siang',
            $hour >= 15 && $hour < 18 => 'Selamat Sore',
            default => 'Selamat Malam',
        };
    }

    protected function loadStatistics($user): void
    {
        $cacheKey = "dashboard_stats_{$user->id}";

        $stats = Cache::remember($cacheKey, self::STATS_CACHE_TTL, function () use ($user) {
            $data = [];

            if ($user->can('view events')) {
                $data['totalEvents'] = Event::count();
            }
            if ($user->can('approve event')) {
                $data['pendingEvents'] = Event::query()->pending()->count();
            }
            if ($user->can('view articles')) {
                $data['totalArticles'] = Article::published()->count();
            }
            if ($user->can('create article')) {
                $data['draftArticles'] = Article::draft()->count();
            }
            if ($user->can('view asset borrowings')) {
                $data['totalBorrowings'] = Borrowing::count();
                $data['pendingBorrowings'] = Borrowing::where('status', BorrowingStatus::PENDING)->count();
            }
            if ($user->can('view documents')) {
                $data['totalDocuments'] = Document::count();
                $data['pendingDocuments'] = Document::query()->pending()->count();
            }
            if ($user->can('view users')) {
                $data['totalUsers'] = User::count();
            }
            if ($user->can('view assets')) {
                $data['totalAssets'] = Asset::count();
            }

            return $data;
        });

        // Assign cached values to properties
        $this->totalEvents = $stats['totalEvents'] ?? 0;
        $this->pendingEvents = $stats['pendingEvents'] ?? 0;
        $this->totalArticles = $stats['totalArticles'] ?? 0;
        $this->draftArticles = $stats['draftArticles'] ?? 0;
        $this->totalBorrowings = $stats['totalBorrowings'] ?? 0;
        $this->pendingBorrowings = $stats['pendingBorrowings'] ?? 0;
        $this->totalDocuments = $stats['totalDocuments'] ?? 0;
        $this->pendingDocuments = $stats['pendingDocuments'] ?? 0;
        $this->totalUsers = $stats['totalUsers'] ?? 0;
        $this->totalAssets = $stats['totalAssets'] ?? 0;
    }

    protected function loadUpcomingEvents($user): void
    {
        if ($user->can('view events')) {
            $startDate = Carbon::today();
            $endDate = Carbon::today()->addDays(7);

            $this->upcomingEvents = EventRecurrence::query()
                ->select(['id', 'event_id', 'date', 'time_start', 'time_end'])
                ->with([
                    'event:id,name,event_category_id',
                    'event.eventCategory:id,name',
                ])
                ->whereHas('event', function ($query) {
                    $query->where('status', EventApprovalStatus::APPROVED);
                })
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'asc')
                ->orderBy('time_start', 'asc')
                ->limit(5)
                ->get();
        }
    }

    protected function loadRecentArticles($user): void
    {
        if ($user->can('view articles')) {
            $this->recentArticles = Article::query()
                ->select(['id', 'title', 'slug', 'featured_image', 'user_id', 'category_id', 'published_at'])
                ->with([
                    'user:id,name',
                    'category:id,name',
                ])
                ->published()
                ->orderBy('published_at', 'desc')
                ->limit(5)
                ->get();
        }
    }

    protected function loadPendingApprovals($user): void
    {
        // Pending events for approval
        if ($user->can('approve event')) {
            $this->pendingEventsList = Event::query()
                ->select(['id', 'name', 'status', 'event_category_id', 'organization_id', 'creator_id', 'creator_type', 'created_at'])
                ->with([
                    'eventCategory:id,name',
                    'organization:id,name',
                ])
                ->pending()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // Pending borrowings for approval
        if ($user->can('view asset borrowings')) {
            $this->pendingBorrowingsList = Borrowing::query()
                ->select(['id', 'borrower', 'start_datetime', 'end_datetime', 'status', 'creator_id', 'creator_type', 'created_at'])
                ->withCount('assets')
                ->where('status', BorrowingStatus::PENDING)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.admin.pages.dashboard');
    }
}
