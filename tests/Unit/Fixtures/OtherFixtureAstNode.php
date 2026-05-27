<?php

declare(strict_types=1);

namespace Tangible\Ast\Tests\Unit\Fixtures;

use Tangible\Ast\AstNode;

/**
 * Second, independent fixture hierarchy with its own registry. Used to prove
 * that two consumers (e.g. Quiz and LMS rules) do not bleed registries into
 * each other.
 */
abstract class OtherFixtureAstNode extends AstNode {
    public static function registry(): array {
        return [
            'pebble' => PebbleNode::class,
        ];
    }
}
