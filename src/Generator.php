<?php

namespace Anolilab\Wordpress\SaltGenerator;

/**
 * Generates the salts for phpdotenv based Wordpress Composer projects.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.anolilab.de
 *
 * @license     MIT
 */

use Composer\Script\Event;
use RandomLib\Factory;
use SecurityLib\Strength;

/**
 * Generator.
 *
 * @author  Daniel Bannert
 */
class Generator
{
    /**
     * All keys that need to be generated.
     *
     * @var array
     */
    protected static $KEYS = [
        'AUTH_KEY',
        'SECURE_AUTH_KEY',
        'LOGGED_IN_KEY',
        'NONCE_KEY',
        'AUTH_SALT',
        'SECURE_AUTH_SALT',
        'LOGGED_IN_SALT',
        'NONCE_SALT'
    ];

    /**
     * Adds generedted salts to env file.
     *
     * @param Event $event
     *
     * @return int
     */
    public static function addSalts(Event $event)
    {
        $composer = $event->getComposer();
        $root     = $composer->getConfig()->get('home');
        $io       = $event->getIO();

        if (!$io->isInteractive()) {
            $generateSalts = $composer->getConfig()->get('generate-salts');
        } else {
            $generateSalts = $io->askConfirmation(
                '<info>Generate salts and append to .env file?</info> [<comment>Y,n</comment>]?',
                true
            );
        }

        if (!$generateSalts) {
            return 1;
        }

        $salts = array_map(function ($key) {
            return sprintf("%s='%s'", $key, self::generateSalt());
        }, self::$KEYS);

        self::writeToFile("{$root}/.env", $salts, $event);
    }

    /**
     * Generates salt.
     *
     * @param Event $event
     *
     * @return string
     */
    public function generateSalt($length = 64)
    {
        $chars  = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $chars .= '!@#$%^&*()';
        $chars .= '-_ []{}<>~`+=,.;:/?|';

        $factory = new Factory();
        $generator = $factory->getGenerator(new Strength(Strength::MEDIUM));

        return $generator->generateString($length, $chars);
    }

    /**
     * Write to file.
     *
     * @param  string $file
     * @param  string $salts
     *
     * @return int
     */
    protected function writeToFile($file, $salts, Event $event)
    {
        try {
            if (copy("{$file}.example", $file)) {
                file_put_contents($file, implode($salts, "\n"), FILE_APPEND | LOCK_EX);
                return 0;
            }
        } catch (\Exception $e) {
            $event->getIO()->write("<error>An error occured while copying your .env file</error>");
            return 1;
        }
    }
}
