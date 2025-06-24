<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Suggestion;
use App\Models\User;
// use Illuminate\Http\Request; // Eğer Request kullanmayacaksanız bu satırı silebilirsiniz.

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_suggestions' => Suggestion::count(),
            'new_suggestions' => Suggestion::where('status', 'new')->count(),
            'in_progress_suggestions' => Suggestion::where('status', 'in_progress')->count(),
            'accepted_suggestions' => Suggestion::where('status', 'accepted')->count(),
            'rejected_suggestions' => Suggestion::where('status', 'rejected')->count(),
            'total_users' => User::where('role', 'user')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_departments' => Department::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
