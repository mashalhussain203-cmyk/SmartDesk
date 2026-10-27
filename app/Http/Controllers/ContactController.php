<?php

namespace App\Http\Controllers;

use App\Services\GmailLiveChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class ContactController extends Controller
{
    /**
     * Toon de publieke contactpagina.
     */
    public function show(): Response
    {
        return response()->view('site.contact')
            ->header('Cache-Control', 'private, no-store, no-cache, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Verwerk en verstuur het contactformulier.
     */
    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'first_name' => ['required', 'string', 'max:100'],
                'last_name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email:rfc', 'max:255'],
                'message' => ['required', 'string', 'min:10', 'max:5000'],
            ],
            [
                'first_name.required' => __('Vul uw voornaam in.'),
                'first_name.string' => __('Uw voornaam is ongeldig.'),
                'first_name.max' => __('Uw voornaam mag maximaal 100 tekens bevatten.'),
                'last_name.required' => __('Vul uw achternaam in.'),
                'last_name.string' => __('Uw achternaam is ongeldig.'),
                'last_name.max' => __('Uw achternaam mag maximaal 100 tekens bevatten.'),
                'email.required' => __('Vul uw e-mailadres in.'),
                'email.email' => __('Vul een geldig e-mailadres in.'),
                'email.max' => __('Uw e-mailadres mag maximaal 255 tekens bevatten.'),
                'message.required' => __('Vul een toelichting in.'),
                'message.string' => __('Uw toelichting is ongeldig.'),
                'message.min' => __('Uw toelichting moet minimaal 10 tekens bevatten.'),
                'message.max' => __('Uw toelichting mag maximaal 5000 tekens bevatten.'),
            ]
        );

        $data = [
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'email' => strtolower(trim($validated['email'])),
            'message' => trim($validated['message']),
        ];

        try {
            // Resolve the service inside the try block because its constructor
            // can throw when the Gmail configuration is incomplete.
            $gmail = app(GmailLiveChatService::class);
            $gmail->sendContactMessage($data);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', __('Uw bericht kon op dit moment niet worden verzonden. Probeer het later opnieuw.'));
        }

        // The owner's message has already been sent. Confirmation failure
        // must not incorrectly tell the visitor that the request failed.
        try {
            $gmail->sendContactConfirmation($data);
        } catch (Throwable $exception) {
            report($exception);
        }

        return redirect()
            ->route('contact')
            ->with(
                'success',
                __('Bedankt voor uw bericht. We hebben uw aanvraag ontvangen en nemen zo snel mogelijk contact met u op.')
            );
    }
}
