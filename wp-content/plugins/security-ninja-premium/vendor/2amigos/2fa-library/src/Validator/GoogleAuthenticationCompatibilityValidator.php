<?php

/*
 * This file is part of the 2amigos/2fa-library project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace WPSecurityNinja\Plugin\Da\TwoFA\Validator;

use WPSecurityNinja\Plugin\Da\TwoFA\Contracts\ValidatorInterface;

class GoogleAuthenticationCompatibilityValidator implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function validate($value): bool
    {
        return ((strlen($value) & (strlen($value) - 1)) === 0);
    }
}
