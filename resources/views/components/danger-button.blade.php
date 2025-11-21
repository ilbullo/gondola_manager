<button {{ $attributes->merge(['type' => 'submit', 'class' => 'order-1 sm:order-2 w-full sm:w-auto px-8 py-3 text-sm font-bold text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 rounded-xl shadow-lg hover:shadow-xl focus:ring-4 focus:ring-red-500/50 transition-all duration-200 flex items-center justify-center gap-2']) }}>
    {{ $slot }}
</button>
