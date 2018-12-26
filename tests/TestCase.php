<?php

use \org\bovigo\vfs\vfsStream;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $root;

    public function setUp()
    {
        $this->root = $this->getFilesystem();
    }

    /**
     * @param array|string|null $copyFileConfig
     * @return \PHPUnit_Framework_MockObject_MockObject|\Composer\Script\Event
     */
    protected function getEventMock($copyFileConfig)
    {
        $event = $this->getMockBuilder('\Composer\Script\Event')
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
     * @param array|string|null $copyFileConfig
     * @return \PHPUnit_Framework_MockObject_MockObject|\Composer\Composer
     */
    protected function getComposerMock($copyFileConfig)
    {
        $package = $this->getPackageMock($copyFileConfig);

        $composer = $this->getMockBuilder('\Composer\Composer')
            ->disableOriginalConstructor()
            ->getMock();

        $composer
            ->expects($this->once())
            ->method('getPackage')
            ->will($this->returnValue($package));

        return $composer;
    }

    /**
     * @param array|string|null $copyFileConfig
     * @return \PHPUnit_Framework_MockObject_MockObject|\Composer\Package\RootPackageInterface
     */
    protected function getPackageMock($copyFileConfig)
    {
        $extra = null;

        if (!is_null($copyFileConfig)) {
            $extra = array(
                'copy-file' => $copyFileConfig,
            );
        }

        $package = $this->getMockBuilder('\Composer\Package\RootPackageInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $package
            ->expects($this->once())
            ->method('getExtra')
            ->will($this->returnValue($extra));

        return $package;
    }

    /**
     * @return \org\bovigo\vfs\vfsStreamDirectory
     */
    protected function getFilesystem()
    {
        $structure = array(
            'from' => array(
                'file1' => 'Some content',
                'file2' => 'Some content',
            ),
            'to' => array(),
            'file3' => 'Some content',
            'from_complex' => array(
                'file4' => 'Some content',
                'sub_dir' => array(
                    'file5' => 'Some content',
                ),
                'git_keep_dir' => array(
                    '.gitkeep' => '',
                ),
            ),
            'dynamic_dir' => array(
                'file1' => 'Exchanged content',
                'file2' => 'Unaltered content',
            ),
        );

        return vfsStream::setup('root', null, $structure);
    }
}