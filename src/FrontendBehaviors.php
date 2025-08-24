<?php

declare(strict_types=1);

namespace Dotclear\Plugin\FrontendWebauthn;

use ArrayObject;
use Dotclear\App;
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Form\{ Div, Li, Link, Submit, Text, Timestamp, Ul };
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\FrontendSession\{ FrontendSessionProfil, FrontendUrl };
use Dotclear\Plugin\widgets\WidgetsElement;
use Exception;

/**
 * @brief       FrontendWebauthn frontend behaviors.
 * @ingroup     FrontendWebauthn
 *
 * @author      Jean-Christian Paul Denis
 * @copyright   AGPL-3.0
 */
class FrontendBehaviors
{
    public static function publicHeadContent(): void
    {
        echo 
        My::cssLoad('frontend-webauthn') .
        My::jsLoad('frontend-webauthn') .
        Html::jsJson(My::id(), [
            'check'  => App::nonce()->getNonce(),
            'action' => My::id(),
            'err'    => __('Failed to authenticate with passkey')
        ]);
    }

    public static function FrontendSessionWidget(ArrayObject $lines, string $url, WidgetsElement $widget): void
    {
        if (App::auth()->userID() == '') {
            $lines[] = (new Li())
                ->class('webauthn_authenticate')
                ->items([
                    (new Link([My::id() . '_submit', My::id() . 'submit_widget']))
                        ->text(__('Connect with a passkey'))
                        ->href($url),
                ]);
        }
    }

    public static function FrontendSessionProfil(FrontendSessionProfil $profil): void
    {
        if (App::auth()->userID() == '') {
            return; // double check
        }

        $webauthn    = new WebAuthn();
        $url         = App::blog()->url() . App::url()->getURLFor('FrontendSession');
        $items       = [];
        $credentials = $webauthn->store()->getCredentials('', (string) App::auth()->userID());

        foreach ($credentials as $credential) {
            if ($webauthn->rpOption()->id() != $credential->rpId()) {
                continue;
            }
            $items[] = (new Li())
                ->separator(', ')
                ->items([
                    (new Text('', Html::escapeHTML($webauthn->provider()->getProvider($credential->UUID()))))
                        ->title(Html::escapeHTML($credential->certificateIssuer() ?: __('Unknown certificat issuer'))),
                    (new Timestamp(Date::dt2str(__('%Y-%m-%d %H:%M'), $credential->createDate())))
                        ->datetime(Date::iso8601((int) strtotime((string) $credential->createDate()), App::auth()->getInfo('user_tz'))),
                    (new Submit([My::id() . 'delete[' . base64_encode((string) $credential->credentialId()) . ']'], __('Delete')))
                            ->class('delete'),
                ]);
        }

        if ($items === []) {
            $items[] = (new Li())
                ->text(__('You have no registered key yet.'));
        }

        $items[] = (new Li())
            ->class('webauthn_register')
            ->items([
                (new Link([My::id() . '_submit', My::id() . 'submit_page']))
                    ->text(__('Register a new passkey'))
                    ->href($url),
            ]);

        $profil->addAction(My::id(), __('Authentication keys'), [(new Ul())->items($items)]);
    }

    public static function FrontendSessionAction(string $action): void
    {
        if ($action === My::id()) {
            FrontendUrl::checkForm();
            $json     = [];
            $webauthn = new WebAuthn();

            switch ($_POST['step'] ?? '') {
                case 'prepareAuthentication':
                    $json = [
                        'message'   => 'ok',
                        'arguments' => $webauthn->prepareGet(),
                    ];
                    break;

                case 'processAuthentication':
                    $data = $webauthn->processGet(
                        $webauthn->store()->decodeValue($_POST['id'] ?? ''),
                        $webauthn->store()->decodeValue($_POST['client'] ?? ''),
                        $webauthn->store()->decodeValue($_POST['authenticator'] ?? ''),
                        $webauthn->store()->decodeValue($_POST['signature'] ?? ''),
                        $webauthn->store()->decodeValue($_POST['user'] ?? '')
                    );

                    if ($data !== '' &&  App::frontend()->context()->frontend_session->check($data)) {
                        $json = [
                            'message' => 'ok',
                            'arguments' => [],
                        ];
                    } else {
                        $json = [
                            'message' => __('This key is not registered'),
                            'arguments' => [],
                        ];
                    }

                    break;

                case 'prepareRegistration':
                    $json = [
                        'message'   => 'ok',
                        'arguments' => $webauthn->prepareCreate(),
                    ];

                    break;

                case 'processRegistration':

                    $webauthn->processCreate(
                        $webauthn->store()->decodeValue($_POST['client'] ?? ''),
                        $webauthn->store()->decodeValue($_POST['attestation'] ?? ''),
                        $webauthn->store()->decodeValue($_POST['transports'] ?? '')
                    );

                    $json = [
                        'message' => 'ok',
                        'arguments' => [],
                    ];

                    App::blog()->triggerBlog();
                    break;

                default:
                    $json = [
                        'message' => 'unknown step',
                        'arguments' => [],
                    ];
            }

            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($json, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES);
            exit();
        }
    }
}
