<?php

namespace App\Http\Controllers;

use App\Models\Arisan;
use App\Models\ArisanParticipant;
use Illuminate\Http\Request;

class ArisanParticipantController extends Controller
{
    public function store(Request $request, Arisan $arisan)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
        ]);

        if ($arisan->participants()->where('member_id', $validated['member_id'])->exists()) {
            return back()->with('error', 'Anggota tersebut sudah terdaftar di Program Arisan ini.');
        }

        $arisan->participants()->create([
            'member_id' => $validated['member_id'],
        ]);

        return back()->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function destroy(Arisan $arisan, ArisanParticipant $participant)
    {
        if ($participant->arisan_id === $arisan->id) {
            $participant->delete();
            return back()->with('success', 'Peserta berhasil dikeluarkan.');
        }
        return back()->with('error', 'Peserta tidak ditemukan atau tidak valid.');
    }
}
