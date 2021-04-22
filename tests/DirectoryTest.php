<?php

use Lukaswhite\Directory\Directory;

class DirectoryTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp( ): void
    {
        $directory = new Directory( sys_get_temp_dir( ) . '/lukaswhite-directory' );
        if ( $directory->exists( ) ) {
            $directory->delete( );
        }
    }

    public function testExists( )
    {
        $directory = new Directory( __DIR__ .'/fixtures/one' );
        $this->assertTrue( $directory->exists( ) );

        $doesNotExist = new Directory( __DIR__ .'/fixtures/eight' );
        $this->assertFalse( $doesNotExist->exists( ) );
    }

    public function testIsDirectory( )
    {
        $directory = new Directory( __DIR__ .'/fixtures/one' );
        $this->assertTrue( $directory->isDirectory( ) );

        $file = new Directory( __DIR__ .'/fixtures/one/one.txt' );
        $this->assertFalse( $file->isDirectory( ) );
    }

    public function testCreate( )
    {
        $directory = new Directory( sys_get_temp_dir( ) . '/lukaswhite-directory' );
        $directory->create( );
        $this->assertTrue( $directory->exists( ) );
        rmdir( sys_get_temp_dir( ) . '/lukaswhite-directory' );
    }

    public function testCreateRecursive( )
    {
        $directory = new Directory( sys_get_temp_dir( ) . '/lukaswhite-directory/one/two/three' );
        $directory->create( 0777, true );
        $this->assertTrue( $directory->exists( ) );
        rmdir( sys_get_temp_dir( ) . '/lukaswhite-directory/one/two/three' );
    }

    public function testCreateIfDoesNotExist( )
    {
        $directory = new Directory( sys_get_temp_dir( ) . '/lukaswhite-directory' );
        $directory->createIfDoesNotExist( );
        $this->assertTrue( $directory->exists( ) );
        $directory->delete( );
    }

    /**
    public function testMostRecentFile( )
    {
        $directory = new Directory( __DIR__ .'/fixtures/one' );
        $this->assertEquals( 'four.csv', basename( $directory->mostRecentFile( ) ) );

        $this->assertEquals( 'two.txt', basename( $directory->mostRecentFile( '*.txt' ) ) );
    }**/

    public function testGetFiles( )
    {
        $directory = new Directory( __DIR__ .'/fixtures/two' );
        $files = $directory->getFiles( true );
        $this->assertTrue( in_array( __DIR__ .'/fixtures/two/file.txt', $files ) );
        $this->assertTrue( in_array( __DIR__ .'/fixtures/two/three', $files ) );
        $this->assertTrue( in_array( __DIR__ .'/fixtures/two/five', $files ) );
    }

    public function testGetFilesRecursive( )
    {
        $directory = new Directory( __DIR__ .'/fixtures/two' );
        $files = $directory->getFiles( true, true );

        $this->assertTrue( in_array( __DIR__ .'/fixtures/two/file.txt', $files ) );
        $this->assertTrue( in_array( __DIR__ .'/fixtures/two/three', $files ) );
        $this->assertTrue( in_array( __DIR__ .'/fixtures/two/five', $files ) );
        $this->assertTrue( in_array( __DIR__ .'/fixtures/two/three/bar.txt', $files ) );
    }

    public function testGetFilesRecursiveWithoutDirectories( )
    {
        $directory = new Directory( __DIR__ .'/fixtures/two' );
        $files = $directory->getFiles( false, true );
        $this->assertEquals( 5, count( $files ) );
    }

    public function testGlob( )
    {
        $directory = new Directory( __DIR__ .'/fixtures/one' );
        $files = $directory->glob( '*.txt' );
        $this->assertEquals( 2, count( $files ) );
        $this->assertTrue( in_array( __DIR__ .'/fixtures/one/one.txt', $files ) );
        $this->assertTrue(in_array( __DIR__ .'/fixtures/one/two.txt', $files ) );
    }

    public function testFileExists( )
    {
        $directory = new Directory( __DIR__ .'/fixtures/one' );
        $this->assertTrue( $directory->fileExists( 'one.txt' ) );
        $this->assertFalse( $directory->fileExists( 'nine.txt' ) );
    }

    public function testCreateEmptyFile( )
    {
        $directory = new Directory( sys_get_temp_dir( ) . '/lukaswhite-directory' );
        $directory->createIfDoesNotExist( );
        $directory->createFile( 'test.txt' );
        $this->assertFileExists( sys_get_temp_dir( ) . '/lukaswhite-directory/test.txt' );
        $directory->delete( );
    }

    public function testCreateFile( )
    {
        $directory = new Directory( sys_get_temp_dir( ) . '/lukaswhite-directory' );
        $directory->createIfDoesNotExist( );
        $directory->createFile( 'test.txt', 'This is a test' );
        $this->assertFileExists( sys_get_temp_dir( ) . '/lukaswhite-directory/test.txt' );
        $this->assertStringEqualsFile( sys_get_temp_dir( ) . '/lukaswhite-directory/test.txt','This is a test'  );
        $directory->delete( );
    }

    public function testCopyInto( )
    {
        $directory = new Directory( sys_get_temp_dir( ) . '/lukaswhite-directory' );
        $directory->createIfDoesNotExist( );
        $directory->copyFileInto( __DIR__ .'/fixtures/two/file.txt' );
        $this->assertTrue( $directory->fileExists( 'file.txt' ) );
        $directory->delete( );
    }

    public function testUniqueFilename( )
    {
        $path = sys_get_temp_dir( ) . '/lukaswhite-directory';
        $directory = new Directory( sys_get_temp_dir( ) . '/lukaswhite-directory' );
        $directory->createIfDoesNotExist( );
        $this->assertEquals( 'logo.png', $directory->ensureUniqueFilename( 'logo.png' ) );
        file_put_contents( sprintf( '%s%s%s', $path, DIRECTORY_SEPARATOR, 'logo.png' ), '' );
        $this->assertEquals( 'logo-1.png', $directory->ensureUniqueFilename( 'logo.png' ) );
        file_put_contents( sprintf( '%s%s%s', $path, DIRECTORY_SEPARATOR, 'logo-1.png' ), '' );
        $this->assertEquals( 'logo-2.png', $directory->ensureUniqueFilename( 'logo.png' ) );
        $this->assertEquals( 'logo1.png', $directory->ensureUniqueFilename( 'logo.png', '%d' ) );
        $directory->delete( );
    }

    public function testDelete( )
    {
        $path = sys_get_temp_dir( ) . '/lukaswhite-directory';
        $directory = new Directory( sys_get_temp_dir( ) . '/lukaswhite-directory' );
        $directory->createIfDoesNotExist( );
        ( new Directory( sys_get_temp_dir( ) . '/lukaswhite-directory/one' ) )
            ->createIfDoesNotExist( );
        ( new Directory( sys_get_temp_dir( ) . '/lukaswhite-directory/one/two' ) )
            ->createIfDoesNotExist( );
        $directory->delete( );
        $this->assertFalse( $directory->exists( ) );

    }

    public function testTotalSize( )
    {
        $directory = new Directory( __DIR__ .'/fixtures/one' );
        $this->assertEquals( 70, $directory->totalSize( ) );
    }
}