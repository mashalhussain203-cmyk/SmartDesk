import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import '../css/admin-live-chat-tailwind.css';

// An isolated React reply bar: Laravel and the existing admin chat own the
// conversations and sends. React only enhances the operator's reply workflow.
const host = document.getElementById('admin-live-chat-react-tools');
const chat = document.getElementById('admin-live-chat');

if (host && chat) {
    const h = React.createElement;

    function ReplyToolbar() {
        const [reply, setReply] = useState(() => chat.dataset.replyId
            ? { id: Number(chat.dataset.replyId), label: chat.dataset.replyLabel || 'Bericht' }
            : null);

        useEffect(() => {
            const selected = (event) => setReply(event.detail || null);
            const cleared = () => setReply(null);

            window.addEventListener('admin-chat:reply-selected', selected);
            window.addEventListener('admin-chat:reply-cleared', cleared);
            return () => {
                window.removeEventListener('admin-chat:reply-selected', selected);
                window.removeEventListener('admin-chat:reply-cleared', cleared);
            };
        }, []);

        const focusComposer = () =>
            window.dispatchEvent(new CustomEvent('admin-chat:reply-focus'));
        const cancelReply = () =>
            window.dispatchEvent(new CustomEvent('admin-chat:reply-cancel'));

        return h('div', {
            className: 'admintw:flex admintw:flex-wrap admintw:items-center admintw:justify-between admintw:gap-3 admintw:rounded-2xl admintw:border admintw:border-violet-300/20 admintw:bg-slate-900/70 admintw:px-4 admintw:py-3 admintw:shadow-lg',
            role: 'status',
            'aria-live': 'polite',
            'data-react-reply-toolbar': '',
        },
            h('div', {
                className: 'admintw:flex admintw:min-w-0 admintw:flex-1 admintw:items-start admintw:gap-3',
            },
                h('span', {
                    className: 'admintw:mt-0.5 admintw:flex admintw:size-9 admintw:shrink-0 admintw:items-center admintw:justify-center admintw:rounded-xl admintw:bg-violet-400/15 admintw:text-violet-200',
                    'aria-hidden': 'true',
                },
                    h('svg', { width: 19, height: 19, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' },
                        h('path', { d: 'M9 17l-5-5 5-5M4 12h10a6 6 0 0 1 6 6v2' }),
                    ),
                ),
                h('div', { className: 'admintw:min-w-0 admintw:flex-1' },
                    h('strong', { className: 'admintw:block admintw:text-sm admintw:font-semibold admintw:text-white' },
                        reply ? 'Antwoorden op bericht' : 'Live antwoorden'),
                    h('p', { className: 'admintw:mt-1 admintw:truncate admintw:text-xs admintw:leading-relaxed admintw:text-slate-300' },
                        reply ? reply.label : 'Kies Beantwoorden onder een bericht of typ direct je reactie.'),
                ),
            ),
            h('div', { className: 'admintw:flex admintw:shrink-0 admintw:items-center admintw:gap-2' },
                reply && h('button', {
                    type: 'button',
                    onClick: cancelReply,
                    className: 'admintw:rounded-xl admintw:border admintw:border-white/15 admintw:px-3 admintw:py-2 admintw:text-xs admintw:font-medium admintw:text-slate-200 admintw:transition-colors hover:admintw:bg-white/10',
                    'aria-label': 'Antwoord op specifiek bericht annuleren',
                }, 'Annuleren'),
                h('button', {
                    type: 'button',
                    onClick: focusComposer,
                    className: 'admintw:rounded-xl admintw:bg-violet-500 admintw:px-4 admintw:py-2 admintw:text-xs admintw:font-bold admintw:text-white admintw:shadow-md admintw:transition-colors hover:admintw:bg-violet-400 focus-visible:admintw:outline-2 focus-visible:admintw:outline-offset-2',
                }, 'Typ antwoord'),
            ),
        );
    }

    try {
        createRoot(host).render(h(ReplyToolbar));
        chat.classList.add('lca-react-ready');
    } catch (error) {
        console.warn('[AdminLiveChat] React toolbar unavailable; using Blade reply preview.', error);
    }
}
