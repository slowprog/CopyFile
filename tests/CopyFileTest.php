<?php

use SlowProg\CopyFile\ScriptHandler;

use org\bovigo\vfs\vfsStream;
use Composer\IO\IOInterface;

class CopyFileTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {

    }

    public function testFilesystem()
    {
        $root = $this->getFilesystem();

        $this->assertTrue($root->hasChild('from/file1'));
        $this->assertTrue($root->hasChild('from/file2'));

        $this->assertTrue($root->hasChild('file3'));

        $this->assertTrue($root->hasChild('from_complex/file4'));
        $this->assertTrue($root->hasChild('from_complex/sub_dir/file5'));
    }

    public function testCopyDirToDir()
    {
        $root = $this->getFilesystem();
        
        $this->assertFalse($root->hasChild('to/file1'));
        $this->assertFalse($root->hasChild('to/file2'));

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/from')=> vfsStream::url('root/to')
        ]));

        $this->assertTrue($root->hasChild('to/file1'));
        $this->assertTrue($root->hasChild('to/file2'));
    }

    public function testCopyToNotExistsDir()
    {
        $root = $this->getFilesystem();

        $this->assertFalse($root->hasChild('not_exists'));

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/from')=> vfsStream::url('root/not_exists')
        ]));

        $this->assertTrue($root->hasChild('not_exists'));
        $this->assertTrue($root->hasChild('not_exists/file1'));
        $this->assertTrue($root->hasChild('not_exists/file2'));
    }

    public function testCopyFromNotExistsDir()
    {
        $root = $this->getFilesystem();

        $this->expectException(InvalidArgumentException::class);

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/fake')=> vfsStream::url('root/to')
        ]));
    }

    public function testCopyDirToFile()
    {
        $root = $this->getFilesystem();

        $this->expectException(InvalidArgumentException::class);

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/from')=> vfsStream::url('root/file3')
        ]));
    }

    public function testCopyFileToDir()
    {
        $root = $this->getFilesystem();

        $this->assertFalse($root->hasChild('to/file3'));

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/file3')=> vfsStream::url('root/to')
        ]));

        $this->assertTrue($root->hasChild('to/file3'));
    }

    public function testCopyFromComplexDir()
    {
        $root = $this->getFilesystem();

        $this->assertFalse($root->hasChild('to/file4'));
        $this->assertFalse($root->hasChild('to/sub_dir/file5'));

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/from_complex')=> vfsStream::url('root/to')
        ]));

        $this->assertTrue($root->hasChild('to/file4'));
        $this->assertTrue($root->hasChild('to/sub_dir/file5'));
    }

    public function testConfigError()
    {
        $root = $this->getFilesystem();

        $this->expectException(InvalidArgumentException::class);

        ScriptHandler::copy($this->getEventMock([]));
        ScriptHandler::copy($this->getEventMock(['to', 'from', 'file3']));
        ScriptHandler::copy($this->getEventMock(null));
        ScriptHandler::copy($this->getEventMock('some string'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getEventMock($copyFileConfig)
    {
        $event = $this->getMockBuilder('Composer\Script\CommandEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event
            ->expects($this->once())
            ->method('getComposer')
            ->will($this->returnValue($this->getComposerMock($copyFileConfig)));

        $event
            ->method('getIO')
            ->will($this->returnValue($this->createMock('\Composer\IO\IOInterface')));

        return $event;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getComposerMock($copyFileConfig)
    {
        $package = $this->getPackageMock($copyFileConfig);

        $composer = $this->getMockBuilder('Composer\Composer')
            ->disableOriginalConstructor()
            ->getMock();

        $composer
            ->expects($this->once())
            ->method('getPackage')
            ->will($this->returnValue($package));

        return $composer;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPackageMock($copyFileConfig)
    {
        $extra = null;

        if (!is_null($copyFileConfig)) {
            $extra = [
                'copy-file' => $copyFileConfig,
            ];
        }

        $package = $this->getMockBuilder('Composer\Package\RootPackageInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $package
            ->expects($this->once())
            ->method('getExtra')
            ->will($this->returnValue($extra));

        return $package;
    }

    private function getFilesystem()
    {
        $structure = [
            'from' => [
                'file1' => 'Some content',
                'file2' => 'Some content',
            ],
            'to' => [],
            'file3' => 'Some content',
            'from_complex' => [
                'file4' => 'Some content',
                'sub_dir' => [
                    'file5' => 'Some content',
                ]
            ],
        ];

        return vfsStream::setup('root', null, $structure);
    }
}
