<?php

namespace App\Support;

class AdminNav
{
    public static function isActive(?string $routeName): bool
    {
        if (! $routeName) {
            return false;
        }

        if (request()->routeIs($routeName)) {
            return true;
        }

        $groups = [
            'admin.users.index' => 'admin.users.*',
            'admin.applications.index' => 'admin.applications.*',
            'admin.payments.index' => 'admin.payments.*',
            'admin.controls.index' => 'admin.controls.*',
            'admin.environment.index' => 'admin.environment.*',
            'admin.master-data.index' => 'admin.master-data.*',
            'admin.templates.index' => 'admin.templates.*',
            'admin.registry.index' => 'admin.registry.*',
            'admin.backup.index' => 'admin.backup.*',
            'admin.sla.index' => 'admin.sla.*',
            'admin.security.index' => 'admin.security.*',
            'admin.audit.index' => 'admin.audit.*',
            'admin.reports.index' => 'admin.reports.*',
            'admin.support.index' => 'admin.support.*',
            'admin.documents.index' => 'admin.documents.*',
            'admin.notifications.index' => 'admin.notifications.*',
            'admin.static-pages.index' => 'admin.static-pages.*',
            'admin.faq.index' => 'admin.faq.*',
            'admin.home-content.index' => 'admin.home-content.*',
            'admin.contact-enquiries.index' => 'admin.contact-enquiries.*',
            'admin.subscriptions.index' => ['admin.subscriptions.*', 'admin.pricing-plans.*'],
            'admin.business-settings.index' => 'admin.business-settings.*',
            'admin.profile.show' => 'admin.profile.*',
            'admin.search' => 'admin.search*',
        ];

        $group = $groups[$routeName] ?? null;

        if (is_array($group)) {
            return request()->routeIs($group);
        }

        return $group && request()->routeIs($group);
    }

    public static function globalSearchUrl(): string
    {
        return route('admin.search');
    }

    public static function globalSuggestUrl(): string
    {
        return route('admin.search.suggest');
    }
}
