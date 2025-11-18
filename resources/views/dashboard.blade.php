<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
                <div class="p-8 sm:p-12 text-center">

                    <!-- Messaggio benvenuto -->
                    <h3 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                        Benvenuto, {{ auth()->user()->name }}!
                    </h3>
                    <p class="text-lg text-gray-600 mb-12">
                        {{ __("Scegli cosa fare oggi") }}
                    </p>

                    <!-- DUE PULSANTI VELOCI -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">

                        <!-- Pulsante 1 → Gestione Agenzie -->
                        <a href="{{ route('agency-manager') }}"
                           class="group block p-10 bg-gradient-to-br from-sky-500 to-blue-600 rounded-2xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                            <div class="text-white">
                                <div class="w-20 h-20 mx-auto mb-6 bg-white/20 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h-4m-6 0H5a2 2 0 002-2v-1" />
                                    </svg>
                                </div>
                                <h4 class="text-2xl font-bold mb-2">Gestione Agenzie</h4>
                                <p class="text-white/90 text-sm">Aggiungi, modifica o elimina le agenzie</p>
                            </div>
                            <div class="mt-6 text-white font-bold text-lg">
                                Vai alle Agenzie
                            </div>
                        </a>

                        <!-- Pulsante 2 → Table Manager (Tabella del Giorno) -->
                        <a href="{{ route('table-manager') }}"
                           class="group block p-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                            <div class="text-white">
                                <div class="w-20 h-20 mx-auto mb-6 bg-white/20 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </div>
                                <h4 class="text-2xl font-bold mb-2">Tabella del Giorno</h4>
                                <p class="text-white/90 text-sm">Assegna licenze, modifica e conferma la tabella</p>
                            </div>
                            <div class="mt-6 text-white font-bold text-lg">
                                Apri Table Manager
                            </div>
                        </a>

                    </div>

                    <!-- Messaggio piccolo sotto (opzionale) -->
                    <p class="mt-16 text-sm text-gray-500">
                        Ultimo accesso: {{ auth()->user()->last_login_at?->diffForHumans() ?? 'mai' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>