<?php

namespace App\Support;

class ApplicantNav
{
    public static function items(): array
    {
        return [
            ['route' => 'applicant.dashboard', 'label' => 'Dashboard', 'icon' => 'fa-gauge-high'],
            ['route' => 'applicant.registration.step', 'params' => ['step' => 1], 'label' => 'New LEI Registration', 'icon' => 'fa-plus', 'group' => 'applicant.registration.*'],
            ['route' => 'applicant.renewal.step', 'params' => ['step' => 1], 'label' => 'LEI Renewal', 'icon' => 'fa-rotate', 'group' => 'applicant.renewal.*'],
            ['route' => 'applicant.transfers.index', 'label' => 'LEI Transfer', 'icon' => 'fa-right-left', 'group' => 'applicant.transfers.*'],
            ['route' => 'applicant.payments.index', 'label' => 'Plans & Payments', 'icon' => 'fa-credit-card', 'group' => 'applicant.payments.*'],
            ['route' => 'applicant.applications.index', 'label' => 'Application Tracking', 'icon' => 'fa-clipboard-list', 'group' => 'applicant.applications.*'],
            ['route' => 'applicant.support.index', 'label' => 'Support Center', 'icon' => 'fa-circle-question', 'group' => 'applicant.support.*', 'section' => 'support'],
            ['route' => 'applicant.notifications.index', 'label' => 'Notifications', 'icon' => 'fa-bell', 'group' => 'applicant.notifications.*', 'section' => 'support'],
        ];
    }

    public static function isActive(array $item): bool
    {
        if (request()->routeIs($item['route'])) {
            return true;
        }

        if (! empty($item['group']) && request()->routeIs($item['group'])) {
            return true;
        }

        return false;
    }

    public static function url(array $item): string
    {
        return route($item['route'], $item['params'] ?? []);
    }
}
