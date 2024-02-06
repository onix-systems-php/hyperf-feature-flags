# Hyperf-feature-flags component

Includes the following general usage classes:

- Annotations:
  - FeatureFlag;
- Aspects:
  - FeatureFlagAspect;
- Constants:
  - Actions;
- DTO:
  - ResetFeatureFlagDTO;
  - UpdateFeatureFlagDTO;
- Event:
  - Action;
- Model:
  - FeatureFlag;
- Repository:
  - FeatureFlagRepository;
- Service:
  - GetCurrentFeatureFlagService;
  - GetFeatureFlagService;
  - GetOverriddenFeatureFlagService;
  - ResetFeatureFlagService;
  - SetFeatureFlagService
- Other:
  - RedisWrapper

## Installation:

```shell script
composer require onix-systems-php/hyperf-feature-flags
```

## Publishing the config:

```shell script
php bin/hyperf.php vendor:publish onix-systems-php/hyperf-feature-flags
```

## Defining feature flags in config:

> Format: `'name' => 'rule'`

`config/autoload/feature-flags.php`

```php 
return [
    'slack-integration' => true,
    'my-custom-feature' => "[date:now] > '2024-02-06' && [date:now] <= '2024-12-31'",
    'my-awesome-feature' => '[config:slack.integration] === true || [feature:my-custom-feature] === false',
];
```

> **Attention!**
> If you want to use dates, in your rules you must take your dates in singular quotes. Otherwise, the rule will always evaluate as true.

## Basic Usage:

Examples:

1. Via: `OnixSystemsPHP\HyperfFeatureFlags\Annotations\FeatureFlag`.

```php
#[FeatureFlag('slack-integration', false)]
private function sendSlackNotification(string $link, Feedback $feedback): bool
{
    //...
}
```

As we can see from this code, that `sendSlackNotification` method will be executed if we `slack-integrations` is true in our config, in our `feature_flags` table or redis.
the second parameter in `FeatureFlag` aspect was set to false then it indicates that if `slack-integartion` is not exists anywhere by default aspect will return false and this method will also return false.

1. Via: `OnixSystemsPHP\HyperfFeatureFlags\Services\GetFeatureFlagService`

This example introduces how you can use it from your controller method which should return `Psr\Http\Message\ResponseInterface`. Here we don't use annotation because we need to return not a primitive but an object.
So if `$this->getFeatureFlagService->run('slack-integration')` will return false, we just return `$this->response->json([])`. Otherwise, we go next.
```php
class SlackController
{
    public function __construct(private \OnixSystemsPHP\HyperfFeatureFlags\Services\GetFeatureFlagService $getFeatureFlagService) {}
    public function webhook(): Psr\Http\Message\ResponseInterface
    {
        if (!$this->getFeatureFlagService->run('slack-integration')) {
            return $this->response->json([])
        }
    }
}
```

For example, we have in our config these rules:
```php
return [
    'my-awesome-feature' => true,
    'my-custom-feature' => "[date:now] > '2050-31-12' || [config:slack.integration] || [feature:my-awesome-feature]",
]
```
What will be the value of this rule?

1. The first part, will evaluate `[date:now] > '2050-31-12'`. Obviously, until `2050-31-12` it will be false.
2. The second part, will evaluate `[config:slack.integration]`. It will take `integration` value from the `config/autoload/slack.php` file if you have. Assume, we don't have `slack-integration` in our config file. So it will be `null`.
3. The third part, will evaluate `[feature:my-awesome-feature]`. Nothing fancy, it will take from our file `/cofnig/autoload/feature_flags.php`, `true` value.
4. Finally, evaluation of this rule `my-custom-feature` will be `false || null || true`. As the result of evaluation will be `true`.

## Classes:

##### OnixSystemsPHP\HyperfFeatureFlags\Services\SetFeatureFlagService

This class allows you to set your feature flag to `feature_flags` table.
The method `run()` accepts one argument: `UpdateFeatureFlagDTO` with name and rule of the feature flag.
*It checks if the given user can set this feature flag. So you should implement policy logic.*

##### OnixSystemsPHP\HyperfFeatureFlags\Services\ResetFeatureFlagService

This class allows you to reset your feature. In simply words it just deletes your flag from the database and redis.
The method `run()` accepts one argument: `ResetFeatureFlagDTO` with name and rule of the feature flag.
*It checks if the given user can reset this feature flag. So you should implement policy logic.*

##### OnixSystemsPHP\HyperfFeatureFlags\Services\GetCurrentFeatureFlagService

This class allows you to get all current feature flags.
The method `run()` returns an associative array with name and value (which evaluates based on rule) of the feature flag.

##### OnixSystemsPHP\HyperfFeatureFlags\Services\GetOverriddenFeatureFlagsService

This class allows you to get all overridden features. In simply words it just grab your flags from the database and redis.
The method `run()` returns an associative array with name and value of the feature flag.

##### OnixSystemsPHP\HyperfFeatureFlags\Services\GetFeatureFlagService

This class allows you to get value of your rule. 
Firstly, it goes to redis and checks if it presents in it, then if yes takes it. 
Secondly if no, goes to database if there is any flag with this name if yes, takes it. 
Finally, goes to the config and return evaluated rule from the config even if there is such rule in config, it simply returns `false`, in any of these cases it stores value to redis.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
