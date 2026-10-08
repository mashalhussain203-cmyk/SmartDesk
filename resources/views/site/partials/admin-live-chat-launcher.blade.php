{{-- Admin-only entry point. The operator inbox performs its own is_admin authorization. --}}
<style>
    .admin-chat-launcher {
        --acl-purple: #b39aff;
        --acl-blue: #82b5ff;
        position: fixed;
        z-index: 1200;
        inset: auto max(20px, env(safe-area-inset-right)) max(22px, env(safe-area-inset-bottom)) auto;
        display: grid;
        grid-template-columns: 54px minmax(0, 1fr) auto;
        align-items: center;
        gap: 13px;
        min-width: 262px;
        min-height: 76px;
        padding: 11px 15px 11px 11px;
        border: 1px solid rgba(192,171,255,.45);
        border-radius: 22px;
        background:
            radial-gradient(circle at 0% 0%, rgba(165,134,255,.22), transparent 65%),
            linear-gradient(135deg,#22213f 0%,#151e35 68%,#121927 100%);
        box-shadow: 0 23px 60px rgba(0,0,0,.48), inset 0 1px rgba(255,255,255,.12);
        color: #f7f6ff;
        font: 700 13px/1.35 Inter, system-ui, -apple-system, sans-serif;
        text-decoration: none;
        text-align: start;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        -webkit-tap-highlight-color: transparent;
    }
    .admin-chat-launcher:hover {
        color: #fff;
        transform: translateY(-4px);
        border-color: rgba(211,190,255,.8);
        box-shadow: 0 28px 68px rgba(0,0,0,.54), 0 0 34px rgba(118,104,241,.12);
    }
    .admin-chat-launcher:focus-visible {
        outline: 3px solid var(--acl-purple);
        outline-offset: 4px;
    }
    .admin-chat-launcher__icon {
        position: relative;
        display: grid;
        place-items: center;
        width: 54px;
        height: 54px;
        border: 1px solid rgba(255,255,255,.23);
        border-radius: 17px;
        background: linear-gradient(145deg,#9d7cf2,#657ef1);
        box-shadow: 0 11px 25px rgba(91,78,202,.30), inset 0 1px rgba(255,255,255,.28);
        color: #fff;
    }
    .admin-chat-launcher__icon svg {
        display: block;
        width: 25px;
        height: 25px;
    }
    .admin-chat-launcher__icon::after {
        content: "";
        position: absolute;
        inset: auto -3px -3px auto;
        width: 12px;
        height: 12px;
        border: 2px solid #171d34;
        border-radius: 50%;
        background: #75e8b2;
    }
    .admin-chat-launcher__copy {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .admin-chat-launcher__copy strong {
        color: #fff;
        font-size: 14px;
        font-weight: 810;
        letter-spacing: -.025em;
    }
    .admin-chat-launcher__copy small {
        color: #b6bddb;
        font-size: 11px;
        font-weight: 530;
        white-space: nowrap;
    }
    .admin-chat-launcher__arrow {
        display: grid;
        place-items: center;
        width: 27px;
        height: 27px;
        border: 1px solid rgba(191,181,253,.2);
        border-radius: 9px;
        color: #c9c1ff;
        background: rgba(160,141,255,.12);
        font-size: 16px;
        line-height: 1;
    }
    @media (max-width: 699px) {
        .admin-chat-launcher {
            inset: auto max(14px, env(safe-area-inset-right)) max(14px, env(safe-area-inset-bottom)) auto;
            min-width: 60px;
            min-height: 60px;
            grid-template-columns: 1fr;
            padding: 7px;
            gap: 0;
            border-radius: 20px;
        }
        .admin-chat-launcher__icon { width: 46px; height: 46px; border-radius: 15px; }
        .admin-chat-launcher__copy, .admin-chat-launcher__arrow { display: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        .admin-chat-launcher { transition: none; }
        .admin-chat-launcher:hover { transform: none; }
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
            <path d="M8 11.5h8M8 15h5"/>
        </svg>
    </span>
    <span class="admin-chat-launcher__copy">
        <strong>{{ __('Live chat') }} · Admin</strong>
        <small>{{ __('Gesprekken beheren') }}</small>
    </span>
    <span class="admin-chat-launcher__arrow" aria-hidden="true">↗</span>
</a>
