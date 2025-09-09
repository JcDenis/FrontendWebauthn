<?php

declare(strict_types=1);

namespace Dotclear\Plugin\FrontendWebauthn;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Helper\L10n;

/**
 * @brief       FrontendWebauthn module frontend process.
 * @ingroup     FrontendWebauthn
 *
 * @author      Jean-Christian Paul Denis
 * @copyright   AGPL-3.0
 */
class Frontend
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        L10n::set(implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'locales', App::lang()->getLang(), 'public']));

        App::behavior()->addBehaviors([
            'publicHeadContent' => FrontendBehaviors::publicHeadContent(...),
            'FrontendSessionWidget' => FrontendBehaviors::FrontendSessionWidget(...),
            'FrontendSessionProfil' => FrontendBehaviors::FrontendSessionProfil(...),
            'FrontendSessionAction' => FrontendBehaviors::FrontendSessionAction(...),
        ]);

        return true;
    }
}
