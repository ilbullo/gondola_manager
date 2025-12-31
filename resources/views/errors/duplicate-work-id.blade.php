@extends('layouts.exception')

@section('title', 'ID lavoro duplicato')

@section('content')
    <div class="space-y-6">

        <!-- Messaggio principale -->
        <p class="text-lg font-medium text-amber-700 leading-relaxed">
            {{ $message }}
        </p>

        <!-- Dettagli essenziali -->
        <div class="bg-amber-50 rounded-md p-6 border border-amber-100 text-sm">
            <div class="flex justify-between items-center">
                <span class="font-medium text-gray-700">Licenza interessata</span>
                <span class="font-semibold text-gray-900">#{{ $license }}</span>
            </div>
        </div>

        <!-- Suggerimento / azione -->
        <p class="text-sm text-gray-600">
            Controlla la riga della matrice per questa licenza: lo stesso ID lavoro appare più volte. 
            Rimuovi i duplicati prima di riprovare l'operazione.
        </p>

    </div>
@endsection