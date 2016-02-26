<?php

namespace Bolt\Extension\Bolt\Members\AccessControl;

use Bolt\Extension\Bolt\Members\Storage;
use Bolt\Extension\Bolt\Members\Storage\Records;

/**
 * Verification class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Verification
{
    const KEY_NAME = 'account-verification-key';

    /** @var boolean */
    protected $success = false;
    /** @var string */
    protected $message;
    /** @var string */
    protected $code;

    /** @var  Records */
    private $records;

    /**
     * Constructor.
     *
     * @param Records $records
     */
    public function __construct(Records $records)
    {
        $this->records = $records;
    }

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
    public function validateCode($code)
    {
        $this->code = $code;
        if (strlen($code) !== 40) {
            $this->message = 'Invalid code';

            return;
        }

        // Get the verification key meta entity
        $metaEntities = $this->records->getAccountMetaValues(self::KEY_NAME, $code);
        if ($metaEntities === false) {
            $this->message = 'Expired meta code';

            return;
        }
        /** @var Storage\Entity\AccountMeta $metaEntity */
        $metaEntity = reset($metaEntities);
        $guid = $metaEntity->getGuid();

        // Get the account and set it as verified
        $account = $this->records->getAccountByGuid($guid);
        if ($account === false) {
            $this->message = 'Expired meta code';

            return;
        }
        $account->setVerified(true);
        $this->records->saveAccount($account);

        // Remove meta record
        $this->records->deleteAccountMeta($metaEntity);

        $this->success = true;
        $this->message = 'Account validated!';
    }
}
