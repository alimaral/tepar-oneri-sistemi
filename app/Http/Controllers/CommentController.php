<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Suggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewCommentNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class CommentController extends Controller
{
    public function store(Request $request, Suggestion $suggestion)
    {
        $request->validate([
            'body' => 'required|string|min:3|max:5000', // Yorum uzunluğu için bir sınır ekleyebilirsin
        ]);

        // Yorumu oluştur ve kaydet
        $comment = $suggestion->comments()->create([
            'user_id' => Auth::id(), // Yorumu yapan, o an giriş yapmış kullanıcı
            'body' => $request->body,
        ]);

        // --- BİLDİRİM GÖNDERME MANTIĞI ---
        $commenter = Auth::user(); // Yorumu yapan kişi
        $suggestionOwner = $suggestion->user; // Önerinin sahibi

        // 1. Öneri sahibine bildirim (eğer yorumu yapan kişi öneri sahibi değilse)
        if ($suggestionOwner->id !== $commenter->id) {
            // $suggestionOwner, bildirimi alacak kişi
            // $commenter, yorumu yapan kişi
            $suggestionOwner->notify(new NewCommentNotification($comment, $suggestionOwner, $commenter));
        }

        // 2. Tüm yöneticilere bildirim
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            // Yorumu yapan kişi bu admin değilse bildirim gönder
            if ($admin->id !== $commenter->id) {
                // Ek kontrol: Eğer öneri sahibi aynı zamanda bir adminse ve yukarıda zaten bildirim aldıysa,
                // bu admine tekrar bildirim gitmesini engelle.
                $alreadyNotifiedAsOwner = ($suggestionOwner->id === $admin->id && $suggestionOwner->id !== $commenter->id);
                if (!$alreadyNotifiedAsOwner) {
                    // $admin, bildirimi alacak kişi
                    // $commenter, yorumu yapan kişi
                    $admin->notify(new NewCommentNotification($comment, $admin, $commenter));
                }
            }
        }
        // --- BİLDİRİM GÖNDERME MANTIĞI SONU ---

        return back()->with('success', 'Yorumunuz başarıyla eklendi.');
    }
}
