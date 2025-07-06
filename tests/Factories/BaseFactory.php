<?php

namespace XMultibyte\ApiDoc\Tests\Factories;

abstract class BaseFactory
{
    protected array $attributes = [];
    protected array $states = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = array_merge($this->getDefaults(), $attributes);
    }

    public static function new(array $attributes = []): static
    {
        return new static($attributes);
    }

    public function with(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    public function state(string $state): static
    {
        if (isset($this->states[$state])) {
            $this->attributes = array_merge($this->attributes, $this->states[$state]);
        }
        return $this;
    }

    public function count(int $count): array
    {
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            $items[] = $this->make();
        }
        return $items;
    }

    abstract protected function getDefaults(): array;
    abstract public function make(): array;

    protected function faker(): \Faker\Generator
    {
        return \Faker\Factory::create();
    }

    protected function randomChoice(array $choices)
    {
        return $choices[array_rand($choices)];
    }

    protected function randomBoolean(): bool
    {
        return (bool) random_int(0, 1);
    }
}
