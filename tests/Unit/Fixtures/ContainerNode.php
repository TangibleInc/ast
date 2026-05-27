<?php

declare(strict_types=1);

namespace Tangible\Ast\Tests\Unit\Fixtures;

use Tangible\Ast\AstNode;

final class ContainerNode extends FixtureAstNode {
    /** @param AstNode[] $children */
    public function __construct(public readonly array $children = []) {
    }

    public function getType(): string {
        return 'container';
    }

    protected static function fromData(\stdClass $data): static {
        return new self(children: static::parseChildren((array) ($data->children ?? [])));
    }

    protected function toStdClass(): \stdClass {
        $std = new \stdClass();
        $std->type = $this->getType();
        $std->children = $this->serializeChildren($this->children);

        return $std;
    }
}
