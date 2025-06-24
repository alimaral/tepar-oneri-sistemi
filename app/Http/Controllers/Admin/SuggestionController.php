<?php

namespace App\Http\Controllers\Admin;
use App\Notifications\SuggestionStatusUpdatedNotification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Models\Suggestion;
use App\Models\Department; // Filtreleme için
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Bildirim okundu işaretlemesi için
use App\Exports\SuggestionsExport;

// Bildirimler için (Adım 5'te oluşturulacak ve kullanılacak)
// use App\Notifications\SuggestionStatusUpdatedNotification;
// use Illuminate\Support\Facades\Notification as LaravelNotification;

class SuggestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Suggestion::with(['user', 'department'])->latest();

        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }
        if ($request->filled('department_filter')) {
            $query->where('department_id', $request->department_filter);
        }
        if ($request->filled('search_term')) {
            $searchTerm = $request->search_term;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('body', 'like', '%' . $searchTerm . '%');
            });
        }

        $suggestions = $query->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();
        $statuses = ['new' => 'Yeni', 'in_progress' => 'İnceleniyor', 'accepted' => 'Kabul Edildi', 'rejected' => 'Reddedildi'];

        return view('admin.suggestions.index', compact('suggestions', 'departments', 'statuses'));
    }

    public function show(Request $request, Suggestion $suggestion)
    {
        if ($request->has('read') && Auth::check()) {
            $notification = Auth::user()->notifications()->where('id', $request->read)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        }

        $suggestion->load('user', 'department', 'comments.user'); // comments ilişkisini de yükleyelim
        return view('admin.suggestions.show', compact('suggestion'));
    }

    public function edit(Suggestion $suggestion)
    {
        $suggestion->load('user', 'department');
        $statuses = ['new' => 'Yeni', 'in_progress' => 'İnceleniyor', 'accepted' => 'Kabul Edildi', 'rejected' => 'Reddedildi'];
        return view('admin.suggestions.edit', compact('suggestion', 'statuses'));
    }

    public function update(Request $request, Suggestion $suggestion)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,in_progress,accepted,rejected',
            // 'admin_comment' => 'nullable|string|max:1000'
        ]);

        $oldStatus = $suggestion->status;
        $suggestion->status = $validated['status'];
        // if (isset($validated['admin_comment'])) {
        //    $suggestion->admin_comment = $validated['admin_comment'];
        // }
        $suggestion->save();

        // Durum gerçekten değiştiyse ve öneriyi gönderen kullanıcıya bildirim gönder
        if ($oldStatus !== $suggestion->status && $suggestion->user) {
            $suggestion->user->notify(new SuggestionStatusUpdatedNotification($suggestion)); // Bu satırın yorumda olmaması gerekiyor
        }

        return redirect()->route('admin.suggestions.index')->with('success', 'Öneri durumu başarıyla güncellendi.');
    }

    public function export()
    {
        return Excel::download(new SuggestionsExport, 'oneri_listesi.xlsx');
    }
}
