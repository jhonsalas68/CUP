@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-200 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:focus:ring-indigo-500 rounded-xl shadow-sm text-sm py-2.5 transition duration-150']) }}>
