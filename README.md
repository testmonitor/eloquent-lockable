# Eloquent Lockable

[![Latest Stable Version](https://poser.pugx.org/testmonitor/eloquent-lockable/v/stable)](https://packagist.org/packages/testmonitor/eloquent-lockable)
[![CircleCI](https://img.shields.io/circleci/project/github/testmonitor/eloquent-lockable.svg)](https://circleci.com/gh/testmonitor/eloquent-lockable)
[![codecov](https://codecov.io/gh/testmonitor/eloquent-lockable/graph/badge.svg?token=KOVD6QX7PD)](https://codecov.io/gh/testmonitor/eloquent-lockable)
[![StyleCI](https://styleci.io/repos/968120528/shield)](https://styleci.io/repos/968120528)
[![License](https://poser.pugx.org/testmonitor/eloquent-lockable/license)](https://packagist.org/packages/eloquent-lockable)

Make Laravel Eloquent models read-only after they're locked. Prevents updates and deletes based on a "locked" attribute. Includes a trait and required interface to clearly define and enforce lockable models.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Tests](#tests)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Installation

This package can be installed through Composer:

```sh
$ composer require testmonitor/eloquent-lockable
```

No need to publish anything — just use the trait and you’re good to go.

## Usage

First, make sure to add a `locked` column to your model's table:

```php
Schema::table('invoices', function (Blueprint $table) {
    $table->boolean('locked')->default(false);
});
```

Next, implement the Lockable trait and interface:

```php
use Illuminate\Database\Eloquent\Model;
use TestMonitor\Lockable\Traits\Lockable;
use TestMonitor\Lockable\Contracts\IsLockable;

class Invoice extends Model implements IsLockable
{
    use Lockable;
}
```

That's it!

### Locking & Unlocking

Now you can start locking and unlocking models:

```php
$invoice->markLocked();
$invoice->markUnlocked();

$invoice->isLocked();    // true or false
```

### Exceptions

Trying to update or delete a locked model will throw a ModelLockedException.

```php
try {
    $invoice->update(['amount' => 999]);
} catch (ModelLockedException $exception) {
    $model = $exception->getModel();
}
```

### Temporary Locking

Temporarily lock or unlock a model using a callback:

```php
$invoice->whileLocked(function ($model) {
    // Model is locked inside this closure
});

$invoice->whileUnlocked(function ($model) {
    // Temporarily unlocked
});
```

These automatically restore the original lock state — even if an exception is thrown.

### Configurable Lock Column

Want to use a different column like archived or readonly?

Override the getLockColumn() method in your model:

```php
public function getLockColumn(): string
{
    return 'archived';
}
```

## Tests

The package contains integration tests. You can run them using PHPUnit.

```
$ vendor/bin/phpunit
```

## Changelog

Refer to [CHANGELOG](CHANGELOG.md) for more information.

## Contributing

Refer to [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

## Credits

- [Thijs Kok](https://www.testmonitor.com/)
- [Stephan Grootveld](https://www.testmonitor.com/)
- [Frank Keulen](https://www.testmonitor.com/)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Refer to the [License](LICENSE.md) for more information.
