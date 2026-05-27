<?php

declare(strict_types=1);

namespace Tangible\Ast\Tests\Unit\Fixtures;

final class PebbleNode extends OtherFixtureAstNode {
    public function __construct(public readonly int $weight) {
    }

    public function getType(): string {
        return 'pebble';
    }

    protected static function fromData(\stdClass $data): static {
        return new self(weight: (int) ($data->weight ?? 0));
    }

    protected function toStdClass(): \stdClass {
        $std = new \stdClass();
        $std->type = $this->getType();
        $std->weight = $this->weight;

        return $std;
    }
}
