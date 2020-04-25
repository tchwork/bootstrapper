Tchwork Bootstrapper
====================

What if we could decouple the bootstrapping logic of our apps from any global state?

This package makes it possible if you follow a few conventions.

Demo
----

Take a Symfony default skeleton and require `tchwork/bootstrapper`:
```sh
symfony new test-app --version=dev-master # Symfony 5.1 works best for the example
cd test-app/
composer require tchwork/bootstrapper:@dev
```

Replace the content of the `public/index.php` file by:
```php
<?php

use App\Kernel;

require dirname(__DIR__).'/vendor/tchwork/bootstrapper/bootstrap.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
```

And the content of the `bin/console` file by:
```php
#!/usr/bin/env php
<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;

require dirname(__DIR__).'/vendor/tchwork/bootstrapper/bootstrap.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

    return new Application($kernel);
};
```

Profit.

The closures are going to be called automatically.
The `$context` argument will be provided with the `$_SERVER` superglobal, augmented with the values found in `.env` files.
The return value will be handled automatically using generic handlers.

Try also this front controller, e.g. `public/hello.php`:
```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require dirname(__DIR__).'/vendor/tchwork/bootstrapper/bootstrap.php';

return function (Request $request) {
    return new Response('Hello World!');
};
```

The `$request` argument will be created automatically from superglobals and the returned response will be sent as expected.

The mechanism to create the arguments and handle the return value can be configured to deal with any kind of objects.

Runtime Conventions
-------------------

In order to boot from global state, a few conventions are needed:

 - `.env` files are always loaded if they are found in the root dir of your app (see the Symfony Dotenv component for more details);
 - PHP warnings and notices are turned into `ErrorException` (see the Symfony ErrorHandler component for more details);
 - the `APP_ENV` and the `APP_DEBUG` environement variables are used to configure the mode in which the app should run;
 - on the command line, `-e|--env` allows forcing a specific value for `APP_ENV` and `--no-debug` allows forcing `APP_DEBUG` to `0`.

Keeping this list as short as possible is desired.

Extensibility
-------------

This package ships argument resolvers and return value handlers that work with Symfony components.

If you want to make it know about any other namespaces, there are two conventions to follow:

 - to create an argument for a class/interface named `MyNamespace\InputObject`,
   create a derived class with the `Tchwork\Bootstrapper\` prefix and the `Singleton` suffix.
   Then, implement a static method `get()` on that class. It should return the object computed from global state:
   ```php
   namespace Tchwork\Bootstrapper\MyNamespace

   use MyNamespace\InputObject;

   class InputObjectSingleton
   {
       private static $inputObject;

       public static function get(): InputObject
       {
           return self::$inputObject ?? self::$inputObject = new InputObject();
       }
   }
   ```

 - to handle a return value of type `MyNamespace\OutputObject`, create a class with the `Handler` suffix and a `handle()` method:
   ```php
   namespace Tchwork\Bootstrapper\MyNamespace

   use MyNamespace\OutputObject;

   class OutputObjectHandler
   {
       public static function handle(OutputObject $outputObject): int
       {
           // do something with $outputObject

           return 0; // the method shall return the exit status code - 0 means successfull
       }
   }
   ```

That's it.

Please give it a try and tell me what you think about it!

Protip: adding `auto_prepend_file=/path/to/test-app/vendor/tchwork/bootstrapper/bootstrap.php` to your `php.ini` file allows removing the `require` statements in the examples.
