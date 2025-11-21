@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-start gap-4 p-4 bg-red-50 border-l-4 border-red-600 rounded-r-xl shadow-sm">
                <!-- Icona triangolo con punto esclamativo -->
                <div class="flex-shrink-0 mt-0.5">
                    <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>

                <!-- Testo messaggio -->
                <span class="text-red-900 font-medium leading-relaxed">
                    {{ $message }}
                </span>
            </li>
        @endforeach
    </ul>
@endif
