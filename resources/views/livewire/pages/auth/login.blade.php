<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\form;
use function Livewire\Volt\layout;

layout('layouts.guest'); 

form(LoginForm::class);

$login = function () {
    $this->validate();
    $this->form->authenticate();
    Session::regenerate();
    $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
};

?>

<div class="w-full">
    {{-- LOGIN CARD --}}
    <div class="bg-white rounded-[2.5rem] shadow-2xl border border-slate-200 overflow-hidden">
        {{-- Header interno alla card - Mantiene il focus --}}
        <div class="bg-slate-900 p-6 text-center">
            <h2 class="text-white font-black uppercase italic tracking-[0.2em] text-sm">Autenticazione</h2>
        </div>

        <div class="p-8 sm:p-10">
            <x-auth-session-status class="mb-6" :status="session('status')" />

            <form wire:submit="login" class="space-y-5">
                {{-- LICENSE NUMBER --}}
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Numero Licenza</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm5 3h-7a1 1 0 01-1-1v-1a2 2 0 012-2h3a2 2 0 012 2v1a1 1 0 01-1 1z" /></svg>
                        </span>
                        <input wire:model="form.license_number" type="text" required autofocus
                            class="block w-full pl-12 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-slate-900 font-bold text-sm focus:ring-4 focus:ring-indigo-100 transition-all placeholder-slate-300"
                            placeholder="Inserisci licenza...">
                    </div>
                    <x-input-error :messages="$errors->get('form.license_number')" class="mt-2 ml-1" />
                </div>

                {{-- PASSWORD --}}
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </span>
                        <input wire:model="form.password" type="password" required autocomplete="current-password"
                            class="block w-full pl-12 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-slate-900 font-bold text-sm focus:ring-4 focus:ring-indigo-100 transition-all placeholder-slate-300"
                            placeholder="••••••••">
                    </div>
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2 ml-1" />
                </div>

                {{-- OPTIONS --}}
                <div class="flex items-center justify-between">
                    <label for="remember" class="inline-flex items-center cursor-pointer group">
                        <div class="relative">
                            <input wire:model="form.remember" id="remember" type="checkbox" class="sr-only">
                            <div class="w-8 h-4 bg-slate-200 rounded-full border border-slate-300 transition-colors group-has-[:checked]:bg-emerald-500 group-has-[:checked]:border-emerald-600"></div>
                            <div class="absolute left-0.5 top-0.5 w-3 h-3 bg-white rounded-full transition-transform group-has-[:checked]:translate-x-4"></div>
                        </div>
                        <span class="ms-2 text-[10px] font-black text-slate-400 uppercase tracking-widest group-hover:text-slate-600 transition-colors">Ricordami</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:text-slate-900 transition-colors" href="{{ route('password.request') }}" wire:navigate>
                            Persa?
                        </a>
                    @endif
                </div>

                {{-- SUBMIT --}}
                <div class="pt-2">
                    <button type="submit" 
                        class="w-full py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-sm uppercase tracking-[0.2em] shadow-xl shadow-indigo-100 transition-all active:scale-95 flex justify-center items-center gap-3">
                        <span>Entra nel Sistema</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- FOOTER WEB - Appare solo se il layout ha spazio --}}
    <div class="hidden lg:block mt-8 text-center">
        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em]">
            &copy; {{ date('Y') }} GondolaManager &bull; All Rights Reserved
        </p>
    </div>
</div>