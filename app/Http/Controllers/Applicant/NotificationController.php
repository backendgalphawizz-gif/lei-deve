<?php

namespace App\Http\Controllers\Applicant;

class NotificationController extends ApplicantPortalController
{
    public function index()
    {
        $this->sharePortalContext();

        $notifications = collect([
            ['title' => 'Clarification required on application #APP-10293', 'time' => '2 hours ago', 'type' => 'action'],
            ['title' => 'Renewal reminder for Oceanic Holdings & Trust', 'time' => 'Yesterday', 'type' => 'warning'],
            ['title' => 'Payment received for LEI Registration', 'time' => 'Oct 12, 2024', 'type' => 'success'],
        ]);

        return view('applicant.notifications.index', compact('notifications'));
    }
}
