{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-2xl rounded-2xl border border-gray-200">

                <div class="p-8 sm:p-12 text-center">

                    <!-- Benvenuto -->
                    <header class="mb-10">
                        <h1 class="text-3xl sm:text-4xl font-black text-gray-900 mb-3">
                            Benvenuto, {{ auth()->user()->name }}!
                        </h1>
                        <p class="text-lg text-gray-600">
                            {{ __("Scegli l'operazione da eseguire oggi") }}
                        </p>
                    </header>

                    <!-- /Benvenuto -->

                    <!-- CARD DI ACCESSO RAPIDO -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto mt-12">

                        <!-- 1. Gestione Agenzie -->
                        <a
                            href="{{ route('agency-manager') }}"
                            class="group block bg-gradient-to-br from-sky-500 to-blue-600 rounded-2xl shadow-xl hover:shadow-2xl
                                   transform hover:-translate-y-3 focus:-translate-y-1 focus:outline-none focus:ring-4 focus:ring-sky-400 focus:ring-offset-4
                                   transition-all duration-300 overflow-hidden"
                            aria-label="Vai alla gestione delle agenzie"
                        >
                            <div class="p-10 text-white text-center">
                                <div class="w-24 h-24 mx-auto mb-6 bg-white/20 rounded-full flex items-center justify-center
                                            group-hover:scale-110 group-focus:scale-110 transition-transform duration-300">
                                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h-4m-6 0H5a2 2 0 002-2v-1" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold mb-2">Gestione Agenzie</h3>
                                <p class="text-white/90 text-sm leading-relaxed">
                                    Aggiungi, modifica o elimina le agenzie del sistema
                                </p>
                                <span class="inline-block mt-6 text-lg font-bold opacity-90
                                             group-hover:opacity-100 group-focus:opacity-100 transition-opacity">
                                    Vai alle Agenzie →
                                </span>
                            </div>
                        </a>

                        </a>

                        <!-- 2. Gestione Utenti (NUOVO) -->
                        <a
                            href="{{ route('user-manager') }}"
                            class="group block bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl shadow-xl hover:shadow-2xl
                                   transform hover:-translate-y-3 focus:-translate-y-1 focus:outline-none focus:ring-4 focus:ring-purple-400 focus:ring-offset-4
                                   transition-all duration-300 overflow-hidden"
                            aria-label="Vai alla gestione degli utenti"
                        >
                            <div class="p-10 text-white text-center">
                                <div class="w-24 h-24 mx-auto mb-6 bg-white/20 rounded-full flex items-center justify-center
                                            group-hover:scale-110 group-focus:scale-110 transition-transform duration-300">
                                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a1 1 0 01-1-1v-1a4 4 0 014-4h8a4 4 0 014 4v1a1 1 0 01-1 1zm6-12h.01M9 9h6 6 6 9v6l3 3 3-3V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 8a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold mb-2">Gestione Utenti</h3>
                                <p class="text-white/90 text-sm leading-relaxed">
                                    Crea, modifica o elimina gli account utente
                                </p>
                                <span class="inline-block mt-6 text-lg font-bold opacity-90
                                             group-hover:opacity-100 group-focus:opacity-100 transition-opacity">
                                    Vai agli Utenti →
                                </span>
                            </div>
                        </a>

                        <!-- 3. Tabella del Giorno -->
                        <a
                            href="{{ route('table-manager') }}"
                            class="group block bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-xl hover:shadow-2xl
                                   transform hover:-translate-y-3 focus:-translate-y-1 focus:outline-none focus:ring-4 focus:ring-emerald-400 focus:ring-offset-4
                                   transition-all duration-300 overflow-hidden"
                            aria-label="Apri la tabella del giorno"
                        >
                            <div class="p-10 text-white text-center">
                                <div class="w-24 h-24 mx-auto mb-6 bg-white/20 rounded-full flex items-center justify-center
                                            group-hover:scale-110 group-focus:scale-110 transition-transform duration-300">
                                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold mb-2">Tabella del Giorno</h3>
                                <p class="text-white/90 text-sm leading-relaxed">
                                    Assegna licenze e conferma la tabella giornaliera
                                </p>
                                <span class="inline-block mt-6 text-lg font-bold opacity-90
                                             group-hover:opacity-100 group-focus:opacity-100 transition-opacity">
                                    Apri Table Manager →
                                </span>
                            </div>
                        </a>

                    </div>
                    <!-- /CARD DI ACCESSO RAPIDO -->

                    <!-- Footer informativo -->
                    <footer class="mt-16 text-center">
                        <p class="text-sm text-gray-500">
                            Ultimo accesso: 
                            <time datetime="{{ auth()->user()->last_login_at?->toDateTimeString() }}">
                                {{ auth()->user()->last_login_at?->diffForHumans() ?? 'mai' }}
                            </time>
                        </p>
                    </footer>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>