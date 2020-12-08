<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Task\ComposerHandler;
use Maestro2\Core\Task\ComposerTask;
use Maestro2\Core\Process\TestProcessRunner;
use Maestro2\Core\Queue\Queue;
use Maestro2\Core\Queue\TestEnqueuer;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\JsonMergeHandler;
use Maestro2\Tests\Unit\Core\Task\HandlerTestCase;
use PHPUnit\Framework\TestCase;

class ComposerHandlerTest extends HandlerTestCase
{
    private TestProcessRunner $testRunner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testRunner = new TestProcessRunner();
    }

    protected function createHandler(): Handler
    {
        return new ComposerHandler(
            TestEnqueuer::fromHandlers([
                new JsonMergeHandler()
            ]),
            $this->testRunner
        );
    }

    public function testCreatesComposer(): void
    {
        $this->runTask(new ComposerTask(
            path: $this->workspace()->path(),
            require: [
                'foobar/barfoo' => '^1.0',
            ]
        ));

        self::assertEquals(<<<'EOT'
{
    "require": {
        "foobar/barfoo": "^1.0"
    }
}
EOT
        , $this->workspace()->getContents('composer.json'));
    }

    public function testUpdatesComposer(): void
    {
        $this->workspace()->put('composer.json', <<<'EOT'
{
    "require": {
        "foobar/barfoo": "^1.0"
    }
}
EOT
        );

        $this->runTask(new ComposerTask(
            path: $this->workspace()->path(),
            require: [
                'baz/boo' => '^1.0',
            ]
        ));

        self::assertEquals(<<<'EOT'
{
    "require": {
        "foobar/barfoo": "^1.0",
        "baz/boo": "^1.0"
    }
}
EOT
        , $this->workspace()->getContents('composer.json'));
    }

    public function testRemoves(): void
    {
        $this->workspace()->put('composer.json', <<<'EOT'
{
    "require": {
        "foobar/barfoo": "^1.0",
        "baz/boo": "^1.0"
    }
}
EOT
        );

        $this->runTask(new ComposerTask(
            path: $this->workspace()->path(),
            remove: [
                'foobar/barfoo'
            ]
        ));

        self::assertEquals(<<<'EOT'
{
    "require": {
        "baz/boo": "^1.0"
    }
}
EOT
        , $this->workspace()->getContents('composer.json'));
    }

    public function testRequireDev(): void
    {
        $this->runTask(new ComposerTask(
            dev: true,
            path: $this->workspace()->path(),
            require: [
                'foobar/barfoo' => '^1.0',
            ]
        ));

        self::assertEquals(<<<'EOT'
{
    "require-dev": {
        "foobar/barfoo": "^1.0"
    }
}
EOT
        , $this->workspace()->getContents('composer.json'));
    }

    public function testUpdate(): void
    {
        $this->runTask(new ComposerTask(
            path: $this->workspace()->path(),
            update: true
        ));

        $this->assertEquals([
            'composer',
            'update',
            '--working-dir=' . $this->workspace()->path()
        ], $this->testRunner->pop()->args());
    }
}