<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $search = is_string($request->query('q')) ? trim($request->query('q')) : '';

        $messages = ContactMessage::query()
            ->when($request->query('status') === 'unread', fn ($query) => $query->unread())
            ->when($request->query('status') === 'read', fn ($query) => $query->where('is_read', true))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.mb_strtolower($search).'%';
                $query->where(fn ($query) => $query
                    ->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(subject) LIKE ?', [$like]));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.contact-messages.index', [
            'messages' => $messages,
            'unreadCount' => ContactMessage::unread()->count(),
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        $contactMessage->markAsRead();

        return view('admin.contact-messages.show', ['message' => $contactMessage]);
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()->route('admin.contact-messages.index')->with('success', 'Pesan berhasil dihapus.');
    }
}
