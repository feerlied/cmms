<?php

declare(strict_types=1);

namespace Diagnostic;

enum DiagnosticOrigin: string {
    case LEXICAL = 'lexical';
    case SYNTACTIC = 'syntactic';
    case SEMANTIC = 'semantic';
}
