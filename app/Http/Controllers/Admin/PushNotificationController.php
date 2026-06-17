<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiPushNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PushNotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = LeiPushNotification::query()->orderBy('sort_order');
        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->query('q') . '%');
        }

        return view('admin.notifications.index', [
            'notifications' => $query->get(),
            'search' => $request->query('q', ''),
            'userTypes' => ['user' => 'User', 'vendor' => 'Vendor', 'all' => 'All Users'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_type' => 'required|in:user,vendor,all',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:4096',
        ]);

        $imageUrl = $this->storeImage($request);
        $maxSort = (int) LeiPushNotification::max('sort_order');
        $file = $request->file('image');
        $fileName = $file?->getClientOriginalName() ?? 'notification.pdf';
        $fileType = strtolower($file?->getClientOriginalExtension() ?: 'pdf');
        $desc = trim($data['description'] ?? '') ?: pathinfo($fileName, PATHINFO_FILENAME);

        LeiPushNotification::create([
            'title' => $desc,
            'description' => $desc,
            'file_name' => $fileName,
            'file_type' => in_array($fileType, ['pdf', 'docx', 'doc', 'jpg', 'jpeg', 'png']) ? ($fileType === 'doc' ? 'docx' : $fileType) : 'pdf',
            'image_url' => $imageUrl,
            'user_type' => $data['user_type'],
            'notification_count' => 1,
            'is_active' => true,
            'sort_order' => $maxSort + 1,
        ]);

        return response()->json(['ok' => true, 'message' => 'Notification sent successfully.', 'redirect' => route('admin.notifications.index')]);
    }

    public function update(Request $request, LeiPushNotification $notification)
    {
        $data = $request->validate([
            'title' => 'required|string|max:128',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:4096',
        ]);

        $payload = [
            'title' => $data['title'],
            'description' => $data['description'] ?? $data['title'],
        ];
        if ($url = $this->storeImage($request)) {
            $payload['image_url'] = $url;
        }
        $notification->update($payload);

        return response()->json(['ok' => true, 'message' => 'Notification updated.', 'redirect' => route('admin.notifications.index')]);
    }

    public function destroy(LeiPushNotification $notification)
    {
        $notification->delete();

        return response()->json(['ok' => true, 'message' => 'Notification deleted.']);
    }

    public function resend(LeiPushNotification $notification)
    {
        $notification->increment('notification_count');

        return response()->json(['ok' => true, 'message' => 'Notification resent.', 'count' => $notification->fresh()->notification_count]);
    }

    public function toggle(LeiPushNotification $notification)
    {
        $notification->update(['is_active' => ! $notification->is_active]);

        return response()->json(['ok' => true, 'is_active' => $notification->is_active]);
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $path = $request->file('image')->store('push-notifications', 'public');

        return Storage::disk('public')->url($path);
    }
}
