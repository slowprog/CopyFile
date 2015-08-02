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

        if (!isset($extras['copy-file'])) {
            throw new \InvalidArgumentException('The dirs or files needs to be configured through the extra.copy-file setting.');
        }
		
        $files = $extras['copy-file'];
        
		if ($files === array_values($files)) {
            throw new \InvalidArgumentException('The extra.copy-file must be hash like "{<dir_or_file_from>: <dir_to>}".');
        }
        
        $finder = new Finder;
        $fs = new Filesystem;
        $io = $event->getIO();
        
        foreach ($files as $from => $to) {
	        if (!is_dir($to)) 
		        throw new \InvalidArgumentException('Destination directory is not a directory.');
	        
	        try {
	            $fs->mkdir($to);
	        } catch (IOException $e) {
	            throw new \InvalidArgumentException(sprintf('<error>Could not create directory %s.</error>', $to));
	        }
	        
	        if (false === file_exists($from))
	            throw new \InvalidArgumentException(sprintf('<error>Source directory or file "%s" does not exist.</error>', $from));
	        
	        if (is_dir($from)) {
		        $finder->files()->in($from);
		        foreach ($finder as $file) {
		            $dest = sprintf('%s/%s', $to, $file->getBaseName());
		            try {
		                $fs->copy($file, $dest);
		            } catch (IOException $e) {
		                throw new \InvalidArgumentException(sprintf('<error>Could not copy %s</error>', $file->getBaseName()));
		            }
		        }  
	        } else {
	            try {
	                $fs->copy($from, $to.'/'.basename($from));
	            } catch (IOException $e) {
	                throw new \InvalidArgumentException(sprintf('<error>Could not copy %s</error>', $from));
	            }
	        }
	        
	        $io->write(sprintf('Copied file(s) from <comment>%s</comment> to <comment>%s</comment>.', $from, $to));    
        }
    }
}