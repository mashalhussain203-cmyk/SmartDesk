@extends('layouts.site-layout')

@section('title', 'Hersteladres bevestigen | Mashal Studio')

@section(
    'meta_description',
    'Controleer de 6-cijferige code om je herstel-e-mailadres veilig te bevestigen.'
)

@section('content')
<style>
    :root {
        --verify-bg: #020202;
        --verify-card: rgba(15,15,18,.94);
        --verify-card-2: rgba(8,8,10,.97);
        --verify-text: #f7f7f8;
        --verify-muted: #8f8e94;
        --verify-yellow: #f4ee1f;
        --verify-orange: #ff6b23;
        --verify-danger: #ff9b9b;
        --verify-success: #9bf5c7;
    }

    .forgot-verify-page,
    .forgot-verify-page * {
        box-sizing: border-box;
    }

    .forgot-verify-page {
        position: relative;
        isolation: isolate;
        min-height: calc(100dvh - 76px);
        overflow: hidden;
        display: flex;
        justify-content: center;
        color: var(--verify-text);
        background:
            radial-gradient(
                circle at 50% 28%,
                rgba(255,177,0,.05),
                transparent 28rem
            ),
            var(--verify-bg);
    }

    /*
    |--------------------------------------------------------------------------
    | Animated background
    |--------------------------------------------------------------------------
    */

    .forgot-verify-bg {
        position: absolute;
        inset: 0;
        z-index: -4;
        overflow: hidden;
        pointer-events: none;
    }

    .forgot-verify-bg::before,
    .forgot-verify-bg::after {
        content: "";
        position: absolute;
        width: min(1000px, 110vw);
        height: 130px;
        border-radius: 999px;
        opacity: .25;
        will-change: transform, opacity;
    }

    .forgot-verify-bg::before {
        left: -34%;
        top: 18%;
        background:
            linear-gradient(
                180deg,
                transparent 0 28%,
                rgba(255,126,0,.06) 36%,
                rgba(255,195,53,.36) 47%,
                rgba(255,233,130,.68) 50%,
                rgba(255,126,0,.20) 57%,
                transparent 72%
            );
        transform:
            rotate(29deg)
            translate3d(0,0,0);
        animation:
            forgotVerifyBgA
            12s ease-in-out infinite alternate;
    }

    .forgot-verify-bg::after {
        right: -38%;
        top: 54%;
        background:
            linear-gradient(
                180deg,
                transparent 0 28%,
                rgba(255,147,0,.05) 38%,
                rgba(255,210,81,.28) 48%,
                rgba(255,236,145,.55) 51%,
                rgba(255,119,0,.17) 58%,
                transparent 72%
            );
        transform:
            rotate(-26deg)
            translate3d(0,0,0);
        animation:
            forgotVerifyBgB
            15s ease-in-out infinite alternate;
    }

    .forgot-verify-streak {
        position: absolute;
        left: 50%;
        bottom: 12%;
        width: min(760px, 78vw);
        height: 2px;
        border-radius: 999px;
        opacity: .22;
        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(255,125,25,.25),
                rgba(241,216,74,.78),
                rgba(255,125,25,.25),
                transparent
            );
        transform:
            translate3d(-50%,0,0)
            rotate(-8deg);
        will-change: transform, opacity;
        animation:
            forgotVerifyStreak
            9s ease-in-out infinite alternate;
    }

    @keyframes forgotVerifyBgA {
        from {
            transform:
                rotate(29deg)
                translate3d(-4%,-5px,0)
                scale(.98);
            opacity: .18;
        }

        to {
            transform:
                rotate(24deg)
                translate3d(13%,16px,0)
                scale(1.04);
            opacity: .32;
        }
    }

    @keyframes forgotVerifyBgB {
        from {
            transform:
                rotate(-26deg)
                translate3d(5%,5px,0);
            opacity: .16;
        }

        to {
            transform:
                rotate(-21deg)
                translate3d(-12%,-14px,0)
                scale(1.05);
            opacity: .30;
        }
    }

    @keyframes forgotVerifyStreak {
        from {
            transform:
                translate3d(-54%,0,0)
                rotate(-8deg);
            opacity: .14;
        }

        to {
            transform:
                translate3d(-46%,-8px,0)
                rotate(-5deg);
            opacity: .28;
        }
    }

    