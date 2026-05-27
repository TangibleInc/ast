<?php

declare(strict_types=1);

namespace Tangible\Ast\Validation;

enum ValidationErrorKind: string {
    case UnknownNodeType = 'unknown_node_type';
    case MissingRequiredAttr = 'missing_required_attr';
    case InvalidAttrValue = 'invalid_attr_value';
    case InvalidChildType = 'invalid_child_type';
    case DuplicateIdentifier = 'duplicate_identifier';
    case InvalidIdentifier = 'invalid_identifier';
}
