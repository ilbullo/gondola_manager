<button {{ $attributes->merge(['type' => 'submit', 'class' => 'px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2']) }}>
    {{ $slot }}
</button>
