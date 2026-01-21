<?php

namespace App\Http\Controllers;

use App\Models\Tenancy;
use App\Services\MpesaMessageParserService;
use App\Services\PaymentAllocationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MpesaMessageController extends Controller
{
    private $parserService;
    private $allocationService;

    public function __construct(
        MpesaMessageParserService $parserService,
        PaymentAllocationService $allocationService
    ) {
        $this->parserService = $parserService;
        $this->allocationService = $allocationService;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // We only want to show tenancies that are currently active.
        $tenancies = Tenancy::with(['tenant', 'unit.property'])
            ->where('status', 'active')
            ->get();

        return view('mpesa-messages.create', compact('tenancies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'tenancy_id' => 'required|exists:tenancies,id',
            'raw_text' => 'required|string|max:1000',
        ]);

        $parsedData = $this->parserService->parse($validatedData['raw_text']);

        if (!$parsedData) {
            return back()->withInput()->with('error', 'The M-Pesa message format is invalid or could not be parsed.');
        }

        $result = $this->allocationService->allocatePayment($validatedData['tenancy_id'], $parsedData);

        if ($result['success']) {
            return redirect()->route('payments.index')->with('success', $result['message']);
        } else {
            return back()->withInput()->with('error', $result['message']);
        }
    }
}
