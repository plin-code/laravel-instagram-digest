<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Support;

use PlinCode\InstagramDigest\Contracts\DigestAction;

class ActionRegistry
{
    /** @var array<string, DigestAction> */
    private array $actions = [];

    public function register(DigestAction $action): self
    {
        $this->actions[$action->key()] = $action;

        return $this;
    }

    /**
     * @param  array<DigestAction>  $actions
     */
    public function replaceAll(array $actions): self
    {
        $this->actions = [];
        foreach ($actions as $action) {
            $this->register($action);
        }

        return $this;
    }

    public function find(string $key): ?DigestAction
    {
        return $this->actions[$key] ?? null;
    }

    /** @return array<string> */
    public function keys(): array
    {
        return array_keys($this->actions);
    }

    /** @return array<DigestAction> */
    public function all(): array
    {
        return array_values($this->actions);
    }
}
