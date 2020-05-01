Tchwork Bootstrapper
====================

What if we could decouple the bootstrapping logic of our apps from any global state?

This package makes it possible with a few conventions.

BootstrapperInterface
---------------------

The core of this package is the `BootstrapperInterface`, which describes a high-order bootstrapping logic.

It is designed to be totally generic and able to run any application outside of the global state in 6 steps:

 1. your front-controller returns a `Closure` that wraps your app;
 2. `BootstrapperInterface::getRuntime()` is given this closure and returns a closure too (potentially the same but
    it could also be decorated) and its arguments (typically PHP superglobals turned into your domain objects);
 3. the returned closure, let's call it the "runtime", is called with the arguments computed at the previous step;
 4. the result of the runtime closure, the runtime closure and its arguments are all passed to `BootstrapperInterface::getHandler()`,
    which should return another closure, the "handler", that will handle the result itself;
 5. the handler closure is now called with the result of the runtime closure as argument;
 6. the PHP engine is terminated with the integer status code returned by the handler closure.

This process is extremely flexible as it allows implementations of `BootstrapperInterface` to hook into any critical steps.

Bootstrapping files
-------------------

The simplest way to use this package is to require the provided `bootstrap.php` file or an equivalent *instead of* the typical `vendor/autoload.php` file.

This will use an instance of `Bootstrapper` (see below) by default, but you can provide another implementation by using the `$_SERVER['APP_BOOTSTRAPPER']` variable.
When provided, `$_SERVER['APP_BOOTSTRAPPER']` should be set to a class name or an instance of `BootstrapperInterface` that will be used to run the app.

By design, requiring the `bootstrap.php` file *after* the `vendor/autoload.php` one will *not* do anything.
This allows requiring your front-controller several times without any side-effect.

If you are in the context of a Symfony app, you can include the `symfony-bootstrap.php` file instead,
which sets `$_SERVER['APP_BOOTSTRAPPER']` to `SymfonyBootstrapper`, adding common Symfony bootstrapping logic to the process:

 - `.env` files are always loaded if they are found in the root dir of your app;
 - PHP warnings and notices are turned into `ErrorException`;
 - the `APP_ENV` and the `APP_DEBUG` environement variables are used to configure the mode in which the app should run;
 - on the command line, `-e|--env` allows forcing a specific value for `APP_ENV` and `--no-debug` allows forcing `APP_DEBUG` to `0`.

Example
-------

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

require_once dirname(__DIR__).'/vendor/tchwork/bootstrapper/symfony-bootstrap.php';

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

require_once dirname(__DIR__).'/vendor/tchwork/bootstrapper/symfony-bootstrap.php';

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

require_once dirname(__DIR__).'/vendor/tchwork/bootstrapper/symfony-bootstrap.php';

return function (Request $request) {
    return new Response('Hello World!');
};
```

The `$request` argument will be created automatically from superglobals and the returned response will be sent as expected.

The mechanism to create the arguments and handle the return value can be configured to deal with any kind of objects.

Extensibility
-------------

This section describes the extensibility mechanism supported by `Bootstrapper`.
You can provide any other mechanism by implementing `BootstrapperInterface`.

`Bootstrapper` builds on two simple conventions to provide argument resolvers and return value handlers:

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

This package already provides some for the Symfony component.
Check their source code for inspiration.

Please give it a try and tell me what you think about it!

Protip: adding `auto_prepend_file=/path/to/your-bootstrap.php` to your `php.ini` file allows removing the `require` statements in the examples.
