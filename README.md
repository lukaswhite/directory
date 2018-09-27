# Directory

A PHP class that represents a directory on a filesystem.

With it, you can:

- Get the files in it, including `glob`-like patterns
- Find the most recent file
- Derive a unique filename based on a pattern
- Create or copy files in it
- Obtain the total size
- Delete it recursively

## Creating a Instance

Simply path the full path to the directory to the constructor:

```php
$directory = new Directory( '/path/to/dir' );
```

## Checking it Exists

The directory needn't exist when you create an instance; use the `exists()` method to check that it does.

```php
if ( $directory->exists ) {
	// do something
}
```	

## Checking it's a Directory

It might also a good idea to check that the path you provided is actually a directory, not a file.

```php
if ( ! $directory->isDirectory( ) ) {
	// looks like it's a file!
}
```	

## Creating it

To create a directory, pass the path to the constructor and then call `create()`.

```php
$directory = new Directory( '/path/to/new/directory' );
$directory->create( );
```

By default the mode is set to `0777`, but you can override this by passing it as an argument:

```php
$directory->create( 0755 );
```

Alternatively, you can use `createIfDoesNotExist()` which, as the name suggests, will create it if it does not already exist.

> This action is recusrive; i.e. it will create any necessary parent directories.

## Listing Files

Call `getFiles()` to get a list of the files in the directory.

```php
$files = $directory->getFiles( );
```

This will return an array with the full path to the files in the directory, excluding directories.

To include the directories:

```php
$files = $directory->getFiles( true );
```

To get all of the files in a directory including any subdirectories, pass `true` as the second argument:

```php
$files = $directory->getFiles( false, true );
```

## Glob

To glob the directory:

```php
$textFiles = $directory->glob( '*.txt' );
```

## Checking if a File Exists

To check whether a directory contains a file with a particular name:

```php
if ( $directory->fileExists( 'logo.png' ) ) {
	// do something
}
```

## Getting the Most Recent file

Use `mostRecentFile()` to get the most recently modified file.

```php
$recent = $directory->mostRecentFile( );
```

You can use a pattern; for example to get the most recently modified text file:

```php
$recent = $directory->mostRecentFile( '*.txt' );
```

To include directories, pass `true` as the second argument.

## Getting the Total Size

To get the total size, in bytes, of a directory:

```php
$size = $directory->totalSize( );
```

## Unique Filenames

Suppose you allow users to upload an avatar, which you store in a directory named `avatars` with the filename in the form `username.png`.

That works fine initially, but causes problems if a user uploads a replacement.

To get around that, `ensureUniqueFilename()` will return a similar filename that doesn't exist.

For example, if `joebloggs.png` exists, it'll return `joebloggs-1.png`. A subsequent call will return `joebloggs-2.png`, and so on.

The method returns the filename only, but you can get the full path with `fullPathToFile()`.

For example:

```php
$directory = new Directory( '/path/to/avatars' );
$filename = $directory-> ensureUniqueFilename( 'joebloggs.png' );
// joebloggs-1.png
$filepath = $directory->fullPathToFile( $filename );
// /path/to/avatars/joebloggs-1.png
```

## Creating a File

To create an empty file in a directory:

```php
$directory->createFile( 'filename.txt' );
```

To create a file, providing its contents:

```php
$directory->createFile( 'filename.txt', 'the contents' );
```

## Copying a File Into a Directory

To copy a file into the directory:

```php
$directory->copyFileInto( '/path/to/your/file' );
```

## Deleting a Directory

> Use with caution!

To delete a directory, its contents and it's sub-directories, simply call `delete()`.