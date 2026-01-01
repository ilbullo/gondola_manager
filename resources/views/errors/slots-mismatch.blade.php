@extends('layouts.exception')

@section('title', 'Incoerenza slot')

@section('content')
    <div class="space-y-4">
        <div class="p-3 bg-rose-50 border-l-4 border-rose-500 rounded-r-md">
            <p class="text-xs font-semibold text-amber-800 leading-tight">
                {{ $message }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-px bg-slate-100 border border-slate-200 rounded-lg overflow-hidden">
            <div class="bg-white p-3 flex justify-between items-center">
                <span class="text-[11px] font-bold uppercase text-slate-400">Licenza</span>
                <span class="text-sm font-mono font-bold text-blue-600">#{{ $license }}</span>
            </div>
            
            <div class="bg-white p-3 flex justify-between items-center text-sm">
                <span class="text-[11px] font-bold uppercase text-slate-400">Dichiarati</span>
                <span class="font-semibold text-slate-700">{{ $declared_slots }}</span>
            </div>

            <div class="bg-white p-3 flex justify-between items-center text-sm border-l-2 border-l-amber-500">
                <span class="text-[11px] font-bold uppercase text-slate-400 italic">Rilevati</span>
                <span class="font-bold text-amber-600">{{ $actual_count }}</span>
            </div>
        </div>

        <div class="rounded-lg border border-slate-100 bg-slate-50/50 p-3">
            <div class="flex gap-2">
                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-[11px] text-slate-500 leading-normal">
                    <strong>Nota tecnica:</strong> È stata rilevata una discrepanza tra il contatore degli slot e i lavori effettivamente presenti nella riga. Potrebbe essere necessario ricaricare la matrice.
                </p>
            </div>
        </div>
    </div>
@endsection