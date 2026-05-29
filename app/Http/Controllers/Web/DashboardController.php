<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Send each user to the dashboard matching their role. */
    public function index(Request $request)
    {
        return match ($request->user()->role) {
            UserRole::InsuranceCompany => redirect()->route('insurance.index'),
            UserRole::LabAdmin         => redirect()->route('lab.index'),
            UserRole::Doctor           => redirect()->route('doctor.index'),
        };
    }
}
