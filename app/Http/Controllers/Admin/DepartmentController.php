<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        // Departmanları öneri sayılarıyla birlikte çekmek için (index view'ında göstermek üzere)
        $departments = Department::withCount('suggestions')->orderBy('name')->paginate(10);
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')->with('success', 'Bölüm başarıyla oluşturuldu.');
    }

    public function show(Department $department)
    {
        // Genellikle admin tarafında department için ayrı bir show sayfası olmaz,
        // index veya edit yeterli olabilir. Ama isterseniz oluşturabilirsiniz.
        return redirect()->route('admin.departments.edit', $department);

    }

    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id, // Güncellerken kendi ID'sini unique kontrolünden hariç tut
            'description' => 'nullable|string|max:1000',
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')->with('success', 'Bölüm başarıyla güncellendi.');
    }

    public function destroy(Department $department)
    {
        // İlişkili öneriler varsa silmeyi engelle
        if ($department->suggestions()->exists()) {
            return redirect()->route('admin.departments.index')->with('error', 'Bu bölüme ait öneriler bulunduğu için silinemiyor. Lütfen önce ilgili önerileri başka bir bölüme taşıyın veya silin.');
        }

        try {
            $department->delete();
            return redirect()->route('admin.departments.index')->with('success', 'Bölüm başarıyla silindi.');
        } catch (\Exception $e) {
            // Loglama eklenebilir: Log::error('Bölüm silinirken hata: ' . $e->getMessage());
            return redirect()->route('admin.departments.index')->with('error', 'Bölüm silinirken bir hata oluştu.');
        }
    }
}
