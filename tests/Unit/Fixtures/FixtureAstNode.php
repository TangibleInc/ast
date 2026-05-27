<?php

declare(strict_types=1);

namespace Tangible\Ast\Tests\Unit\Fixtures;

use Tangible\Ast\AstNode;

/**
 * Intermediate base that fixes a closed registry for the primary fixture
 * hierarchy. Mirrors the role `QuizAstNode` will play in the Quiz refactor.
 */
abstract class FixtureAstNode extends AstNode {
    public static function registry(): array {
        return [
            'leaf' => LeafNode::class,
            'container' => ContainerNode::class,
        ];
    }

    /** @param AstNode[] $children */
    protected function serializeChildren(array $children): array {
        return array_map(static fn (AstNode $n) => $n->toStdClass(), $children);
    }

    /** @return AstNode[] */
    protected static function parseChildren(array $raw): array {
        return array_map(static fn ($c) => static::fromJson($c), $raw);
    }
}
