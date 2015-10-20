<?php

namespace Anolilab\Wordpress\SaltGenerator\Tests;

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

use Anolilab\Wordpress\SaltGenerator\Generator;
use Composer\Script\Event;
use Mockery as Mock;

/**
 * GeneratorTest.
 *
 * @author  Daniel Bannert
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateSalt()
    {
        $gen = new Generator();

        $this->assertEquals(strlen($gen->generateSalt()), 64);
        $this->assertInternalType('string', $gen->generateSalt());
    }

    public function testWriteToFile()
    {
        file_put_contents(__DIR__.'/.env.example', '');
        $gen = new SubGenerator();

        $salts = array_map(function ($key) use ($gen) {
            return sprintf("%s='%s'", $key, $gen->generateSalt());
        }, $gen->getKeys());

        $mock = Mock::mock('Composer\Script\Event');

        $this->assertEquals($gen->writeToFile(__DIR__.'/.env', $salts, $mock), 0);

        $file = file_get_contents(__DIR__.'/.env');

        $this->assertRegExp("/AUTH_KEY/", $file);
        $this->assertRegExp("/SECURE_AUTH_KEY/", $file);
        $this->assertRegExp("/LOGGED_IN_KEY/", $file);
        $this->assertRegExp("/NONCE_KEY/", $file);
        $this->assertRegExp("/AUTH_SALT/", $file);
        $this->assertRegExp("/SECURE_AUTH_SALT/", $file);
        $this->assertRegExp("/LOGGED_IN_SALT/", $file);
        $this->assertRegExp("/NONCE_SALT/", $file);

        @unlink(__DIR__.'/.env.example');
        @unlink(__DIR__.'/.env');
    }

    public function testFailWriteToFile()
    {
        $gen = new SubGenerator();
        $io = Mock::mock('Composer\IO\IOInterface')
          ->shouldReceive('write')
          ->getMock();
        $mock = Mock::mock('Composer\Script\Event')
          ->shouldReceive('getIO')
          ->andReturn($io)
          ->getMock();

        $salts = array_map(function ($key) use ($gen) {
            return sprintf("%s='%s'", $key, $gen->generateSalt());
        }, $gen->getKeys());

        $this->assertEquals(
            $gen->writeToFile(__DIR__.'/.env', $salts, $mock),
            1
        );
    }

    public function testAddSalts()
    {
        $gen = new Generator();
        $config = Mock::mock('Composer\Config');
        $config->shouldReceive('get')->andReturnUsing(function ($key) {
            if ($key === 'home') {
                return __DIR__;
            }
        });
        $composer = Mock::mock('Composer\Composer')
          ->shouldReceive('getConfig')
          ->andReturn($config)
          ->getMock();

        $io = Mock::mock('Composer\IO\IOInterface')
          ->shouldReceive('write')
          ->shouldReceive('isInteractive')
          ->andReturn(false)
          ->getMock();

        $mock = Mock::mock('Composer\Script\Event')
          ->shouldReceive('getIO')
          ->andReturn($io)
          ->shouldReceive('getComposer')
          ->andReturn($composer)
          ->getMock();

        file_put_contents(__DIR__.'/.env.example', '');

        $gen::addSalts($mock);

        @unlink(__DIR__.'/.env.example');
        @unlink(__DIR__.'/.env');
    }
}

class SubGenerator extends Generator
{
    public function writeToFile($file, $salts, Event $event)
    {
        return parent::writeToFile($file, $salts, $event);
    }

    public function getKeys()
    {
        return self::$KEYS;
    }
}
