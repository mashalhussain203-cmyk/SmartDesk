<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Maak de tabel voor login-goedkeuringsverzoeken.
     */
    public function up(): void
    {
        Schema::create(
            'login_approval_challenges',
            function (Blueprint $table): void {

                /*
                |--------------------------------------------------------------------------
                | Primary key
                |--------------------------------------------------------------------------
                */

                $table
                    ->uuid('id')
                    ->primary();


                /*
                |--------------------------------------------------------------------------
                | Gebruiker
                |--------------------------------------------------------------------------
                */

                $table
                    ->unsignedBigInteger('user_id')
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Sessie waar de login gestart is
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'origin_session_id',
                        255
                    )
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                |
                | Bijvoorbeeld:
                |
                | - pending
                | - approved
                | - rejected
                | - expired
                | - consumed
                |
                */

                $table
                    ->string(
                        'status',
                        20
                    )
                    ->default('pending')
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Goedkeuringsnummer
                |--------------------------------------------------------------------------
                |
                | Alleen de hash van het nummer wordt opgeslagen.
                |
                */

                $table
                    ->string('number_hash');


                /*
                |--------------------------------------------------------------------------
                | Beschikbare goedkeuringsopties
                |--------------------------------------------------------------------------
                */

                $table
                    ->json('options');


                /*
                |--------------------------------------------------------------------------
                | Ingelogd blijven
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean('remember')
                    ->default(false);


                /*
                |--------------------------------------------------------------------------
                | Login provider
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'login_provider',
                        50
                    )
                    ->default('password');


                /*
                |--------------------------------------------------------------------------
                | Request informatie
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'requested_ip',
                        45
                    )
                    ->nullable();

                $table
                    ->text('requested_user_agent')
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Sessie die de aanvraag heeft goedgekeurd
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'approved_by_session_id',
                        255
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Goedkeuringsdatum
                |--------------------------------------------------------------------------
                */

                $table
                    ->dateTime('approved_at')
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Verlooptijd
                |--------------------------------------------------------------------------
                |
                | We gebruiken bewust DATETIME in plaats van TIMESTAMP.
                |
                | Sommige MySQL/MariaDB-configuraties geven anders:
                |
                | Invalid default value for 'expires_at'
                |
                */

                $table
                    ->dateTime('expires_at')
                    ->index();


                /*
                |--------------------------------------------------------------------------
                | Verwerkt
                |--------------------------------------------------------------------------
                */

                $table
                    ->dateTime('consumed_at')
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Laravel timestamps
                |--------------------------------------------------------------------------
                */

                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Samengestelde index
                |--------------------------------------------------------------------------
                |
                | Handig voor queries zoals:
                |
                | gebruiker + status + vervaldatum
                |
                */

                $table->index(
                    [
                        'user_id',
                        'status',
                        'expires_at',
                    ],
                    'login_approval_user_status_expiry'
                );
            }
        );
    }


    /**
     * Verwijder de tabel wanneer de migration wordt teruggedraaid.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'login_approval_challenges'
        );
    }
};