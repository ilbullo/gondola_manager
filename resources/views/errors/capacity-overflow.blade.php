@extends('layouts.exception')  <!-- o il nome esatto del tuo file layout -->

@section('title', 'Capacità superata')

@section('icon')
    <svg class="w-10 h-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
@endsection

@section('content')
    <div class="space-y-6">

        <!-- Messaggio principale dell'eccezione -->
        <p class="text-lg font-medium text-red-700 leading-relaxed">
            {{ $message }}
        </p>

        <!-- Dettagli strutturati -->
        <div class="bg-red-50 rounded-md p-6 border border-red-100 text-sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <dt class="font-medium text-gray-700 mb-1">Licenza</dt>
                    <dd class="text-gray-900 font-semibold">#{{ $license }}</dd>
                </div>

                <div>
                    <dt class="font-medium text-gray-700 mb-1">Lavori assegnati</dt>
                    <dd class="text-gray-900 font-semibold">{{ $assigned }}</dd>
                </div>

                <div class="sm:col-span-2 border-t border-red-200 pt-4">
                    <dt class="font-medium text-gray-700 mb-1">Capacità massima consentita</dt>
                    <dd class="text-gray-900 font-semibold">{{ $capacity }}</dd>
                </div>
            </div>
        </div>

        <!-- Testo di suggerimento / azione richiesta -->
        <p class="text-sm text-gray-600 leading-relaxed">
            Per risolvere il problema, riduci il numero di lavori assegnati a questa licenza 
            oppure aumenta la capacità massima consentita per la licenza #{{ $license }}.
        </p>

    </div>
@endsection

<!-- Opzionale: se vuoi aggiungere JS specifico per questa pagina di errore -->
@section('scripts')
    <!-- Esempio: focus su un pulsante o scroll automatico -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('a[href="{{ url()->previous() ?? '/' }}"]').focus();
        });
    </script>
@endsection