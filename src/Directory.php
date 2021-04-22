<?php

namespace Lukaswhite\Directory;

use resource;

/**
 * Class Directory
 *
 * A class that represents a directory on a file system.
 *
 * @package Lukaswhite\Directory
 */
class Directory
{
    /**
     * The path to this directory
     *
     * @var string
     */
    private $path;

    /**
     * Directory constructor.
     *
     * @param string $path
     */
    public function __construct( string $path )
    {
        $this->path = $path;
    }

    /**
     * Determine whether this directory exists
     *
     * @return bool
     */
    public function exists( ) : bool
    {
        return file_exists( $this->path );
    }

    /**
     * Determine whether this is indeed a directory
     *
     * @return bool
     */
    public function isDirectory( ) : bool
    {
        return is_dir( $this->path );
    }

    /**
     * Create this directory
     *
     * @param integer $mode
     * @return self
     */
    public function create( int $mode = 0777 ) : self
    {
        mkdir( $this->path, $mode, true );
        return $this;
    }

    /**
     * Create this directory, if it does not already exist
     *
     * @param integer $mode
     * @return self
     */
    public function createIfDoesNotExist( int $mode = 0777 ) : self
    {
        if ( ! $this->exists( ) ) {
            $this->create( $mode );
        }
        return $this;
    }

    /**
     * Get the most recent file in the directory; optionally matching the specified
     * pattern.
     *
     * @param string $pattern
     * @param bool $includeDirectories
     * @return string
     */
    public function mostRecentFile( string $pattern = null, bool $includeDirectories = false ) : string
    {
        if ( $pattern ) {
            $files = $this->glob( $pattern );
        } else {
            $files = $this->getFiles( $includeDirectories, false );
        }

        $lastMod = 0;
        $lastModFile = '';
        foreach ( $files as $filepath ) {

            if ( filectime( $filepath ) > $lastMod) {
                $lastMod = filectime( $filepath );
                $lastModFile = $filepath;
            }
        }

        return $lastModFile;
    }

    /**
     * Perform a glob on the directory
     *
     * @param string $pattern
     * @return array
     */
    public function glob( string $pattern ) : array
    {
        return glob( sprintf(
            '%s%s%s',
            $this->path,
            DIRECTORY_SEPARATOR,
            $pattern
        ) );
    }

    /**
     * Get the files in a directory
     *
     * @param bool $includeDirectories
     * @param $recursive
     * @return array
     *
     * @todo Implement recursive listing
     */
    public function getFiles( bool $includeDirectories = false, bool $recursive = false )
    {
        // First get the files, excluding the .. and . directories
        $files = array_map(
            function( $filename) {
                return $this->fullPathToFile( $filename );
            },
            array_diff(
                scandir( $this->path ),
                [
                    '..',
                    '.'
                ]
            )
        );

        if ( $recursive ) {

            foreach( array_filter( $files, function( $filepath ) {
                return is_dir( $filepath );
            } ) as $directory ) {
                $files = array_merge( $files, ( new self( $directory ) )
                    ->getFiles( $includeDirectories, true ) );
            }
        }

        // If we have been asked for directories, then we can simply return the list as-is.
        if ( $includeDirectories ) {
            return $files;
        }

        // Otherwise, run a simple filter to *exclude* directories
        return array_filter( $files, function( $filepath ) {
            return ! is_dir( $filepath );
        } );

    }

    /**
     * Get the full path to a file, in the context of this directory.
     *
     * @param string $filename
     * @return string
     */
    public function fullPathToFile( string $filename ) : string
    {
        return sprintf(
            '%s%s%s',
            $this->path,
            DIRECTORY_SEPARATOR,
            $filename
        );
    }

    /**
     * Determine whether a file with the specfied name exists.
     *
     * @param string $filename
     * @return bool
     */
    public function fileExists( string $filename ) : bool
    {
        return in_array(
            $filename,
            array_map(
                function( $filepath ) {
                    return basename( $filepath );
                },
                $this->getFiles( false )
            )
        );

    }


    /**
     * Given a filename, ensure that it's unique. If the file does not exist in this directory
     * it simply returns it. Otherwise, it'll add a numeric suffix.
     *
     * - suppose logo.png does not exist; it'll return logo.png
     * - suppose logo.png does exist; it'll return logo-1.png
     * - suppose logo.png and logo-1.png exist; it'll return logo-2.png
     * etc
     *
     * You can also override the default suffix; for example if the suffix is simply %d
     * then in the examples above, it'd return logo1.png, logo2.png etc.
     *
     * You can also override the starting number; for example if you set it to zero then
     * in the examples above, it'd return logo-0.png, logo-1.png etc.
     *
     * @param string $filename
     * @param string $suffix
     * @param integer $start
     * @return string
     */
    public function ensureUniqueFilename( string $filename, string $suffix = '-%d', int $start = 1 ) : string
    {
        // If the file doesn't already exist as-is, then simply return it.
        if ( ! file_exists( $this->fullPathToFile( $filename ) ) ) {
            return $filename;
        }

        // Split up the filename into its constituent parts.
        $parts      =   pathinfo( $filename );
        $extension  =   $parts[ 'extension' ];
        $basename   =   basename( $parts[ 'basename' ], sprintf( '.%s', $extension ) );

        // Start incrementing
        $i = $start;

        // Now keep incrementing and generating the corresponding filename, until
        // we find a variation that doesn't exist.
        do {
            $filename = sprintf(
                '%s%s.%s',
                $basename,
                sprintf( $suffix, $i ),
                $extension
            );
            $i++;
        } while(
            file_exists( $this->fullPathToFile( $filename ) )
        );

        // Finally, return the filename
        return $filename;
    }

    /**
     * Get the total size of the directory
     *
     * @return int
     */
    public function totalSize( ) : int
    {
        $files = $this->getFiles( false, true );
        $size = 0;
        foreach( $files as $filepath ) {
            $size += filesize( $filepath );
        }
        return $size;
    }

    /**
     * Create a file
     *
     * @param string $filename
     * @param mixed $contents
     * @param int $flags
     * @param resource $context
     */
    public function createFile( string $filename, $contents = null, int $flags = 0, resource $context = null )
    {
        $filepath = $this->fullPathToFile( $filename );
        if ( $contents ) {
            file_put_contents( $filepath, $contents, $flags, $context );
        } else {
            touch( $filepath );
        }
    }

    /**
     * Copy a file into this directory
     *
     * @param string $filepath
     * @return $this
     */
    public function copyFileInto( string $filepath ) : self
    {
        copy( $filepath, $this->fullPathToFile( basename( $filepath ) ) );
        return $this;
    }

    /**
     * Recursively delete a directory
     *
     * @param string $src
     */
    public function delete( string $src  = null )
    {
        if ( ! $src ) {
            $src = $this->path;
        }
        $dir = opendir( $src );
        while( false !== ( $file = readdir( $dir ) ) )
        {
            if ( ( $file != '.' ) && ( $file != '..' ) )
            {
                $full = sprintf(
                    '%s%s%s',
                    $src,
                    DIRECTORY_SEPARATOR,
                    $file
                );
                if ( is_dir( $full ) ) {
                    $this->delete( $full );
                }
                else {
                    unlink( $full );
                }
            }
        }
        closedir( $dir );
        rmdir( $src );
    }
}