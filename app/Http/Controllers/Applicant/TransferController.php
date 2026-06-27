<?php

namespace App\Http\Controllers\Applicant;

class TransferController extends ApplicantPortalController
{
    public function index()
    {
        $this->sharePortalContext();

        $transfers = collect([
            ['entity' => 'Nordic Logistics AB', 'country' => 'Sweden', 'lei' => '549300WXYZ12ABCD5678', 'lou' => 'Nordic LOU', 'status' => 'action_required', 'submitted' => 'Oct 24, 2023'],
            ['entity' => 'Zenith FinTech Inc.', 'country' => 'United States', 'lei' => '549300ABCD98EFGH1234', 'lou' => 'Rapid LEI', 'status' => 'in_progress', 'submitted' => 'Oct 22, 2023'],
            ['entity' => 'Blue Horizon Marine', 'country' => 'Greece', 'lei' => '549300MNOP56QRST7890', 'lou' => 'Euro LEI', 'status' => 'pending', 'submitted' => 'Oct 20, 2023'],
        ]);

        $stats = [
            'total' => 24,
            'in_progress' => 8,
            'action_required' => 3,
            'completed' => 13,
        ];

        return view('applicant.transfers.index', compact('transfers', 'stats'));
    }
}
