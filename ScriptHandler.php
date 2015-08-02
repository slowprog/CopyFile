<?php

namespace SlowProg\CopyFile;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Composer\Script\CommandEvent;

class ScriptHandler
{
    /**
     * @param Composer\Script\CommandEvent $event
     */
    public static function copy(CommandEvent $event)
    {
	    $extras = $event->getComposer()->getPackage()->getExtra();

        if (!isset($extras['copy-fonts'])) {
            throw new \InvalidArgumentException('The dirs of fonts needs to be configured through the extra.copy-fonts setting.');
        }
		
        $fonts = $extras['copy-fonts'];
        
		if ($fonts === array_values($fonts)) {
            throw new \InvalidArgumentException('The extra.copy-fonts must be associative array like "<dir_from>: <dir_to>".');
        }
        
        $finder = new Finder;
        $fs = new Filesystem;
        $io = $event->getIO();
        
        foreach ($fonts as $from => $to) {
	        try {
	            $fs->mkdir($to);
	        } catch (IOException $e) {
	            $io->write(sprintf('<error>Could not create directory %s.</error>', $to));
	            return;
	        }
	        
	        if (false === file_exists($from)) {
	            $io->write(sprintf(
	                '<error>Fonts directory "%s" does not exist. Did you install twbs/bootstrap?</error>',
	                $from
	            ));
	            return;
	        }
	        
	        $finder->files()->in($from);
	        foreach ($finder as $file) {
	            $dest = sprintf('%s/%s', $to, $file->getBaseName());
	            try {
	                $fs->copy($file, $dest);
	            } catch (IOException $e) {
	                $io->write(sprintf('<error>Could not copy %s</error>', $file->getBaseName()));
	                return;
	            }
	        }
	        $io->write(sprintf('Copied fonts from <comment>%s</comment> to <comment>%s</comment>.', $from, $to));
        }
    }
}