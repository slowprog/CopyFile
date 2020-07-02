<?php

use \SlowProg\CopyFile\ScriptHandler;
use \org\bovigo\vfs\vfsStream;

class CopyFileTest extends TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testFilesystem()
    {
        $this->assertTrue($this->root->hasChild('from/file1'));
        $this->assertTrue($this->root->hasChild('from/file2'));

        $this->assertTrue($this->root->hasChild('file3'));

        $this->assertTrue($this->root->hasChild('from_complex/file4'));
        $this->assertTrue($this->root->hasChild('from_complex/sub_dir/file5'));
    }

    public function testCopyDirToDir()
    {
        $this->assertFalse($this->root->hasChild('to/file1'));
        $this->assertFalse($this->root->hasChild('to/file2'));

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/from')=> vfsStream::url('root/to')
        ]));

        $this->assertTrue($this->root->hasChild('to/file1'));
        $this->assertTrue($this->root->hasChild('to/file2'));
    }

    public function testCopyDirToNotExistsDir()
    {
        $this->assertFalse($this->root->hasChild('not_exists'));

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/from')=> vfsStream::url('root/not_exists')
        ]));

        $this->assertTrue($this->root->hasChild('not_exists'));
        $this->assertTrue($this->root->hasChild('not_exists/file1'));
        $this->assertTrue($this->root->hasChild('not_exists/file2'));
    }

    public function testCopyFromNotExistsDir()
    {
        $this->expectException(InvalidArgumentException::class);

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/fake')=> vfsStream::url('root/to')
        ]));
    }

    public function testCopyDirToFile()
    {
        $this->expectException(InvalidArgumentException::class);

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/from')=> vfsStream::url('root/file3')
        ]));
    }

    public function testCopyFileToDir()
    {
        $this->assertFalse($this->root->hasChild('to/file3'));

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/file3')=> vfsStream::url('root/to/')
        ]));

        $this->assertTrue($this->root->hasChild('to/file3'));
    }

    public function testCopyFileToFile()
    {
        $this->assertFalse($this->root->hasChild('to/file_new'));

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/file3')=> vfsStream::url('root/to/file_new')
        ]));

        $this->assertTrue($this->root->hasChild('to/file_new'));
    }

    public function testCopyFromComplexDir()
    {
        $this->assertFalse($this->root->hasChild('to/file4'));
        $this->assertFalse($this->root->hasChild('to/sub_dir/file5'));
        $this->assertFalse($this->root->hasChild('to/git_keep_dir'));

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/from_complex')=> vfsStream::url('root/to')
        ]));

        $this->assertTrue($this->root->hasChild('to/file4'));
        $this->assertTrue($this->root->hasChild('to/sub_dir/file5'));
        $this->assertTrue($this->root->hasChild('to/git_keep_dir'));
    }

    public function testRewriteExists()
    {
        $this->root->lastModified(0);
        $this->root->getChild('dynamic_dir/file1')->lastModified(1);

        $exchanged = vfsStream::url('root/dynamic_dir/file1');
        $unaltered = vfsStream::url('root/dynamic_dir/file2');

        // allow filemtime check apply for vfs protocol
        $this->getFunctionMock('Symfony\Component\Filesystem', 'parse_url')
            ->expects($this->any())->willReturn(null);

        // preset filemtime check
        $this->getFunctionMock('Symfony\Component\Filesystem', 'filemtime')
            ->expects($this->any())->willReturnCallback(function ($filename) use ($unaltered) {
                return $filename === $unaltered ? 1 : 2;
            });

        ScriptHandler::copy($this->getEventMock(array(
            vfsStream::url('root/from') => vfsStream::url('root/dynamic_dir') . '?'
        )));

        $this->assertFileNotEquals(vfsStream::url('root/from/file1'), $exchanged);
        $this->assertFileEquals(vfsStream::url('root/from/file2'), $unaltered);
    }

    public function testCopyByPattern()
    {
        $this->assertFalse($this->root->hasChild('to/file4'));
        $this->assertFalse($this->root->hasChild('to/sub_dir/file5'));
        $this->assertFalse($this->root->hasChild('to/git_keep_dir'));

        ScriptHandler::copy($this->getEventMock([
            vfsStream::url('root/from_complex') . '#\w{4}\d' => vfsStream::url('root/to')
        ]));

        $this->assertTrue($this->root->hasChild('to/file4'));
        $this->assertTrue($this->root->hasChild('to/sub_dir/file5'));
        $this->assertFalse($this->root->hasChild('to/git_keep_dir'));
    }

    public function testConfigError()
    {
        $this->assertEquals(5, count($this->root->getChildren()));

        ScriptHandler::copy($this->getEventMock([]));
        ScriptHandler::copy($this->getEventMock(['to', 'from', 'file3']));
        ScriptHandler::copy($this->getEventMock(null));
        ScriptHandler::copy($this->getEventMock('some string'));

        $this->assertEquals(5, count($this->root->getChildren()));
    }
}
