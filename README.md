# INTRODUCTION #

The **SearchableFile** class is designed to allow you to handle text files which __do not fit into memory__, and will never fit into memory, whatever your *php.ini* file settings are. With this class, you can perform operations as would do any lexer or parser to operate on file contents and analyze them.

It has been designed to minimize as much as possible the overhead implied by performing file IO to get the real data instead of working directly into memory.

The initial motivation for this class was to be able to handle big RTF contents, while preserving performance. However, as you will see, it is completely independent of the underlying file format.

## Using the SearchableFile class ##

The **SearchableFile** class can be seen as some kind of wrapper around a text file opened in read-only mode. It won't allow you to perform in-place modifications, since it's aimed at reading text streams, analyzing contents and optionally performing some modifications on-the-fly, then finally writing them to some output stream.

Creating a **SearchableFile** object is pretty simple ; just specify a filename when instantiating it :

	$sf 	=  new SearchableFile ( 'verybigfile.txt' ) ;

You can also specify a block size for IO operations (the default is 16k) :

	$sf 	=  new SearchableFile ( 'verybigfile.txt', 64 * 1024 ) ;

Once created, you can use any of the search functions that the class has to offer ; the following example will look for the first character in the set [\\{}] :

	$index 	=  $sf -> strchr ( "\\{}" ) ;

Or you can just extract a substring from your file :

	$text 	=  $sf -> substr ( 10000, 20 ) ; 	// Extract 20 characters at position 10000

You can also use the object as an array, to access individual characters :

	$ch 	=  $sf [1000000] ;

or cycle through file contents using an iterator :

	foreach ( $sf  as  $ch )
	{
		// do something with the character $ch
	}  

However, please note that such constructs should be used with care, since PHP will be terribly slow at doing that.

You can also use the equivalent of the *preg_match()* and *preg_match_all()* builtin PHP functions ; the following example tries to find the first occurrence of the *"\pict"* or *"\bin"* strings :

	$status 	=  $sf -> pcre_match ( '/\\((bin)|(pict))/', $match, PREG_OFFSET_CAPTURE ) ;

while the following will try to find *all* the occurrences of those strings :

	$status 	=  $sf -> pcre_match_all ( '/(?P<pattern> \\((bin)|(pict)))/', $match, PREG_OFFSET_CAPTURE ) ;

Please have a look at the *Making the pcre functions work* section later in this paragraph, because there are some restrictions on using them.

## Making the examples work ##

All the examples provided with this package use a file named *"verybigfile.rtf"* and assume that this is an RTF file which contains embedded pictures. They should be used as command-line scripts.

I won't pollute this repository by providing a useless data file of almost 1Gb, but you can recreate it very easily :

1.  Find a Word document that contains embedded images
2.  Export it in RTF format
3.  Copy the file contents several times to generate the file *verybigfile.rtf*. 

Under Unix systems, you can do it that way :

	cat myfile.txt myfile.txt ... myfile.txt >verybigfile.rtf

The same, using Msdos commands on Windows systems :

	copy myfile.txt+myfile.txt...+myfile.txt verybigfile.rtf

Most of the examples test the **SearchableFile** functions and try to compare their timing with the same method using in-memory data (load the file contents using *file\_get\_contents()*, then use PHP builtin functions to achieve the same goal).

For this reason, you should ensure that :

- Your *verybigfile.rtf* file should not exceed 700-800Mb (especially on Windows systems, where it is tough to read a file greater than 1Gb)
- Your *php.ini* **memory\_limit** setting has been set to a sufficient value. For example :

		memory_limit 	=  1024M

## Making the pcre functions work ##

The PCRE functions provided by the **SearchableFile** class are : *pcre\_match()* and *pcre\_match\_all()* ; there is no magic in them, they simply rely on an external command, **pcregrep**, which is not included in standard Linux distributions.

To install it :
- On Debian distributions, run the following command :

	apt_get install pcregrep

- On CentOs distributions, it seems to be :

	yum install pcre	 	# not tested !

- On Windows systems, you will need the Cygwin package ([http://www.cygwin.org/](http://www.cygwin.org/ "http://www.cygwin.org/")). This is to my opinion the longest installation program in the world, but it's worth the waiting.

If none of the above conditions are met, then you will not be able to use the pcre functions.

 
# SearchableFile API #

The following sections describe the **SearchableFile** methods and properties.

## Methods ##

### Constructor ###

	$sf 	= new SearchableFile ( $filename, $block_size = 16384, $open = true ) ;

Creates a searchable file object.

Since the class uses direct IO to access chunks of data, an optional block size can be specified (the default is 16k). Note that, at least on Windows systems, ideal block sizes range between 16 and 64Kb. Below or above that, performances seem to degrade.

If the *$open* parameter is false, the file won't be opened. You will need to call the **Open()** method later for that.

### Destructor ###

The destructor of the **SearchableFile** class closes the file,if already opened.


### Close ###

	$sf -> Close ( ) ;

Closes the searchable file, if it was opened. 

No exception is thrown if it was already opened.

### Open ###

	$sf -> Open ( ) ;

Opens the file. Throws an exception if the file was already opened or could not be opened.

### multistrpos, multistripos ###

	$pos 	=  $sf -> multistrpos  ( $searched_strings, $offset = 0, &$found_index = null, &$found_string = null ) ;
	$pos 	=  $sf -> multistripos ( $searched_strings, $offset = 0, &$found_index = null, &$found_string = null ) ;

These function behave like the PHP standard *strpos()* and *stripos()* functions, but can be used to find the first occurrence of a string within a set of searched strings.

The parameters are the following :

- *$searched\_strings* (array of strings) : strings to be searched for.
- *$offset* (integer) : offset in the file where the search is to be started.
- *$found_index* (integer, optional) : variable that will receive the index, in the *$searched\_strings* array, of the matched string.
-  *$found\_string* (string, optional) : matched string (can be different from the original in the *$searched\_strings* array element, if the *multistripos()* function is used).

Returns either the byte offset of a found occurrence in the *$searched\_strings* array, or false if the string was not found in the file.

### pcre\_match ###

	$status 	=  pcre_match ( $pattern, &$matches = null, $flags = 0, $start_offset = 0 ) ;

*pcre_match()* tries to behave like the builtin *preg_match()* function, but operates on a file rather than in memory.

For achieving that, it uses the **pcregrep** linux command to extract match offset using the *--file-offsets* parameter.

The meaning of the parameters is the following :

- *$pattern* (string) : Pcre pattern to be matched.
- *$matches* (array) : Array that will receive the match values.
- *$flags* (integer) : Any PREG_* flags recognized by the PHP preg_match() builtin function.
- *$start\_offset* (integer) : Offset in the file where the search is to be started.

The function returns false if some error occurred (the starting offset is beyond the end of the file, or the search pattern is incorrect) ; otherwise the number of matches is returned (0 or 1).

**Notes :**

- *pcre\_match()* first uses the pcregrep command to find the strings matching the specified pattern ; then it uses the *SearchableFile::substr()* method to extract that data ; and finally it applies the *preg\_match()* function to generate an appropriate $matches array. For these reasons, anchor characters ("^" and "$") should be avoided : if you have to cope with huge files, you have to make some concessions.

- The **pcregrep** command results are cached ; so a next call to the *pcre\_match()* function with the same  pattern will extract the results from the cache, instead of running the **pcregrep** command again.
		

### pcre\_match\_all ###

	$status 	=  pcre_match_all ( $pattern, &$matches = null, $flags = 0, $start_offset = 0 ) ;

*pcre\_match\_all()* tries to behave like *preg\_match\_all()*, but operates on a file rather than in memory.	For achieving that, it uses the **pcregrep** linux command to extract match offset using the *--file-offsets* parameter.

It returns false if some error occurred (the starting offset is beyond the end of the file, or the search pattern is incorrect, or an individual *preg\_match()* on one of the sub-results failed for some reason) ; otherwise the number of matches is returned.


### strchr ###

	$pos 	=  $sf -> strchr ( $cset, $offset = 0 ) ;

Finds the offset of the first character belonging to *$cset*.

Returns either the byte offset of the first character found belonging to *$cset*, or false if no more characters from *$cset* are present in the file.

The *$offset* parameter indicates where the search is to be started.

Unlike the useless PHP *strchr()* function, which returns a substring starting with the searched character or string, but much more like the C *strchr()* function, which returns a pointer to the found character, *strchr()* returns the offset in the file of the searched character(s).
 
### strpos, stripos ###

    $pos 	=  $sf -> strpos  ( $searched_string, $offset = 0 ) ;
	$pos 	=  $sf -> stripos ( $searched_string, $offset = 0 ) ;

Behave like the PHP standard strpos() and stripos() functions. The parameters are the following :

- *$searched_string* (string) : string to be searched.
- *$offset* (integer) : byte offset where the search should start.

Returns either the byte offset of a found occurrence of *$searched_string*, or false if the string was not found in the file.

### substr ###

	$text 	=  $sf -> substr ( $start, $offset ) ;

Extracts a substring from the searchable file. The *$start* and *$length* parameters have the same meaning that for the php builtin *substr()* function.

Returns the specified substring or false if one of the following conditions occur :

- The file is less than *$start* bytes
- *$length* is negative, and goes past $start backwards

An empty string is returned if *$length* has been specified and is zero (ie, 0, false or null).


