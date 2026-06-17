<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiNmConfig;
use App\Models\LeiNmDeliveryLog;
use App\Models\LeiNmPlaceholder;
use App\Models\LeiNmTemplate;
use App\Models\LeiNmTrigger;
use App\Services\NotificationManagementService;
use Illuminate\Http\Request;

class NotificationManagementController extends Controller
{
    public function __construct(private NotificationManagementService $nm) {}

    public function index(Request $request)
    {
        $config = LeiNmConfig::first();
        $channel = $request->query('channel', $config?->template_channel ?? 'email');

        return view('admin.notifications.index', [
            'statCards' => $this->nm->computeStatCards(),
            'config' => $config,
            'templates' => LeiNmTemplate::where('channel', $channel)->orderBy('sort_order')->get(),
            'triggers' => LeiNmTrigger::orderBy('sort_order')->get(),
            'placeholders' => LeiNmPlaceholder::orderBy('sort_order')->get(),
            'deliveryLogs' => LeiNmDeliveryLog::orderBy('sort_order')->get(),
            'activeChannel' => $channel,
        ]);
    }

    public function setChannel(Request $request)
    {
        $data = $request->validate(['channel' => 'required|in:email,sms']);
        $config = LeiNmConfig::first();
        if ($config) {
            $config->update(['template_channel' => $data['channel']]);
        }

        return response()->json(['ok' => true, 'redirect' => route('admin.notifications.index', ['channel' => $data['channel']])]);
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:64',
            'subtitle' => 'nullable|string|max:255',
            'category' => 'required|string|max:32',
            'channel' => 'required|in:email,sms',
        ]);

        LeiNmTemplate::create([
            'name' => $data['name'],
            'subtitle' => $data['subtitle'],
            'category' => $data['category'],
            'channel' => $data['channel'],
            'status' => 'draft',
            'last_updated_label' => now()->format('M d, Y'),
            'sort_order' => (int) LeiNmTemplate::max('sort_order') + 1,
        ]);

        return response()->json(['ok' => true, 'message' => 'Template created.', 'redirect' => route('admin.notifications.index', ['channel' => $data['channel']])]);
    }

    public function saveBroadcastDraft(Request $request)
    {
        $data = $request->validate([
            'broadcast_channel' => 'required|string|max:64',
            'broadcast_audience' => 'required|string|max:64',
            'broadcast_message' => 'nullable|string|max:5000',
        ]);

        LeiNmConfig::first()?->update($data);

        return response()->json(['ok' => true, 'message' => 'Draft saved.']);
    }

    public function executeBroadcast(Request $request)
    {
        $data = $request->validate([
            'broadcast_channel' => 'required|string|max:64',
            'broadcast_audience' => 'required|string|max:64',
            'broadcast_message' => 'required|string|max:5000',
        ]);

        $config = LeiNmConfig::first();
        $config?->update($data);

        LeiNmDeliveryLog::create([
            'delivery_type' => 'email',
            'recipient' => 'broadcast@lei.registry',
            'template_label' => 'System Broadcast',
            'status' => 'delivered',
            'time_label' => 'Just now',
            'sort_order' => (int) LeiNmDeliveryLog::max('sort_order') + 1,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Broadcast executed.',
            'stats' => $this->nm->statsPayload(),
        ]);
    }

    public function updateOtp(Request $request)
    {
        $data = $request->validate([
            'otp_length' => 'required|integer|min:4|max:8',
            'otp_expiry_min' => 'required|integer|min:1|max:30',
            'otp_retry_limit' => 'required|integer|min:1|max:10',
        ]);

        LeiNmConfig::first()?->update($data);

        return response()->json(['ok' => true, 'message' => 'Security policy updated.']);
    }

    public function toggleTrigger(Request $request, LeiNmTrigger $trigger)
    {
        $trigger->update(['is_enabled' => ! $trigger->is_enabled]);

        return response()->json([
            'ok' => true,
            'is_enabled' => $trigger->is_enabled,
            'stats' => $this->nm->statsPayload(),
        ]);
    }
}
