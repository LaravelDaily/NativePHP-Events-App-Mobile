/*
   +----------------------------------------------------------------------+
   | Copyright (c) The PHP Group                                          |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.01 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available through the world-wide-web at the following url:           |
   | https://www.php.net/license/3_01.txt                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Author: Stig Sæther Bakken <ssb@php.net>                             |
   +----------------------------------------------------------------------+
*/

#define CONFIGURE_COMMAND " './configure'  '--host=arm-apple-darwin' '--prefix=' '--with-valgrind=no' '--enable-shared=no' '--enable-static=yes' '--disable-all' '--disable-cgi' '--disable-phpdbg' '--without-pcre-jit' '--disable-cli' '--disable-fpm' '--disable-micro' '--enable-embed=static' '--with-config-file-path=/usr/local/etc/php' '--with-config-file-scan-dir=/usr/local/etc/php/conf.d' '--enable-bcmath' '--enable-ctype' '--with-curl' '--enable-dom' '--enable-fileinfo' '--enable-filter' '--with-zlib' '--enable-gd' '--enable-mbstring' '--disable-mbregex' '--enable-nativephp' '--with-openssl=/Users/simonhamp/Projects/static-php-cli/buildroot' '--with-openssl-dir=/Users/simonhamp/Projects/static-php-cli/buildroot' '--enable-pdo' '--with-sqlite3=/Users/simonhamp/Projects/static-php-cli/buildroot' '--with-pdo-sqlite' '--enable-phar' '--enable-session' '--enable-simplexml' '--enable-sockets' '--enable-tokenizer' '--enable-xml' '--with-libxml=/Users/simonhamp/Projects/static-php-cli/buildroot' '--with-zip=/Users/simonhamp/Projects/static-php-cli/buildroot' 'host_alias=arm-apple-darwin' 'PKG_CONFIG=/Users/simonhamp/Projects/static-php-cli/buildroot/bin/pkg-config' 'PKG_CONFIG_PATH=/Users/simonhamp/Projects/static-php-cli/buildroot/lib/pkgconfig' 'CPP=/Applications/Xcode.app/Contents/Developer/usr/bin/gcc -E' 'LIBXML_CFLAGS=-I/Users/simonhamp/Projects/static-php-cli/buildroot/include/libxml2 -lxml2' 'LIBXML_LIBS=-L/Users/simonhamp/Projects/static-php-cli/buildroot/lib' 'OPENSSL_CFLAGS=-I/Users/simonhamp/Projects/static-php-cli/buildroot/include/openssl' 'OPENSSL_LIBS=-L/Users/simonhamp/Projects/static-php-cli/buildroot/lib -lssl -lcrypto' 'SQLITE_CFLAGS=-I/Users/simonhamp/Projects/static-php-cli/buildroot/include' 'SQLITE_LIBS=-L/Users/simonhamp/Projects/static-php-cli/buildroot/lib' 'ZLIB_CFLAGS=-I/Users/simonhamp/Projects/static-php-cli/buildroot/include' 'ZLIB_LIBS=-L/Users/simonhamp/Projects/static-php-cli/buildroot/lib' 'CURL_CFLAGS=-I/Users/simonhamp/Projects/static-php-cli/buildroot/include/curl -DCURL_STATICLIB' 'CURL_LIBS=-L/Users/simonhamp/Projects/static-php-cli/buildroot/lib -lcurl -lz' 'PNG_CFLAGS=-I/Users/simonhamp/Projects/static-php-cli/buildroot/include/libpng16' 'PNG_LIBS=-L/Users/simonhamp/Projects/static-php-cli/buildroot/lib' 'LIBZIP_CFLAGS=-I/Users/simonhamp/Projects/static-php-cli/buildroot/include' 'LIBZIP_LIBS=-L/Users/simonhamp/Projects/static-php-cli/buildroot/lib -lzip'"
#define PHP_ODBC_CFLAGS	""
#define PHP_ODBC_LFLAGS		""
#define PHP_ODBC_LIBS		""
#define PHP_ODBC_TYPE		""
#define PHP_PROG_SENDMAIL	"/usr/sbin/sendmail"
#define PEAR_INSTALLDIR         ""
#define PHP_INCLUDE_PATH	".:"
#define PHP_EXTENSION_DIR       "/lib/php/extensions/no-debug-non-zts-20240924"
#define PHP_PREFIX              ""
#define PHP_BINDIR              "/bin"
#define PHP_SBINDIR             "/sbin"
#define PHP_MANDIR              "/php/man"
#define PHP_LIBDIR              "/lib/php"
#define PHP_DATADIR             "/share/php"
#define PHP_SYSCONFDIR          "/etc"
#define PHP_LOCALSTATEDIR       "/var"
#define PHP_CONFIG_FILE_PATH    "/usr/local/etc/php"
#define PHP_CONFIG_FILE_SCAN_DIR    "/usr/local/etc/php/conf.d"
#define PHP_SHLIB_SUFFIX        "so"
#define PHP_SHLIB_EXT_PREFIX    ""
