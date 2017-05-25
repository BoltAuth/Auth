<?php

namespace Bolt\Extension\Bolt\Members\AccessControl\Validator;

use Bolt\Extension\Bolt\Members\Exception\AccountVerificationException;
use Bolt\Extension\Bolt\Members\Storage;
use Bolt\Extension\Bolt\Members\Storage\Records;

/**
 * Verification class.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 * Copyright (C) 2017 Svante Richter
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 *            Copyright (C) 2017 Svante Richter
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
    /** @var Storage\Entity\Account */
    protected $account;

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
     * @return Storage\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Validate a validation code's validity, validly.
     *
     * @param Records $records
     * @param string  $code
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
            $this->throwException(new AccountVerificationException('Stored meta code not found', AccountVerificationException::MISSING_META));
        }
        /** @var Storage\Entity\AccountMeta $metaEntity */
        $metaEntity = reset($metaEntities);
        if ($metaEntity === false) {
            $this->throwException(new AccountVerificationException('Stored meta code previously removed.', AccountVerificationException::REMOVED_META));
        }

        $guid = $metaEntity->getGuid();

        // Get the account and set it as verified
        $this->account = $records->getAccountByGuid($guid);
        if ($this->account === false) {
            $this->throwException(new AccountVerificationException('Missing account record.', AccountVerificationException::MISSING_ACCOUNT));
        }
        $this->account->setVerified(true);
        $records->saveAccount($this->account);

        // Remove meta record
        $records->deleteAccountMeta($metaEntity);

        $this->success = true;
        $this->message = 'Account validated!';
    }

    /**
     * Store the message for feedback, and throw the exception.
     *
     * @param \Exception $e
     *
     * @throws \Exception
     */
    private function throwException(\Exception $e)
    {
        $this->message = 'Expired meta code';

        throw $e;
    }
}
