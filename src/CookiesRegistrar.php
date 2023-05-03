<?php

namespace Whitecube\LaravelCookieConsent;

use Closure;
use ReflectionFunction;
use Illuminate\Support\Str;

class CookiesRegistrar
{
    /**
     * The registered cookies, mapped under consent-categories.
     */
    protected array $categories = [];

    /**
     * Access the pre-defined "operational" consent-category.
     */
    public function operational(): CookiesCategory
    {
        return $this->getOrMakeCategory('operational');
    }

    /**
     * Access the pre-defined "analytics" consent-category.
     */
    public function analytics(): CookiesCategory
    {
        // TODO : make special AnalyticsCookiesCategory
        return $this->getOrMakeCategory('analytics');
    }

    /**
     * Access the pre-defined "optional" consent-category.
     */
    public function optional(): CookiesCategory
    {
        return $this->getOrMakeCategory('optional');
    }

    /**
     * Define a custom category.
     */
    public function category(string $key, ?Closure $maker = null): static
    {
        $this->registerCategory($key, $maker);

        return $this;
    }

    /**
     * Magically call a custom category or a cookie creation
     * method when inside a cookies group definition.
     */
    public function __call(string $method, array $arguments)
    {
        if($key = $this->getCategoryKeyFromMethod($method)) {
            return $this->categories[$key];
        }

        // TODO : check if we're calling cookie methods inside a group definition.

        throw new \BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }

    /**
     * Retrieve all defined cookies consent-categories.
     */
    public function getCategories(): array
    {
        return array_values($this->categories);
    }

    /**
     * Retrieve a single cookies consent-category.
     */
    protected function getOrMakeCategory(string $key, ?Closure $maker = null): CookiesCategory
    {
        return $this->categories[$key]
            ?? $this->registerCategory($key, $maker);
    }

    /**
     * Create & configure a new cookies consent-category.
     */
    protected function registerCategory(string $key, ?Closure $maker = null): CookiesCategory
    {
        if($maker && $this->closureExpectsCategoryParameter($maker)) {
            $instance = new CookiesCategory($key);
            $instance = $maker($instance) ?? $instance;
        } else if ($maker) {
            $instance = $maker($key);
        } else {
            $instance = new CookiesCategory($key);
        }

        if(! is_a($instance, CookiesCategory::class)) {
            throw new \UnexpectedValueException('Unknown cookies category instance.');
        }

        return $this->categories[$key] = $instance;
    }

    /**
     * Check if given function is expecting a category instance as first parameter.
     */
    protected function closureExpectsCategoryParameter(Closure $maker): bool
    {
        $reflection = new ReflectionFunction($maker);
        $parameter = $reflection->getParameters()[0]?->getType()?->getName();

        return $parameter === CookiesCategory::class;
    }

    /**
     * Find the correct registered category key for a given method name.
     */
    protected function getCategoryKeyFromMethod(string $method): ?string
    {
        if(array_key_exists($method, $this->categories)) {
            return $method;
        }

        foreach (array_keys($this->categories) as $key) {
            if(Str::camel($key) === $method) return $key;
        }

        return null;
    }
}
