<?php

declare(strict_types=1);

namespace Dotclear\Plugin\FrontendWebauthn;

use Dotclear\App;
use Dotclear\Core\Process;

/**
 * @brief       FrontendWebauthn module frontend process.
 * @ingroup     FrontendWebauthn
 *
 * @author      Jean-Christian Paul Denis
 * @copyright   AGPL-3.0
 */
class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehaviors([
            'publicHeadContent' => FrontendBehaviors::publicHeadContent(...),
            'FrontendSessionWidget' => FrontendBehaviors::FrontendSessionWidget(...),
            'FrontendSessionProfil' => FrontendBehaviors::FrontendSessionProfil(...),
            'FrontendSessionAction' => FrontendBehaviors::FrontendSessionAction(...),
        ]);

        return true;
    }
}
