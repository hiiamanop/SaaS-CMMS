<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $years = range(now()->year - 2, now()->year + 1);

        $data = ReportService::forMonth($year, $month);

        return view('reports.index', array_merge($data, compact('year', 'month', 'years')));
    }
}
