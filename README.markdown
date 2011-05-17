# PHP Routing

This code is a PHP implementation of Rails-style URL routing. It is
intended to be a drop-in bit of code that you can use with any website.

The project is an adaptation of the code found here:
http://blog.sosedoff.com/2009/09/20/rails-like-php-url-router/

## Usage

To use this code you only need the two files under the lib/model/
directory: class.Router.php and class.Route.php. If you take these
two files, put them in your own project's code and make sure they're
included on the page you want to use them on, you're ready to go.

The way that I recommend using this code is to take a look at the
.htaccess file in the same directory as this README. It will route
all requests that aren't to a currently existing file/directory
and send them to router.php and in router.php there are some usage
examples.

## External Libraries

The project uses the SimpleTest library for PHP Unit Testing. This is
included with the source but you will not need it for normal use. You
will only need it if you wish to fork this repository and contribute
code.
