<?php

declare(strict_types=1);

namespace Tangible\Ast\Tests\Unit\Fixtures;

final class LeafNode extends FixtureAstNode {
    public function __construct(public readonly string $value) {
    }

    public function getType(): string {
        return 'leaf';
    }

    protected static function fromData(\stdClass $data): static {
        return new self(value: (string) ($data->value ?? ''));
    }

    protected function toStdClass(): \stdClass {
        $std = new \stdClass();
        $std->type = $this->getType();
        $std->value = $this->value;

        return $std;
    }
}
