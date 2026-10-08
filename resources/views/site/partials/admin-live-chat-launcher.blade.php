{{-- Only include this launcher for authenticated admins. The admin controller enforces is_admin again. --}}
<style>
    .admin-chat-launcher {
        position: fixed;
        right: max(18px, env(safe-area-inset-right));
        bottom: max(20px, env(safe-area-inset-bottom));
        z-index: 1200;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        min-height: 58px;
        padding: 9px 17px 9px 10px;
        border: 1px solid rgba(143, 130, 255, .5);
        border-radius: 18px;
        background: linear-gradient(135deg, #24213b, #101522);
        color: #fff;
        text-decoration: none;
        font: 700 13px/1.3 system-ui, sans-serif;
        box-shadow: 0 14px 42px rgba(0, 0, 0, .4);
        transition: transform .15s ease, border-color .15s ease;
    }

    .admin-chat-launcher:hover {
        transform: translateY(-2px);
        border-color: #aea2ff;
        color: #fff;
    }

    .admin-chat-launcher:focus-visible {
        outline: 3px solid #aea2ff;
        outline-offset: 3px;
    }

    .admin-chat-launcher__icon {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        display: grid;
        place-items: center;
        border-radius: 13px;
        background: linear-gradient(135deg, #796bff, #4f8eff);
    }

    .admin-chat-launcher__icon svg {
        width: 22px;
        height: 22px;
    }

    .admin-chat-launcher__copy {
        display: flex;
        flex-direction: column;
        gap: 3px;
        text-align: start;
    }

    .admin-chat-launcher__copy small {
        color: #b5b8c8;
        font-size: 11px;
        font-weight: 500;
    }

    @media (max-width: 699px) {
        .admin-chat-launcher {
            right: max(12px, env(safe-area-inset-right));
            bottom: max(12px, env(safe-area-inset-bottom));
            min-width: 56px;
            min-height: 56px;
            padding: 8px;
            border-radius: 17px;
        }

        .admin-chat-launcher__copy {
            display: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .admin-chat-launcher {
            transition: none;
        }
    }
</style>

<a
    id="admin-live-chat-launcher"
    class="admin-chat-launcher"
    href="{{ route('admin.live-chat.index') }}"
    aria-label="{{ __('Live chat') }} — {{ __('Admin') }}"
    title="{{ __('Live chat') }} — {{ __('Admin') }}"
>
    <span class="admin-chat-launcher__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 11.5a8 8 0 0 1-11.5 7.2L3 21l1.8-5.7A8 8 0 1 1 20 11.5Z"/>
            <path d="M8.5 11.5h7M12 8v7"/>
        </svg>
    </span>
    <span class="admin-chat-launcher__copy">
        <strong>{{ __('Live chat') }}</strong>
        <small>{{ __('Gesprekken beheren') }}</small>
    </span>
</a>
