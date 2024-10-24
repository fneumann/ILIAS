<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Authentication\Password;

use ilAuthUtils;
use ilDBInterface;
use ilFileUtils;
use ilObjUser;
use ilPasswordException;
use ilPasswordUtils;
use ilSession;
use ilSetting;
use ilUserException;

class LocalUserPasswordManager
{
    private const MIN_SALT_SIZE = 16;

    private static ?self $instance = null;
    private ?LocalUserPasswordEncoderFactory $encoderFactory = null;
    private ?ilSetting $settings = null;
    private ?ilDBInterface $db = null;
    private ?string $encoderName = null;

    /**
     * Please use the singleton method for instance creation
     * The constructor is still public because of the unit tests
     * @param  array<string, mixed> $config
     * @throws ilUserException
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                switch (strtolower($key)) {
                    case 'settings':
                        $this->setSettings($value);

                        break;
                    case 'db':
                        $this->setDb($value);

                        break;
                    case 'password_encoder':
                        $this->setEncoderName($value);

                        break;
                    case 'encoder_factory':
                        $this->setEncoderFactory($value);

                        break;
                }
            }
        }

        if (!$this->getEncoderName()) {
            throw new ilUserException(sprintf(
                '"password_encoder" must be set in %s.',
                print_r($config, true)
            ));
        }

        if (!$this->getEncoderFactory() instanceof LocalUserPasswordEncoderFactory) {
            throw new ilUserException(sprintf(
                '"encoder_factory" must be instance of LocalUserPasswordEncoderFactory and set in %s.',
                print_r($config, true)
            ));
        }
    }

    /**
     * Singleton method to reduce footprint (included files, created instances)
     * @throws ilUserException
     * @throws ilPasswordException
     */
    public static function getInstance(): self
    {
        global $DIC;

        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $password_manager = new LocalUserPasswordManager(
            [
                'encoder_factory' => new LocalUserPasswordEncoderFactory(
                    [
                        // bcrypt (native PHP impl.) is still the default for the factory
                        'default_password_encoder' => 'bcryptphp',
                        // Recommended: https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html#argon2id
                        'memory_cost' => 19_456,
                        'ignore_security_flaw' => true,
                        'data_directory' => ilFileUtils::getDataDir()
                    ]
                ),
                // https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html#argon2id
                'password_encoder' => 'argon2id',
                'settings' => $DIC->isDependencyAvailable('settings') ? $DIC->settings() : null,
                'db' => $DIC->database(),
            ]
        );

        self::$instance = $password_manager;

        return self::$instance;
    }

    public function setSettings(?ilSetting $settings): void
    {
        $this->settings = $settings;
    }

    public function setDb(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function getEncoderName(): ?string
    {
        return $this->encoderName;
    }

    public function setEncoderName(string $encoderName): void
    {
        $this->encoderName = $encoderName;
    }

    public function getEncoderFactory(): ?LocalUserPasswordEncoderFactory
    {
        return $this->encoderFactory;
    }

    public function setEncoderFactory(LocalUserPasswordEncoderFactory $encoderFactory): void
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function encodePassword(ilObjUser $user, string $raw): void
    {
        $encoder = $this->getEncoderFactory()->getEncoderByName($this->getEncoderName());
        $user->setPasswordEncodingType($encoder->getName());
        if ($encoder->requiresSalt()) {
            $user->setPasswordSalt(
                substr(
                    str_replace(
                        '+',
                        '.',
                        base64_encode(ilPasswordUtils::getBytes(self::MIN_SALT_SIZE))
                    ),
                    0,
                    22
                )
            );
        } else {
            $user->setPasswordSalt(null);
        }
        $user->setPasswd(
            $encoder->encodePassword($raw, (string) $user->getPasswordSalt()),
            ilObjUser::PASSWD_CRYPTED
        );
    }

    public function isEncodingTypeSupported(string $name): bool
    {
        return in_array($name, $this->getEncoderFactory()->getSupportedEncoderNames());
    }

    public function verifyPassword(ilObjUser $user, string $raw): bool
    {
        $encoder = $this->getEncoderFactory()->getEncoderByName($user->getPasswordEncodingType());
        if ($this->getEncoderName() !== $encoder->getName()) {
            if ($encoder->isPasswordValid($user->getPasswd(), $raw, (string) $user->getPasswordSalt())) {
                $user->resetPassword($raw, $raw);

                return true;
            }
        } elseif ($encoder->isPasswordValid($user->getPasswd(), $raw, (string) $user->getPasswordSalt())) {
            if ($encoder->requiresReencoding($user->getPasswd())) {
                $user->resetPassword($raw, $raw);
            }

            return true;
        }

        return false;
    }

    public function resetLastPasswordChangeForLocalUsers(): void
    {
        $defaultAuthMode = $this->settings->get('auth_mode');
        $defaultAuthModeCondition = '';
        if ((int) $defaultAuthMode === ilAuthUtils::AUTH_LOCAL) {
            $defaultAuthModeCondition = ' OR auth_mode = ' . $this->db->quote('default', 'text');
        }

        $this->db->manipulateF(
            "UPDATE usr_data SET passwd_policy_reset = %s WHERE (auth_mode = %s $defaultAuthModeCondition)",
            ['integer', 'text'],
            [1, 'local']
        );
    }

    public function allowPasswordChange(ilObjUser $user): bool
    {
        if (ilSession::get('used_external_auth_mode')) {
            return false;
        }

        $status = ilAuthUtils::isPasswordModificationEnabled($user->getAuthMode(true));
        if ($status) {
            return true;
        }

        return ilAuthUtils::isPasswordModificationHidden()
            && ($user->isPasswordChangeDemanded() || $user->isPasswordExpired());
    }
}
