@props(['disabled' => false])

@if ($attributes->get('type') === 'password')
    <div class="relative w-full" data-password-wrapper>
        <input @disabled($disabled) {{ $attributes->except('type')->merge(['type' => 'password', 'class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pr-12']) }}>
        <button type="button" data-password-toggle class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-gray-400 hover:text-gray-700" aria-label="Show password" title="Show password">
            <svg data-password-eye-show xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.062 12.348a1 1 0 0 1 0-.696C3.423 7.943 7.36 5 12 5c4.64 0 8.577 2.943 9.938 6.652a1 1 0 0 1 0 .696C20.577 16.057 16.64 19 12 19c-4.64 0-8.577-2.943-9.938-6.652Z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg data-password-eye-hide xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 3 18 18"/><path d="M10.58 10.58a2 2 0 0 0 2.83 2.83"/><path d="M9.88 5.09A10.94 10.94 0 0 1 12 4.9c4.64 0 8.58 2.94 9.94 6.65a1 1 0 0 1 0 .7 10.99 10.99 0 0 1-3.01 4.02M6.61 6.61a11 11 0 0 0-4.55 4.94 1 1 0 0 0 0 .7C3.42 16.06 7.36 19 12 19c1.36 0 2.65-.25 3.83-.7"/></svg>
        </button>
    </div>
@else
    <input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}>
@endif

@if ($attributes->get('type') === 'password')
<script>
(function () {
    const initPasswordToggles = () => document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        if (button.dataset.initialized) return;
        button.dataset.initialized = '1';
        button.addEventListener('click', () => {
            const wrapper = button.closest('[data-password-wrapper]');
            const input = wrapper?.querySelector('input');
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            button.title = show ? 'Hide password' : 'Show password';
            button.querySelector('[data-password-eye-show]')?.classList.toggle('hidden', show);
            button.querySelector('[data-password-eye-hide]')?.classList.toggle('hidden', !show);
        });
    });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initPasswordToggles, { once: true }); else initPasswordToggles();
})();
</script>
@endif
