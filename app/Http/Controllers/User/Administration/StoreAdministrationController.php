<?php

namespace App\Http\Controllers\User\Administration;

use App\Http\Controllers\Controller;
use App\Models\Administration;
use App\Models\StatusAdministration;
use App\Models\Statussdm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreAdministrationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'id_category'   => 'required|uuid|exists:category_administrations,id',
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'description'   => 'nullable|string',
            'bring_laptop'  => 'boolean',
            'contacted'     => 'boolean',
        ]);

        $status = StatusAdministration::where('name', 'pending')->first();

        $user = Auth::user()->id;

        Administration::create([
            'id_user'       => $user,
            'id_category'   => $request->id_category,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'description'   => $request->description,
            'bring_laptop'  => $request->boolean('bring_laptop'),
            'contacted'     => $request->boolean('contacted'),
            'id_status'     => $status->id,
        ]);

        $absentStatus = Statussdm::firstOrCreate(['status_sdm' => 'Absent']);
        User::where('id', $user)->update(['id_activity_status_sdm' => $absentStatus->id]);

        return redirect()->route('staff.administration.index')->with('success', 'Absen berhasil dibuat');
    }
}
