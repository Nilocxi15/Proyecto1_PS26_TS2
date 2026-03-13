<?php

namespace App\Http\Controllers;

use App\Services\CitizenHomeDataService;
use Illuminate\View\View;

class CitizenHomeController extends Controller
{
    public function __construct(private readonly CitizenHomeDataService $citizenHomeDataService)
    {
    }

    public function publicHome(): View
    {
        return view('citizen.home', $this->citizenHomeDataService->buildHomeData());
    }

    public function citizenHome(): View
    {
        return view('citizen.home', $this->citizenHomeDataService->buildHomeData());
    }
}
