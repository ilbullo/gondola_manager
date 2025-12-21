<?php

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.guest');

state(['email' => '']);

rules(['email' => ['required', 'string', 'email']]);

$sendPasswordResetLink = function () {
    $this->validate();

    $status = Password::sendResetLink(
        $this->only('email')
    );

    if ($status != Password::RESET_LINK_SENT) {
        $this->addError('email', __($status));
        return;
    }

    $this->reset('email');
    Session::flash('status', __($status));
};

?>

<div class="w-full">
    {{-- RESET CARD --}}
    <div class="bg-white rounded-[2.5rem] shadow-2xl border border-slate-200 overflow-hidden">
        
        {{-- Header Card --}}
        <div class="bg-slate-900 p-6 text-center relative">
            {{-- Tasto per tornare indietro --}}
            <a href="{{ route('login') }}" class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition-colors" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="text-white font-black uppercase italic tracking-[0.2em] text-sm">Recupero Password</h2>
        </div>

        <div class="p-8 sm:p-10">
            {{-- Spiegazione Professionale --}}
            <div class="mb-8 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-tight leading-relaxed">
                    {{ __('Password dimenticata? Inserisci la tua email e ti invieremo un link per impostarne una nuova.') }}
                </p>
            </div>

            <x-auth-session-status class="mb-6" :status="session('status')" />

            <form wire:submit="sendPasswordResetLink" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Indirizzo Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                            </svg>
                        </span>
                        <input wire:model="email" id="email" type="email" name="email" required autofocus
                            class="block w-full pl-12 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-slate-900 font-bold text-sm focus:ring-4 focus:ring-indigo-100 transition-all placeholder-slate-300"
                            placeholder="esempio@email.it">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2 ml-1" />
                </div>

                {{-- SUBMIT --}}
                <div class="pt-2">
                    <button type="submit" 
                        class="w-full py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-sm uppercase tracking-[0.1em] shadow-xl shadow-indigo-100 transition-all active:scale-95 flex justify-center items-center gap-3 group">
                        <span>{{ __('Invia Link di Reset') }}</span>
                        <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
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