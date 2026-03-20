<?php

namespace App\Http\Controllers;

use App\Models\Arisan;
use App\Models\ArisanDraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArisanDrawController extends Controller
{
    public function store(Request $request, Arisan $arisan)
    {
        $validated = $request->validate([
            'period_month' => 'required|string|max:7', // "2024-05" format standard
            'winner_ids' => 'required|array|min:1',
            'winner_ids.*' => 'exists:members,id',
            'notes' => 'nullable|string',
        ]);

        // Cek bentrokan periode
        if ($arisan->draws()->where('period_month', $validated['period_month'])->exists()) {
            return back()->with('error', 'Sesi penarikan untuk bulan/periode tersebut sudah ada.');
        }
        
        // Cek kepatuhan menang ganda
        $existingWinners = $arisan->winners()->whereIn('member_id', $validated['winner_ids'])->pluck('member_id')->toArray();
        if (!empty($existingWinners)) {
            return back()->with('error', 'Salah satu pemenang yang dipilih sudah pernah menerima uang dari arisan ini sebelumnya.');
        }

        DB::transaction(function() use ($arisan, $validated) {
            $draw = $arisan->draws()->create([
                'period_month' => $validated['period_month'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['winner_ids'] as $winnerId) {
                $draw->winners()->create([
                    'arisan_id' => $arisan->id, // dicatat ulang per-arisan
                    'member_id' => $winnerId,
                ]);
            }
        });

        $count = count($validated['winner_ids']);
        return back()->with('success', "Pemenang Arisan ($count orang) berhasil diundi dan dicatat.");
    }

    public function destroy(Arisan $arisan, ArisanDraw $draw)
    {
        if ($draw->arisan_id === $arisan->id) {
            $draw->delete(); // winners will cascade
            return back()->with('success', 'Hasil penarikan/kocokan berhasil dicabut.');
        }
        return back()->with('error', 'Kocokan tidak ditemukan.');
    }
}
