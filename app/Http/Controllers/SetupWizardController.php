<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

class SetupWizardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Wizard', [
            'appUrl' => url('/'),
            'developerMode' => Setting::get('developer_mode', true),
        ]);
    }
}
