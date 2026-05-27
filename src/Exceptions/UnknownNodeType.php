<?php

declare(strict_types=1);

namespace Tangible\Ast\Exceptions;

final class UnknownNodeType extends \InvalidArgumentException {
    public function __construct(?string $type) {
        parent::__construct(\sprintf('Unknown AST node type: %s', $type ?? 'null'));
    }
}
