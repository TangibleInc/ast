<?php

declare(strict_types=1);

namespace Tangible\Ast\Validation;

final readonly class ValidationError {
    public function __construct(
        /** JSON-path-style location within the AST, e.g. "body[0].children[2]" */
        public readonly string $path,
        public readonly ValidationErrorKind $kind,
        public readonly string $message,
    ) {
    }
}
