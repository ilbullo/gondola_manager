@extends('layouts.exception')  <!-- o il nome esatto del tuo file layout -->

@section('title', 'Incoerenza slot')

@section('icon')
    <svg class="w-10 h-10 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
@endsection

@section('content')
    <div class="space-y-6">

        <p class="text-lg font-medium text-amber-700 leading-relaxed">
            {{ $message }}
        </p>

        <div class="bg-amber-50 rounded-md p-6 border border-amber-100 text-sm space-y-4">
            <div class="flex justify-between border-b border-amber-200 pb-3">
                <span class="font-medium text-gray-700">Licenza</span>
                <span class="font-semibold text-gray-900">#{{ $license }}</span>
            </div>

            <div class="flex justify-between border-b border-amber-200 pb-3">
                <span class="font-medium text-gray-700">Slot dichiarati</span>
                <span class="font-semibold text-gray-900">{{ $declared_slots }}</span>
            </div>

            <div class="flex justify-between">
                <span class="font-medium text-gray-700">Lavori effettivamente presenti</span>
                <span class="font-semibold text-gray-900">{{ $actual_count }}</span>
            </div>
        </div>

        <p class="text-sm text-gray-600">
            Controlla la matrice della licenza: il contatore degli slot occupati non corrisponde 
            al numero reale di lavori presenti nella riga.
        </p>

    </div>
@endsection