<?php

declare(strict_types=1);

namespace Tangible\Ast\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tangible\Ast\Exceptions\UnknownNodeType;
use Tangible\Ast\Tests\Unit\Fixtures\ContainerNode;
use Tangible\Ast\Tests\Unit\Fixtures\FixtureAstNode;
use Tangible\Ast\Tests\Unit\Fixtures\LeafNode;
use Tangible\Ast\Tests\Unit\Fixtures\OtherFixtureAstNode;
use Tangible\Ast\Tests\Unit\Fixtures\PebbleNode;

final class AstNodeTest extends TestCase {
    // -----------------------------------------------------------------
    // fromJson — happy paths
    // -----------------------------------------------------------------

    public function test_fromJson_constructs_concrete_class_from_type_discriminator(): void {
        $node = FixtureAstNode::fromJson('{"type":"leaf","value":"hello"}');

        self::assertInstanceOf(LeafNode::class, $node);
        self::assertSame('hello', $node->value);
    }

    public function test_fromJson_accepts_array_input(): void {
        $node = FixtureAstNode::fromJson(['type' => 'leaf', 'value' => 'x']);

        self::assertInstanceOf(LeafNode::class, $node);
        self::assertSame('x', $node->value);
    }

    public function test_fromJson_accepts_stdClass_input(): void {
        $data = (object) ['type' => 'leaf', 'value' => 'y'];
        $node = FixtureAstNode::fromJson($data);

        self::assertInstanceOf(LeafNode::class, $node);
        self::assertSame('y', $node->value);
    }

    // -----------------------------------------------------------------
    // Round-trip (including nested children)
    // -----------------------------------------------------------------

    public function test_simple_leaf_roundtrips_through_json(): void {
        $original = new LeafNode(value: 'roundtrip');
        $parsed = FixtureAstNode::fromJson($original->toJson());

        self::assertTrue($original->equals($parsed));
    }

    public function test_nested_container_roundtrips_through_json(): void {
        $original = new ContainerNode(children: [
            new LeafNode(value: 'a'),
            new ContainerNode(children: [
                new LeafNode(value: 'b'),
                new LeafNode(value: 'c'),
            ]),
        ]);
        $parsed = FixtureAstNode::fromJson($original->toJson());

        self::assertTrue($original->equals($parsed));
        self::assertInstanceOf(ContainerNode::class, $parsed);
    }

    public function test_json_encode_matches_toJson(): void {
        $node = new ContainerNode(children: [new LeafNode(value: 'a')]);

        self::assertSame($node->toJson(), json_encode($node));
    }

    public function test_json_encode_of_mixed_node_and_raw_array_structure(): void {
        // A json column may hold nodes (post-update) or raw arrays (hydrated);
        // both must encode to the same wire shape.
        $mixed = [new LeafNode(value: 'a'), ['type' => 'leaf', 'value' => 'b']];

        self::assertSame(
            '[{"type":"leaf","value":"a"},{"type":"leaf","value":"b"}]',
            json_encode($mixed),
        );
    }

    public function test_children_dispatch_uses_subclass_registry_via_LSB(): void {
        // ContainerNode::fromData calls `static::parseChildren`, which calls
        // `static::fromJson` per child. LSB must resolve to FixtureAstNode's
        // registry, not AstNode's (which has none).
        $original = new ContainerNode(children: [new LeafNode(value: 'lsb')]);
        $parsed = FixtureAstNode::fromJson($original->toJson());

        self::assertInstanceOf(ContainerNode::class, $parsed);
        self::assertCount(1, $parsed->children);
        self::assertInstanceOf(LeafNode::class, $parsed->children[0]);
    }

    // -----------------------------------------------------------------
    // Equality
    // -----------------------------------------------------------------

    public function test_equals_returns_true_for_identical_nodes(): void {
        $a = new LeafNode(value: 'same');
        $b = new LeafNode(value: 'same');

        self::assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_values(): void {
        $a = new LeafNode(value: 'one');
        $b = new LeafNode(value: 'two');

        self::assertFalse($a->equals($b));
    }

    public function test_equals_returns_false_for_different_types(): void {
        $a = new LeafNode(value: 'x');
        $b = new ContainerNode(children: []);

        self::assertFalse($a->equals($b));
    }

    // -----------------------------------------------------------------
    // UnknownNodeType
    // -----------------------------------------------------------------

    public function test_fromJson_throws_when_type_is_missing(): void {
        $this->expectException(UnknownNodeType::class);
        FixtureAstNode::fromJson('{"foo":"bar"}');
    }

    public function test_fromJson_throws_when_type_is_unknown(): void {
        $this->expectException(UnknownNodeType::class);
        FixtureAstNode::fromJson('{"type":"nonexistent"}');
    }

    // -----------------------------------------------------------------
    // Consumer isolation — two registries do not bleed
    // -----------------------------------------------------------------

    public function test_first_consumer_cannot_construct_second_consumers_node(): void {
        // 'pebble' is registered on OtherFixtureAstNode, not FixtureAstNode.
        $this->expectException(UnknownNodeType::class);
        FixtureAstNode::fromJson('{"type":"pebble","weight":5}');
    }

    public function test_second_consumer_cannot_construct_first_consumers_node(): void {
        $this->expectException(UnknownNodeType::class);
        OtherFixtureAstNode::fromJson('{"type":"leaf","value":"x"}');
    }

    public function test_second_consumer_constructs_from_its_own_registry(): void {
        $node = OtherFixtureAstNode::fromJson('{"type":"pebble","weight":7}');

        self::assertInstanceOf(PebbleNode::class, $node);
        self::assertSame(7, $node->weight);
    }
}
