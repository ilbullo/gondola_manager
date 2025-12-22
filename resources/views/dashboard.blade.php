{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <div class="h-full flex flex-col overflow-hidden bg-slate-50">
        
        {{-- HEADER FISSO (BENVENUTO) --}}
        <div class="shrink-0 p-6 sm:p-10 sm:pb-6">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-4xl sm:text-5xl font-black text-slate-900 uppercase italic tracking-tighter leading-none mb-2">
                    Ciao, {{ auth()->user()->name }}!
                </h1>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] ml-1">
                    Seleziona un modulo operativo per iniziare
                </p>
            </div>
        </div>

        {{-- AREA SCORREVOLE (GRID CARD) --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar p-6 sm:p-10 pt-0">
            <div class="max-w-7xl mx-auto">
                
                {{-- GRID CARD OPERATIVE --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    
                    @if ($isAdmin || $isBancale)
                        {{-- GESTIONE AGENZIE --}}
                        <a href="{{ route('agency-manager') }}"
                            class="group relative bg-white p-8 rounded-[2.5rem] shadow-xl border border-slate-200 overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl active:scale-95 flex flex-col h-full">
                            <div class="relative z-10 flex flex-col h-full">
                                <div class="w-16 h-16 bg-sky-50 text-sky-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-sky-600 group-hover:text-white transition-colors duration-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h-4m-6 0H5a2 2 0 002-2v-1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <h3 class="text-2xl font-black text-slate-800 uppercase italic tracking-tighter mb-2">Agenzie</h3>
                                <p class="text-xs font-bold text-slate-400 uppercase leading-relaxed mb-6 flex-grow">Anagrafica e gestione delle agenzie attive nel sistema.</p>
                                <span class="text-[10px] font-black text-sky-600 uppercase tracking-widest flex items-center gap-2">Apri Modulo <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 8l4 4m0 0l-4 4m4-4H3" stroke-width="3"/></svg></span>
                            </div>
                            <div class="absolute -right-4 -bottom-4 text-slate-100 opacity-20 group-hover:opacity-40 transition-opacity">
                                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h-4m-6 0H5a2 2 0 002-2v-1"/></svg>
                            </div>
                        </a>

                        {{-- TABELLA DEL GIORNO --}}
                        <a href="{{ route('table-manager') }}"
                            class="group relative bg-white p-8 rounded-[2.5rem] shadow-xl border border-slate-200 overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl active:scale-95 flex flex-col h-full">
                            <div class="relative z-10 flex flex-col h-full">
                                <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <h3 class="text-2xl font-black text-slate-800 uppercase italic tracking-tighter mb-2">Tabella</h3>
                                <p class="text-xs font-bold text-slate-400 uppercase leading-relaxed mb-6 flex-grow">Assegnazione licenze e convalida operativa giornaliera.</p>
                                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest flex items-center gap-2">Apri Modulo <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 8l4 4m0 0l-4 4m4-4H3" stroke-width="3"/></svg></span>
                            </div>
                            <div class="absolute -right-4 -bottom-4 text-slate-100 opacity-20 group-hover:opacity-40 transition-opacity">
                                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                        </a>
                    @endif

                    @if ($isAdmin)
                        {{-- GESTIONE UTENTI --}}
                        <a href="{{ route('user-manager') }}"
                            class="group relative bg-white p-8 rounded-[2.5rem] shadow-xl border border-slate-200 overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl active:scale-95 flex flex-col h-full">
                            <div class="relative z-10 flex flex-col h-full">
                                <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a1 1 0 01-1-1v-1a4 4 0 014-4h8a4 4 0 014 4v1a1 1 0 01-1 1z" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <h3 class="text-2xl font-black text-slate-800 uppercase italic tracking-tighter mb-2">Utenti</h3>
                                <p class="text-xs font-bold text-slate-400 uppercase leading-relaxed mb-6 flex-grow">Amministrazione accessi, ruoli e permessi di sistema.</p>
                                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">Apri Modulo <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 8l4 4m0 0l-4 4m4-4H3" stroke-width="3"/></svg></span>
                            </div>
                            <div class="absolute -right-4 -bottom-4 text-slate-100 opacity-20 group-hover:opacity-40 transition-opacity">
                                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a1 1 0 01-1-1v-1a4 4 0 014-4h8a4 4 0 014 4v1a1 1 0 01-1 1z"/></svg>
                            </div>
                        </a>
                    @endif

                </div>

                {{-- FOOTER OPERATIVO --}}
                <div class="mt-12 mb-8 py-8 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sistema Operativo Attivo</span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Ultimo accesso: 
                        <span class="text-slate-600 italic">
                            {{ auth()->user()->last_login_at?->diffForHumans() ?? 'In questo momento' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('custom_css')
        <style>
            .custom-scrollbar::-webkit-scrollbar { width: 4px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        </style>
    @endpush
</x-app-layout>