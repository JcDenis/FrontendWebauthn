<?php

declare(strict_types=1);

namespace Dotclear\Plugin\FrontendWebauthn;

use ArrayObject;
use Dotclear\App;
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Form\{ Div, Hidden, Li, Link, Submit, Text, Timestamp, Ul };
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\FrontendSession\{ FrontendSession, FrontendSessionProfil, FrontendUrl };
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

    /**
     * @param   ArrayObject<int, Li>    $lines
     */
    public static function FrontendSessionWidget(ArrayObject $lines, string $url, WidgetsElement $widget): void
    {
        if (App::auth()->userID() == '') {
            $lines[] = (new Li())
                ->class('webauthn_authenticate')
                ->items([
                    (new Link([My::id() . '_submit', My::id() . 'submit_widget']))
                        ->text(__('Sign in with a passkey'))
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
        $tz          = is_string(App::auth()->getInfo('user_tz')) ? App::auth()->getInfo('user_tz') : '';

        foreach ($credentials as $credential) {
            if ($webauthn->rpOption()->id() != $credential->rpId()) {
                continue;
            }
            $items[] = (new Li())
                ->class('webauthn_unregister')
                ->separator(', ')
                ->items([
                    (new Text('', Html::escapeHTML($credential->label() ?: __('Unlabeled key'))))
                        ->title(Html::escapeHTML($webauthn->provider()->getProvider($credential->UUID()))),
                    (new Timestamp(Date::dt2str(__('%Y-%m-%d %H:%M'), $credential->createDate())))
                        ->datetime(Date::iso8601((int) strtotime((string) $credential->createDate()), $tz)),
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
                    ->text(__('Register a new key'))
                    ->href($url),
                (new Hidden('FrontendSessionaction', My::id())),
                (new Hidden('FrontendSessioncheck', App::nonce()->getNonce())),
                (new Hidden('step', 'deleteCredential')),
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
                // from form
                case 'deleteCredential':
                    if (!empty($_POST[My::id() . 'delete']) && is_array($_POST[My::id() . 'delete'])) {
                        $k = key($_POST[My::id() . 'delete']);
                        if (is_string($k)) {
                            $webauthn->store()->delCredential(base64_decode($k, false));
                            return;
                        }
                    }

                    break;

                // from js
                case 'prepareAuthentication':
                    $json = [
                        'message'   => 'ok',
                        'arguments' => $webauthn->prepareGet(),
                    ];
                    break;

                // from js
                case 'processAuthentication':
                    $data = $webauthn->processGet(
                        $webauthn->store()->decodeValue(isset($_POST['id']) && is_string($_POST['id']) ? $_POST['id'] : ''),
                        $webauthn->store()->decodeValue(isset($_POST['client']) && is_string($_POST['client']) ? $_POST['client'] : ''),
                        $webauthn->store()->decodeValue(isset($_POST['authenticator']) && is_string($_POST['authenticator']) ? $_POST['authenticator'] : ''),
                        $webauthn->store()->decodeValue(isset($_POST['signature']) && is_string($_POST['signature']) ? $_POST['signature'] : ''),
                        $webauthn->store()->decodeValue(isset($_POST['user']) && is_string($_POST['user']) ? $_POST['user'] : '')
                    );

                    $fs = App::frontend()->context()->__get('frontend_session');
                    if ($data !== '' && $fs instanceof FrontendSession && $fs->check($data)) {
                        $json = [
                            'message' => 'ok',
                            'arguments' => ['user' => $data],
                        ];
                    } else {
                        $json = [
                            'message' => __('This key is not registered'),
                            'arguments' => [],
                        ];
                    }

                    break;

                // from js
                case 'prepareRegistration':
                    $json = [
                        'message'   => 'ok',
                        'arguments' => $webauthn->prepareCreate(),
                    ];

                    break;

                // from js
                case 'processRegistration':

                    $webauthn->processCreate(
                        $webauthn->store()->decodeValue(isset($_POST['client']) && is_string($_POST['client']) ? $_POST['client'] : ''),
                        $webauthn->store()->decodeValue(isset($_POST['attestation']) && is_string($_POST['attestation']) ? $_POST['attestation'] : ''),
                        $webauthn->store()->decodeValue(isset($_POST['transports']) && is_string($_POST['transports']) ? $_POST['transports'] : ''),
                        isset($_POST['label']) && is_string($_POST['label']) ? $_POST['label'] : ''
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
