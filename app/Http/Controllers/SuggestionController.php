<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\NewSuggestionNotification;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Notification; // Eklendi

class SuggestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Sadece giriş yapmış kullanıcının kendi önerilerini göster
        $suggestions = Auth::user()->suggestions()->with('department')->latest()->paginate(10);
        return view('suggestions.index', compact('suggestions'));
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('suggestions.create', compact('departments'));
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048', // Max 2MB
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            // Dosyayı 'storage/app/public/suggestion_attachments' klasörüne kaydet
            // ve benzersiz bir isim ver.
            $attachmentPath = $request->file('attachment')->store('suggestion_attachments', 'public');
        }

        $suggestion = Auth::user()->suggestions()->create([
            'department_id' => $validated['department_id'],
            'title' => $validated['title'],
            'body' => $validated['body'],
            'status' => 'new',
            'attachment_path' => $attachmentPath,
        ]);

        // Yönetici(ler)e bildirim gönder
        $admins = User::where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            LaravelNotification::send($admins, new NewSuggestionNotification($suggestion));
        }

        return redirect()->route('suggestions.index')->with('success', 'Öneriniz başarıyla gönderildi!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Suggestion $suggestion)
    {
        // Kullanıcının sadece kendi önerisini görebilmesini sağla
        if (Auth::id() !== $suggestion->user_id && Auth::user()->role !== 'admin') {
            abort(403, 'Bu öneriyi görüntüleme yetkiniz yok.');
        }


        $suggestion->load('user', 'department', 'comments.user'); // comments ve comment'i yapan user'ı da yükle
        return view('suggestions.show', compact('suggestion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Suggestion $suggestion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Suggestion $suggestion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Suggestion $suggestion)
    {
        //
    }

    public function export()
    {
        // 1. Veriyi Hazırla: Tüm önerileri ilişkili kullanıcı ve bölüm bilgileriyle çek.
        $suggestions = Suggestion::with(['user', 'department'])->orderBy('created_at', 'desc')->get();

        // 2. Excel'e Yazılacak Veriyi Bir Diziye Dönüştür:
        // Bu dizi, her bir elemanı Excel'de bir satıra karşılık gelecek şekilde alt diziler içerecek.
        $dataForExcel = [];

        // Başlık Satırını Ekle (Excel'in ilk satırı)
        $dataForExcel[] = [
            'ID',
            'Başlık',
            'Açıklama',
            'Öneriyi Yapan Kullanıcı Adı',
            'Kullanıcı E-posta',
            'Bölüm Adı',
            'Durum',
            'Ek Dosya Linki',
            'Oluşturulma Tarihi',
            'Son Güncelleme Tarihi',
        ];

        // Durumların okunabilir karşılıkları için bir dizi
        $statusTranslations = [
            'new' => 'Yeni',
            'in_progress' => 'İnceleniyor',
            'accepted' => 'Kabul Edildi',
            'rejected' => 'Reddedildi',
        ];

        // Her bir öneri için veri satırlarını oluştur ve $dataForExcel dizisine ekle
        foreach ($suggestions as $suggestion) {
            $attachmentLink = '';
            if ($suggestion->attachment_path) {
                // Eğer APP_URL doğru ayarlandıysa Storage::url() tam link verir.
                // Eğer APP_URL ile ilgili sorun yaşıyorsan ve sadece public/storage/dosya_yolu
                // şeklinde bir link işini görüyorsa, ona göre düzenleyebilirsin.
                // Ancak ideal olan Storage::url() kullanmaktır.
                try {
                    $attachmentLink = Storage::url($suggestion->attachment_path);
                } catch (\Exception $e) {
                    // Storage::url() bir hata fırlatırsa (örn: disk bulunamadı), boş bırak.
                    // Loglama da eklenebilir: Log::error("Storage URL oluşturulamadı: " . $e->getMessage());
                    $attachmentLink = 'Link oluşturulamadı';
                }
            } else {
                $attachmentLink = 'Yok';
            }

            $dataForExcel[] = [
                $suggestion->id,
                $suggestion->title,
                strip_tags($suggestion->body), // HTML etiketlerini temizle
                $suggestion->user ? $suggestion->user->name : 'Bilinmeyen Kullanıcı', // Kullanıcı silinmiş olabilir, kontrol et
                $suggestion->user ? $suggestion->user->email : 'N/A',
                $suggestion->department ? $suggestion->department->name : 'Bilinmeyen Bölüm', // Bölüm silinmiş olabilir
                $statusTranslations[$suggestion->status] ?? ucfirst(str_replace('_', ' ', $suggestion->status)),
                $attachmentLink,
                $suggestion->created_at ? $suggestion->created_at->format('d/m/Y H:i:s') : 'N/A',
                $suggestion->updated_at ? $suggestion->updated_at->format('d/m/Y H:i:s') : 'N/A',
            ];
        }

        // 3. Excel Dosyasını Oluştur ve İndir:
        return Excel::create('oneri_listesi', function($excel) use ($dataForExcel) {

            // 'Öneriler' adında bir sayfa (sheet) oluştur
            $excel->sheet('Öneriler', function($sheet) use ($dataForExcel) {

                // Hazırladığımız $dataForExcel dizisini kullanarak sayfayı doldur.
                // Parametreler:
                // 1. $dataForExcel: Veri kaynağı olan dizi.
                // 2. null: Null değerlerin nasıl işleneceği (varsayılan kalsın).
                // 3. 'A1': Verilerin yazılmaya başlanacağı hücre.
                // 4. false: Sütun başlıklarını otomatik olarak oluşturma (çünkü biz zaten $dataForExcel'e ekledik).
                // 5. false: Sıkı null denetimi yapma.
                $sheet->fromArray($dataForExcel, null, 'A1', false, false);

                // İsteğe bağlı: İlk satırı (başlık satırı) kalın yapabilirsin
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                });

                // İsteğe bağlı: Sütun genişliklerini otomatik ayarla (bazı versiyonlarda ve içeriklerde iyi çalışmayabilir)
                // $sheet->setAutoSize(true);

            });

        })->download('xlsx'); // .xlsx formatında indir (veya 'xls', 'csv')
    }



}
