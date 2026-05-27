<?php

declare(strict_types=1);

namespace Tangible\Ast;

use Tangible\Ast\Exceptions\UnknownNodeType;

/**
 * Abstract base for every node in a JSON-serializable AST.
 *
 * Dispatch pattern: `AstNode::fromJson()` reads the `type` discriminator and
 * delegates to the concrete class registered for that type. Each concrete class
 * implements `fromData(\stdClass): static` to construct itself from parsed JSON.
 *
 * Subclasses must provide a `registry()` returning a map of `type => class-string`.
 * A consumer typically defines an intermediate abstract subclass that fixes the
 * registry (closed set) or applies a filter hook (open set), and concrete node
 * classes extend from there.
 */
abstract class AstNode {
    // -----------------------------------------------------------------
    // Subclass contract
    // -----------------------------------------------------------------

    abstract public function getType(): string;

    /** Produce the stdClass representation that round-trips through json_encode. */
    abstract protected function toStdClass(): \stdClass;

    /**
     * Construct an instance from a parsed JSON object.
     * Every concrete node class must override this.
     */
    protected static function fromData(\stdClass $data): static {
        throw new \LogicException(static::class.' must implement fromData()');
    }

    /**
     * Map of node `type` discriminator to concrete class. Resolved via late
     * static binding inside `fromJson()`, so subclasses can override to provide
     * a closed or filter-extensible registry. Exposed publicly so consumers
     * (e.g. validators, UI builders) can introspect the registered types.
     *
     * @return array<string, class-string<self>>
     */
    abstract public static function registry(): array;

    // -----------------------------------------------------------------
    // Public API
    // -----------------------------------------------------------------

    final public function toJson(): string {
        return json_encode($this->toStdClass(), \JSON_THROW_ON_ERROR);
    }

    /**
     * Factory: parse any AST node from JSON, dispatching on `type`.
     *
     * @throws UnknownNodeType when the `type` value has no registered class
     */
    final public static function fromJson(\stdClass|array|string $data): self {
        if (\is_string($data)) {
            $data = json_decode($data, false, 512, \JSON_THROW_ON_ERROR);
        }
        if (\is_array($data)) {
            $data = (object) $data;
        }

        $type = $data->type ?? null;
        $registry = static::registry();

        if ($type === null || !\array_key_exists($type, $registry)) {
            throw new UnknownNodeType($type);
        }

        /** @var class-string<self> $class */
        $class = $registry[$type];

        return $class::fromData($data);
    }

    /** Value equality via JSON representation. */
    final public function equals(self $other): bool {
        return $this->toJson() === $other->toJson();
    }
}
