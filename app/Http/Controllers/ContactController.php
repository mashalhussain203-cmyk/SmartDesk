<?php

namespace App\Http\Controllers;

use App\Mail\ContactConfirmationMail;
use App\Mail\ContactReceivedMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    /**
     * Toon de publieke contactpagina.
     */
    public function show(): View
    {
        return view('site.contact');
    }

    /**
     * Verwerk en verstuur het contactformulier.
     */
    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'first_name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'last_name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'email' => [
                    'required',
                    'email:rfc',
                    'max:255',
                ],

                'message' => [
                    'required',
                    'string',
                    'min:10',
                    'max:5000',
                ],
            ],
            [
                'first_name.required' => 'Vul uw voornaam in.',
                'first_name.string' => 'Uw voornaam is ongeldig.',
                'first_name.max' => 'Uw voornaam mag maximaal 100 tekens bevatten.',

                'last_name.required' => 'Vul uw achternaam in.',
                'last_name.string' => 'Uw achternaam is ongeldig.',
                'last_name.max' => 'Uw achternaam mag maximaal 100 tekens bevatten.',

                'email.required' => 'Vul uw e-mailadres in.',
                'email.email' => 'Vul een geldig e-mailadres in.',
                'email.max' => 'Uw e-mailadres mag maximaal 255 tekens bevatten.',

                'message.required' => 'Vul een toelichting in.',
                'message.string' => 'Uw toelichting is ongeldig.',
                'message.min' => 'Uw toelichting moet minimaal 10 tekens bevatten.',
                'message.max' => 'Uw toelichting mag maximaal 5000 tekens bevatten.',
            ]
        );

        $data = [
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'email' => strtolower(trim($validated['email'])),
            'message' => trim($validated['message']),
        ];

        $contactAddress = config('mail.contact_to');

        if (! is_string($contactAddress) || $contactAddress === '') {
            $contactAddress = config('mail.from.address');
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | Mail naar SmartDesk / Mashal
            |--------------------------------------------------------------------------
            */

            Mail::to($contactAddress)
                ->send(new ContactReceivedMail($data));

            /*
            |--------------------------------------------------------------------------
            | Bevestigingsmail naar de bezoeker
            |--------------------------------------------------------------------------
            */

            Mail::to($data['email'])
                ->send(new ContactConfirmationMail($data));

            return redirect()
                ->route('contact')
                ->with(
                    'success',
                    'Bedankt voor uw bericht. We hebben uw aanvraag ontvangen en nemen zo snel mogelijk contact met u op.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Uw bericht kon op dit moment niet worden verzonden. Probeer het later opnieuw.'
                );
        }
    }
}