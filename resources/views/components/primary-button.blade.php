<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-xl font-bold text-sm text-white hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 shadow-lg shadow-indigo-500/20 active:scale-[0.98] transition duration-150']) }}>
    {{ $slot }}
</button>
