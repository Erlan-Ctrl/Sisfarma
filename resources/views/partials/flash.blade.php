@if (session('status'))
    <div class="mb-6 flash flash--success flash-animate-in" data-flash="1" data-flash-autodismiss="5000" role="status" aria-live="polite">
        <div class="flash__icon" aria-hidden="true">
            <svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.35 7.4a1 1 0 0 1-1.42.003L3.29 9.5a1 1 0 1 1 1.42-1.4l3.52 3.573 6.64-6.68a1 1 0 0 1 1.414-.004Z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="flash__content">
            <p class="flash__title">Pronto</p>
            <p class="flash__body">{{ session('status') }}</p>
        </div>
        <button class="flash__close" type="button" data-flash-close="1" aria-label="Fechar aviso">
            <svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                <path d="M6.28 5.22a.75.75 0 0 1 1.06 0L10 7.88l2.66-2.66a.75.75 0 1 1 1.06 1.06L11.06 8.94l2.66 2.66a.75.75 0 1 1-1.06 1.06L10 10l-2.66 2.66a.75.75 0 1 1-1.06-1.06l2.66-2.66-2.66-2.66a.75.75 0 0 1 0-1.06Z"/>
            </svg>
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 flash flash--danger flash-animate-in" data-flash="1" role="alert">
        <div class="flash__icon" aria-hidden="true">
            <svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.5a.75.75 0 0 0-1.5 0v4.25a.75.75 0 0 0 1.5 0V6.5Zm0 7.25a.75.75 0 0 0-1.5 0v.5a.75.75 0 0 0 1.5 0v-.5Z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="flash__content">
            <p class="flash__title">Revise os campos abaixo</p>
            <ul class="flash__list text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
        <button class="flash__close" type="button" data-flash-close="1" aria-label="Fechar aviso">
            <svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                <path d="M6.28 5.22a.75.75 0 0 1 1.06 0L10 7.88l2.66-2.66a.75.75 0 1 1 1.06 1.06L11.06 8.94l2.66 2.66a.75.75 0 1 1-1.06 1.06L10 10l-2.66 2.66a.75.75 0 1 1-1.06-1.06l2.66-2.66-2.66-2.66a.75.75 0 0 1 0-1.06Z"/>
            </svg>
        </button>
    </div>
@endif
