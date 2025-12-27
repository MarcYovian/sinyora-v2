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

    public function mount()
    {
        $this->authorize('access', 'admin.dashboard.index');
        $this->loadStatistics();
        $this->loadUpcomingEvents();
        $this->loadRecentArticles();
        $this->loadPendingApprovals();
    }

    protected function loadStatistics()
    {
        $user = Auth::user();

        // Events statistics
        if ($user->can('view events')) {
            $this->totalEvents = Event::count();
        }

        if ($user->can('approve event')) {
            $this->pendingEvents = Event::query()->pending()->count();
        }

        // Articles statistics
        if ($user->can('view articles')) {
            $this->totalArticles = Article::published()->count();
        }

        if ($user->can('create article')) {
            $this->draftArticles = Article::draft()->count();
        }

        // Borrowings statistics
        if ($user->can('view asset borrowings')) {
            $this->totalBorrowings = Borrowing::count();
            $this->pendingBorrowings = Borrowing::where('status', BorrowingStatus::PENDING)->count();
        }

        // Documents statistics
        if ($user->can('view documents')) {
            $this->totalDocuments = Document::count();
            $this->pendingDocuments = Document::query()->pending()->count();
        }

        // Users statistics
        if ($user->can('view users')) {
            $this->totalUsers = User::count();
        }

        // Assets statistics
        if ($user->can('view assets')) {
            $this->totalAssets = Asset::count();
        }
    }

    protected function loadUpcomingEvents()
    {
        $user = Auth::user();

        if ($user->can('view events')) {
            $startDate = Carbon::today();
            $endDate = Carbon::today()->addDays(7);

            $this->upcomingEvents = EventRecurrence::with(['event.eventCategory', 'event.locations'])
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

    protected function loadRecentArticles()
    {
        $user = Auth::user();

        if ($user->can('view articles')) {
            $this->recentArticles = Article::with(['user', 'category'])
                ->published()
                ->orderBy('published_at', 'desc')
                ->limit(5)
                ->get();
        }
    }

    protected function loadPendingApprovals()
    {
        $user = Auth::user();

        // Pending events for approval
        if ($user->can('approve event')) {
            $this->pendingEventsList = Event::query()
                ->with(['eventCategory', 'organization', 'creator'])
                ->pending()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // Pending borrowings for approval
        if ($user->can('view asset borrowings')) {
            $this->pendingBorrowingsList = Borrowing::with(['assets', 'creator'])
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
