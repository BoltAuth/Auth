<?php

namespace Bolt\Extension\Bolt\Members\AccessControl\Validator;

use Bolt\Extension\Bolt\Members\Storage;
use Bolt\Extension\Bolt\Members\Storage\Records;

/**
 * Verification class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
class AccountVerification
{
    const KEY_NAME = 'account-verification-key';

    /** @var boolean */
    protected $success = false;
    /** @var string */
    protected $message;
    /** @var string */
    protected $code;

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Validate a validation code's validity, validly.
     *
     * @param string $code
     */
    public function validateCode(Records $records, $code)
    {
        $this->code = $code;
        if (strlen($code) !== 40) {
            $this->message = 'Invalid code';

            return;
        }

        // Get the verification key meta entity
        $metaEntities = $records->getAccountMetaValues(self::KEY_NAME, $code);
        if ($metaEntities === false) {
            $this->message = 'Expired meta code';

            return;
        }
        /** @var Storage\Entity\AccountMeta $metaEntity */
        $metaEntity = reset($metaEntities);
        $guid = $metaEntity->getGuid();

        // Get the account and set it as verified
        $account = $records->getAccountByGuid($guid);
        if ($account === false) {
            $this->message = 'Expired meta code';

            return;
        }
        $account->setVerified(true);
        $records->saveAccount($account);

        // Remove meta record
        $records->deleteAccountMeta($metaEntity);

        $this->success = true;
        $this->message = 'Account validated!';
    }
}
