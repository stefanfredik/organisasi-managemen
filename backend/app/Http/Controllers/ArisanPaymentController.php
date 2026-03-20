<?php

namespace App\Http\Controllers;

use App\Models\Arisan;
use App\Models\ArisanPayment;
use Illuminate\Http\Request;

class ArisanPaymentController extends Controller
{
    public function store(Request $request, Arisan $arisan)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'month' => 'required|string|max:7',
        ]);

        if ($arisan->payments()->where('member_id', $validated['member_id'])->where('month', $validated['month'])->exists()) {
            return back()->with('error', 'Pembayaran untuk bulan tersebut sudah tercatat.');
        }

        // Penerima arisan pada bulan tersebut tidak perlu menyetor
        $isWinner = $arisan->draws()
            ->where('period_month', $validated['month'])
            ->whereHas('winners', fn($q) => $q->where('member_id', $validated['member_id']))
            ->exists();

        if ($isWinner) {
            return back()->with('error', 'Anggota ini adalah penerima arisan bulan tersebut dan tidak perlu menyetor.');
        }

        $arisan->payments()->create([
            'member_id' => $validated['member_id'],
            'month' => $validated['month'],
            'amount' => $arisan->amount_per_month,
        ]);

        return back()->with('success', 'Setoran berhasil dicatat.');
    }

    public function destroy(Arisan $arisan, ArisanPayment $payment)
    {
        if ($payment->arisan_id === $arisan->id) {
            $payment->delete();
            return back()->with('success', 'Catatan setoran dibatalkan.');
        }

        return back()->with('error', 'Data pembayaran tidak ditemukan.');
    }
}
