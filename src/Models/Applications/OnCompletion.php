<?php

namespace Rootsoft\Algorand\Models\Applications;

use MyCLabs\Enum\Enum;

/**
 * @method static OnCompletion NO_OP_OC()
 * @method static OnCompletion OPT_IN_OC()
 * @method static OnCompletion CLOSE_OUT_OC()
 * @method static OnCompletion CLEAR_STATE_OC()
 * @method static OnCompletion UPDATE_APPLICATION_OC()
 * @method static OnCompletion DELETE_APPLICATION_OC()
 */
final class OnCompletion extends Enum
{
    private const NO_OP_OC = 0;
    private const OPT_IN_OC = 1;
    private const CLOSE_OUT_OC = 2;
    private const CLEAR_STATE_OC = 3;
    private const UPDATE_APPLICATION_OC = 4;
    private const DELETE_APPLICATION_OC = 5;
}